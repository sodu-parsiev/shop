import Alpine from 'alpinejs';
import './animations';

const ATTRIBUTION_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

window.storefrontAnalytics = window.storefrontAnalytics || {
    track(event, payload = {}) {
        const data = { event, ...payload };

        window.dataLayer?.push(data);

        if (typeof window.gtag === 'function') {
            window.gtag('event', event, payload);
        }

        if (typeof window.ym === 'function' && window.YM_COUNTER_ID) {
            window.ym(window.YM_COUNTER_ID, 'reachGoal', event, payload);
        }

        window.dispatchEvent(new CustomEvent('storefront:analytics', { detail: data }));
    },
};

function storedAttribution() {
    try {
        return JSON.parse(window.localStorage.getItem('storefront_attribution') || '{}');
    } catch {
        return {};
    }
}

function currentAttribution() {
    const params = new URLSearchParams(window.location.search);
    const stored = storedAttribution();
    const attribution = {
        ...stored,
        source_url: window.location.href,
        referrer_url: stored.referrer_url || document.referrer || '',
        landing_url: stored.landing_url || window.location.href,
    };

    ATTRIBUTION_KEYS.forEach((key) => {
        if (params.has(key)) {
            attribution[key] = params.get(key) || '';
        } else {
            attribution[key] = attribution[key] || '';
        }
    });

    window.localStorage.setItem('storefront_attribution', JSON.stringify(attribution));

    return attribution;
}

function parseJsonArray(value) {
    try {
        return JSON.parse(value || '[]').map(String);
    } catch {
        return [];
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.store('volume', { selected: '5000_10000' });
    Alpine.store('attribution', currentAttribution());

    Alpine.store('orderBuilder', {
        drawerOpen: false,
        lines: [],
        quantity: 5000,
        addProduct(product) {
            const moq = Number(product.moq) || 5000;
            const quantity = Math.max(Number(this.quantity) || moq, moq);
            const productId = Number(product.id);
            const existing = this.lines.find((line) => line.product_id === productId);
            const line = {
                product_id: productId,
                name: product.name,
                category: product.category,
                availability: product.availability,
                image: product.image,
                moq,
                quantity,
                colors: product.colors ?? [],
                sizes: product.sizes ?? [],
                densities: product.densities ?? [],
                availableColors: product.availableColors ?? [],
                availableSizes: product.availableSizes ?? [],
                availableDensities: product.availableDensities ?? [],
                colorSwatches: product.colorSwatches ?? {},
            };

            if (existing) {
                Object.assign(existing, line);
            } else {
                this.lines.push(line);
            }

            window.storefrontAnalytics.track('add_to_request', {
                product_id: productId,
                product_name: product.name,
                quantity,
            });

            this.drawerOpen = true;
        },
        close() {
            this.drawerOpen = false;
        },
        open() {
            this.drawerOpen = true;
        },
        remove(productId) {
            const line = this.lines.find((item) => item.product_id === Number(productId));
            this.lines = this.lines.filter((line) => line.product_id !== Number(productId));

            window.storefrontAnalytics.track('remove_from_request', {
                product_id: Number(productId),
                product_name: line?.name || '',
            });
        },
        setPreset(value, volumeKey) {
            this.quantity = Number(value);
            Alpine.store('volume').selected = volumeKey;
        },
        setQuantity(value) {
            this.quantity = Math.max(5000, Number(value) || 5000);
            Alpine.store('volume').selected = this.volumeKeyFor(this.quantity);
        },
        updateLineQuantity(productId, value) {
            const line = this.lines.find((item) => item.product_id === Number(productId));

            if (!line) {
                return;
            }

            line.quantity = Math.max(Number(value) || line.moq, line.moq);
        },
        volumeKeyFor(quantity) {
            if (quantity >= 25000) {
                return '25000_plus';
            }

            if (quantity >= 10000) {
                return '10000_25000';
            }

            return '5000_10000';
        },
    });

    Alpine.data('mobileNav', () => ({
        open: false,
    }));

    Alpine.data('faqAccordion', () => ({
        openIndex: null,
        toggle(i) {
            this.openIndex = this.openIndex === i ? null : i;
        },
    }));

    Alpine.data('requestForm', (oldLines = []) => ({
        started: false,
        submitting: false,
        init() {
            if (oldLines.length > 0 && Alpine.store('orderBuilder').lines.length === 0) {
                Alpine.store('orderBuilder').lines = oldLines;
            }
        },
        start() {
            if (this.started) {
                return;
            }

            this.started = true;
            window.storefrontAnalytics.track('form_start');
        },
        submit(event) {
            if (this.submitting) {
                event.preventDefault();

                return;
            }

            this.submitting = true;
            window.storefrontAnalytics.track('form_submit', {
                line_count: Alpine.store('orderBuilder').lines.length,
            });
        },
    }));

    Alpine.data('catalogFilter', (config = {}) => ({
        labels: config.labels || { category: {}, color: {}, density: {}, size: {} },
        total: Number(config.total) || 0,
        visibleCount: Number(config.total) || 0,
        category: 'all',
        availability: 'all',
        color: 'all',
        density: 'all',
        size: 'all',
        init() {
            const params = new URLSearchParams(window.location.search);

            ['category', 'availability', 'color', 'density', 'size'].forEach((field) => {
                const value = params.get(field);

                if (value && this.isValidFilterValue(field, value)) {
                    this[field] = value;
                }
            });

            this.$nextTick(() => {
                this.refresh();
                this.updateUrl();
                window.storefrontAnalytics.track('catalog_view', { total: this.total });
            });
        },
        setFilter(field, value) {
            this[field] = value || 'all';
            this.refresh();
            this.updateUrl();

            window.storefrontAnalytics.track('catalog_filter', {
                field,
                value: this[field],
                visible_count: this.visibleCount,
            });
        },
        reset() {
            this.category = 'all';
            this.availability = 'all';
            this.color = 'all';
            this.density = 'all';
            this.size = 'all';
            this.refresh();
            this.updateUrl();
            window.storefrontAnalytics.track('catalog_filter_reset');
        },
        matches(card) {
            return this.matchesCard(card);
        },
        matchesCard(card) {
            const categoryMatches = this.category === 'all' || String(this.category) === String(card.dataset.category);
            const inStock = card.dataset.inStock === 'true';
            const availabilityMatches = this.availability === 'all'
                || (this.availability === 'stock' && inStock)
                || (this.availability === 'order' && !inStock);
            const colorMatches = this.color === 'all' || parseJsonArray(card.dataset.colors).includes(String(this.color));
            const densityMatches = this.density === 'all' || parseJsonArray(card.dataset.densities).includes(String(this.density));
            const sizeMatches = this.size === 'all' || parseJsonArray(card.dataset.sizes).includes(String(this.size));

            return categoryMatches && availabilityMatches && colorMatches && densityMatches && sizeMatches;
        },
        refresh() {
            this.visibleCount = [...this.$root.querySelectorAll('[data-product-card]')]
                .filter((card) => this.matchesCard(card))
                .length;
        },
        updateUrl() {
            const url = new URL(window.location.href);

            ['category', 'availability', 'color', 'density', 'size'].forEach((field) => {
                if (this[field] === 'all') {
                    url.searchParams.delete(field);
                } else {
                    url.searchParams.set(field, this[field]);
                }
            });

            window.history.replaceState({}, '', url);
        },
        selectedOptionLabel(type, fallback) {
            const value = this[type];

            if (!value || value === 'all') {
                return fallback;
            }

            return this.labels[type]?.[value] || fallback;
        },
        isValidFilterValue(field, value) {
            if (field === 'availability') {
                return ['all', 'stock', 'order'].includes(value);
            }

            return value === 'all' || Boolean(this.labels[field]?.[value]);
        },
        visibleCountLabel() {
            return `${this.visibleCount.toLocaleString('ru-RU')} из ${this.total.toLocaleString('ru-RU')} моделей`;
        },
    }));

});

window.Alpine = Alpine;
Alpine.start();

import Alpine from 'alpinejs';
import './animations';

const ATTRIBUTION_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
const DEFAULT_ORDER_QUANTITIES = [100, 500, 1000, 5000];

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

const ORDER_DRAFT_STORAGE_KEY = 'storefront_order_draft';

function loadOrderDraft() {
    try {
        const parsed = JSON.parse(window.localStorage.getItem(ORDER_DRAFT_STORAGE_KEY) || '{}');

        return {
            lines: Array.isArray(parsed.lines) ? parsed.lines : [],
            quantity: Number(parsed.quantity) || 100,
        };
    } catch {
        return { lines: [], quantity: 100 };
    }
}

function persistOrderDraft(state) {
    try {
        window.localStorage.setItem(ORDER_DRAFT_STORAGE_KEY, JSON.stringify({
            lines: state.lines,
            quantity: state.quantity,
        }));
    } catch {
        // localStorage unavailable (private mode / quota) — draft simply won't persist.
    }
}

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

function sortedUniqueNumbers(values) {
    return [...new Set((values || []).map(Number).filter((value) => Number.isFinite(value) && value > 0))]
        .sort((a, b) => a - b);
}

function orderQuantitiesFor(product) {
    const quantities = sortedUniqueNumbers(product.priceQuantities);

    return quantities.length > 0 ? quantities : DEFAULT_ORDER_QUANTITIES;
}

function evenSplitVariantQuantities(colors, sizes, total) {
    const cells = [];

    colors.forEach((color) => sizes.forEach((size) => cells.push([color, size])));

    const count = cells.length;
    const base = count > 0 ? Math.floor(total / count) : 0;
    const remainder = count > 0 ? total % count : 0;

    const result = {};

    cells.forEach(([color, size], index) => {
        result[color] = result[color] || {};
        result[color][size] = base + (index < remainder ? 1 : 0);
    });

    return result;
}

function variantQuantityTotal(variantQuantities) {
    return Object.values(variantQuantities)
        .flatMap((sizes) => Object.values(sizes))
        .reduce((sum, quantity) => sum + (Number(quantity) || 0), 0);
}

function normalizeOrderQuantity(value, allowedQuantities = DEFAULT_ORDER_QUANTITIES, minimum = 100) {
    const quantities = sortedUniqueNumbers(allowedQuantities).filter((quantity) => quantity >= minimum);
    const fallback = quantities[0] || minimum;
    const requested = Number(value) || fallback;

    if (quantities.includes(requested)) {
        return requested;
    }

    return quantities.find((quantity) => quantity >= requested) || quantities[quantities.length - 1] || fallback;
}

document.addEventListener('alpine:init', () => {
    Alpine.store('volume', { selected: '100' });
    Alpine.store('attribution', currentAttribution());

    const orderDraft = loadOrderDraft();

    Alpine.store('orderBuilder', {
        drawerOpen: false,
        lines: orderDraft.lines,
        quantity: orderDraft.quantity,
        clear() {
            this.lines = [];
        },
        addProduct(product) {
            const priceQuantities = orderQuantitiesFor(product);
            const moq = Number(product.moq) || priceQuantities[0] || 100;
            const quantity = normalizeOrderQuantity(this.quantity, priceQuantities, moq);
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
                priceTiers: product.priceTiers ?? {},
                priceTiersByDensity: product.priceTiersByDensity ?? {},
                densityId: product.densityId ?? null,
                densityOptions: product.densityOptions ?? [],
                priceQuantities,
                colors: product.colors ?? [],
                sizes: product.sizes ?? [],
                densities: product.densities ?? [],
                availableColors: product.availableColors ?? [],
                availableSizes: product.availableSizes ?? [],
                availableDensities: product.availableDensities ?? [],
                colorSwatches: product.colorSwatches ?? {},
                variantQuantities: null,
            };

            if (line.availableColors.length > 0 && line.availableSizes.length > 0) {
                line.variantQuantities = evenSplitVariantQuantities(line.availableColors, line.availableSizes, line.quantity);
            }

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
            this.quantity = normalizeOrderQuantity(value);
            Alpine.store('volume').selected = String(volumeKey);
        },
        setQuantity(value) {
            this.quantity = normalizeOrderQuantity(value);
            Alpine.store('volume').selected = this.volumeKeyFor(this.quantity);
        },
        updateLineQuantity(productId, value) {
            const line = this.lines.find((item) => item.product_id === Number(productId));

            if (!line) {
                return;
            }

            line.quantity = normalizeOrderQuantity(value, line.priceQuantities, line.moq);

            if (line.availableColors.length > 0 && line.availableSizes.length > 0) {
                line.variantQuantities = evenSplitVariantQuantities(line.availableColors, line.availableSizes, line.quantity);
            }
        },
        updateVariantQuantity(productId, color, size, value) {
            const line = this.lines.find((item) => item.product_id === Number(productId));

            if (!line || !line.variantQuantities) {
                return;
            }

            const quantity = Math.max(0, Math.trunc(Number(value)) || 0);

            line.variantQuantities[color] = line.variantQuantities[color] || {};
            line.variantQuantities[color][size] = quantity;
        },
        variantTotal(line) {
            return line.variantQuantities ? variantQuantityTotal(line.variantQuantities) : line.quantity;
        },
        submissionLines() {
            return this.lines.flatMap((line) => {
                if (!line.variantQuantities) {
                    return [{
                        product_id: line.product_id,
                        quantity: line.quantity,
                        density: (line.densities || []).join(', '),
                        size: (line.sizes || []).join(', '),
                        color: (line.colors || []).join(', '),
                    }];
                }

                const rows = [];

                Object.entries(line.variantQuantities).forEach(([color, sizes]) => {
                    Object.entries(sizes).forEach(([size, quantity]) => {
                        if (Number(quantity) > 0) {
                            rows.push({
                                product_id: line.product_id,
                                quantity: Number(quantity),
                                density: (line.densities || []).join(', '),
                                size,
                                color,
                            });
                        }
                    });
                });

                return rows;
            });
        },
        updateLineDensity(productId, densityId) {
            const line = this.lines.find((item) => item.product_id === Number(productId));

            if (!line) {
                return;
            }

            const option = line.densityOptions.find((opt) => Number(opt.id) === Number(densityId));

            if (!option) {
                return;
            }

            line.densityId = option.id;
            line.densities = [option.name];
        },
        priceFor(line) {
            if (line.densityId && line.priceTiersByDensity?.[String(line.densityId)]) {
                return line.priceTiersByDensity[String(line.densityId)]?.[String(line.quantity)] || 'По запросу';
            }

            return line.priceTiers?.[String(line.quantity)] || 'По запросу';
        },
        volumeKeyFor(quantity) {
            return String(normalizeOrderQuantity(quantity));
        },
    });

    Alpine.effect(() => {
        const orderBuilder = Alpine.store('orderBuilder');

        persistOrderDraft({ lines: orderBuilder.lines, quantity: orderBuilder.quantity });
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
        variantMismatch: false,
        init() {
            if (oldLines.length > 0) {
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

            const orderBuilder = Alpine.store('orderBuilder');
            const unbalanced = orderBuilder.lines.some((line) => (
                line.variantQuantities && orderBuilder.variantTotal(line) !== line.quantity
            ));

            if (unbalanced) {
                event.preventDefault();
                this.variantMismatch = true;

                return;
            }

            this.variantMismatch = false;
            this.submitting = true;
            window.storefrontAnalytics.track('form_submit', {
                line_count: orderBuilder.lines.length,
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

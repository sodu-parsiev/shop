import Alpine from 'alpinejs';
import './animations';

const ATTRIBUTION_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'yclid'];
const DEFAULT_ORDER_QUANTITIES = [100, 500, 1000, 5000];

// Yandex Metrika goals already created for this counter — only these reach `reachGoal`.
const YM_GOALS = new Set([
    'lead_submitted',
    'lead_form_started',
    'price_request_clicked',
    'product_viewed',
    'product_inquiry_clicked',
    'inquiry_opened',
    'whatsapp_clicked',
    'email_clicked',
]);

window.storefrontAnalytics = window.storefrontAnalytics || {
    track(event, payload = {}) {
        const data = { event, ...payload };

        window.dataLayer?.push(data);

        if (typeof window.gtag === 'function') {
            window.gtag('event', event, payload);
        }

        if (YM_GOALS.has(event) && typeof window.ym === 'function' && window.YM_COUNTER_ID) {
            window.ym(window.YM_COUNTER_ID, 'reachGoal', event, payload);
        }

        if (event === 'contact_click') {
            if (payload.type === 'whatsapp') {
                this.track('whatsapp_clicked');
            } else if (payload.type === 'email') {
                this.track('email_clicked');
            }
        }

        window.dispatchEvent(new CustomEvent('storefront:analytics', { detail: data }));
    },
};

const ORDER_DRAFT_STORAGE_KEY = 'storefront_order_draft';

// Catalog filter fields whose options narrow to what the other active filters leave possible,
// mapped to the product-card dataset key that holds the card's values.
const FACET_FIELDS = { color: 'colors', density: 'densities', size: 'sizes' };

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
        addProduct(product, placement = null) {
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
                colorImages: product.colorImages ?? {},
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

            window.storefrontAnalytics.track('product_inquiry_clicked', {
                product_id: productId,
                category: product.category,
            });

            this._openDrawer(placement);
        },
        close() {
            this.drawerOpen = false;
        },
        open(placement = null) {
            this._openDrawer(placement);
        },
        _openDrawer(placement = null) {
            const wasOpen = this.drawerOpen;
            this.drawerOpen = true;

            if (!wasOpen) {
                window.storefrontAnalytics.track('inquiry_opened', placement ? { placement } : {});
            }
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
            window.storefrontAnalytics.track('lead_form_started', { form_type: 'price_request', placement: 'contacts' });
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
        availableOptions: null,
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

            if (field === 'category') {
                this.availability = 'all';
                this.color = 'all';
                this.density = 'all';
                this.size = 'all';
            }

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
        matchesCard(card, ignoredField = null) {
            const categoryMatches = this.category === 'all' || String(this.category) === String(card.dataset.category);
            const inStock = card.dataset.inStock === 'true';
            const availabilityMatches = this.availability === 'all'
                || (this.availability === 'stock' && inStock)
                || (this.availability === 'order' && !inStock);
            const facetsMatch = Object.entries(FACET_FIELDS).every(([field, datasetKey]) => field === ignoredField
                || this[field] === 'all'
                || parseJsonArray(card.dataset[datasetKey]).includes(String(this[field])));

            return categoryMatches && availabilityMatches && facetsMatch;
        },
        refresh() {
            const cards = [...this.$root.querySelectorAll('[data-product-card]')];

            this.pruneUnavailableSelections(cards);
            this.availableOptions = Object.fromEntries(
                Object.keys(FACET_FIELDS).map((field) => [field, this.facetValues(cards, field)]),
            );
            this.visibleCount = cards.filter((card) => this.matchesCard(card)).length;
        },
        // Values of `field` still reachable given every other active filter.
        facetValues(cards, field) {
            const values = {};

            cards
                .filter((card) => this.matchesCard(card, field))
                .forEach((card) => parseJsonArray(card.dataset[FACET_FIELDS[field]]).forEach((value) => {
                    values[value] = true;
                }));

            return values;
        },
        // Drop selections that the other filters made impossible (e.g. after an availability
        // switch or a stale deep link). Resetting one field only widens the rest, so one pass suffices.
        pruneUnavailableSelections(cards) {
            Object.keys(FACET_FIELDS).forEach((field) => {
                if (this[field] !== 'all' && !this.facetValues(cards, field)[String(this[field])]) {
                    this[field] = 'all';
                }
            });
        },
        isOptionAvailable(field, value) {
            return this.availableOptions === null || Boolean(this.availableOptions[field]?.[String(value)]);
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

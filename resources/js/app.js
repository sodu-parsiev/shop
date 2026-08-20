import Alpine from 'alpinejs';
import './animations';

document.addEventListener('alpine:init', () => {
    Alpine.store('volume', { selected: '5000_10000' });

    Alpine.store('orderBuilder', {
        drawerOpen: false,
        lines: [],
        quantity: 5000,
        preferredDensity: 'Уточнить с менеджером',
        preferredSize: 'Смешанная размерная сетка',
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
                density: this.preferredDensity,
                size: this.preferredSize,
            };

            if (existing) {
                Object.assign(existing, line);
            } else {
                this.lines.push(line);
            }

            this.drawerOpen = true;
        },
        close() {
            this.drawerOpen = false;
        },
        open() {
            this.drawerOpen = true;
        },
        remove(productId) {
            this.lines = this.lines.filter((line) => line.product_id !== Number(productId));
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

    Alpine.data('catalogFilter', () => ({
        category: 'all',
        availability: 'all',
        setCategory(id) {
            this.category = id;
        },
        setAvailability(value) {
            this.availability = value;
        },
        matches(productCategoryId, inStock) {
            const categoryMatches = this.category === 'all' || String(this.category) === String(productCategoryId);
            const availabilityMatches = this.availability === 'all'
                || (this.availability === 'stock' && inStock)
                || (this.availability === 'order' && !inStock);

            return categoryMatches && availabilityMatches;
        },
    }));

});

window.Alpine = Alpine;
Alpine.start();

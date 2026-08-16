import Alpine from 'alpinejs';
import './animations';

document.addEventListener('alpine:init', () => {
    Alpine.store('volume', { selected: '5000_10000' });

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

    Alpine.data('qtyStepper', () => ({
        qty: 5000,
        setPreset(value, volumeKey) {
            this.qty = value;
            Alpine.store('volume').selected = volumeKey;
        },
    }));

    Alpine.data('applicationForm', () => ({
        volume: Alpine.store('volume').selected,
    }));
});

window.Alpine = Alpine;
Alpine.start();

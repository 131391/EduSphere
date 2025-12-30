/**
 * Location Cascade Handler
 * 
 * Handles cascading behavior for Country > State > City dropdowns.
 * Works with both standard selects and Select2.
 */

class LocationCascade {
    constructor() {
        this.init();
    }

    init() {
        // Initialize on page load
        this.initCascades();

        // Re-initialize when new content is loaded (e.g., modals)
        const observer = new MutationObserver((mutations) => {
            let shouldInit = false;
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    shouldInit = true;
                }
            });
            if (shouldInit) {
                this.initCascades();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    initCascades() {
        const countrySelects = document.querySelectorAll('[data-location-cascade="true"]');

        countrySelects.forEach(countrySelect => {
            if (countrySelect.dataset.cascadeInitialized) return;

            const container = countrySelect.closest('form') || document.body;
            const stateSelect = container.querySelector('[data-state-select]');
            const citySelect = container.querySelector('[data-city-select]');

            if (!stateSelect || !citySelect) return;

            // Mark as initialized
            countrySelect.dataset.cascadeInitialized = 'true';

            // Load initial countries if empty
            if (countrySelect.options.length <= 1) {
                this.loadCountries(countrySelect);
            }

            // Handle Country Change
            this.bindChange(countrySelect, () => {
                this.resetSelect(stateSelect);
                this.resetSelect(citySelect);

                const countryId = countrySelect.value;
                if (countryId) {
                    this.loadStates(stateSelect, countryId);
                }
            });

            // Handle State Change
            this.bindChange(stateSelect, () => {
                this.resetSelect(citySelect);

                const stateId = stateSelect.value;
                if (stateId) {
                    this.loadCities(citySelect, stateId);
                }
            });

            // Handle pre-selected values (e.g., old input or edit mode)
            if (countrySelect.value && stateSelect.options.length <= 1) {
                const selectedState = stateSelect.getAttribute('data-selected');
                this.loadStates(stateSelect, countrySelect.value, selectedState);
            }

            if (stateSelect.getAttribute('data-selected') && citySelect.options.length <= 1) {
                // Wait for states to load first if needed
                const checkStateLoaded = setInterval(() => {
                    if (stateSelect.options.length > 1) {
                        clearInterval(checkStateLoaded);
                        if (stateSelect.value) {
                            const selectedCity = citySelect.getAttribute('data-selected');
                            this.loadCities(citySelect, stateSelect.value, selectedCity);
                        }
                    }
                }, 100);
            }
        });
    }

    bindChange(element, callback) {
        // Standard change event
        element.addEventListener('change', callback);

        // Select2 change event
        if (window.jQuery) {
            $(element).on('select2:select', callback);
            $(element).on('select2:clear', callback);
        }
    }

    async loadCountries(select) {
        this.setLoading(select, true);
        try {
            const response = await fetch('/api/location/countries');
            const data = await response.json();

            if (data.success) {
                this.populateSelect(select, data.data, 'id', 'name');

                // Select India by default if nothing selected
                if (!select.value) {
                    const india = data.data.find(c => c.name === 'India');
                    if (india) {
                        this.setValue(select, india.id);
                        // Trigger change to load states
                        this.triggerChange(select);
                    }
                }
            }
        } catch (error) {
            console.error('Error loading countries:', error);
        } finally {
            this.setLoading(select, false);
        }
    }

    async loadStates(select, countryId, selectedValue = null) {
        this.setLoading(select, true);
        try {
            const response = await fetch(`/api/location/states/${countryId}`);
            const data = await response.json();

            if (data.success) {
                this.populateSelect(select, data.data, 'id', 'name');
                if (selectedValue) {
                    this.setValue(select, selectedValue);
                    // Do not trigger change here to avoid race condition with initialization logic
                    // The setInterval block in initCascades will handle loading cities
                }
            }
        } catch (error) {
            console.error('Error loading states:', error);
        } finally {
            this.setLoading(select, false);
        }
    }

    async loadCities(select, stateId, selectedValue = null) {
        this.setLoading(select, true);
        try {
            const response = await fetch(`/api/location/cities/${stateId}`);
            const data = await response.json();

            if (data.success) {
                this.populateSelect(select, data.data, 'id', 'name');
                if (selectedValue) {
                    this.setValue(select, selectedValue);
                }
            }
        } catch (error) {
            console.error('Error loading cities:', error);
        } finally {
            this.setLoading(select, false);
        }
    }

    populateSelect(select, items, valueKey, textKey) {
        // Keep placeholder
        const placeholder = select.options[0];
        select.innerHTML = '';
        if (placeholder) select.add(placeholder);

        items.forEach(item => {
            const option = new Option(item[textKey], item[valueKey]);
            select.add(option);
        });

        // Refresh Select2 if active
        if (window.jQuery && $(select).hasClass('select2-hidden-accessible')) {
            $(select).trigger('change.select2');
        }
    }

    resetSelect(select) {
        const placeholder = select.options[0];
        select.innerHTML = '';
        if (placeholder) select.add(placeholder);
        this.setValue(select, '');
    }

    setValue(select, value) {
        select.value = value;
        if (window.jQuery && $(select).hasClass('select2-hidden-accessible')) {
            $(select).val(value).trigger('change.select2');
        }
    }

    triggerChange(select) {
        select.dispatchEvent(new Event('change'));
        if (window.jQuery && $(select).hasClass('select2-hidden-accessible')) {
            $(select).trigger('change');
        }
    }

    setLoading(select, isLoading) {
        if (isLoading) {
            select.disabled = true;
            select.dataset.originalPlaceholder = select.options[0]?.text || '';
            if (select.options[0]) select.options[0].text = 'Loading...';
        } else {
            select.disabled = false;
            if (select.options[0]) select.options[0].text = select.dataset.originalPlaceholder || 'Select Option';
        }

        // Update Select2 state
        if (window.jQuery && $(select).hasClass('select2-hidden-accessible')) {
            $(select).prop('disabled', isLoading);
        }
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    window.locationCascade = new LocationCascade();
});

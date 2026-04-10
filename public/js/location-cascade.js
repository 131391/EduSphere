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
        // Wait a small bit for global Select2 initializers to finish
        setTimeout(() => {
            const countrySelects = document.querySelectorAll('[data-location-cascade="true"]');

            countrySelects.forEach(countrySelect => {
                if (countrySelect.dataset.cascadeInitialized) return;

                // Find container, prioritizing closest field-group container (like grid) so multiple location sets in one form don't conflict.
                const container = countrySelect.closest('.grid') || countrySelect.closest('.fieldset') || countrySelect.closest('form') || document.body;
                const stateSelect = container.querySelector('[data-state-select]');
                const citySelect = container.querySelector('[data-city-select]');

                if (!stateSelect || !citySelect) return;

                // Mark as initialized
                countrySelect.dataset.cascadeInitialized = 'true';

                // Load initial countries if empty
                if (countrySelect.options.length <= 2) {
                    // If it has a dummy option, reset it
                    if (countrySelect.options.length === 2 && countrySelect.options[1].value) {
                        countrySelect.setAttribute('data-selected', countrySelect.options[1].value);
                        countrySelect.innerHTML = '<option value="">Select Country</option>';
                    }
                    const selectedCountry = countrySelect.getAttribute('data-selected');
                    this.loadCountries(countrySelect, selectedCountry);
                }

                // Handle Country Change
                let lastCountryId = countrySelect.value;
                this.bindChange(countrySelect, () => {
                    const countryId = countrySelect.value;
                    if (countryId == lastCountryId) return; // Prevent phantom triggers (loose equality for string/number)
                    lastCountryId = countryId;

                    this.resetSelect(stateSelect);
                    this.resetSelect(citySelect);

                    if (countryId) {
                        const selectedState = stateSelect.getAttribute('data-selected');
                        this.loadStates(stateSelect, countryId, selectedState);
                    }
                });

                // Handle State Change
                let lastStateId = stateSelect.value;
                this.bindChange(stateSelect, () => {
                    const stateId = stateSelect.value;
                    if (stateId == lastStateId) return; // Prevent phantom triggers (loose equality)
                    lastStateId = stateId;

                    this.resetSelect(citySelect);

                    if (stateId) {
                        const selectedCity = citySelect.getAttribute('data-selected');
                        this.loadCities(citySelect, stateId, selectedCity);
                    }
                });

                // --- Recursive Initial Sync Engine ---
                // This ensures that if any part of the chain is pre-populated (via SSR),
                // the subsequent child parts are also synchronized and backfilled.
                
                const syncChain = async () => {
                    // 1. Sync State if Country is set
                    if (countrySelect.value && !stateSelect.dataset.initSynced) {
                        const selectedState = stateSelect.getAttribute('data-selected');
                        if (selectedState) {
                            stateSelect.dataset.initSynced = 'true';
                            await this.loadStates(stateSelect, countrySelect.value, selectedState);
                        }
                    }

                    // 2. Sync City if State is set (Wait a bit for State to potentially load if it wasn't SSR)
                    // We check again after a small delay to catch async state loads
                    setTimeout(async () => {
                        if (stateSelect.value && !citySelect.dataset.initSynced) {
                            const selectedCity = citySelect.getAttribute('data-selected');
                            if (selectedCity) {
                                citySelect.dataset.initSynced = 'true';
                                await this.loadCities(citySelect, stateSelect.value, selectedCity);
                            }
                        }
                    }, 300);
                };

                syncChain();
            });
        }, 250);
    }

    bindChange(element, callback) {
        let isProcessing = false;
        const wrappedCallback = (e) => {
            if (isProcessing) return;
            isProcessing = true;
            callback(e);
            // Increased to 100ms to allow DOM and Select2 to settle
            setTimeout(() => { isProcessing = false; }, 100);
        };

        if (window.jQuery) {
            // Bind both native jQuery change and select2 events
            $(element).on('change select2:select select2:clear', wrappedCallback);
        } else {
            // Standard change event fallback
            element.addEventListener('change', wrappedCallback);
        }
    }

    async loadCountries(select, selectedValue = null) {
        this.setLoading(select, true);
        try {
            const response = await fetch('/api/location/countries');
            const data = await response.json();

            if (data.success) {
                this.populateSelect(select, data.data, 'id', 'name');

                // Select the old value if available
                if (selectedValue) {
                    this.setValue(select, selectedValue);
                    this.triggerChange(select);
                } else if (!select.value) {
                    // Select India by default if nothing selected
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
            // Optionally remove data-selected after full cycle
            setTimeout(() => select.removeAttribute('data-selected'), 1000);
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
                    this.triggerChange(select); // Organically triggers city load
                }
            }
        } catch (error) {
            console.error('Error loading states:', error);
        } finally {
            this.setLoading(select, false);
            // Optionally remove data-selected after full cycle
            setTimeout(() => select.removeAttribute('data-selected'), 1000);
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
            // Optionally remove data-selected after full cycle
            setTimeout(() => select.removeAttribute('data-selected'), 1000);
        }
    }

    populateSelect(select, items, valueKey, textKey) {
        // Capture current selected value (SSR support)
        const currentValue = select.value;
        const placeholder = select.options[0];
        const placeholderText = placeholder ? placeholder.text : 'Select an option';
        
        // Build new options list
        let optionsHtml = `<option value="">${placeholderText}</option>`;
        items.forEach(item => {
            const isSelected = item[valueKey] == currentValue ? 'selected' : '';
            optionsHtml += `<option value="${item[valueKey]}" ${isSelected}>${item[textKey]}</option>`;
        });
        
        select.innerHTML = optionsHtml;

        // Restore value if it was wiped by the innerHTML change
        if (currentValue && !select.value) {
            select.value = currentValue;
        }

        // Refresh Select2 if active
        if (window.jQuery && $(select).hasClass('select2-hidden-accessible')) {
            // Ensure Select2 is updated with the new options
            $(select).select2({
                placeholder: placeholderText,
                width: '100%'
            }).trigger('change.select2');
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
            $(select).val(value).trigger('change');
        }
    }

    triggerChange(select) {
        // Use jQuery trigger if available to hit all listeners (native + jQuery)
        if (window.jQuery) {
            $(select).trigger('change');
        } else {
            select.dispatchEvent(new Event('change'));
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

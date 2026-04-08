$(document).ready(function () {
    const initializeLocationSelector = (container) => {
        const countrySelect = $(container).find('.country-select');
        const stateSelect = $(container).find('.state-select');
        const citySelect = $(container).find('.city-select');

        const selectedCountry = $(container).data('selected-country');
        const selectedState = $(container).data('selected-state');
        const selectedCity = $(container).data('selected-city');

        // Load States if country is selected
        const loadStates = (countryId, selectedId = null) => {
            if (!countryId) {
                stateSelect.empty().append('<option value=""></option>').prop('disabled', true);
                citySelect.empty().append('<option value=""></option>').prop('disabled', true);
                return;
            }

            stateSelect.prop('disabled', true);
            $.ajax({
                url: `/api/location/states/${countryId}`,
                method: 'GET',
                success: function (response) {
                    stateSelect.empty().append('<option value=""></option>');
                    if (response.success && response.data) {
                        response.data.forEach(state => {
                            const selected = selectedId == state.id ? 'selected' : '';
                            stateSelect.append(`<option value="${state.id}" ${selected}>${state.name}</option>`);
                        });
                        stateSelect.prop('disabled', false);
                        if (selectedId) {
                            loadCities(selectedId, selectedCity);
                        }
                    }
                }
            });
        };

        // Load Cities if state is selected
        const loadCities = (stateId, selectedId = null) => {
            if (!stateId) {
                citySelect.empty().append('<option value=""></option>').prop('disabled', true);
                return;
            }

            citySelect.prop('disabled', true);
            $.ajax({
                url: `/api/location/cities/${stateId}`,
                method: 'GET',
                success: function (response) {
                    citySelect.empty().append('<option value=""></option>');
                    if (response.success && response.data) {
                        response.data.forEach(city => {
                            const selected = selectedId == city.id ? 'selected' : '';
                            citySelect.append(`<option value="${city.id}" ${selected}>${city.name}</option>`);
                        });
                        citySelect.prop('disabled', false);
                    }
                }
            });
        };

        // Event listeners
        countrySelect.on('change', function () {
            loadStates($(this).val());
        });

        stateSelect.on('change', function () {
            loadCities($(this).val());
        });

        // Initial load if editing
        if (selectedCountry) {
            loadStates(selectedCountry, selectedState);
        }
    };

    $('.location-selector-container').each(function () {
        initializeLocationSelector(this);
    });
});

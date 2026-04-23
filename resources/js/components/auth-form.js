document.addEventListener('alpine:init', () => {
    Alpine.data('ajaxAuthForm', (config = {}) => ({
        loading: false,
        message: '',
        messageType: 'error',
        errors: {},
        capsLockOn: false,
        passwordVisibility: {},
        form: { ...(config.initialForm || {}) },

        get hasClientErrors() {
            return Object.keys(this.errors).length > 0 || !!this.message;
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        setMessage(message, type = 'error') {
            this.message = message;
            this.messageType = type;
        },

        isPasswordVisible(field = 'password') {
            return !!this.passwordVisibility[field];
        },

        togglePasswordVisibility(field = 'password') {
            this.passwordVisibility[field] = !this.isPasswordVisible(field);
        },

        syncCapsLock(event) {
            if (typeof event.getModifierState !== 'function') {
                return;
            }

            this.capsLockOn = event.getModifierState('CapsLock');
        },

        validate() {
            if (typeof config.validate !== 'function') {
                this.errors = {};
                return true;
            }

            const result = config.validate.call(this);

            if (result === true) {
                this.errors = {};
                return true;
            }

            this.errors = result || {};
            return Object.keys(this.errors).length === 0;
        },

        async submit(event) {
            this.setMessage('', this.messageType);

            if (!this.validate()) {
                return;
            }

            this.loading = true;

            try {
                const payload = typeof config.transformPayload === 'function'
                    ? config.transformPayload.call(this)
                    : this.form;

                const response = await window.axios({
                    method: config.method || 'post',
                    url: config.url || event.target.action,
                    data: payload,
                    headers: {
                        Accept: 'application/json',
                        ...(config.headers || {}),
                    },
                });

                this.errors = {};
                this.setMessage(
                    response.data?.message || config.successMessage || 'Request completed successfully.',
                    'success'
                );

                if (typeof config.onSuccess === 'function') {
                    await config.onSuccess.call(this, response.data, response, event);
                }
            } catch (error) {
                this.errors = {};

                if (error.response?.status === 422) {
                    const responseErrors = error.response.data?.errors || {};
                    this.errors = Object.fromEntries(
                        Object.entries(responseErrors).map(([field, messages]) => [field, messages[0]])
                    );

                    this.setMessage(
                        error.response.data?.message || config.validationMessage || 'Please correct the highlighted fields.',
                        'error'
                    );
                } else {
                    this.setMessage(
                        error.response?.data?.message || config.errorMessage || 'Something went wrong. Please try again.',
                        'error'
                    );
                }

                if (typeof config.onError === 'function') {
                    await config.onError.call(this, error, event);
                }
            } finally {
                this.loading = false;
            }
        },
    }));
});

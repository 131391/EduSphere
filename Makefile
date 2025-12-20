# Makefile for EduSphere - Uses PHP 8.2 without changing system default

.PHONY: help install update migrate serve test

PHP82 = php8.2
COMPOSER = $(PHP82) /usr/bin/composer

help: ## Show this help message
	@echo "EduSphere Makefile - PHP 8.2 Commands"
	@echo ""
	@echo "Usage: make [target]"
	@echo ""
	@echo "Available targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

check-php: ## Check if PHP 8.2 is available
	@if ! command -v php8.2 &> /dev/null; then \
		echo "❌ PHP 8.2 is not installed."; \
		echo "Run: make install-php82"; \
		exit 1; \
	else \
		echo "✅ PHP 8.2 is available: $$($(PHP82) -v | head -1)"; \
	fi

install-php82: ## Install PHP 8.2 (keeps PHP 7.4)
	@echo "Installing PHP 8.2 alongside PHP 7.4..."
	sudo add-apt-repository ppa:ondrej/php -y
	sudo apt update
	sudo apt install -y php8.2 php8.2-cli php8.2-common \
		php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl \
		php8.2-zip php8.2-gd php8.2-bcmath php8.2-redis php8.2-intl
	@echo "✅ PHP 8.2 installed! PHP 7.4 remains unchanged."

install: check-php ## Install Composer dependencies
	$(COMPOSER) install

update: check-php ## Update Composer dependencies
	$(COMPOSER) update

migrate: check-php ## Run database migrations
	$(PHP82) artisan migrate

migrate-fresh: check-php ## Fresh migration with seeding
	$(PHP82) artisan migrate:fresh --seed

serve: check-php ## Start development server
	$(PHP82) artisan serve

key: check-php ## Generate application key
	$(PHP82) artisan key:generate

cache-clear: check-php ## Clear all caches
	$(PHP82) artisan optimize:clear

cache: check-php ## Cache configuration
	$(PHP82) artisan config:cache
	$(PHP82) artisan route:cache
	$(PHP82) artisan view:cache

test: check-php ## Run tests
	$(PHP82) artisan test

tinker: check-php ## Open Tinker
	$(PHP82) artisan tinker

composer: check-php ## Run Composer command (usage: make composer CMD="require package")
	$(COMPOSER) $(CMD)

artisan: check-php ## Run Artisan command (usage: make artisan CMD="migrate")
	$(PHP82) artisan $(CMD)


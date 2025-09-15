# PHPCop Build System

.PHONY: help phar clean install test

help: ## Show this help message
	@echo "PHPCop Build Commands:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

phar: ## Build PHAR archive
	@echo "Building PHPCop PHAR..."
	php -d phar.readonly=0 build-phar.php

clean: ## Clean build artifacts
	@echo "Cleaning build artifacts..."
	rm -f phpcop.phar

install: ## Install dependencies
	composer install --no-dev --optimize-autoloader

test: phar ## Test PHAR build
	@echo "Testing PHAR..."
	php phpcop.phar --version
	@echo "PHAR test completed successfully!"

all: install phar test ## Full build pipeline
	@echo "Build pipeline completed!"
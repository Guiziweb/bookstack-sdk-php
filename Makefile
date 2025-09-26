.PHONY: help test test-unit test-unit-coverage test-integration coverage php-cs-fixer stan insights quality security install ci-setup ci-test ci-down

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install dependencies
	composer install

test: ## Run all tests
	vendor/bin/phpunit --testsuite=all

test-unit: ## Run unit tests only
	vendor/bin/phpunit --testsuite=unit

test-unit-coverage: ## Run unit tests with coverage
	vendor/bin/phpunit --testsuite=unit --coverage-text

test-integration: ## Run integration tests (requires BookStack instance)
	vendor/bin/phpunit --testsuite=integration --testdox

php-cs-fixer: ## Fix code style
	vendor/bin/php-cs-fixer fix --allow-risky=yes

stan: ## Run static analysis
	vendor/bin/phpstan analyse

insights: ## Run code quality analysis
	vendor/bin/phpinsights

security: ## Run security audit
	@echo "Checking for known security vulnerabilities..."
	composer audit
	@echo "Validating composer.json..."
	composer validate --strict

quality: ## Run full quality checks (PHPStan + PHPInsights + CS-Fixer)
	@echo "Running static analysis..."
	vendor/bin/phpstan analyse
	@echo "Running quality analysis..."
	vendor/bin/phpinsights --no-interaction --min-quality=80 --min-complexity=80 --min-architecture=75 --min-style=90
	@echo "Checking code style..."
	vendor/bin/php-cs-fixer fix --dry-run --diff --allow-risky=yes

ci-setup: ## Setup BookStack for CI testing
	docker-compose -f docker-compose.ci.yml up -d
	./scripts/setup-bookstack-ci.sh

ci-test: ## Run tests against CI BookStack instance
	cp .env.ci .env && vendor/bin/phpunit --testsuite=integration --testdox

ci-down: ## Stop CI BookStack instance
	docker-compose -f docker-compose.ci.yml down -v
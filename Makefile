.PHONY: help up down build logs test test-unit test-integration test-functional consume publish-test setup-es cache-clear

PHP = docker compose exec php

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start all Docker services
	docker compose up -d

down: ## Stop all Docker services
	docker compose down

build: ## Build Docker images
	docker compose build

logs: ## Show Docker logs
	docker compose logs -f

test: ## Run all tests
	$(PHP) php vendor/bin/phpunit

test-unit: ## Run unit tests
	$(PHP) php vendor/bin/phpunit --testsuite unit

test-integration: ## Run integration tests
	$(PHP) php vendor/bin/phpunit --testsuite integration

test-functional: ## Run functional tests (requires Docker services)
	$(PHP) php vendor/bin/phpunit --testsuite functional

consume: ## Start the messenger consumer
	$(PHP) php bin/console messenger:consume logs_processing -vv

publish-test: ## Publish a test log message
	$(PHP) php bin/console app:publish-test-log

setup-es: ## Create Elasticsearch index template
	$(PHP) php bin/console app:setup-elasticsearch

cache-clear: ## Clear Symfony cache
	$(PHP) php bin/console cache:clear

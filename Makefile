# =============================================================================
# APS Dream Home - Makefile
# Common Docker operations
#
# Usage:
#   make help        - Show this help
#   make build       - Build all Docker images
#   make up          - Start the full stack
#   make down        - Stop the stack
#   make logs        - Tail logs from all services
#   make shell       - Open a shell in the app container
#   make migrate     - Run database migrations
#   make seed        - Run database seeders
#   make restart     - Restart the stack
#   make backup      - Trigger a database backup
#   make deploy      - Pull, build, restart (zero-downtime)
# =============================================================================

# Config
COMPOSE       = docker compose
COMPOSE_PROD  = docker compose -f docker-compose.yml -f docker-compose.production.yml
PROJECT       = apsdreamhome
APP_VERSION  ?= latest
SHELL         = /bin/bash

# Default target
.DEFAULT_GOAL := help

# Colors
GREEN  = \033[0;32m
YELLOW = \033[1;33m
RED    = \033[0;31m
NC     = \033[0m

.PHONY: help
help: ## Show this help message
	@echo "$(GREEN)APS Dream Home - Docker Operations$(NC)"
	@echo ""
	@echo "$(YELLOW)Usage:$(NC)"
	@echo "  make $(GREEN)<target>$(NC)"
	@echo ""
	@echo "$(YELLOW)Targets:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-20s$(NC) %s\n", $$1, $$2}'

# =============================================================================
# Build
# =============================================================================
.PHONY: build
build: ## Build all Docker images
	@echo "$(GREEN)Building all Docker images...$(NC)"
	APP_VERSION=$(APP_VERSION) $(COMPOSE) build --no-cache --parallel
	@echo "$(GREEN)Build complete.$(NC)"

.PHONY: build-app
build-app: ## Build only the app image
	@echo "$(GREEN)Building app image...$(NC)"
	APP_VERSION=$(APP_VERSION) $(COMPOSE) build app

.PHONY: build-websocket
build-websocket: ## Build only the WebSocket image
	@echo "$(GREEN)Building websocket image...$(NC)"
	APP_VERSION=$(APP_VERSION) $(COMPOSE) build websocket

# =============================================================================
# Lifecycle
# =============================================================================
.PHONY: up
up: ## Start all containers (detached)
	@echo "$(GREEN)Starting stack...$(NC)"
	APP_VERSION=$(APP_VERSION) $(COMPOSE) up -d
	@echo "$(GREEN)Stack started. View logs with 'make logs'.$(NC)"

.PHONY: down
down: ## Stop all containers
	@echo "$(YELLOW)Stopping stack...$(NC)"
	$(COMPOSE) down

.PHONY: down-v
down-v: ## Stop stack AND remove volumes (DESTRUCTIVE)
	@echo "$(RED)Stopping stack and removing volumes (data will be lost)...$(NC)"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		$(COMPOSE) down -v; \
	else \
		echo "Cancelled."; \
	fi

.PHONY: restart
restart: ## Restart all containers
	@echo "$(YELLOW)Restarting stack...$(NC)"
	$(COMPOSE) restart
	@echo "$(GREEN)Stack restarted.$(NC)"

.PHONY: restart-app
restart-app: ## Restart only the app container
	@echo "$(YELLOW)Restarting app container...$(NC)"
	$(COMPOSE) restart app

.PHONY: restart-web
restart-web: ## Restart only the websocket container
	@echo "$(YELLOW)Restarting websocket container...$(NC)"
	$(COMPOSE) restart websocket

.PHONY: ps
ps: ## Show running containers
	$(COMPOSE) ps

# =============================================================================
# Logs
# =============================================================================
.PHONY: logs
logs: ## Tail logs from all services
	$(COMPOSE) logs -f --tail=200

.PHONY: logs-app
logs-app: ## Tail logs from the app container
	$(COMPOSE) logs -f --tail=200 app

.PHONY: logs-web
logs-web: ## Tail logs from the websocket container
	$(COMPOSE) logs -f --tail=200 websocket

.PHONY: logs-db
logs-db: ## Tail logs from the db container
	$(COMPOSE) logs -f --tail=200 db

.PHONY: logs-nginx
logs-nginx: ## Tail logs from the nginx container
	$(COMPOSE) logs -f --tail=200 nginx

# =============================================================================
# Shell / Debug
# =============================================================================
.PHONY: shell
shell: ## Open bash shell in the app container
	$(COMPOSE) exec app bash

.PHONY: shell-websocket
shell-websocket: ## Open shell in the websocket container
	$(COMPOSE) exec websocket sh

.PHONY: shell-db
shell-db: ## Open MySQL shell
	$(COMPOSE) exec db mysql -u root -p"$${MYSQL_ROOT_PASSWORD:-rootroot}" $${DB_DATABASE:-apsdreamhome}

.PHONY: shell-redis
shell-redis: ## Open Redis CLI
	$(COMPOSE) exec redis redis-cli

# =============================================================================
# Database operations
# =============================================================================
.PHONY: migrate
migrate: ## Run database migrations
	@echo "$(GREEN)Running database migrations...$(NC)"
	$(COMPOSE) exec app php scripts/create_migrations_table.php
	$(COMPOSE) exec app php scripts/track_migration.php
	@echo "$(GREEN)Migrations complete.$(NC)"

.PHONY: migrate-fresh
migrate-fresh: ## Drop all tables and re-run migrations (DESTRUCTIVE)
	@echo "$(RED)WARNING: This will drop all tables!$(NC)"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		$(COMPOSE) exec db mysql -u root -p"$${MYSQL_ROOT_PASSWORD:-rootroot}" -e "DROP DATABASE IF EXISTS $${DB_DATABASE:-apsdreamhome}; CREATE DATABASE $${DB_DATABASE:-apsdreamhome} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; \
		rm -f storage/.migrated; \
		$(COMPOSE) restart app; \
	else \
		echo "Cancelled."; \
	fi

.PHONY: seed
seed: ## Run database seeders
	@echo "$(GREEN)Running database seeders...$(NC)"
	$(COMPOSE) exec app sh -c "for f in scripts/seed_*.php; do echo \"Seeding: \$$f\"; php \$$f || echo \"  -- failed\"; done"
	@echo "$(GREEN)Seeding complete.$(NC)"

.PHONY: db-backup
db-backup: ## Trigger a manual database backup
	@echo "$(GREEN)Backing up database...$(NC)"
	mkdir -p backups
	$(COMPOSE) exec db sh -c "mysqldump -u root -p\"$${MYSQL_ROOT_PASSWORD:-rootroot}\" $${DB_DATABASE:-apsdreamhome}" 2>/dev/null | gzip > backups/manual_backup_$$(date +%Y%m%d_%H%M%S).sql.gz
	@echo "$(GREEN)Backup saved to backups/.$(NC)"

.PHONY: db-restore
db-restore: ## Restore database from a backup file (use FILE=path/to/backup.sql.gz)
	@if [ -z "$(FILE)" ]; then \
		echo "$(RED)Usage: make db-restore FILE=backups/your-backup.sql.gz$(NC)"; \
		exit 1; \
	fi
	@echo "$(YELLOW)Restoring from $(FILE)...$(NC)"
	@read -p "This will overwrite the current database. Continue? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		gunzip -c $(FILE) | $(COMPOSE) exec -T db mysql -u root -p"$${MYSQL_ROOT_PASSWORD:-rootroot}" $${DB_DATABASE:-apsdreamhome}; \
		echo "$(GREEN)Restore complete.$(NC)"; \
	else \
		echo "Cancelled."; \
	fi

# =============================================================================
# Health & Monitoring
# =============================================================================
.PHONY: health
health: ## Check health of all services
	@echo "$(GREEN)Service health:$(NC)"
	@$(COMPOSE) ps --format json 2>/dev/null | \
		jq -r '.[] | "  \(.Name): \(.Health // "no healthcheck")"' 2>/dev/null || \
		$(COMPOSE) ps

.PHONY: smoke-test
smoke-test: ## Run a full end-to-end smoke test of the running stack
	@chmod +x docker-smoke-test.sh
	@bash docker-smoke-test.sh

.PHONY: stats
stats: ## Show resource usage
	$(COMPOSE) stats --no-stream

.PHONY: top
top: ## Show running processes in all containers
	$(COMPOSE) top

# =============================================================================
# Cache & Optimization
# =============================================================================
.PHONY: clear-cache
clear-cache: ## Clear application cache
	$(COMPOSE) exec app find storage/cache -type f -name "*.php" -delete
	$(COMPOSE) exec app sh -c "rm -rf storage/cache/views/* storage/cache/data/* 2>/dev/null || true"
	@echo "$(GREEN)Cache cleared.$(NC)"

.PHONY: clear-redis
clear-redis: ## Clear all Redis cache
	$(COMPOSE) exec redis redis-cli FLUSHALL
	@echo "$(GREEN)Redis cleared.$(NC)"

.PHONY: composer-install
composer-install: ## Install composer dependencies in app container
	$(COMPOSE) exec app composer install --no-interaction --prefer-dist

.PHONY: composer-update
composer-update: ## Update composer dependencies in app container
	$(COMPOSE) exec app composer update --no-interaction --prefer-dist

# =============================================================================
# Maintenance
# =============================================================================
.PHONY: prune
prune: ## Remove unused Docker resources
	@echo "$(YELLOW)Pruning unused Docker resources...$(NC)"
	docker system prune -f
	docker volume prune -f
	@echo "$(GREEN)Prune complete.$(NC)"

.PHONY: clean
clean: down ## Stop stack and remove generated files
	@echo "$(YELLOW)Removing generated files...$(NC)"
	rm -rf storage/cache/* storage/logs/*.log storage/.migrated
	@echo "$(GREEN)Clean complete.$(NC)"

.PHONY: cleanup-artifacts
cleanup-artifacts: ## Remove regeneratable build artifacts (Flutter, .dart_tool, runtime cache)
	@echo "$(YELLOW)Removing regeneratable build artifacts...$(NC)"
	rm -rf mobile/apsdreamhome_app_v2/android/build
	rm -rf mobile/apsdreamhome_app_v2/android/.gradle
	rm -rf mobile/apsdreamhome_app_v2/build
	rm -rf mobile/apsdreamhome_app_v2/.dart_tool
	rm -rf mobile/apsdreamhome_app_v2/ios/Pods
	rm -rf storage/cache/*
	@echo "$(GREEN)Build artifacts removed. Disk space freed.$(NC)"

.PHONY: cleanup-artifacts-dryrun
cleanup-artifacts-dryrun: ## Show what cleanup-artifacts would remove
	@echo "$(YELLOW)Would remove:$(NC)"
	@for p in mobile/apsdreamhome_app_v2/android/build \
	         mobile/apsdreamhome_app_v2/android/.gradle \
	         mobile/apsdreamhome_app_v2/build \
	         mobile/apsdreamhome_app_v2/.dart_tool \
	         mobile/apsdreamhome_app_v2/ios/Pods \
	         storage/cache/*; do \
		if [ -e "$$p" ]; then \
			sz=$$(du -sh "$$p" 2>/dev/null | cut -f1); \
			echo "  [exists] $$p ($$sz)"; \
		else \
			echo "  [skip]   $$p (not found)"; \
		fi; \
	done

# =============================================================================
# Production
# =============================================================================
.PHONY: prod-up
prod-up: ## Start stack in production mode (with production overrides)
	@echo "$(GREEN)Starting production stack...$(NC)"
	APP_ENV=production APP_DEBUG=false $(COMPOSE_PROD) up -d

.PHONY: deploy
deploy: ## Deploy: pull, build, restart (zero-downtime for stateless services)
	@echo "$(GREEN)Deploying...$(NC)"
	git pull --rebase
	APP_VERSION=$(APP_VERSION) $(COMPOSE) build app websocket
	APP_VERSION=$(APP_VERSION) $(COMPOSE) up -d --no-deps --no-recreate app
	APP_VERSION=$(APP_VERSION) $(COMPOSE) up -d --no-deps --no-recreate websocket
	@echo "$(GREEN)Deploy complete.$(NC)"

.PHONY: ssl-init
ssl-init: ## Initialize Let's Encrypt SSL (run once)
	@echo "$(GREEN)Requesting Let's Encrypt certificate...$(NC)"
	@if [ -z "$(DOMAIN)" ]; then \
		echo "$(RED)Usage: make ssl-init DOMAIN=example.com EMAIL=admin@example.com$(NC)"; \
		exit 1; \
	fi
	@if [ -z "$(EMAIL)" ]; then \
		echo "$(RED)Usage: make ssl-init DOMAIN=example.com EMAIL=admin@example.com$(NC)"; \
		exit 1; \
	fi
	docker run --rm \
		-v $(PWD)/docker/ssl:/etc/letsencrypt \
		-v $(PWD)/docker/certbot/www:/var/www/certbot \
		certbot/certbot certonly --webroot \
		--webroot-path=/var/www/certbot \
		--email $(EMAIL) \
		--agree-tos --no-eff-email \
		-d $(DOMAIN) -d www.$(DOMAIN)
	@echo "$(GREEN)Certificate obtained. Enable SSL by uncommenting lines in docker/nginx/conf.d/ssl-redirect.conf$(NC)"

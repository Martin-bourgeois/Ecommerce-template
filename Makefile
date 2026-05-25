.PHONY: help install setup clean test db-fresh db-seed docker-up docker-down docker-logs dev

help:
	@echo "E-Commerce Platform - Available Commands"
	@echo "========================================"
	@echo "install        - Install dependencies"
	@echo "setup          - Full setup (install, migrations, seeds)"
	@echo "clean          - Clean cache and logs"
	@echo "test           - Run tests"
	@echo "dev            - Start development server"
	@echo "db-fresh       - Refresh database"
	@echo "db-seed        - Seed database"
	@echo "docker-up      - Start Docker containers"
	@echo "docker-down    - Stop Docker containers"
	@echo "docker-logs    - View Docker logs"
	@echo "composer-update - Update Composer dependencies"
	@echo "ide-helper     - Generate IDE helper files"

install:
	docker-compose exec app composer install

setup:
	docker-compose exec app composer install
	docker-compose exec app cp .env.example .env
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan settings:seed
	docker-compose exec app php artisan db:seed
	docker-compose exec app npm install
	docker-compose exec app npm run build

clean:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan view:clear
	docker-compose exec app php artisan route:clear

test:
	docker-compose exec app php artisan test

dev:
	docker-compose exec app php artisan serve --host=0.0.0.0 --port=8000

db-fresh:
	docker-compose exec app php artisan migrate:fresh --force

db-seed:
	docker-compose exec app php artisan db:seed
	docker-compose exec app php artisan settings:seed

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down

docker-logs:
	docker-compose logs -f app

docker-logs-nginx:
	docker-compose logs -f nginx

docker-logs-postgres:
	docker-compose logs -f postgres

composer-update:
	docker-compose exec app composer update

ide-helper:
	docker-compose exec app composer ide-helper

artisan:
	docker-compose exec app php artisan $(ARGS)

tinker:
	docker-compose exec app php artisan tinker

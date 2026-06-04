dev:
	docker compose -f docker-compose.dev.yml up -d && php artisan serve --host=0.0.0.0 --port=8000

migrate:
	php artisan migrate
	
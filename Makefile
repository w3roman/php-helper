.PHONY: tests

default:
	@echo 'Enter command'

bash:
	docker compose run --rm php8.0-cli bash

build:
	docker compose build

tests: build
	docker compose run --rm php8.0-cli composer i
	docker compose run --rm php8.0-cli vendor/bin/phpunit --color=always tests/MainTest.php
	docker network prune -f

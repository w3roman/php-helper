.PHONY: tests

default:
	@echo 'Enter command'

sh:
	docker compose run --rm php8.0-cli sh

build:
	docker compose build

tests: build
	docker compose run --rm php8.0-cli composer i
	docker compose run --rm php8.0-cli vendor/bin/phpunit --color=always tests/MainTest.php
	docker compose run --rm php8.1-cli vendor/bin/phpunit --color=always tests/MainTest.php
	docker compose run --rm php8.2-cli vendor/bin/phpunit --color=always tests/MainTest.php
	docker compose run --rm php8.3-cli vendor/bin/phpunit --color=always tests/MainTest.php
	docker compose run --rm php8.4-cli vendor/bin/phpunit --color=always tests/MainTest.php
	docker network prune -f
t: tests

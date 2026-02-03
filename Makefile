help:			## Display help information.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

start:			## Start services
	docker compose up -d

test:			## Run tests. Params: {{ v=8.1 }}.
	PHP_VERSION=$(filter-out $@,$(v)) docker compose build --pull yii2-authclient-php
	PHP_VERSION=$(filter-out $@,$(v)) docker compose up -d
	PHP_VERSION=$(filter-out $@,$(v)) docker compose exec yii2-authclient-php sh -c "php -v && composer update && vendor/bin/phpunit --coverage-clover=coverage.xml"
	make down

build:			## Build an image from a docker-compose file. Params: {{ v=8.1 }}.
	PHP_VERSION=$(filter-out $@,$(v)) docker compose up -d --build

down:			## Stop and remove containers, networks
	docker compose down

sh:			## Enter the container with the application
	docker exec -it yii2-authclient-php-1 bash

static-analysis:	## Run code static analyze. Params: {{ v=8.1 }}.
	make build v=$(filter-out $@,$(v))
	PHP_VERSION=$(filter-out $@,$(v)) docker compose exec yii2-authclient-php sh -c "php -v && composer update && vendor/bin/phpstan analyse --memory-limit 512M"
	make down

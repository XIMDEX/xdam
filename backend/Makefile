UID=$(shell id -u)
GID=$(shell id -g)
DOCKER_PHP_SERVICE=xdam_php_fpm
DOCKER_SOLR_SERVICE=xdam_solr

export UID := ${UID}

fresh-start: erase build up composer-install sleep init

erase: clear-cache
	-docker-compose down -v --remove-orphans; exit 0;

clear-cache:
	-docker-compose run --rm --no-deps -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} php artisan cache:clear && php artisan route:clear && php artisan config:clear && php artisan view:clear && composer dump-autoload -o

prune: erase
	docker system prune --volumes
	docker image prune --all

build:
	docker pull mlocati/php-extension-installer
	docker-compose build && \
	docker-compose pull

composer-install:
	docker-compose run --rm --no-deps -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} composer install -o

init: migrate
	docker-compose exec ${DOCKER_SOLR_SERVICE} sh -c "\
	 solr create -c activity -n data_driven_schema_configs &&\
	 solr create -c assessment -n data_driven_schema_configs &&\
	 solr create -c course -n data_driven_schema_configs &&\
	 solr create -c multimedia -n data_driven_schema_configs &&\
	 solr create -c book -n data_driven_schema_configs\
	"
	-docker-compose exec -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} php artisan solr:install
	docker-compose exec -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} php artisan passport:install --force

migrate:
	docker-compose exec -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} php artisan migrate --seed

up:
	docker-compose up -d

stop:
	docker-compose stop

shell:
	docker-compose run --rm -u ${UID}:${GID} ${DOCKER_PHP_SERVICE} bash

logs:
	docker-compose logs -f ${DOCKER_PHP_SERVICE}

sleep:
	sync
	sleep 5

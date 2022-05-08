#!/bin/bash

OS = $(shell uname)
UID = $(shell id -u)
APP = symfony-blog-php-pfm
WEB = symfony-blog-web
DB = symfony-blog-db

help: ## Show this help message
	@echo 'usage: make [target]'
	@echo
	@echo 'targets:'
	@egrep '^(.+)\:\ ##\ (.+)' ${MAKEFILE_LIST} | column -t -c 2 -s ':#'

start: ## Start the containers
	docker network create network || true
	U_ID=${UID} docker-compose up -d

stop: ## Stop the containers
	U_ID=${UID} docker-compose stop

kill: ## Stop the containers
	U_ID=${UID} docker-compose kill

remove: ## Stop the containers
	U_ID=${UID} docker-compose rm

restart: ## Restart the containers
	$(MAKE) stop && $(MAKE) start

build: ## Rebuilds all the containers
	docker network create network || true
	U_ID=${UID} docker-compose build

composer: ## Install composer dependencies
	U_ID=${UID} docker exec -it --user ${UID} ${APP} sh -c "cd app/symfony && composer install"

ssh-php-fpm: ## bash into the be container
	U_ID=${UID} docker exec -it --user ${UID} ${APP} bash

ssh-db: ## bash into the be container
	U_ID=${UID} docker exec -it --user ${UID} ${DB} mysql -u root -proot database -h localhost

ssh-web: ## bash into the be container
	U_ID=${UID} docker exec -it --user ${UID} ${WEB} /bin/bash

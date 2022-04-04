#!/bin/bash

OS = $(shell uname)
UID = $(shell id -u)
APP = service-php-pfm
WEB = service-web
DB = service-db

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

ssh-php-fpm: ## bash into the be container
	U_ID=${UID} docker exec -it --user ${UID} ${APP} bash

ssh-db: ## bash into the be container
	U_ID=${UID} docker exec -it --user ${UID} ${DB} mysql -u root -proot database -h localhost

ssh-web: ## bash into the be container
	U_ID=${UID} docker exec -it --user ${UID} ${WEB} /bin/bash

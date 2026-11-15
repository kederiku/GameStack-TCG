SHELL := /bin/sh
.DEFAULT_GOAL := help
ifeq ($(shell uname -s),Linux)
export DOCKER_UID ?= $(shell id -u)
export DOCKER_GID ?= $(shell id -g)
endif
DC := docker compose
RUN := $(DC) run --rm --no-deps app
EXEC := $(DC) exec app
ARGS ?=

.PHONY: help env init up down stop restart logs ps admin artisan composer npm shell migrate test check build assets format storage-init storage-password

help: ## Afficher les commandes disponibles
	@awk 'BEGIN {FS = ":.*## "} /^[a-zA-Z_-]+:.*## / {printf "  %-12s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

env: ## Créer .env s’il n’existe pas
	@test -f .env || cp .env.example .env

init: env ## Installer le projet puis démarrer tous les services
	$(DC) build app
	$(DC) stop app vite queue
	$(DC) up -d --wait postgres mailpit
	$(RUN) composer install --no-interaction --prefer-dist
	$(RUN) php artisan config:clear
	$(RUN) php docker/initialize-key.php
	$(MAKE) storage-init
	$(RUN) php artisan migrate --force
	$(RUN) sh -c 'test -L public/storage || php artisan storage:link'
	$(RUN) npm ci
	$(RUN) npm run build
	$(DC) up -d --wait

up: env storage-init ## Démarrer les services (après make init)
	$(DC) up -d --wait

storage-init: env ## Initialiser Garage sans effacer les objets ni renouveler les accès
	$(RUN) php docker/garage/configure.php
	$(DC) up -d --wait garage
	$(RUN) php docker/garage/initialize.php
	$(RUN) php artisan config:clear

storage-password: env ## Renouveler le mot de passe Web UI (fichier privé garage/login.txt)
	$(RUN) php docker/garage/configure.php --password
	$(DC) up -d --no-deps --force-recreate garage-webui

down: ## Arrêter les services en conservant la base et les images
	$(DC) down --remove-orphans
	@rm -f public/hot

stop: ## Suspendre les services sans supprimer les conteneurs
	$(DC) stop
	@rm -f public/hot

restart: ## Redémarrer les services
	$(DC) restart

logs: ## Suivre les logs (ARGS=app pour un service)
	$(DC) logs -f $(ARGS)

ps: ## Afficher l’état des services
	$(DC) ps

admin: ## Créer un administrateur (saisie interactive)
	$(EXEC) php artisan app:create-admin

artisan: ## Exécuter Artisan : make artisan ARGS='route:list'
	$(EXEC) php artisan $(ARGS)

composer: ## Exécuter Composer : make composer ARGS='show'
	$(RUN) composer $(ARGS)

npm: ## Exécuter npm : make npm ARGS='run types:check'
	$(RUN) npm $(ARGS)

shell: ## Ouvrir un shell dans l’application
	$(EXEC) bash

migrate: ## Exécuter les migrations
	$(EXEC) php artisan migrate

test: ## Exécuter les tests dans une base PostgreSQL dédiée
	$(RUN) php artisan config:clear
	$(RUN) php artisan test $(ARGS)

check: ## Vérifier PHP, frontend, types et tests
	$(RUN) composer test
	$(RUN) npm run check
	$(RUN) php artisan wayfinder:generate --with-form
	$(RUN) npm run types:check

build: ## Construire l’image Docker
	$(DC) build app

assets: ## Compiler les assets de production
	$(RUN) npm run build

format: ## Formater PHP et frontend
	$(RUN) composer lint
	$(RUN) npm run check:fix

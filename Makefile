#!/usr/bin/make
SHELL = /bin/sh

docker_bin := $(shell command -v docker 2> /dev/null)
# Путь к docker-compose или docker compose в зависимости от наличия
docker_compose_bin := $(shell command -v docker-compose 2> /dev/null)

ifndef docker_compose_bin
	docker_compose_bin := docker compose
endif

APP_DIR := ./app
APP_CONTAINER_NAME := app
PHP_CLI_RUN := $(docker_compose_bin) run --rm $(APP_CONTAINER_NAME) php




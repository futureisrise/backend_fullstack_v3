include .env

docker_php = frozeneon-php
docker_mysql = frozeneon-mysql

MYSQL_DUMPS_DIR=./db_dump

help:
	@echo ""
	@echo "usage: make COMMAND"
	@echo ""
	@echo "Commands:"
	@echo "  clean               Clean directories for reset"
	@echo "  composer-up         Update PHP dependencies with composer"
	@echo "  init                Cleanup data files and reinit project"
	@echo "  docker-start        Create and start containers"
	@echo "  docker-stop         Stop all services"
	@echo "  gen-certs           Generate SSL certificates"
	@echo "  logs                Follow log output"
	@echo "  mysql-init          Init database"

init:
	@make clean
	@make docker-start
	@echo "[$$(date '+%Y-%m-%d %H:%M:%S')] Wait 25 seconds to initialize MySQL"
	@sleep 25
	@make mysql-init
	@make composer-up

clean:
	-@sudo docker rm $$(docker stop frozeneon-nginx)
	-@sudo docker rm $$(docker stop frozeneon-php)
	-@sudo docker rm $$(docker stop frozeneon-phpmyadmin)
	-@sudo docker rm $$(docker stop frozeneon-mysql)
	-@sudo rm -Rf data/db/*

composer-up:
	@sudo docker exec -u root -i -w /var/www/html/application $(docker_php) composer install --prefer-source --no-interaction

docker-start:
	@sudo docker-compose up -d

docker-stop:
	@sudo docker-compose --env-file .env stop

gen-certs:
	@sudo docker run --rm -v $(shell pwd)/etc/ssl:/certificates -e "SERVER=$(NGINX_HOST)" jacoelho/generate-certificate

logs:
	@sudo docker-compose logs -f

mysql-init:
	@sudo docker exec -i $(docker_mysql) mysql -u"$(MYSQL_ROOT_USER)" -p"$(MYSQL_ROOT_PASSWORD)" test_task < $(MYSQL_DUMPS_DIR)/init_db.sql

.PHONY: clean init help

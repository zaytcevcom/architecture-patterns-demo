pull: pull-git pull-composer pull-migrate pull-cache pull-restart

pull-git:
	git pull

pull-composer:
	#sudo docker compose -f docker-compose-production.yml run --rm php-cli composer install
	/www/server/php/82/bin/php -n composer.phar install --no-interaction --ignore-platform-reqs

pull-migrate:
	sudo docker compose -f docker-compose-production.yml run --rm php-cli composer app migrations:migrate
	#/www/server/php/82/bin/php -n composer.phar app-server migrations:migrate

pull-cache:
	#sudo rm -rf var/cache/* var/log/*
	#sudo chmod -R 777 var/cache var/log
	sudo docker compose -f docker-compose-production.yml run --rm php-cli composer app orm:clear-cache:metadata
	sudo docker compose -f docker-compose-production.yml run --rm php-cli composer app orm:clear-cache:query
	sudo docker compose -f docker-compose-production.yml run --rm php-cli composer app orm:clear-cache:result
	sudo docker compose -f docker-compose-production.yml run --rm php-cli composer app orm:generate-proxies
	sudo chown -R www var/cache var/log


pull-restart:
	sudo docker compose -f docker-compose-production.yml down --remove-orphans
	sudo docker compose -f docker-compose-production.yml up -d

init: init-ci
init-ci: docker-down-clear \
	app-clear \
	docker-pull docker-build docker-up \
	app-init

up: docker-up
down: docker-down
restart: down up

#linter and code-style
lint: app-lint
analyze: app-analyze
validate-schema: app-db-validate-schema
cs-fix: app-cs-fix
test: app-test

update-deps: app-composer-update

#check all
check: lint analyze validate-schema test

#Docker
docker-up:
	docker compose up -d

docker-down:
	docker compose down --remove-orphans

docker-down-clear:
	docker compose down -v --remove-orphans

docker-pull:
	docker compose pull

docker-build:
	docker compose build --pull

app-clear:
	docker run --rm -v ${PWD}/:/app -w /app alpine sh -c 'rm -rf var/cache/* var/log/* var/test/*'


#Composer
app-init: app-permissions app-composer-install #app-wait-db app-db-migrations #app-db-fixtures

app-permissions:
	docker run --rm -v ${PWD}/:/app -w /app alpine chmod 777 var/cache var/log var/test

app-composer-install:
	docker compose run --rm php-cli composer install

app-composer-update:
	docker compose run --rm php-cli composer update

app-composer-autoload: #refresh autoloader
	docker compose run --rm php-cli composer dump-autoload

app-composer-outdated: #get not updated
	docker compose run --rm php-cli composer outdated

app-wait-db:
	docker compose run --rm php-cli wait-for-it db:3306 -t 30


#DB
app-db-validate-schema:
	docker compose run --rm php-cli composer app orm:validate-schema

app-db-migrations-diff:
	docker compose run --rm php-cli composer app migrations:diff

app-db-migrations:
	docker compose run --rm php-cli composer app migrations:migrate -- --no-interaction

app-db-fixtures:
	docker compose run --rm php-cli composer app fixtures:load


#Lint and analyze
app-lint:
	docker compose run --rm php-cli composer lint
	docker compose run --rm php-cli composer php-cs-fixer fix -- --dry-run --diff

app-cs-fix:
	docker compose run --rm php-cli composer php-cs-fixer fix

app-analyze:
	docker compose run --rm php-cli composer psalm


#Tests
app-test:
	docker compose run --rm php-cli composer test

app-test-coverage:
	docker compose run --rm php-cli composer test-coverage

app-test-unit:
	docker compose run --rm php-cli composer test -- --testsuite=unit

app-test-unit-coverage:
	docker compose run --rm php-cli composer test-coverage -- --testsuite=unit

app-test-functional:
	docker compose run --rm php-cli composer test -- --testsuite=functional

app-test-functional-coverage:
	docker compose run --rm php-cli composer test-coverage -- --testsuite=functional

#Console
console:
	docker compose run --rm php-cli composer app

console-dev-token:
	docker compose run --rm php-cli composer app oauth:e2e-token

token:
	docker compose run --rm php-cli composer app oauth:token

#Steps deploy:
#0 - docker login -u dockerhub -p dockerhub dockerhub.zay.media
#1 - REGISTRY=dockerhub.zay.media IMAGE_TAG=master-1 make build
#2 - REGISTRY=dockerhub.zay.media IMAGE_TAG=master-1 make push
#3 - HOST=deploy@185.46.9.149 PORT=22 REGISTRY=dockerhub.zay.media IMAGE_TAG=master-1 BUILD_NUMBER=1 make deploy

#docker compose run --rm php-cli composer require monolog/monolog

#docker compose run --rm php-cli composer outdated --direct - просмотр мажорных обновлений
#docker compose run --rm php-cli composer update --with-dependencies vimeo/psalm
#docker compose run --rm php-cli composer require --with-all-dependencies vimeo/psalm - установка с зависимостями
#docker compose run --rm php-cli composer why psr/container - почему не можем обновиться
#docker compose run --rm php-cli composer why-not psr/container 2 - почему не указанной версии

#docker compose run --rm php-cli composer app migrations:diff

#запуск процесса
#docker compose run --rm php-cli php bin/app.php push:receiver
#docker compose run --rm php-cli composer app push:receiver
#поиск и удаление процесса
#ps aux | grep "push:receiver"
#kill -KILL [PID]

# --- SCREEN ---------------------------
# screen -ls
# screen -r
# screen -d -RR - когда не получается перейти в процесс

# --- SCRIPTS ---------------------------
# push:receiver
# push-voip:receiver
# oauth:clear-expired
# features:bots
# sudo /www/server/php/82/bin/php composer.phar app-server oauth:clear-expired

# --- DEPLOY ---------------------------
# cd /www/wwwroot/APIDEV
# sudo rm -rf var/cache/* var/log/*
# sudo screen -S push-receiver /www/server/php/82/bin/php composer.phar app-server push:receiver
# sudo /www/server/php/82/bin/php composer.phar app-server migrations:migrate
# sudo screen -S stream /www/server/php/82/bin/php composer.phar app-server stream

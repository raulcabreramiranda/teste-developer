include .env

.PHONY: list
list:
	@echo "  "
	@echo "  start        > Ativar todos os contenedores de Kong, User Api, Order Api"
	@echo "  stop         > Deter e apagar todos os contenedores de Kong, User Api, Order Api"
	@echo "  start_konga  > Instalar Kong Gateway"
	@echo "  register     > Register Servicos no Gateway Kong. Tem que configurar o .env"
	@echo ""

.PHONY: start_konga
start_konga:
	docker run -p 1337:1337  --name konga  -e "NODE_ENV=production"  -e "TOKEN_SECRET={{somerandomstring}}"   pantsel/konga

.PHONY: start
start:
	@docker-compose -f ./docker-compose.yml up -d
	@docker-compose -f ./user-api/docker-compose.yml up -d
	@docker-compose -f ./order-api/docker-compose.yml up -d


.PHONY: register
register:
	@docker exec teste-user_api-web php artisan kong:un   ${KONG_PATH}
	@docker exec teste-order_api-web php artisan kong:un  ${KONG_PATH}
	@docker exec teste-user_api-web php artisan kong:re   ${KONG_PATH} ${API_USERS_PATH}
	@docker exec teste-order_api-web php artisan kong:re  ${KONG_PATH} ${API_ORDERS_PATH}


.PHONY: config
config:
	@docker exec teste-user_api-web php artisan migrate
	@docker exec teste-order_api-web php artisan migrate
	@docker exec teste-user_api-web composer test
	@docker exec teste-order_api-web composer test


.PHONY: stop
stop:
	@docker stop teste-kong-kong
	@docker stop teste-kong-migrations
	@docker stop teste-kong-db
	@docker stop teste-user_api-web
	@docker stop teste-user_api-redis
	@docker stop teste-user_api-postgres
	@docker stop teste-user_api-postgres_teste
	@docker stop teste-user_api-elasticsearch
	@docker stop teste-order_api-web
	@docker stop teste-order_api-redis
	@docker stop teste-order_api-postgres
	@docker stop teste-order_api-postgres_teste
	@docker stop teste-order_api-elasticsearch
	@docker rm teste-kong-kong
	@docker rm teste-kong-migrations
	@docker rm teste-kong-db
	@docker rm teste-user_api-web
	@docker rm teste-user_api-redis
	@docker rm teste-user_api-postgres
	@docker rm teste-user_api-postgres_teste
	@docker rm teste-user_api-elasticsearch
	@docker rm teste-order_api-web
	@docker rm teste-order_api-redis
	@docker rm teste-order_api-postgres
	@docker rm teste-order_api-postgres_teste
	@docker rm teste-order_api-elasticsearch

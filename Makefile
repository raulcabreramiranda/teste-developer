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
	@docker exec serasa-user_api-web php artisan kong:un   ${KONG_PATH}
	@docker exec serasa-order_api-web php artisan kong:un  ${KONG_PATH}
	@docker exec serasa-user_api-web php artisan kong:re   ${KONG_PATH} ${API_USERS_PATH}
	@docker exec serasa-order_api-web php artisan kong:re  ${KONG_PATH} ${API_ORDERS_PATH}


.PHONY: config
config:
	@docker exec serasa-user_api-web php artisan migrate
	@docker exec serasa-order_api-web php artisan migrate
	@docker exec serasa-user_api-web composer test
	@docker exec serasa-order_api-web composer test


.PHONY: stop
stop:
	@docker stop serasa-kong-kong
	@docker stop serasa-kong-migrations
	@docker stop serasa-kong-db
	@docker stop serasa-user_api-web
	@docker stop serasa-user_api-redis
	@docker stop serasa-user_api-postgres
	@docker stop serasa-user_api-postgres_teste
	@docker stop serasa-user_api-elasticsearch
	@docker stop serasa-order_api-web
	@docker stop serasa-order_api-redis
	@docker stop serasa-order_api-postgres
	@docker stop serasa-order_api-postgres_teste
	@docker stop serasa-order_api-elasticsearch
	@docker rm serasa-kong-kong
	@docker rm serasa-kong-migrations
	@docker rm serasa-kong-db
	@docker rm serasa-user_api-web
	@docker rm serasa-user_api-redis
	@docker rm serasa-user_api-postgres
	@docker rm serasa-user_api-postgres_teste
	@docker rm serasa-user_api-elasticsearch
	@docker rm serasa-order_api-web
	@docker rm serasa-order_api-redis
	@docker rm serasa-order_api-postgres
	@docker rm serasa-order_api-postgres_teste
	@docker rm serasa-order_api-elasticsearch

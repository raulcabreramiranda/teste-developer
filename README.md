# Teste para analista desenvolvedor

Olá, obrigado pela oportunide.   

Com este teste tente demostrar mis conhecimentos mesmo faltando alguns pontos.

No mesmo como foi solicitado criação de dois microserviços um de pedidos e outro de usuarios. 
Para o acesso aos serviços foi utilizado Kong como gateway no mesmo foi implentado um serviço muito simple de autenticação a travez de keys so que bem simple.
Falatando a segurança entre o gateway e os serviços.
Para executar o codigo foi feito um arquivo Makefile 
o mesmo conta com os comandos (list, start, stop, start_konga)

Logo de executar make start para iniciar os contenedores dois microserviçoes e o gateway Kong. tem que rodar dois comandos para registrar os micro-serviços no Kong.
Dentro de cada servico foi creado um CRUD alem de um servico de pesquisa inteligente usando o ElasticSearch. 
E dentro do servico pedido foi criado uma endpoint para pesquisar por nome de usuario.  

## Passos para a instalação

- Configurar o .env com o endereço do Kong e das API
- make start
- make config
- make register



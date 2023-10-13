# Open Food Facts - CHALLENGE - 20200916

### Esta API foi desenvolvida como parte deste desafio para criar uma REST API destinada a utilizar os dados do projeto Open Food Facts, um banco de dados de informações nutricionais de diversos produtos alimentícios. Seu principal propósito é oferecer suporte eficaz à equipe de nutricionistas da empresa Fitness Foods LC.

# Tecnologias. 

-   [PHP](https://www.php.net/docs.php)
-   [Laravel](https://laravel.com/)
-   [MySQL](https://dev.mysql.com/doc/)
-   [Docker](https://docs.docker.com/)

# Como configurar.
* Inicialmente, é necessário fazer a instalação do Docker, para que possamos instalar as dependencias necessárias para o nosso projeto. Caso não tenha o docker, vamos instalar passo a passo:

* Rode esse comando para instalar alguns pacotes necessários:

```sh
$ sudo apt-get install  curl apt-transport-https ca-certificates software-properties-common
```

* Agora configure os repositórios do Docker. Rode este comando para configurar a chave GPG:

```sh
$ curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
```

* Adicione o repositório:

```sh
$ sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
```

* Atualize as informações do repositório:

```sh
$ sudo apt update
```

* Instale o Docker: 

```sh
$ sudo apt install docker-ce
```

*  Adicione o usuário atual ao grupo "docker" com permissões de administração.

```sh
$ sudo usermod -aG docker $USER
```

* Entre na pasta do projeto e use o seguinte comando para instalar as dependencias:

```sh
$ ./vendor/bin/sail up
```

* Após a instalação, faça uma cópia do arquivo .env.example para .env e cofigure essa área de acordo com suas credenciais de banco de dados.

```sh
DB_CONNECTION=mysql
DB_HOST=(ip database)
DB_PORT=(porta database)
DB_DATABASE=(seu database)
DB_USERNAME=(seu usuario)
DB_PASSWORD=(sua senha)
```

* Crie o schema no banco de dados com o mesmo nome do DB_DATABASE do do arquivo .env.

* Agora faça a migração das tabelas do banco de dados:
```sh
$ ./vendor/bin/sail artisan migrate
```

# Para visualizar documentação das rotas.

* Rode o seguinte comando para gerar a documentação:

```sh
$ ./vendor/bin/sail artisan scribe:generate
```

* Agora acesse a rota web docs do laravel para visualizar:

  ```
    exemplo: localhost/docs
  ```

# Instruções para configurar o CRON.

* Em seu terminal linux insira o seguinte comando:
  ```
    sudo nano /etc/crontab
  ```
  
* Na ultima linha insira:

    ```
    30  3    * * *   root    cd (caminho onde está o seu projeto) && php artisan seedTables:add >> /dev/null 2>&1
    ```

# Instruções para executar os testes

* Crie um arquivo chamado "database.sqlite" dentro da pasta database.

* Faça uma copia da .env com o nome de .env.testing e sincronize com o database sqlite.
  
```sh
DB_CONNECTION=sqlite
DB_DATABASE=database/databse.sqlite
```

* Rode o comando a seguir para executar os testes:

```sh
$ php artisan test --env=env.testing
```

# Endpoints

| Endpoint                             | Response                                                                                                                                                             | Body                               |
| ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------ |
| GET /                                | Retorna detalhes da API, se conexão leitura e escritura com a base de dados está OK, horário da última vez que o CRON foi executado, tempo online e uso de memória. |                                                  |
| PUT /products/{codigo do produto}    | Retorna o produto com as alterações feita                                                                                                                           | {json com dados de alteração} |
| DELETE /products/{codigo do produto} | Retorna o produto no qual o status foi alterado para "trash"                                                                                                        |                                                  |
| GET /products/{codigo do produto}    | Retorna o produto compativel com o codigo enviado                                                                                                                   |                                                  |
| GET /products                        | Retorna todos os produtos cadastrados no banco de dados                                                                                                             |
|                                      |

## Pré-requisitos

``` sudo apt install docker && sudo apt install docker-compose ```

## Instalação

``` cp .env.example .env ```

### Env config
```
DB_CONNECTION=mysql
DB_HOST=db_sgp
DB_PORT=3306
DB_DATABASE=sgp
DB_USERNAME=root
DB_PASSWORD=Aq!sw2de3
```

## Setup

``` docker-compose up -d ```

## Dentro do app_sgp docker container

``` php artisan key:generate & composer install & php artisan migrate --seed ```

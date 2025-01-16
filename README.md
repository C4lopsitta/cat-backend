# cat-backend

## Running server
You will need `docker` and `docker-compose`, these instructions are made for newer 
versions of `docker-compose` that use the command `docker compose` instead of the 
older one with the dash. To run the project, use the following command:
```bash
$ docker compose up -d --build
```
If you don't want to run the compose as a daemon, remove the `-d` instruction.

## Docker structure
- nginx web server
- php-fpm server
- mariadb database

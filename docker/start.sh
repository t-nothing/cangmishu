#!/usr/bin/env sh

./env.py

docker-compose build 

docker-compose up -d

cp ./www/index.php ../www/

cat .env

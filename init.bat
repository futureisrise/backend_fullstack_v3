@echo off

echo ----------------- Stop and remove containers -----------------

FOR /F %%d IN ('docker stop frozeneon-nginx') DO (docker rm %%d)
FOR /F %%d IN ('docker stop frozeneon-php') DO (docker rm %%d)
FOR /F %%d IN ('docker stop frozeneon-phpmyadmin') DO (docker rm %%d)
FOR /F %%d IN ('docker stop frozeneon-mysql') DO (docker rm %%d)

echo ---------------- Rename mysql data folder --------------------

rename .\data\db db_temp

echo ------------------------ Build docker ------------------------

docker-compose up -d

echo ------------ Wait 25 seconds to initialize MySQL -------------

timeout /t 25

docker exec -i frozeneon-mysql mysql -u"root" -p"root" test_task < ./db_dump/init_db.sql

echo ---------------------- Composer install ----------------------

docker exec -u root -i -w /var/www/html/application frozeneon-php composer install --prefer-source --no-interaction

echo -------------- Rempve mysql data temp folder -----------------

rmdir .\data\db_temp /s /q
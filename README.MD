# Инструкция по сборке и запуску контейнера

### 1. Сборка контейнера

Для сборки контейнера используйте следующую команду с указанием версии Docker Compose:

```bash
docker-compose --version  # Проверить версию Docker Compose

```
+е сборку с флагом --build:
```bash
docker-compose up --build

``````

### 2. Запуск контейнера в фоновом режиме
```bash
docker-compose up -d
```
### 3. Установка прав на папку проекта
```bash
sudo chmod -R 755 /var/www/wordpress-plugins  # Устанавливаем права на папку
sudo chown -R $USER:www-data /var/www/wordpress-plugins  # Назначаем владельца

```
### 4. Вход в контейнер и запуск зависимостей
```bash
docker exec -it ${CONTAINER_PREFIX}_wordpress bash
docker exec -it wordpress-plugins_wordpress bash

composer install
```


### 5. Пересборка контейнера
```bash
docker-compose build
```
### 6. Проверка установки WordPress
```bash
wp core version

```

### 7. Загрузка базы
1. Скопируйте дамп в контейнер
```bash
docker cp /home/user/Downloads/wordpress-plugins.sql wordpress-plugins_db:/wordpress-plugins.sql
```
2. Зайдите внутрь контейнера
```bash
docker exec -it wordpress-plugins_db bash
```
3. Импортируйте базу в MySQL
```bash
mysql -u root -p wordpress < /wordpress-plugins.sql
```
4. Сменить домен
```bash
mysql -u root -p 
use wordpress;

UPDATE wp_options SET option_value = replace(option_value, 'https://api-blogency.adaurum.ru', 'http://localhost:8000') WHERE option_name = 'home' OR option_name = 'siteurl';
UPDATE wp_options SET option_value = replace(option_value, 'https://api-blogency.adaurum.ru', 'http://localhost:8000') WHERE option_name = 'blogname';
UPDATE wp_posts SET guid = replace(guid, 'https://api-blogency.adaurum.ru','http://localhost:8000');
UPDATE wp_posts SET post_content = replace(post_content, 'https://api-blogency.adaurum.ru', 'http://localhost:8000');
```
5. Очистите временный файл (по желанию)
```bash
rm /wordpress-plugins.sql
```

### Заход под root в контейнер
```bash
docker exec -it -u root <container_id> bash
docker exec -it -u root wordpress-plugins_wordpress bash
```	
Использовать wp придеться с флагом  --allow-root

### Route
Веротность, что не хватит оперативной памяти
```bash
wp eval 'print_r( rest_get_server()->get_routes() );' --allow-root
```	



# Production

Start redis

	cd redis 

copy env file

	cp .env.example .env

	docker-compose up -d

create .env in /
add variables

	composer install
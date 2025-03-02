FROM wordpress:6.7.1-php8.3-apache
# Устанавливаем пользователя root для выполнения операций с apt-get
USER root  
# Установка зависимостей
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    git \
    libzip-dev \
    && apt-get clean


# Проверка, установлено ли расширение Redis, и установка только если оно отсутствует
RUN if ! pecl list | grep -q redis; then \
        pecl install redis; \
    fi \
    && docker-php-ext-enable redis
# Установка Composer.Перед созданием символической ссылки проверяется, существует ли файл
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    if [ ! -f /usr/bin/composer ]; then ln -s /usr/local/bin/composer /usr/bin/composer; fi


# Установка WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

USER 1000 
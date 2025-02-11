FROM yiisoftware/yii2-php:8.3-apache-min

ARG USER_ID
ARG GROUP_ID

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip sudo libpng-dev \
    && docker-php-ext-install pdo_mysql zip gd

# Увеличиваем лимит памяти (PhpSpreadsheet требует больше при обработке больших XLSX-файлов.)
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/custom.ini

# Создаем группу и пользователя с правами хоста
RUN groupadd -g ${GROUP_ID} hostgroup \
    && useradd -u ${USER_ID} -g hostgroup -m -s /bin/bash hostuser

# Даем права пользователю на файлы проекта
RUN chown -R hostuser:hostgroup /var/www/html

# Настройка Apache
RUN a2enmod rewrite

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Разрешаем запуск от имени hostuser
USER hostuser

WORKDIR /var/www/html

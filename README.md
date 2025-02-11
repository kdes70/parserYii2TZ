# Yii2 Parser this table for sheet MA + Docker Development Environment

## 📌 Описание
Данный проект предоставляет готовое окружение для разработки на Yii2 с использованием Docker. Включает PHP 8.3, Apache, MariaDB 10.11 и Composer.

## 🚀 Быстрый старт

### 1. Установите зависимости
Перед началом убедитесь, что у вас установлены:
- **Docker** (https://docs.docker.com/get-docker/)
- **Docker Compose** (входит в состав Docker)

### 2. Создайте `.env` файл
В корневой директории создайте файл `.env` и добавьте туда:
```ini
UID=$(id -u)
GID=$(id -g)
```
Это позволит передавать UID и GID вашего пользователя в контейнер для корректных прав на файлы.

### 3. Соберите и запустите контейнеры
```sh
docker compose up -d --build
```
Контейнеры:
- **app** (PHP 8.3 + Apache)
- **db** (MariaDB 10.11)

Проект будет доступен по адресу: [http://localhost:8888](http://localhost:8888)

### 4. Установка зависимостей
Выполните команду внутри контейнера:
```sh
docker compose exec app composer install
```

### 5. Примените миграции базы данных
```sh
docker compose exec app php yii migrate
```

## 🔄 Основные команды

### Запуск и остановка контейнеров
```sh
docker compose up -d  # Запуск в фоне
docker compose down   # Остановка контейнеров
```

### Вход в контейнер
```sh
docker compose exec app bash
```

### Запустить парсер
```sh
docker-compose exec app ./yii parse
```

### Очистка кеша
```sh
docker compose exec app php yii cache/flush-all
```

### Запуск тестов
```sh
docker compose exec app vendor/bin/codecept run
```

### Логи контейнеров
```sh
docker compose logs -f
```

## 🔧 Полезные настройки

### Пересборка контейнеров
Если произошли изменения в `Dockerfile` или `docker-compose.yml`, пересоберите контейнер:
```sh
docker compose build --no-cache
```

### Удаление контейнеров и данных БД
```sh
docker compose down --volumes
```


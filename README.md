# individual-work

Данная лабораторная работа посвящена разработке и развертыванию web-приложения для системы электронной подписи документов. Проект реализован на RestAPI, оркестрации с помощью Docker Compose, а также включает средства мониторинга и логирования. В качестве фронтенда используется Vue.js, а в качестве бэкенда — Laravel. В системе реализована интеграция с MinIO (объектное хранилище), Redis (очередь и кэш), PostgreSQL (основная база данных), а также инструменты мониторинга: Grafana, Prometheus, Jaeger, Loki и OpenTelemetry.

## 1. В infrastructure и в vsign, из .env.example создаем .env
## 2. Далее собераем все сервисные контейнеры из двух docker compose в infrastructure и vsign
```bash
// infrastructure/
docker compose --profile monitor up --build --watch

// vsign/docker
docker compose up --build --watch
```
## 3. Далее в случае проблем с копированием vsign_boot.sh, внутри vsign контейнера вызываем vsign_boot.sh
```bash
docker exec vsign ./vsign_boot.sh
```
Очень важно удостовериться что вы запустили этот скрипт: 
> он копирует статику в контейнер caddy в shared_volume, 
> и билдит frontend, 
> а так же кеширует данные laravel приложения.

## 4. По желанию можно создать отдельный бакет для minio, а не использовать стандартный, и изменить .env приложения
```
AWS_BUCKET=attachments <-- Заменить на кастомный бакет по желанию
```

## 5. Проверка что все успешно.

> 1. После этих шагов должна быть запущена infrastructure(email, minio, grafana, jaeger, prometheus, loki, caddy)
> 2. Контейнеры vsign приложения(**vsign, queue, redis, postgres**) 
> 3. Нужно убедиться что все сервисы запущенны и работают коректно а так же vsign должен находиться в одной сети с caddy для проксирования запросов
> 4. В особенности проверить что запущен queue_worker так как он отвечает за отправку smpt and webhook notifications

---
## Примечание
> 1. Креды по умолчанию для сервисов по типу grafana, minio можно узнать в docker compose, infrastructure
> 2. docker compose infrastructure желательно запускать первым, но не обязательно
> 3. Все сервисы доступны через домены с их названием + localdev.me:8443 пример сервис email(smtp) -> https://email.localdev.me:8443
> исключение https://web.minio.localdev.me:8443
- Интеграция с другим сервисом, изначально приложение было как сервис 
 для подписи для одного другого сервиса поэтому тут возможность для настройки 
 только одного вебхука, настройки вебхука задаются так:

```
DOCUMENT_SIGNED_WEBHOOK_ENABLED=true
DOCUMENT_SIGNED_WEBHOOK_URL=https://vaip.localdev.me:8443/api/v1/projects/receive_signed_report
DOCUMENT_SIGNED_WEBHOOK_TIMEOUT=30
DOCUMENT_SIGNED_WEBHOOK_RETRY_ATTEMPTS=3
DOCUMENT_SIGNED_WEBHOOK_RETRY_DELAY=60
```

## **Функциональные возможности**

* Загрузка, хранение и отображение документов.
* Подписание документов через встроенный механизм.
* Хранение подписанных документов в MinIO.
* Отправка email-уведомлений
* Ведение истории изменений и логирования событий.
* Поддержка OpenTelemetry для трассировки, логов и метрик.

## API

### Public Routes

| Method | Endpoint             | Name                           | Description                             |
|--------|----------------------|--------------------------------|-----------------------------------------|
| GET    | `/public-key`        | `public-key.get-public-key`    | Получить публичный ключ                 |
| POST   | `/sign-request`      | `signature-request.initialize` | Инициализировать запрос на подпись      |
| GET    | `/total-users`       | `total-users.get`              | Получить общее количество пользователей |
| GET    | `/documents-signed`  | `document-signed.get`          | Получить количество подписанных документов |

---

### Authentication (Guest Only)

| Method | Endpoint                     | Name                               | Description                                     |
|--------|------------------------------|------------------------------------|-------------------------------------------------|
| GET    | `/auth/github/redirect`      | `auth.redirect-to-github`         | Перенаправление на авторизацию через GitHub    |
| GET    | `/auth/github/callback`      | `auth.handle-github-callback`     | Обработка обратного вызова GitHub OAuth        |

---

### Authentication (Authenticated Users)

| Method | Endpoint        | Name         | Description                |
|--------|-----------------|--------------|----------------------------|
| GET    | `/user`         | `auth.user`  | Получить информацию о пользователе |

---

### Documents (Authenticated Users)

| Method | Endpoint                      | Name                      | Description                        |
|--------|-------------------------------|---------------------------|------------------------------------|
| GET    | `/documents/to-sign`          | `documents.to-sign`       | Получить список документов на подпись |
| POST   | `/documents/{document}/sign`  | `documents.sign`          | Подписать документ                 |

## Структура базы данных

### Пользователи

| Поле                | Тип                | Описание                          |
|---------------------|--------------------|-----------------------------------|
| id                  | BIGINT, PK         | Уникальный идентификатор          |
| name                | STRING             | Имя пользователя                  |
| email               | STRING, UNIQUE     | Email пользователя (может быть null) |
| email_verified_at   | TIMESTAMP, NULL    | Дата подтверждения email          |
| password            | STRING             | Хэш пароля                        |
| remember_token      | STRING, NULL       | Токен для "запомнить меня"        |
| github_id           | STRING, UNIQUE     | GitHub ID (nullable)              |
| created_at / updated_at | TIMESTAMPS     | Дата создания и обновления       |

---

### Таблица сброса паролей

| Поле      | Тип     | Описание                   |
|-----------|----------|----------------------------|
| email     | STRING, PK | Email пользователя         |
| token     | STRING     | Токен сброса пароля        |
| created_at| TIMESTAMP, NULL | Дата создания записи    |

---

### Сессии

| Поле         | Тип            | Описание                     |
|--------------|----------------|------------------------------|
| id           | STRING, PK     | ID сессии                    |
| user_id      | BIGINT, FK     | ID пользователя (nullable)   |
| ip_address   | STRING(45)     | IP-адрес                     |
| user_agent   | TEXT, NULL     | User-Agent                   |
| payload      | LONGTEXT       | Данные сессии                |
| last_activity| INTEGER        | Метка времени последней активности |

---

### Кэш

| Поле      | Тип         | Описание               |
|-----------|-------------|------------------------|
| key       | STRING, PK  | Ключ                   |
| value     | MEDIUMTEXT  | Значение               |
| expiration| INTEGER     | Время истечения        |

---

### Блокировки кэша

| Поле      | Тип         | Описание               |
|-----------|-------------|------------------------|
| key       | STRING, PK  | Ключ                   |
| owner     | STRING      | Владелец блокировки    |
| expiration| INTEGER     | Время истечения        |

---

### Очередь заданий

| Поле         | Тип               | Описание                    |
|--------------|-------------------|-----------------------------|
| id           | BIGINT, PK        | ID задания                  |
| queue        | STRING, INDEX     | Название очереди            |
| payload      | LONGTEXT          | Данные задания              |
| attempts     | UNSIGNED TINYINT  | Кол-во попыток              |
| reserved_at  | UNSIGNED INT, NULL| Метка времени резерва       |
| available_at | UNSIGNED INT      | Когда доступно              |
| created_at   | UNSIGNED INT      | Когда создано               |

---

### Пакеты заданий

| Поле            | Тип           | Описание                        |
|------------------|---------------|---------------------------------|
| id               | STRING, PK    | Идентификатор пакета            |
| name             | STRING        | Имя пакета                      |
| total_jobs       | INTEGER       | Всего заданий                   |
| pending_jobs     | INTEGER       | Ожидающих заданий               |
| failed_jobs      | INTEGER       | Проваленных заданий             |
| failed_job_ids   | LONGTEXT      | Список ID проваленных заданий   |
| options          | MEDIUMTEXT, NULL | Доп. опции                    |
| cancelled_at     | INTEGER, NULL | Отменено в                     |
| created_at       | INTEGER       | Создано                        |
| finished_at      | INTEGER, NULL | Завершено                      |

---

### Проваленные задания

| Поле       | Тип         | Описание                  |
|------------|-------------|---------------------------|
| id         | BIGINT, PK  | ID записи                 |
| uuid       | STRING, UNIQUE | Уникальный UUID         |
| connection | TEXT        | Подключение               |
| queue      | TEXT        | Очередь                   |
| payload    | LONGTEXT    | Полезная нагрузка         |
| exception  | LONGTEXT    | Текст исключения          |
| failed_at  | TIMESTAMP   | Время провала             |

---

### Публичные ключи

| Поле         | Тип       | Описание                  |
|--------------|-----------|---------------------------|
| id           | BIGINT, PK| ID ключа                  |
| public_key   | TEXT      | Публичный ключ            |
| expires_at   | TIMESTAMP, NULL | Дата истечения     |
| created_at / updated_at | TIMESTAMPS | Метки времени |

---

### Документы

| Поле              | Тип           | Описание                        |
|-------------------|---------------|---------------------------------|
| id                | BIGINT, PK    | ID документа                    |
| path              | STRING        | Путь к файлу                    |
| original_filename | STRING        | Исходное имя файла              |
| mime_type         | STRING        | MIME-тип                        |
| size              | UNSIGNED BIGINT | Размер в байтах               |
| hash              | STRING, UNIQUE| Уникальный хеш файла            |
| created_at / updated_at | TIMESTAMPS | Метки времени                |

---

### Запросы на подпись

| Поле       | Тип                      | Описание                        |
|------------|--------------------------|---------------------------------|
| id         | UUID, PK                 | Уникальный идентификатор запроса |
| status     | ENUM                     | Статус (`pending`, `completed`, `rejected`) |
| document_id| BIGINT, FK               | Документ (on delete cascade)    |
| created_at / updated_at | TIMESTAMPS  | Метки времени                   |

---

### Подписи документов

| Поле             | Тип         | Описание                          |
|------------------|-------------|-----------------------------------|
| id               | BIGINT, PK  | ID подписи                        |
| request_id       | UUID, FK    | Ссылка на запрос подписи          |
| user_id          | BIGINT, FK  | Подписавший пользователь          |
| signed_pdf_path  | STRING      | Путь к подписанному PDF           |
| signed_at        | TIMESTAMP   | Время подписания (по умолчанию now) |

---

### Персональные токены доступа

| Поле          | Тип            | Описание                        |
|---------------|----------------|---------------------------------|
| id            | BIGINT, PK     | ID токена                       |
| tokenable_type| STRING         | Тип модели (morphs)             |
| tokenable_id  | BIGINT         | ID модели (morphs)              |
| name          | STRING         | Название токена                 |
| token         | STRING(64), UNIQUE | Сам токен                     |
| abilities     | TEXT, NULL     | Возможности токена              |
| last_used_at  | TIMESTAMP, NULL| Последнее использование         |
| expires_at    | TIMESTAMP, NULL| Истекает                        |
| created_at / updated_at | TIMESTAMPS | Метки времени             |

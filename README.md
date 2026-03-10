# Сервис обработки логов

Микросервис обработки логов на Symfony 7 + PHP 8.3. Читает сообщения из RabbitMQ, индексирует в Elasticsearch, предоставляет Search API.

## Архитектура

### Поток данных

```
┌──────────┐
│ Producer │  CLI-команда, внешний сервис и т.д.
└────┬─────┘
     │  LogMessage (AMQP)
     ▼
┌──────────┐
│ RabbitMQ │  exchange: logs → queue: logs_processing
└────┬─────┘
     │  retry: 3 попытки, delay 1s × 5 multiplier
     ▼  failure → DLQ (logs_failed)
┌─────────────────────┐
│  Symfony Consumer    │
│  LogMessageHandler  │  валидация через Symfony Validator
└────┬────────────────┘
     │
     ▼
┌─────────────────────┐
│  ProcessLogUseCase  │  LogDTO → LogEntry (доменная сущность)
└────┬────────────────┘
     │  LogIndexerInterface (порт)
     ▼
┌───────────────────────────┐
│  ElasticsearchLogIndexer  │  автосоздание индексов + маппинг
│  (адаптер)                │  index: logs-{service}-YYYY.MM.DD
└────┬──────────────────────┘
     │
     ▼
┌───────────────┐
│ Elasticsearch │
└────┬──────────┘
     │  LogSearchRepositoryInterface (порт)
     ▼
┌────────────────────────────────┐
│  Search API                    │
│  GET /api/logs/search          │
│  фильтры: service, level,     │
│  message, trace_id, date range │
└────────────────────────────────┘
```

### Структура проекта

```
src/
  Domain/           # Чистый PHP: сущности, value objects
  Application/      # Use cases, DTO, порты
  Infrastructure/   # Адаптеры: Elasticsearch, Messaging, Metrics, Console
  Http/             # Контроллеры
```

## Быстрый старт

```bash
cp .env.example .env
make up
make setup-es        # первый запуск: создание index template в ES
make publish-test
make consume          # в отдельном терминале
curl "http://localhost:8080/api/logs/search?service=test-service"
```

Без Makefile:

```bash
cp .env.example .env
docker compose up -d
docker compose exec php php bin/console app:setup-elasticsearch
docker compose exec php php bin/console app:publish-test-log
docker compose exec php php bin/console messenger:consume logs_processing -vv --limit=10
curl "http://localhost:8080/api/logs/search?service=test-service"
```

## API

### Поиск логов

```
GET /api/logs/search
```

| Параметр   | Тип    | Описание                                      |
|-----------|--------|-----------------------------------------------|
| service   | string | Фильтр по имени сервиса (точное совпадение)    |
| level     | string | Фильтр по уровню лога (точное совпадение)      |
| message   | string | Полнотекстовый поиск по сообщению              |
| trace_id  | string | Фильтр по trace ID (точное совпадение)         |
| from      | string | Начало диапазона (ISO 8601)                    |
| to        | string | Конец диапазона (ISO 8601)                     |
| page      | int    | Номер страницы (по умолчанию: 1)               |
| per_page  | int    | Результатов на страницу (по умолчанию: 20, макс: 100) |

**Примеры запросов:**

```bash
curl "http://localhost:8080/api/logs/search?service=payment-service"
curl "http://localhost:8080/api/logs/search?level=error"
curl "http://localhost:8080/api/logs/search?message=failed"
curl "http://localhost:8080/api/logs/search?trace_id=550e8400e29b41d4a716446655440000"
curl "http://localhost:8080/api/logs/search?from=2026-03-09T00:00:00Z&to=2026-03-09T23:59:59Z"
curl "http://localhost:8080/api/logs/search?service=api-gateway&level=error&page=1&per_page=5"
```

**Пример ответа:**
```json
{
  "total": 1,
  "page": 1,
  "per_page": 20,
  "logs": [
    {
      "_id": "abc123",
      "_index": "logs-test-service-2026.03.10",
      "service": "test-service",
      "level": "info",
      "message": "User logged in",
      "timestamp": "2026-03-10T10:30:00+00:00",
      "trace_id": "550e8400e29b41d4a716446655440000",
      "context": {"user_id": 42}
    }
  ]
}
```

### Служебные эндпоинты

| Эндпоинт         | Описание                                 | Успех | Ошибка |
|-------------------|------------------------------------------|-------|--------|
| `GET /liveness`   | Проверка что процесс жив                 | 200   | —      |
| `GET /readiness`  | Проверка связи с Elasticsearch           | 200   | 503    |
| `GET /metrics`    | Метрики в формате Prometheus             | 200   | 503    |

## Маппинг индексов Elasticsearch

Индексы создаются автоматически по паттерну `logs-{service}-YYYY.MM.DD`.

| Поле      | Тип       | Описание                          |
|-----------|-----------|-----------------------------------|
| service   | keyword   | Имя сервиса (точное совпадение)    |
| level     | keyword   | Уровень лога (точное совпадение)   |
| message   | text      | Сообщение (полнотекстовый поиск)   |
| timestamp | date      | Время в ISO 8601                   |
| trace_id  | keyword   | Trace/correlation ID               |
| context   | object    | Произвольный JSON                  |

Маппинг применяется при первой записи в каждый дневной индекс через `ElasticsearchLogIndexer::ensureIndexExists()`.

Для создания index template (1 shard, 1 replica, refresh_interval 5s + маппинги):

```bash
make setup-es
```

## Тестирование

```bash
make test               # все тесты
make test-unit          # доменные объекты, без зависимостей
make test-integration   # HTTP-контроллеры через WebTestCase
make test-functional    # полный пайплайн с реальным ES (требует docker)
```

### Ручное тестирование

```bash
make up

curl http://localhost:8080/readiness
# {"status":"ready","elasticsearch":true}

docker compose exec php php bin/console app:publish-test-log
docker compose exec php php bin/console app:publish-test-log -s payment-service -l error -m "Payment failed"

docker compose exec php php bin/console messenger:consume logs_processing -vv --limit=10

curl "http://localhost:8080/api/logs/search?service=test-service"
curl "http://localhost:8080/api/logs/search?level=error"
curl http://localhost:8080/metrics
```

### Retry и DLQ

```bash
docker compose exec php php bin/console messenger:failed:show
docker compose exec php php bin/console messenger:failed:retry

# Веб-интерфейс RabbitMQ
open http://localhost:15672   # guest / guest
```

## Docker-сервисы

| Сервис         | Порт  | Описание                  |
|---------------|-------|---------------------------|
| nginx         | 8080  | HTTP-прокси               |
| php           | 9000  | PHP-FPM                   |
| rabbitmq      | 5672  | AMQP-брокер               |
| rabbitmq      | 15672 | Веб-интерфейс RabbitMQ    |
| elasticsearch | 9200  | Поисковый движок          |
| kibana        | 5601  | Дашборд Elasticsearch     |

## Makefile

```bash
make help              make up                make down
make build             make logs              make consume
make publish-test      make test              make test-unit
make test-integration  make test-functional   make setup-es
make cache-clear
```
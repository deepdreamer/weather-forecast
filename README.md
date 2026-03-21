# Weather Forecast API

A simple REST API built with CodeIgniter 4 that returns weather forecasts for Czech cities.

## Endpoint

`POST /api/weather`

### Request

```json
{
    "city": "string"
}
```

### Supported cities

- `praha`
- `brno`
- `ostrava`
- `olomouc`
- `plzeň`
- `pardubice`

### Response

```json
{
    "city": "Praha",
    "temperature": "..."
}
```

## Setup

1. Copy the environment file and adjust as needed:

```bash
cp weather-forecast/env weather-forecast/.env
```

2. For local development, change the environment mode in `.env`:

```
CI_ENVIRONMENT = development
```

3. Make the `writable` directory writable:

```bash
chmod -R 777 weather-forecast/writable
```

4. Install dependencies:

```bash
docker compose up -d
```

5. Install dependencies:

```bash
docker compose exec app composer install
```

---

## Running tests & static analysis

### PHPUnit

```bash
docker compose exec app composer test
```

### PHPStan (level 9)

```bash
docker compose exec app composer phpstan
```

---

## Examples

```bash
docker compose exec app curl -X POST http://nginx/api/weather \
  -H "Content-Type: application/json" \
  -d '{"city": "praha"}'
```

```bash
docker compose exec app curl -X POST http://nginx/api/weather \
  -H "Content-Type: application/json" \
  -d '{"city": "brno"}'
```

```bash
docker compose exec app curl -X POST http://nginx/api/weather \
  -H "Content-Type: application/json" \
  -d '{"city": "ostrava"}'
```

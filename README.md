# Customer & Service REST API

A Laravel 13 backend exposing CRUD REST APIs over two entities — **Customer** and **Service** — where each customer has many services. Every endpoint is protected, documented with Swagger, and backed by MySQL through Eloquent.

| Requirement | Implementation |
| --- | --- |
| PHP | 8.3 |
| Framework | Laravel 13 |
| Authentication | HTTP Basic Authentication (required) **and** Laravel Sanctum personal access tokens (bonus), accepted on the same routes |
| ORM | Eloquent |
| SQL database | MySQL 8.4 (SQL Server ready — see [Using SQL Server](#using-sql-server)) |
| API documentation | Swagger UI / OpenAPI 3 at `/api/documentation`, plus a Postman collection in `postman/` |
| Tests | Pest 4 — 164 tests |
| Caching | Redis (service listings, invalidated on write) |
| Containers | Docker + Docker Compose (app, MySQL, Redis) |
| CI/CD | GitHub Actions — style, static analysis, migrations, tests, image build |
| Architecture | Controllers → services → repositories, with the OpenAPI document kept in its own namespace |

---

## Quick start with Docker

```bash
cp .env.example .env
docker compose up -d --build
```

The entrypoint waits for MySQL, generates the application key if it is missing, runs the migrations and regenerates the OpenAPI document.

- API base URL — <http://localhost:8000/api/v1>
- Swagger UI — <http://localhost:8000/api/documentation>
- OpenAPI document — <http://localhost:8000/docs>

Seed the demo data — an API user (`test@example.com` / `password`) plus three customers with five services:

```bash
docker compose exec app php artisan db:seed --class=DemoDataSeeder
```

`DemoDataSeeder` uses no Faker, so it runs inside the production image where dev dependencies are absent. The factory-based `DatabaseSeeder` is for local development.

MySQL is published on host port **3307** and Redis on **6380** to avoid clashing with services you may already run; override with `FORWARD_DB_PORT` / `FORWARD_REDIS_PORT`.

## Local setup without Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
docker compose up -d mysql redis   # or point .env at your own MySQL/Redis
php artisan migrate --seed
php artisan serve
```

> The default `.env` targets MySQL on `127.0.0.1:3307` and Redis on `127.0.0.1:6380`, matching the ports Docker Compose publishes. Set `CACHE_STORE=file` if you would rather not run Redis — the cache layer detects non-taggable stores and falls back to a version-counter strategy automatically.

---

## Authentication

Every endpoint under `/api/v1` (except `POST /api/v1/auth/login`) requires authentication. `App\Http\Middleware\AuthenticateApi` accepts either scheme on the same route:

**HTTP Basic** — the required scheme:

```bash
curl -u test@example.com:password http://localhost:8000/api/v1/customers
```

**Sanctum bearer token** — the bonus scheme:

```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"test@example.com","password":"password"}' | jq -r .access_token)

curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/v1/customers
```

Requests with no credentials receive a JSON `401` carrying `WWW-Authenticate: Basic realm="API"`. Authentication runs **before** route model binding, so an unauthenticated client cannot tell an existing record from a missing one.

Tokens are Sanctum personal access tokens stored in `personal_access_tokens`. They expire after `SANCTUM_TOKEN_EXPIRATION` minutes (default 60; leave it empty for tokens that never expire), `POST /auth/refresh` revokes the token it was called with and issues a new one, and `POST /auth/logout` revokes it for good. Because the API is stateless, `config/sanctum.php` consults no session guard — only the bearer token.

---

## Endpoints

| Method | Path | Description |
| --- | --- | --- |
| `POST` | `/api/v1/auth/login` | Exchange credentials for an access token |
| `GET` | `/api/v1/auth/me` | The authenticated user |
| `POST` | `/api/v1/auth/refresh` | Rotate the current access token |
| `POST` | `/api/v1/auth/logout` | Revoke the current access token |
| `GET` | `/api/v1/customers` | **View all customers** |
| `POST` | `/api/v1/customers` | **Create a customer** |
| `GET` | `/api/v1/customers/{customer}` | **View a customer** |
| `PUT\|PATCH` | `/api/v1/customers/{customer}` | **Update a customer** |
| `DELETE` | `/api/v1/customers/{customer}` | **Delete a customer** (and its services) |
| `GET` | `/api/v1/customers/{customer}/services` | **View services of a customer** |
| `POST` | `/api/v1/customers/{customer}/services` | **Create a service for a customer** |
| `GET` | `/api/v1/services` | **View all services** |
| `GET` | `/api/v1/services/{service}` | View a service |
| `PUT\|PATCH` | `/api/v1/services/{service}` | **Update a service** |
| `DELETE` | `/api/v1/services/{service}` | **Delete a service** |

All listing endpoints are paginated and accept `search`, `status`, `sort`, `direction`, `per_page` and `page`; service listings additionally accept `customer_id` and `billing_cycle`.

### Example

```bash
curl -u test@example.com:password -X POST \
  http://localhost:8000/api/v1/customers/1/services \
  -H 'Content-Type: application/json' \
  -d '{
        "name": "Managed Hosting",
        "price": 149.99,
        "currency": "EUR",
        "billing_cycle": "monthly",
        "status": "active",
        "starts_at": "2026-01-01"
      }'
```

---

## Data model

```
customers                          services
---------                          --------
id                                 id
name                               customer_id ──► customers.id (cascade on delete)
email (unique)                     name
phone, company                     description
address, city, country             price, currency
status (active|inactive)           billing_cycle (one_time|monthly|quarterly|yearly)
notes                              status (pending|active|suspended|cancelled)
timestamps, deleted_at             starts_at, ends_at
                                   timestamps, deleted_at
```

Statuses and billing cycles are PHP backed enums (`app/Enums`), cast on the models. Both tables use soft deletes; deleting a customer soft-deletes its services in the same transaction.

---

## Caching

`App\Support\ServiceCache` caches the two service listing payloads (`/services` and `/customers/{id}/services`), keyed by a fingerprint of the query string.

- On a taggable store (Redis, array) it caches under the `services` tag and flushes the tag on write.
- On a store that cannot tag (database, file) it embeds a version counter in the key and bumps it on write.

Only plain arrays are cached, never Eloquent objects, because Laravel 13 ships with `cache.serializable_classes` disabled. Creating, updating or deleting a service — or deleting a customer — invalidates the listings immediately. TTL is `CACHE_SERVICE_TTL` (default 300s).

---

## Tests

```bash
php artisan test --compact                                   # everything
php artisan test --compact --filter=ServiceApiTest           # one file
CACHE_STORE=redis php artisan test --compact                 # against Redis
```

Feature tests live in `tests/Feature/Api`, covering customer CRUD, service CRUD, per-customer scoping, both authentication schemes (including token rotation and revocation), cache hits and invalidation, and the generated OpenAPI document. `tests/Feature/Repositories` and `tests/Feature/Services` cover the two layers below the controllers directly. Unit tests cover the enums, the listing query objects and the cache key/version strategy. Tests run on in-memory SQLite, so no services are required.

---

## Swagger

The OpenAPI 3 document is generated from PHP 8 attributes that live entirely in `app/OpenApi` — no annotation sits in a controller, request or resource:

```
app/OpenApi/
├── ApiDocument.php          info, server, security schemes, tags, shared responses
├── Parameters/              the query parameters every listing shares
├── Paths/                   one file per controller: Auth, Customer, Service, CustomerService
└── Schemas/                 Customer, Service, Token and pagination schemas
```


```bash
php artisan l5-swagger:generate    # writes storage/api-docs/api-docs.json
```

Open <http://localhost:8000/api/documentation> and use **Authorize** to supply either Basic credentials or a bearer token. The raw document is served at <http://localhost:8000/docs>; its `servers` entry is the relative `/api`, so "Try it out" always targets the host you are browsing.

---

## Postman

`postman/` holds a ready-to-run collection generated from the same OpenAPI document, organised in folders:

```
Customer & Service API
├── Authentication          log in, authenticated user, refresh, log out, HTTP Basic example
├── Customers               list, create, show, update, patch, delete
│   └── Services of a customer   list and create under /customers/{customer}/services
└── Services                list, show, update, patch, delete
```

1. Import `postman/customer-service-api.postman_collection.json` and `postman/local.postman_environment.json`.
2. Select the **local** environment and adjust `baseUrl`, `email` and `password` if needed.
3. Run **Authentication → Log in** once. Its test script stores `access_token` in the `token` collection variable, and every other request inherits the collection's bearer auth — nothing to paste by hand. **Refresh token** replaces it, and **Create customer** / **Create service for a customer** fill in `customerId` and `serviceId` the same way.

Listing requests ship with `search`, `status`, `sort`, `direction`, `per_page`, `page` (plus `customer_id` and `billing_cycle` on services) as disabled query parameters — tick the ones you need.

---

## Using SQL Server

The brief allows MSSQL or any other SQL server; this project ships with MySQL because it runs natively everywhere including Apple Silicon. Switching to SQL Server needs no application changes:

1. Install the driver — locally `pecl install sqlsrv pdo_sqlsrv`, or add `pdo_sqlsrv` to the `install-php-extensions` list in the `Dockerfile`.
2. Point `.env` at it:

   ```dotenv
   DB_CONNECTION=sqlsrv
   DB_HOST=127.0.0.1
   DB_PORT=1433
   DB_DATABASE=moneyflex
   DB_USERNAME=sa
   DB_PASSWORD=Your_password123
   ```

3. Run `php artisan migrate`. The migrations use only portable Schema builder types.

---

## CI/CD

`.github/workflows/api.yml` runs on every push and pull request:

1. **tests** — boots MySQL 8.4 and Redis 7, installs dependencies, checks style with Pint, runs PHPStan (larastan), verifies the OpenAPI document builds, runs the migrations against MySQL, then runs the full Pest suite.
2. **docker** — builds the runtime image with Buildx (GitHub Actions cache) and smoke tests it.

The starter kit's own `.github/workflows/tests.yml` continues to run the frontend checks unchanged.

---

## Project layout

```
app/
├── Enums/                                CustomerStatus, ServiceStatus, BillingCycle
├── Http/
│   ├── Controllers/Api/V1/               thin controllers delegating to the services
│   ├── Middleware/AuthenticateApi.php    Basic or bearer token on the same route
│   ├── Requests/Api/V1/                  form request validation
│   └── Resources/                        CustomerResource, ServiceResource, TokenResource
├── Models/                               Customer, Service, User (HasApiTokens)
├── OpenApi/                              the whole OpenAPI document (see Swagger above)
├── Providers/RepositoryServiceProvider    binds every repository contract
├── Repositories/
│   ├── Contracts/                        CustomerRepositoryInterface, ServiceRepositoryInterface
│   └── Eloquent/                         the only place Eloquent queries are written
├── Services/                             AuthService, CustomerService, ServiceService
└── Support/
    ├── Auth/IssuedToken.php              the token payload returned to clients
    ├── Queries/                          CustomerQuery, ServiceQuery, Sorting
    └── ServiceCache.php                  listing cache with tag / version strategies
docker/                                   entrypoint and php.ini
postman/                                  collection and environment for Postman
routes/api.php                            versioned API routes
tests/                                    Pest feature and unit tests
```

### How a request flows

```
Route → AuthenticateApi → FormRequest → Controller → Service → Repository → Eloquent
                                                        │
                                                        └─► ServiceCache (listing payloads)
```

Controllers only translate HTTP to a use case: they build a query object from the request, call one service method and wrap the result in a resource. Services own the use cases — transactions, cache invalidation and token lifecycle. Repositories own every query; swapping the persistence layer means writing one new implementation and rebinding it in `RepositoryServiceProvider`.

> This repository is based on the Laravel React starter kit; the bundled Inertia frontend is left untouched and is unrelated to the API.

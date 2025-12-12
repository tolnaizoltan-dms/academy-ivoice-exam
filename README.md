# Jóváhagyási Folyamat - DMS One Fejlesztői Akadémia Vizsgafeladat

## Projekt Áttekintés

Ez a projekt a DMS One Fejlesztői Akadémia vizsgafeladata, amely egy **Jóváhagyási Folyamat** implementációja Domain-Driven Design elvek alapján.

### Use Case

> **"Egy ügyintéző benyújt egy számlát, ami ezzel automatikusan átkerül a feletteséhez jóváhagyásra."**

## Architektúra

A projekt **Hexagonal Architecture (Ports & Adapters)** mintát követ, két Bounded Context-tel:

### Bounded Contexts

1. **Számlabefogadás (Invoice Reception)**
   - Felelősség: Nyers adatokból érvényes, befogadott számlát létrehozni
   - Aggregate: `Invoice`
   - Domain Event: `InvoiceSubmitted`

2. **Jóváhagyás (Approval)**
   - Felelősség: Befogadott számlák jóváhagyási/elutasítási folyamatának menedzselése
   - Aggregate: `Approval`
   - Domain Events: `ApprovalProcessStarted`, `InvoiceApproved`, `InvoiceRejected`

### Policy (Automatizmus)

```
InvoiceSubmitted → [Policy: StartApprovalProcessListener] → Approval created with PENDING status
```
## 🔧 Technológiai Stack

- **PHP**: 8.2+
- **Framework**: Laravel 12
- **Testing**: Pest 4 + Laravel Plugin
- **Database**: MySQL 8.0, (SQLite integracios tesztekhez)
- **Container**: Docker (Laravel Sail)
- **Kódminőség**: Pint (PHP CS Fixer), Rector, Larastan (PHPStan)

### Követelmények a futtatáshoz

- Docker & Docker Compose
- PHP 8.4
- Git

### Telepítés

```bash
# 1. Klónozás
git clone <repo-url>
cd invoice

# 2. Environment setup
cp .env.example .env

# 3. a sail csomag telepitese szukseges a kontenerek letrehozasahoz
composer install 

# 4. Build & Install
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate

# 5. Health check
./vendor/bin/sail exec laravel.test curl http://localhost/up
```

### Leállítás

```bash
# szukseg eseten adatbazis adatok torlese
./vendor/bin/sail artisan migrate:fresh
```

```bash
./vendor/bin/sail down
```

## API Használat

A teljes API dokumentáció OpenAPI 3.0 formátumban elérhető: [`docs/openapi.yaml`](docs/openapi.yaml)

### Végpontok

| Művelet | Endpoint | Leírás |
|---------|----------|--------|
| Submit Invoice | `POST /api/v1/invoices` | Új számla benyújtása |
| Approve Invoice | `PUT /api/v1/approvals/{id}/approve` | Számla jóváhagyása |
| Reject Invoice | `PUT /api/v1/approvals/{id}/reject` | Számla elutasítása |

### cURL Példák

**Submit Invoice:**
```bash
curl -X POST http://localhost:8084/api/v1/invoices \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "invoiceNumber": "INV-2025-0001",
    "amount": 15000.50,
    "submitterId": "550e8400-e29b-41d4-a716-446655440000",
    "supervisorId": "550e8400-e29b-41d4-a716-446655440001"
  }'
```

**Approve Invoice:**
```bash
curl -X PUT http://localhost:8084/api/v1/approvals/{approval-id}/approve \
  -H "Accept: application/json"
```

**Reject Invoice:**
```bash
curl -X PUT http://localhost:8084/api/v1/approvals/{approval-id}/reject \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"reason": "Insufficient documentation provided."}'
```

## Tesztelés

Tesztek típusai:
- unit tesztek
- integrációs tesztek
- architekturális tesztek
- TODO: mutációs tesztelés

### Tesztek Futtatása

```bash
# Összes teszt
./vendor/bin/sail test

# Coverage riport
./vendor/bin/sail test --coverage

# Specifikus teszt
./vendor/bin/sail test --filter InvoiceTest

# Unit tesztek
./vendor/bin/sail test tests/Unit

# Feature (integration) tesztek
./vendor/bin/sail test tests/Feature
```

## DDD Implementáció

### Value Objects

Immutable objektumok validációval:
- `InvoiceId`, `InvoiceNumber`, `Amount`, `SubmitterId`
- `ApprovalId`, `ApproverId`, `ApprovalStatus` (Enum)

### Aggregates

- **Invoice**: Factory method (`submit()`), event recording
- **Approval**: Factory method (`start()`), state transitions (`approve()`, `reject()`)

### Domain Events

Laravel Events használatával:
- `InvoiceSubmitted`
- `ApprovalProcessStarted`
- `InvoiceApproved`
- `InvoiceRejected`

### Domain Exceptions

- `InvalidInvoiceException`
- `InvalidApprovalException`

## Logolás

A Policy végrehajtása logolja az automatizált folyamatot:

```
[INFO] Policy triggered: Starting approval process for submitted invoice
       {"invoice_id": "...", "invoice_number": "INV-2025-0001", ...}

[INFO] Approval process started successfully
       {"approval_id": "...", "invoice_id": "...", "status": "pending"}
```

Log fájlok: `storage/logs/`

## Docker

A projekt Laravel Sail-t használ, ami Docker Compose-ra épül.

**Szolgáltatások:**
- `invoice.test` - PHP 8.4
- `mysql` - MySQL 8.0

**Konfiguráció:** `compose.yaml`

## Hivatkozások

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Pest Testing Framework](https://pestphp.com/)

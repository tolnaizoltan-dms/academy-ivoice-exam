# Jóváhagyási Folyamat - Laravel Projekt Terv

## 📋 Áttekintés

### Use Case
**"Egy ügyintéző benyújt egy számlát, ami ezzel automatikusan átkerül a feletteséhez jóváhagyásra."**

### Bounded Contexts
1. **Számlabefogadás** (Invoice Reception) - Nyers adatokból érvényes, befogadott számlát létrehozni
2. **Jóváhagyás** (Approval) - Befogadott számlák jóváhagyási/elutasítási folyamatának menedzselése

---

## 🏗️ Architektúra Áttekintés (DDD in Laravel)

### DDD Rétegek Laravel Architektúrában

```
┌─────────────────────────────────────────────────────────┐
│              Laravel HTTP Layer (Routes/Controllers)     │
│                     (Primary Adapter)                    │
└───────────────────────────┬─────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────┐
│              Application Layer                           │
│  - Actions (Command Handlers)                            │
│  - DTOs (Request/Response)                               │
│  - Listeners (Event Subscribers - Policy)                │
└───────────────────────────┬─────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────┐
│                 Domain Layer                             │
│  - Aggregates (Invoice, Approval)                        │
│  - Domain Events                                         │
│  - Value Objects                                         │
│  - Domain Exceptions                                     │
│  - Repository Interfaces (Contracts)                     │
└───────────────────────────┬─────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────┐
│         Infrastructure Layer (Laravel Services)          │
│  - Eloquent Models (if needed)                           │
│  - Repository Implementations                            │
│  - Event System (Laravel Events)                         │
│  - Queue System (optional)                               │
└─────────────────────────────────────────────────────────┘
```

### Laravel Features Használata

- **Service Container**: Dependency Injection
- **Events & Listeners**: Policy implementáció (InvoiceSubmitted → StartApprovalProcess)
- **Form Requests**: Input validation
- **Collections**: Domain collections
- **Eloquent/Query Builder**: Persistence
- **Logging**: Monolog via Log facade
- **Testing**: Pest + Feature/Unit tests

---

## 📁 Laravel Könyvtárstruktúra (DDD)

```
invoice/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── InvoiceController.php                # API Controller
│   │   └── Requests/
│   │       └── SubmitInvoiceRequest.php             # Form Request Validation
│   │
│   ├── Domain/                                       # ← DDD Domain Layer
│   │   ├── InvoiceReception/                        # Bounded Context 1
│   │   │   ├── Aggregates/
│   │   │   │   └── Invoice.php
│   │   │   ├── Events/
│   │   │   │   └── InvoiceSubmitted.php             # Laravel Event
│   │   │   ├── ValueObjects/
│   │   │   │   ├── InvoiceId.php
│   │   │   │   ├── InvoiceNumber.php
│   │   │   │   ├── Amount.php
│   │   │   │   └── SubmitterId.php
│   │   │   ├── Contracts/
│   │   │   │   └── InvoiceRepositoryInterface.php
│   │   │   └── Exceptions/
│   │   │       └── InvalidInvoiceException.php
│   │   │
│   │   └── Approval/                                # Bounded Context 2
│   │       ├── Aggregates/
│   │       │   └── Approval.php
│   │       ├── Events/
│   │       │   ├── ApprovalProcessStarted.php       # Laravel Event
│   │       │   ├── InvoiceApproved.php
│   │       │   └── InvoiceRejected.php
│   │       ├── ValueObjects/
│   │       │   ├── ApprovalId.php
│   │       │   ├── ApproverId.php
│   │       │   └── ApprovalStatus.php               # Enum
│   │       ├── Contracts/
│   │       │   └── ApprovalRepositoryInterface.php
│   │       └── Exceptions/
│   │           └── InvalidApprovalException.php
│   │
│   ├── Application/                                  # ← Application Layer
│   │   ├── Actions/                                  # Command Handlers
│   │   │   ├── SubmitInvoiceAction.php
│   │   │   └── StartApprovalProcessAction.php
│   │   ├── DTOs/
│   │   │   ├── SubmitInvoiceData.php
│   │   │   └── StartApprovalData.php
│   │   └── Listeners/                                # Event Subscribers (Policy!)
│   │       └── StartApprovalProcessListener.php     # Policy: InvoiceSubmitted → Start Approval
│   │
│   ├── Infrastructure/                               # ← Infrastructure Layer
│   │   └── Repositories/
│   │       ├── InMemoryInvoiceRepository.php
│   │       └── InMemoryApprovalRepository.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php                   # DI Bindings
│   │   └── EventServiceProvider.php                 # Event → Listener mappings
│   │
│   └── Exceptions/
│       ├── Handler.php
│       └── DomainException.php                      # Base Domain Exception
│
├── routes/
│   └── api.php                                      # API Routes
│
├── tests/
│   ├── Unit/
│   │   ├── Domain/
│   │   │   ├── InvoiceReception/
│   │   │   │   ├── InvoiceTest.php
│   │   │   │   └── ValueObjects/
│   │   │   │       ├── AmountTest.php
│   │   │   │       └── InvoiceNumberTest.php
│   │   │   └── Approval/
│   │   │       ├── ApprovalTest.php
│   │   │       └── ValueObjects/
│   │   │           └── ApprovalStatusTest.php
│   │   └── Application/
│   │       ├── SubmitInvoiceActionTest.php
│   │       └── StartApprovalProcessActionTest.php
│   │
│   └── Feature/
│       ├── SubmitInvoiceTest.php                    # API Integration Test
│       └── ApprovalProcessIntegrationTest.php       # Full Vertical Slice Test
│
├── docker-compose.yml                               # Laravel Sail
├── composer.json
├── phpunit.xml / Pest.php
└── README.md
```

---

## 🔧 Technológiai Stack

- **PHP**: 8.2+ (Laravel 12 requirement)
- **Framework**: Laravel 12
- **Testing**: Pest 4 + Pest Plugin Laravel
- **Database**: MySQL 8.0, SQLite
- **Container**: Laravel Sail (Docker Compose)
- **Event System**: Laravel Events
- **DI**: Laravel Service Container
- **Validation**: Laravel Form Requests
- **Logging**: Laravel Log (Monolog)

---

## 📝 Implementációs Lépések

### FÁZIS 1: Laravel Setup & Configuration
**Cél**: Laravel projekt konfigurálása DDD struktúrához

#### 1.1 Laravel Sail Setup
- [ ] `.env` fájl ellenőrzése/létrehozása
- [ ] `php artisan key:generate` (ha szükséges)
- [ ] Laravel Sail indítása: `./vendor/bin/sail up -d`
- [ ] Adatbázis migráció: `./vendor/bin/sail artisan migrate`
- [ ] Health check: böngészőben `http://localhost`

#### 1.2 Composer Dependencies
- [ ] PHP verzió ellenőrzése `composer.json`-ban (^8.2)
- [ ] Pest már telepítve ✅
- [ ] További package-ek (ha szükséges):
  ```bash
  ./vendor/bin/sail composer require ramsey/uuid
  ```

#### 1.3 Service Provider Setup
- [ ] `EventServiceProvider.php` - Event-Listener mapping
- [ ] `AppServiceProvider.php` - Repository bindings (Interface → Implementation)

#### 1.4 Pest Configuration
- [ ] `tests/Pest.php` konfiguráció ellenőrzése
- [ ] Első teszt futtatása: `./vendor/bin/sail test`

---

### FÁZIS 2: Domain Layer - Shared Kernel & Base Classes
**Cél**: Domain közös elemek létrehozása

#### 2.1 Base Domain Exception
- [ ] `app/Exceptions/DomainException.php`
  - Extends Laravel `Exception`
  - Base class minden domain exception-höz

#### 2.2 Domain Event Base (opcionális)
- [ ] Laravel Events használata (Illuminate\Events\Dispatchable)
- [ ] Minden domain event Laravel Event lesz

**Unit Tesztek**: Opcionális (Laravel Events tested by framework)

---

### FÁZIS 3: Domain Layer - Számlabefogadás Bounded Context
**Cél**: Invoice aggregate és kapcsolódó domain elemek implementálása

#### 3.1 Value Objects
- [ ] `app/Domain/InvoiceReception/ValueObjects/InvoiceId.php`
  - Readonly property: `public readonly string $value`
  - UUID v4 validation
  - `__toString()`, `equals()`
- [ ] `InvoiceNumber.php`
  - Format validation: `INV-YYYY-XXXX`
  - Regex: `/^INV-\d{4}-\d{4}$/`
- [ ] `Amount.php`
  - Positive float validation
  - `getValue(): float`
- [ ] `SubmitterId.php` (UUID)

**Pest Unit Tesztek**:
```php
// tests/Unit/Domain/InvoiceReception/ValueObjects/AmountTest.php
it('creates valid amount', function () {
    $amount = new Amount(1000.50);
    expect($amount->getValue())->toBe(1000.50);
});

it('throws exception for negative amount', function () {
    new Amount(-100);
})->throws(InvalidInvoiceException::class);
```

#### 3.2 Domain Events
- [ ] `app/Domain/InvoiceReception/Events/InvoiceSubmitted.php`
  - Laravel Event: `use Dispatchable, SerializesModels`
  - Properties:
    ```php
    public function __construct(
        public readonly string $invoiceId,
        public readonly string $invoiceNumber,
        public readonly float $amount,
        public readonly string $submitterId,
        public readonly string $supervisorId,
        public readonly DateTimeImmutable $occurredAt,
    ) {}
    ```

#### 3.3 Invoice Aggregate
- [ ] `app/Domain/InvoiceReception/Aggregates/Invoice.php`
  - Private constructor
  - Static factory method:
    ```php
    public static function submit(
        InvoiceNumber $number,
        Amount $amount,
        SubmitterId $submitterId,
        string $supervisorId
    ): self
    ```
  - Domain event tárolása: `protected array $recordedEvents = []`
  - `recordEvent()` helper method
  - `releaseEvents(): array` method
  - Invariants: amount > 0, valid invoice number

- [ ] `app/Domain/InvoiceReception/Exceptions/InvalidInvoiceException.php`

**Pest Unit Tesztek**:
```php
// tests/Unit/Domain/InvoiceReception/InvoiceTest.php
it('can submit a valid invoice', function () {
    $invoice = Invoice::submit(
        new InvoiceNumber('INV-2025-0001'),
        new Amount(1000),
        new SubmitterId('uuid-1'),
        'uuid-supervisor'
    );
    
    expect($invoice)->toBeInstanceOf(Invoice::class);
    expect($invoice->releaseEvents())->toHaveCount(1);
    expect($invoice->releaseEvents()[0])->toBeInstanceOf(InvoiceSubmitted::class);
});
```

#### 3.4 Repository Contract
- [ ] `app/Domain/InvoiceReception/Contracts/InvoiceRepositoryInterface.php`
  ```php
  interface InvoiceRepositoryInterface
  {
      public function save(Invoice $invoice): void;
      public function findById(InvoiceId $id): ?Invoice;
      public function nextIdentity(): InvoiceId;
  }
  ```

---

### FÁZIS 4: Domain Layer - Jóváhagyás Bounded Context
**Cél**: Approval aggregate és kapcsolódó domain elemek implementálása

#### 4.1 Value Objects
- [ ] `app/Domain/Approval/ValueObjects/ApprovalId.php` (UUID)
- [ ] `ApproverId.php` (UUID)
- [ ] `ApprovalStatus.php` (Backed Enum)
  ```php
  enum ApprovalStatus: string
  {
      case PENDING = 'pending';
      case APPROVED = 'approved';
      case REJECTED = 'rejected';
  }
  ```

**Pest Unit Tesztek**: VO validációk

#### 4.2 Domain Events
- [ ] `app/Domain/Approval/Events/ApprovalProcessStarted.php` (Laravel Event)
- [ ] `InvoiceApproved.php`
- [ ] `InvoiceRejected.php`

#### 4.3 Approval Aggregate
- [ ] `app/Domain/Approval/Aggregates/Approval.php`
  - Static factory:
    ```php
    public static function start(
        ApprovalId $id,
        string $invoiceId,
        ApproverId $approverId
    ): self
    ```
  - Methods:
    ```php
    public function approve(): void
    public function reject(string $reason): void
    ```
  - Status transition validation using `match`:
    ```php
    private function ensurePending(): void
    {
        match ($this->status) {
            ApprovalStatus::PENDING => null,
            ApprovalStatus::APPROVED => throw new InvalidApprovalException('Already approved'),
            ApprovalStatus::REJECTED => throw new InvalidApprovalException('Already rejected'),
        };
    }
    ```
  - Event recording

- [ ] `app/Domain/Approval/Exceptions/InvalidApprovalException.php`

**Pest Unit Tesztek**:
```php
it('can start approval process', function () {
    $approval = Approval::start(
        new ApprovalId('uuid'),
        'invoice-uuid',
        new ApproverId('approver-uuid')
    );
    
    expect($approval->getStatus())->toBe(ApprovalStatus::PENDING);
});

it('can approve pending approval', function () {
    $approval = Approval::start(...);
    $approval->approve();
    
    expect($approval->getStatus())->toBe(ApprovalStatus::APPROVED);
});

it('cannot approve already approved', function () {
    $approval = Approval::start(...);
    $approval->approve();
    $approval->approve(); // Should throw
})->throws(InvalidApprovalException::class);
```

#### 4.4 Repository Contract
- [ ] `app/Domain/Approval/Contracts/ApprovalRepositoryInterface.php`

---

### FÁZIS 5: Infrastructure Layer - Repository Implementations
**Cél**: In-memory repository implementációk

#### 5.1 Invoice Repository
- [ ] `app/Infrastructure/Repositories/InMemoryInvoiceRepository.php`
  ```php
  class InMemoryInvoiceRepository implements InvoiceRepositoryInterface
  {
      /** @var array<string, Invoice> */
      private array $invoices = [];
      
      public function save(Invoice $invoice): void
      {
          $this->invoices[$invoice->getId()->value] = $invoice;
      }
      
      public function findById(InvoiceId $id): ?Invoice
      {
          return $this->invoices[$id->value] ?? null;
      }
      
      public function nextIdentity(): InvoiceId
      {
          return new InvoiceId(Str::uuid()->toString());
      }
  }
  ```

#### 5.2 Approval Repository
- [ ] `app/Infrastructure/Repositories/InMemoryApprovalRepository.php`

#### 5.3 Service Provider Binding
- [ ] `app/Providers/AppServiceProvider.php`
  ```php
  public function register(): void
  {
      $this->app->singleton(
          InvoiceRepositoryInterface::class,
          InMemoryInvoiceRepository::class
      );
      
      $this->app->singleton(
          ApprovalRepositoryInterface::class,
          InMemoryApprovalRepository::class
      );
  }
  ```

**Pest Unit Tesztek**: Repository CRUD műveletek

---

### FÁZIS 6: Application Layer - Actions (Command Handlers)
**Cél**: Use case orchestration

#### 6.1 DTOs (Data Transfer Objects)
- [ ] `app/Application/DTOs/SubmitInvoiceData.php`
  ```php
  readonly class SubmitInvoiceData
  {
      public function __construct(
          public string $invoiceNumber,
          public float $amount,
          public string $submitterId,
          public string $supervisorId,
      ) {}
      
      public static function fromRequest(array $data): self
      {
          return new self(
              invoiceNumber: $data['invoiceNumber'],
              amount: (float) $data['amount'],
              submitterId: $data['submitterId'],
              supervisorId: $data['supervisorId'],
          );
      }
  }
  ```

- [ ] `app/Application/DTOs/StartApprovalData.php`

#### 6.2 Submit Invoice Action
- [ ] `app/Application/Actions/SubmitInvoiceAction.php`
  ```php
  class SubmitInvoiceAction
  {
      public function __construct(
          private InvoiceRepositoryInterface $repository,
      ) {}
      
      public function execute(SubmitInvoiceData $data): Invoice
      {
          // Create value objects
          $invoiceNumber = new InvoiceNumber($data->invoiceNumber);
          $amount = new Amount($data->amount);
          $submitterId = new SubmitterId($data->submitterId);
          
          // Create aggregate via factory method
          $invoice = Invoice::submit(
              $this->repository->nextIdentity(),
              $invoiceNumber,
              $amount,
              $submitterId,
              $data->supervisorId
          );
          
          // Persist
          $this->repository->save($invoice);
          
          // Dispatch domain events via Laravel Event system
          foreach ($invoice->releaseEvents() as $event) {
              event($event);
          }
          
          return $invoice;
      }
  }
  ```

**Pest Unit Tesztek**:
```php
it('submits invoice and dispatches event', function () {
    Event::fake([InvoiceSubmitted::class]);
    
    $repository = new InMemoryInvoiceRepository();
    $action = new SubmitInvoiceAction($repository);
    
    $data = new SubmitInvoiceData(
        invoiceNumber: 'INV-2025-0001',
        amount: 1000,
        submitterId: 'uuid-1',
        supervisorId: 'uuid-2'
    );
    
    $invoice = $action->execute($data);
    
    expect($repository->findById($invoice->getId()))->not->toBeNull();
    Event::assertDispatched(InvoiceSubmitted::class);
});
```

#### 6.3 Start Approval Process Action
- [ ] `app/Application/Actions/StartApprovalProcessAction.php`

**Pest Unit Tesztek**: Similar pattern

---

### FÁZIS 7: Application Layer - Policy (Event Listener)
**Cél**: Bounded Context-ek közötti automatizmus

#### 7.1 Event Listener (Policy Implementation)
- [ ] `app/Application/Listeners/StartApprovalProcessListener.php`
  ```php
  class StartApprovalProcessListener
  {
      public function __construct(
          private StartApprovalProcessAction $action,
      ) {}
      
      public function handle(InvoiceSubmitted $event): void
      {
          Log::info('Policy triggered: Starting approval process', [
              'invoice_id' => $event->invoiceId,
              'supervisor_id' => $event->supervisorId,
          ]);
          
          $data = new StartApprovalData(
              invoiceId: $event->invoiceId,
              approverId: $event->supervisorId,
          );
          
          $this->action->execute($data);
          
          Log::info('Approval process started successfully', [
              'invoice_id' => $event->invoiceId,
          ]);
      }
  }
  ```

#### 7.2 Event Registration
- [ ] `app/Providers/EventServiceProvider.php`
  ```php
  protected $listen = [
      InvoiceSubmitted::class => [
          StartApprovalProcessListener::class,
      ],
  ];
  ```

**Pest Unit Tesztek**:
```php
it('triggers approval process when invoice submitted', function () {
    Event::fake([ApprovalProcessStarted::class]);
    
    $listener = new StartApprovalProcessListener(
        new StartApprovalProcessAction(new InMemoryApprovalRepository())
    );
    
    $event = new InvoiceSubmitted(
        invoiceId: 'uuid-1',
        invoiceNumber: 'INV-2025-0001',
        amount: 1000,
        submitterId: 'uuid-2',
        supervisorId: 'uuid-3',
        occurredAt: new DateTimeImmutable(),
    );
    
    $listener->handle($event);
    
    Event::assertDispatched(ApprovalProcessStarted::class);
});
```

---

### FÁZIS 8: API Layer - HTTP Controllers & Validation
**Cél**: REST endpoint implementáció

#### 8.1 Form Request Validation
- [ ] `app/Http/Requests/SubmitInvoiceRequest.php`
  ```php
  class SubmitInvoiceRequest extends FormRequest
  {
      public function rules(): array
      {
          return [
              'invoiceNumber' => ['required', 'string', 'regex:/^INV-\d{4}-\d{4}$/'],
              'amount' => ['required', 'numeric', 'min:0.01'],
              'submitterId' => ['required', 'uuid'],
              'supervisorId' => ['required', 'uuid'],
          ];
      }
      
      public function messages(): array
      {
          return [
              'invoiceNumber.regex' => 'Invoice number must be in format INV-YYYY-XXXX',
              'amount.min' => 'Amount must be greater than 0',
          ];
      }
  }
  ```

#### 8.2 API Controller
- [ ] `app/Http/Controllers/InvoiceController.php`
  ```php
  class InvoiceController extends Controller
  {
      public function __construct(
          private SubmitInvoiceAction $submitInvoiceAction,
      ) {}
      
      public function store(SubmitInvoiceRequest $request): JsonResponse
      {
          try {
              $data = SubmitInvoiceData::fromRequest($request->validated());
              $invoice = $this->submitInvoiceAction->execute($data);
              
              return response()->json([
                  'invoiceId' => $invoice->getId()->value,
                  'status' => 'submitted',
                  'message' => 'Invoice submitted successfully',
              ], 201);
              
          } catch (InvalidInvoiceException $e) {
              return response()->json([
                  'error' => 'Invalid invoice data',
                  'message' => $e->getMessage(),
              ], 400);
              
          } catch (\Exception $e) {
              Log::error('Failed to submit invoice', [
                  'error' => $e->getMessage(),
                  'trace' => $e->getTraceAsString(),
              ]);
              
              return response()->json([
                  'error' => 'Internal server error',
                  'message' => 'Failed to submit invoice',
              ], 500);
          }
      }
  }
  ```

#### 8.3 Routes
- [ ] `routes/api.php`
  ```php
  use App\Http\Controllers\InvoiceController;
  
  Route::prefix('v1')->group(function () {
      Route::post('/invoices', [InvoiceController::class, 'store']);
  });
  ```

---

### FÁZIS 9: Integration Testing (Feature Tests)
**Cél**: End-to-end vertical slice tesztelés

#### 9.1 API Feature Test
- [ ] `tests/Feature/SubmitInvoiceTest.php`
  ```php
  use function Pest\Laravel\postJson;
  
  it('can submit invoice via API', function () {
      $response = postJson('/api/v1/invoices', [
          'invoiceNumber' => 'INV-2025-0001',
          'amount' => 1500.50,
          'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
          'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
      ]);
      
      $response->assertStatus(201)
               ->assertJsonStructure(['invoiceId', 'status', 'message']);
  });
  
  it('validates invoice number format', function () {
      $response = postJson('/api/v1/invoices', [
          'invoiceNumber' => 'INVALID',
          'amount' => 1000,
          'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
          'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
      ]);
      
      $response->assertStatus(422)
               ->assertJsonValidationErrors(['invoiceNumber']);
  });
  ```

#### 9.2 Full Vertical Slice Integration Test
- [ ] `tests/Feature/ApprovalProcessIntegrationTest.php`
  ```php
  it('completes full approval process flow', function () {
      // Arrange
      Event::fake([
          InvoiceSubmitted::class,
          ApprovalProcessStarted::class,
      ]);
      
      $invoiceRepo = app(InvoiceRepositoryInterface::class);
      $approvalRepo = app(ApprovalRepositoryInterface::class);
      
      // Act - Submit invoice
      $response = postJson('/api/v1/invoices', [
          'invoiceNumber' => 'INV-2025-0001',
          'amount' => 1500.50,
          'submitterId' => '550e8400-e29b-41d4-a716-446655440000',
          'supervisorId' => '550e8400-e29b-41d4-a716-446655440001',
      ]);
      
      // Assert HTTP Response
      $response->assertStatus(201);
      $invoiceId = $response->json('invoiceId');
      
      // Assert Invoice Created
      $invoice = $invoiceRepo->findById(new InvoiceId($invoiceId));
      expect($invoice)->not->toBeNull();
      
      // Assert InvoiceSubmitted Event Dispatched
      Event::assertDispatched(InvoiceSubmitted::class, function ($event) use ($invoiceId) {
          return $event->invoiceId === $invoiceId;
      });
      
      // Manually trigger event processing (since Event::fake stops auto-dispatch)
      $event = new InvoiceSubmitted(
          invoiceId: $invoiceId,
          invoiceNumber: 'INV-2025-0001',
          amount: 1500.50,
          submitterId: '550e8400-e29b-41d4-a716-446655440000',
          supervisorId: '550e8400-e29b-41d4-a716-446655440001',
          occurredAt: new DateTimeImmutable(),
      );
      
      app(StartApprovalProcessListener::class)->handle($event);
      
      // Assert ApprovalProcessStarted Event Dispatched
      Event::assertDispatched(ApprovalProcessStarted::class);
      
      // Assert Approval Created (we'd need to track this via repository or event)
      // This requires some infrastructure to query approvals by invoiceId
      // For now, we can verify via logs or add a query method
  });
  
  it('logs policy execution', function () {
      Log::shouldReceive('info')
         ->once()
         ->with('Policy triggered: Starting approval process', Mockery::any());
      
      Log::shouldReceive('info')
         ->once()
         ->with('Approval process started successfully', Mockery::any());
      
      $listener = app(StartApprovalProcessListener::class);
      $event = new InvoiceSubmitted(...);
      
      $listener->handle($event);
  });
  ```

---

### FÁZIS 10: Test Coverage & Code Quality
**Cél**: 80%+ test coverage elérése

#### 10.1 Coverage Analysis
- [ ] Coverage futtatása:
  ```bash
  ./vendor/bin/sail test --coverage --min=80
  ```
  vagy
  ```bash
  ./vendor/bin/sail test --coverage-html coverage/
  ```
- [ ] Coverage report átnézése
- [ ] Hiányzó tesztek pótlása

#### 10.2 Code Quality Tools
- [ ] Laravel Pint (code style):
  ```bash
  ./vendor/bin/sail pint
  ```
- [ ] Static Analysis (opcionális - PHPStan):
  ```bash
  ./vendor/bin/sail composer require --dev phpstan/phpstan
  ./vendor/bin/sail vendor/bin/phpstan analyse app tests --level 8
  ```

---

### FÁZIS 11: Dokumentáció & Delivery
**Cél**: Production-ready projekt

#### 11.1 README.md Frissítése
- [ ] Projekt leírás
- [ ] Architektúra diagram
- [ ] Bounded Contexts magyarázata
- [ ] Installációs útmutató:
  ```markdown
  ## Installation
  
  1. Clone repository
  2. Copy `.env.example` to `.env`
  3. Install dependencies: `./vendor/bin/sail composer install`
  4. Generate key: `./vendor/bin/sail artisan key:generate`
  5. Run migrations: `./vendor/bin/sail artisan migrate`
  6. Start application: `./vendor/bin/sail up -d`
  ```
- [ ] API használat példák (curl vagy Postman)
- [ ] Tesztelés:
  ```bash
  ./vendor/bin/sail test
  ./vendor/bin/sail test --coverage
  ```
- [ ] Design döntések dokumentálása

#### 11.2 API Dokumentáció
- [ ] Endpoint leírások
- [ ] Request/Response példák
- [ ] Error codes

**Példa API Call**:
```bash
curl -X POST http://localhost/api/v1/invoices \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "invoiceNumber": "INV-2025-0001",
    "amount": 15000.50,
    "submitterId": "550e8400-e29b-41d4-a716-446655440000",
    "supervisorId": "550e8400-e29b-41d4-a716-446655440001"
  }'
```

#### 11.3 Docker Compose Ellenőrzés
- [ ] `compose.yml` (Laravel Sail) működik
- [ ] Environment variables dokumentálása
- [ ] Volumes és networks ellenőrzése

#### 11.4 Final Testing
- [ ] Clean install:
  ```bash
  ./vendor/bin/sail down -v
  ./vendor/bin/sail up -d
  ./vendor/bin/sail artisan migrate
  ./vendor/bin/sail test
  ```
- [ ] API manual testing
- [ ] Log fájlok ellenőrzése (`storage/logs/`)

---

## 🧪 Tesztelési Stratégia (Pest)

### Test Coverage Célok
- **Domain Layer**: 90%+ (kritikus üzleti logika)
- **Application Layer**: 85%+
- **Infrastructure Layer**: 70%+
- **HTTP Layer**: 75%+
- **Overall**: 80%+

### Test Pyramid
```
        /\
       /  \      Feature Tests (10-15%)
      /    \     - Full API integration tests
     /------\    - Vertical slice tests
    /        \   
   /          \  Unit Tests (85-90%)
  /____________\ - Aggregates, Value Objects
                 - Actions, Listeners
                 - Repositories
```

### Pest Best Practices
```php
// Use descriptive test names
it('creates invoice with valid data')
it('throws exception when amount is negative')
it('triggers approval process after invoice submission')

// Use datasets for multiple test cases
it('validates invoice number format', function (string $invalidNumber) {
    expect(fn() => new InvoiceNumber($invalidNumber))
        ->toThrow(InvalidInvoiceException::class);
})->with([
    'INV-202-0001',    // Year too short
    'INV-2025-001',    // Number too short
    'INVALID',          // Wrong format
    '2025-0001',        // Missing prefix
]);

// Use Laravel test helpers
use function Pest\Laravel\{postJson, getJson, assertDatabaseHas};
```

---

## 📦 PHP 8.2+ / Laravel Modern Features

- **Readonly Properties**: Value Objects, DTOs, Events
- **Enums**: `ApprovalStatus` (Backed String Enum)
- **Constructor Property Promotion**: Minden VO, DTO, Action
- **Named Arguments**: Factory methods, DTOs
- **Typed Properties**: Strict typing everywhere
- **Match Expression**: Status transitions, validation
- **Nullsafe Operator**: Repository find operations
- **Laravel Collections**: Domain collections
- **Service Container**: Auto-wiring, Singleton bindings
- **Events & Listeners**: Policy implementation
- **Form Requests**: Input validation

---

## 🚀 Development Workflow

### Daily Development
```bash
# Start environment
./vendor/bin/sail up -d

# Run tests (watch mode)
./vendor/bin/sail test --watch

# Code style fix
./vendor/bin/sail pint

# View logs
./vendor/bin/sail logs -f

# Stop environment
./vendor/bin/sail down
```

### Testing Commands
```bash
# Run all tests
./vendor/bin/sail test

# Run specific test
./vendor/bin/sail test --filter InvoiceTest

# Run with coverage
./vendor/bin/sail test --coverage --min=80

# Run only unit tests
./vendor/bin/sail test tests/Unit

# Run only feature tests
./vendor/bin/sail test tests/Feature
```

---

## ✅ Definition of Done

Egy lépés akkor tekinthető befejezettnek, ha:

1. ✅ Kód implementálva clean code elvek szerint
2. ✅ Pest tesztek írva és átmennek (zöld)
3. ✅ Laravel Pint formázva (zero violations)
4. ✅ Dokumentáció frissítve (ha szükséges)
5. ✅ Integration tesztek még mindig zöldek
6. ✅ Nincs linter/static analysis hiba

---

## 🎯 Priorizálás

### Must Have (P0) - Vizsgafeladat követelmények
- ✅ Domain Aggregates & Events (DDD)
- ✅ Value Objects with validation
- ✅ Command Handlers (Actions)
- ✅ Policy (Event Listener: InvoiceSubmitted → StartApproval)
- ✅ REST API endpoint (POST /invoices)
- ✅ Integration Test (vertical slice)
- ✅ Unit Tests (80%+ coverage)
- ✅ Docker environment (Laravel Sail)
- ✅ Logging (Policy execution)
- ✅ Clean Code & Modern PHP

### Nice to Have (P1)
- Query endpoints (GET /invoices/:id, GET /approvals/:id)
- Approve/Reject endpoints
- Persistent storage (database migrations)
- API authentication (Sanctum)
- Rate limiting

### Future Enhancements (P2)
- Event sourcing
- CQRS read models
- Message queue (Laravel Queue)
- Swagger/OpenAPI docs (Scramble package)
- Admin panel (Filament)

---

## 📊 Timeline Estimate

- **FÁZIS 1**: 30 min (Laravel setup)
- **FÁZIS 2**: 15 min (Shared kernel)
- **FÁZIS 3**: 2 óra (Invoice BC + tests)
- **FÁZIS 4**: 1.5 óra (Approval BC + tests)
- **FÁZIS 5**: 45 min (Repositories + bindings)
- **FÁZIS 6**: 1.5 óra (Actions + tests)
- **FÁZIS 7**: 1 óra (Policy + tests)
- **FÁZIS 8**: 1 óra (API Controller + validation)
- **FÁZIS 9**: 1.5 óra (Integration tests)
- **FÁZIS 10**: 1 óra (Coverage & quality)
- **FÁZIS 11**: 1 óra (Docs)

**Becsült összidő**: 12-13 óra tiszta implementáció

---

## 🤝 Együttműködési Mód

### Iteratív fejlesztés lépései:
1. **Megbeszéljük** a következő fázis céljait
2. **Implementáljuk** a kódot (TDD ahol lehetséges)
3. **Írjuk meg** a Pest teszteket
4. **Futtatjuk** a teszteket: `./vendor/bin/sail test`
5. **Ellenőrizzük** a működést
6. **Commitoljuk** a változásokat (opcionális)
7. **Továbblépünk** a következő fázisra

### TDD Flow (ahol alkalmazható):
```
RED → GREEN → REFACTOR
 ↓      ↓        ↓
Write  Make it  Clean up
Test   Pass     Code
```

**Kérdések bármikor feltehetők!** 🙌

---

## 📚 Laravel & DDD Hasznos Hivatkozások

### Laravel Specifikus
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Events](https://laravel.com/docs/12.x/events)
- [Laravel Service Container](https://laravel.com/docs/12.x/container)
- [Laravel Sail](https://laravel.com/docs/12.x/sail)
- [Pest Documentation](https://pestphp.com/)

### DDD & Architecture
- [DDD Aggregates](https://martinfowler.com/bliki/DDD_Aggregate.html)
- [Event Storming](https://www.eventstorming.com/)
- [Bounded Context](https://martinfowler.com/bliki/BoundedContext.html)
- [Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)

### PHP Modern Features
- [PHP 8.2 Release Notes](https://www.php.net/releases/8.2/en.php)
- [PHP Enums](https://www.php.net/manual/en/language.types.enumerations.php)
- [Readonly Properties](https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties)

---

## 🏛️ Architektúrális Döntések Magyarázata

### Miért DDD Laravel-ben?
- **Üzleti komplexitás**: A jóváhagyási folyamat üzleti logika, nem CRUD
- **Bounded Contexts**: Invoice Reception és Approval két független domén
- **Testability**: Domain layer framework-független → könnyű unit testing
- **Maintainability**: Clear separation of concerns

### Miért In-Memory Repositories?
- **Simplicity**: Nincs szükség adatbázis migrációkra a core flow-hoz
- **Fast Tests**: Tesztek villámgyorsak
- **Focus on Domain**: A domain logika a lényeg, nem a persistence
- **Easy Swap**: Interface-ek miatt később könnyen cserélhető Eloquent-re

### Miért Laravel Events Policy-nak?
- **Native Laravel**: Kihasználjuk a framework erejét
- **Async Ready**: Később könnyen queue-zható
- **Logging**: Event subscriber-ben könnyű logolni
- **Testable**: Event::fake() Pest segítség

### Bounded Context Communication
```
InvoiceReception BC          Approval BC
      │                          │
      │  InvoiceSubmitted        │
      │  (Domain Event)          │
      └──────────┬───────────────┘
                 │
         Policy/Listener
    (Application Layer Bridge)
```

---

**Státusz**: ✅ Laravel Projekt Terv elkészült!

**Következő lépés**: FÁZIS 1 - Laravel Setup & Configuration 🚀

---

## 📝 Quick Start Guide

```bash
# 1. Setup környezet
./vendor/bin/sail up -d

# 2. Generálj app key-t (ha szükséges)
./vendor/bin/sail artisan key:generate

# 3. Futtass migrációkat
./vendor/bin/sail artisan migrate

# 4. Futtasd teszteket
./vendor/bin/sail test

# 5. Kezdd el a fejlesztést (FÁZIS 2)
# Hozd létre az első domain osztályt...
```

Készen állsz? Melyik fázissal kezdjük? 💪

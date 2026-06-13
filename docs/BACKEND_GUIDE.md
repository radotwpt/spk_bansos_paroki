# Backend Development Guide - SPK Bansos

## Quick Start

### 1. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate APP_KEY
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spk_bansos
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed database with master data
php artisan db:seed
```

This will create:
- 4 Parokis (parishes)
- 4 Stasis (sub-parishes)
- 4 Lingkungans (community groups)
- 4 SAW Criteria with default weights
- 9 Test Users (1 super admin, 2 paroki, 3 stasi, 4 lingkungan leaders)

### 4. Start Development Server
```bash
php artisan serve
```
Server runs at `http://localhost:8000`

### 5. Test API
```bash
# Login with test user
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@spk-bansos.local",
    "password": "admin12345",
    "device_name": "CLI"
  }'

# Get current user
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer {access_token}"
```

---

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── AuthController.php          # Authentication
│           ├── UserController.php          # User management
│           ├── MasterDataController.php    # Generic CRUD
│           ├── CalonPenerimaController.php # Beneficiary candidates
│           ├── RankingController.php       # SAW ranking
│           ├── ReportController.php        # Reports & exports
│           ├── PenerimaBantuanController.php # Final beneficiaries
│           └── DashboardController.php     # Statistics & analytics
├── Models/                      # 17 Eloquent models
├── Services/
│   ├── SawCalculationService.php  # SAW algorithm implementation
│   └── AuditService.php           # Audit logging
├── Policies/                    # Authorization policies
│   ├── CalonPenerimaPolicy.php
│   └── PeriodeBantuanPolicy.php
└── Providers/
    └── AppServiceProvider.php

database/
├── migrations/           # Database schema
├── seeders/
│   ├── DatabaseSeeder.php      # Roles & orchestration
│   ├── MasterDataSeeder.php    # Master data
│   └── UserSeeder.php          # Test users
└── factories/            # Model factories

routes/
├── api.php              # API routes (v1)
└── web.php              # Web routes (welcome page)

docs/
├── API_DOCUMENTATION.md # Complete API reference
├── database-schema.md   # Database structure
└── BACKEND_GUIDE.md     # This file
```

---

## Test Accounts

| Role | Email | Password | Organization |
|------|-------|----------|--------------|
| Super Admin | admin@spk-bansos.local | admin12345 | - |
| Paroki 1 | paroki1@spk-bansos.local | paroki12345 | Paroki Santo Petrus |
| Paroki 2 | paroki2@spk-bansos.local | paroki12345 | Paroki Santa Maria |
| Stasi 1 | stasi1@spk-bansos.local | stasi12345 | Stasi Pusat |
| Stasi 2 | stasi2@spk-bansos.local | stasi12345 | Stasi Timur |
| Stasi 3 | stasi3@spk-bansos.local | stasi12345 | Stasi Utama |
| Lingkungan 1 | ling1@spk-bansos.local | lingkungan12345 | Lingkungan Kebayoran |
| Lingkungan 2 | ling2@spk-bansos.local | lingkungan12345 | Lingkungan Pondok Indah |
| Lingkungan 3 | ling3@spk-bansos.local | lingkungan12345 | Lingkungan Mampang |
| Lingkungan 4 | ling4@spk-bansos.local | lingkungan12345 | Lingkungan Darmo |

---

## Development Workflow

### Adding a New Endpoint

1. **Create/Update Controller**
   ```php
   // app/Http/Controllers/Api/MyController.php
   namespace App\Http\Controllers\Api;
   
   use App\Http\Controllers\Concerns\ApiResponse;
   use App\Http\Controllers\Controller;
   use Illuminate\Http\Request;
   
   class MyController extends Controller
   {
       use ApiResponse; // For consistent JSON responses
       
       public function index(Request $request) {
           return $this->success($data, 'Message');
       }
   }
   ```

2. **Register Route in routes/api.php**
   ```php
   Route::apiResource('my-resource', MyController::class)
       ->middleware('auth:sanctum');
   ```

3. **Add Authorization Policy (if needed)**
   ```php
   // app/Policies/MyResourcePolicy.php
   public function view(User $user, MyResource $resource): bool {
       return true; // Your logic
   }
   ```

4. **Test Endpoint**
   ```bash
   php artisan tinker
   
   # Or via curl
   curl -X GET http://localhost:8000/api/v1/my-resource \
     -H "Authorization: Bearer {token}"
   ```

---

## Key Patterns & Conventions

### API Response Format
```php
// Success response
$this->success($data, 'Success message', 201);

// Error response
$this->error('Error message', 422, ['field' => ['error 1', 'error 2']]);
```

### Data Visibility
Use the `visibleTo()` scope to respect role-based access:
```php
$data = Model::query()->visibleTo($user)->get();
```

### Audit Logging
Automatically log all changes:
```php
$this->auditService->record(
    'event.name',
    $model,
    $oldValues,
    $newValues,
    request: $request
);
```

### Role Checking
```php
if ($user->hasRole('paroki')) {
    // Paroki-specific logic
}
```

---

## Database Operations

### Create Period with Candidates
```php
// Create period
$period = PeriodeBantuan::create([
    'paroki_id' => 1,
    'code' => 'BANSOS202406',
    'name' => 'Bantuan Juni 2026',
    'quota' => 50,
    'total_budget' => 150000000,
    'default_aid_amount' => 3000000,
]);

// Add candidates
foreach ($candidateData as $data) {
    $period->calonPenerimas()->create($data);
}
```

### Calculate Rankings
```php
$sawService = app(SawCalculationService::class);
$results = $sawService->calculateForPeriod($period, $user);

// Results are ranked SawResult models
foreach ($results as $result) {
    echo "Rank: {$result->rank}, Score: {$result->final_score}\n";
}
```

### Create Beneficiaries
```php
// After finalization, penerima_bantuans are created
$beneficiaries = $period->penerimaBantuans()->where('status', 'selected')->get();

foreach ($beneficiaries as $b) {
    $b->update(['disbursement_date' => now()->addDays(7)]);
}
```

---

## Testing

### Running Tests
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/AuthTest.php

# With coverage
php artisan test --coverage
```

### Creating Tests
```bash
# Generate test file
php artisan make:test FeatureNameTest

# Example test
public function test_user_can_login() {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'password',
    ]);
    
    $response->assertStatus(200)
        ->assertHasToken()
        ->assertJsonStructure(['data' => ['token_type', 'access_token']]);
}
```

---

## Troubleshooting

### Database Connection Error
```
SQLSTATE[HY000] [2002] Connection refused
```
- Ensure MySQL is running
- Check DB_HOST, DB_PORT in .env
- Verify database exists: `mysql -u root -e "CREATE DATABASE IF NOT EXISTS spk_bansos;"`

### Token Expired
- Request new token via login endpoint
- Check `SANCTUM_EXPIRATION` in .env (in minutes)

### Authorization Error
- Check user role vs required role
- Verify role relationships in User model
- Check policy conditions

### Validation Errors
- Review error response for specific field errors
- Check validation rules in controller
- Ensure data types match schema

---

## Performance Optimization

### Database Queries
```php
// Avoid N+1 queries with eager loading
$models = Model::with(['relationship1', 'relationship2'])->get();

// Use select() to limit columns
$data = Model::select('id', 'name', 'email')->get();
```

### Caching
```php
// Cache frequently accessed data
$roles = Cache::remember('roles', 60 * 60, function () {
    return Role::all();
});
```

### Pagination
```php
// Always paginate large result sets
$data = Model::paginate(15); // Not all()
```

---

## Security Best Practices

1. **Input Validation**: Always validate user input
2. **Authorization**: Use policies for resource access
3. **Audit Logging**: Log all significant changes
4. **Rate Limiting**: Implemented by default
5. **CORS**: Configure for frontend domains only
6. **SQL Injection**: Use parameterized queries (Eloquent does this)
7. **XSS Protection**: Return JSON, not HTML

---

## Useful Artisan Commands

```bash
# Database
php artisan migrate              # Run migrations
php artisan migrate:refresh      # Reset and re-seed
php artisan db:seed              # Run seeders
php artisan db:seed --class=MasterDataSeeder

# Models & Factories
php artisan make:model Model -m  # Create model with migration
php artisan make:factory ModelFactory

# Controllers & Resources
php artisan make:controller Api/MyController --api

# Tests
php artisan make:test FeatureTest
php artisan make:test Unit/ServiceTest --unit

# Cache & Optimization
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Debugging
php artisan tinker              # Interactive shell
php artisan tail storage/logs/laravel.log
```

---

## Next Steps for Frontend Integration

1. **Configure CORS**: Update `config/cors.php` with frontend URL
2. **Setup Sanctum**: Configure stateful domains in `.env`
3. **Generate API Token**: Use login endpoint to get token
4. **Implement Authentication**: Store token in localStorage/sessionStorage
5. **Handle Errors**: Implement error boundaries for API failures
6. **Add Loading States**: Show progress indicators during API calls

---

## Documentation & Support

- **API Docs**: See [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Database Schema**: See [database-schema.md](database-schema.md)
- **Laravel Docs**: https://laravel.com/docs/11.x
- **Sanctum Docs**: https://laravel.com/docs/11.x/sanctum
- **Eloquent Docs**: https://laravel.com/docs/11.x/eloquent

---

## Team Contact
For development questions or issues, contact the SPK Bansos development team.

Last Updated: June 6, 2026

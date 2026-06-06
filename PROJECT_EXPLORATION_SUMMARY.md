# SPK Bansos - Project Architecture & Exploration Summary

**Date**: June 6, 2026  
**Project**: Social Assistance Decision Support System (SPK Bansos)  
**Framework**: Laravel 12 + JavaScript + Tailwind CSS  
**Status**: Production Ready  

---

## 📋 Executive Summary

This is a **role-based, hierarchical decision support system** for managing social assistance distribution through a Catholic parish structure. The system implements:

- **Role-based Access Control**: 5 distinct user roles with specific responsibilities
- **Hierarchical Organization**: Parish (Paroki) → Stasi → Environment (Lingkungan)
- **Workflow State Machine**: 6-step approval workflow for candidates
- **Multi-Algorithm Ranking**: SAW (Simple Additive Weighting) algorithm with configurable weights
- **PWA Offline Support**: Service Worker + IndexedDB for offline-first experience
- **Document Generation**: Template-based letter generation for official correspondence

---

## 🏗️ Project Structure Overview

```
spk_bansos/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── KetuaLingkunganStasiController.php    [Phase 2]
│   │   │   │   ├── StasiController.php                   [Phase 2]
│   │   │   │   ├── KetuaLingkunganParokiController.php   [Phase 3]
│   │   │   │   ├── ParokiController.php                  [Phase 4]
│   │   │   │   ├── SawController.php                     [Ranking]
│   │   │   │   ├── DocumentTemplateController.php        [Phase 4]
│   │   │   │   ├── GeneratedLetterController.php         [Phase 4]
│   │   │   │   └── Master/                               [Admin]
│   │   │   └── Middleware/
│   │   │       └── CheckRoleMiddleware.php               [Role guard]
│   │   └── Requests/
│   │       ├── StoreCalonPenerimaRequest.php
│   │       ├── UpdateCalonPenerimaRequest.php
│   │       └── RejectCalonPenerimaRequest.php
│   ├── Models/
│   │   ├── User.php                     [Auth user]
│   │   ├── Stasi.php                    [Stasi entity]
│   │   ├── LingkunganStasi.php           [Lingkungan under Stasi]
│   │   ├── LingkunganParoki.php          [Lingkungan under Paroki]
│   │   ├── CalonPenerima.php             [Candidate]
│   │   ├── BansosPeriod.php              [Period/round]
│   │   ├── SawCriterion.php              [Ranking criteria]
│   │   ├── SawWeight.php                 [Weights per period]
│   │   ├── SawResult.php                 [Ranking results]
│   │   ├── DocumentTemplate.php          [Letter template]
│   │   ├── GeneratedLetter.php           [Generated document]
│   │   └── ActivityLog.php               [Audit trail]
│   ├── Services/
│   │   ├── BansosWorkflowService.php     [State transitions]
│   │   ├── SawCalculationService.php     [Ranking algorithm]
│   │   ├── DocumentService.php           [Document generation]
│   │   └── ActivityLogService.php        [Audit logging]
│   ├── Policies/
│   │   ├── CalonPenerimaPolicy.php       [Authorization rules]
│   │   └── ActivityLogPolicy.php
│   └── Scopes/
│       └── TenantDataScope.php           [Multi-tenancy filter]
├── database/
│   ├── migrations/
│   │   ├── 2026_05_24_*_create_*.php     [Schema]
│   │   └── 2026_05_24_000100_modify_users_table_add_fields.php
│   ├── seeders/
│   │   └── DatabaseSeeder.php
│   └── database.sqlite                  [SQLite DB]
├── resources/
│   ├── js/
│   │   ├── app-modern.js                 [Main app shell ~1000+ lines]
│   │   ├── crud-helpers.js               [Reusable CRUD utilities ~270 lines]
│   │   ├── bootstrap.js                  [API initialization]
│   │   ├── idb-helpers.js                [IndexedDB wrapper]
│   │   ├── pwa.js                        [PWA service]
│   │   ├── sw-register.js                [Service Worker registration]
│   │   └── modules/
│   │       ├── ketua-lingkungan-stasi.js [Candidate input ~500 lines]
│   │       ├── stasi.js                  [Approval workflow ~400 lines]
│   │       ├── ketua-lingkungan-paroki.js [Ranking config ~480 lines]
│   │       └── paroki.js                 [Final decisions ~420 lines]
│   └── views/
│       └── app.blade.php                 [SPA container]
├── routes/
│   ├── api.php                           [API endpoints]
│   └── web.php                           [SPA route]
└── tests/
    ├── Feature/
    │   ├── ApiAuthAndCandidateTest.php
    │   ├── WorkflowCalonPenerimaTest.php
    │   ├── SawCalculationTest.php
    │   └── MasterDataTest.php
    └── Unit/
        └── SawCalculationServiceTest.php
```

---

## 👥 Role-Based System Implementation

### User Roles (5 types)

```php
// Defined in: database/migrations/2026_05_24_000100_modify_users_table_add_fields.php

enum('role', [
    'super_admin',
    'ketua_lingkungan_stasi',     // Ketua Lingkungan Stasi
    'stasi',                        // Stasi
    'ketua_lingkungan_paroki',     // Ketua Lingkungan Paroki
    'paroki'                        // Paroki
])
```

### User Model Relationships

```php
class User {
    // Foreign keys
    stasi_id              → belongsTo(Stasi)
    lingkungan_paroki_id  → belongsTo(LingkunganParoki)
    lingkungan_stasi_id   → belongsTo(LingkunganStasi)
}
```

### Role Permissions (Middleware Pattern)

**File**: `app/Http/Middleware/CheckRoleMiddleware.php`

```php
// Usage in routes: middleware(['role:role1,role2,role3'])
Route::middleware(['role:ketua_lingkungan_stasi,super_admin'])->prefix('v1/lingkungan-stasi')->group(function () {
    // These routes only accessible to ketua_lingkungan_stasi and super_admin
});
```

### 5 Roles & Their Modules

| Role | Module | Responsibilities | API Prefix |
|------|--------|------------------|-----------|
| **Ketua Lingkungan Stasi** | Candidate Input | Create & manage candidates, Submit to Stasi | `/v1/lingkungan-stasi` |
| **Stasi** | Approval Workflow | Review & approve/reject candidates | `/v1/stasi` |
| **Ketua Lingkungan Paroki** | SAW Ranking | Configure weights, execute ranking | `/v1/lingkungan-paroki` |
| **Paroki** | Final Decisions | Review rankings, finalize decisions | `/v1/paroki` |
| **Super Admin** | Master Data | Manage users, periods, entities | `/v1/master` |

---

## 🏛️ "Lingkungan" Hierarchy (Critical Architecture)

The system uses a **nested hierarchical structure** inspired by Catholic parish organization:

### Hierarchy Levels

```
PAROKI (Parish)
  ├─ LINGKUNGAN PAROKI (Environment/Ward under Parish)
  │    └─ Users: ketua_lingkungan_paroki, paroki
  │
STASI (Deanery/Administrative unit)
  ├─ LINGKUNGAN STASI (Environment/Ward under Stasi)
  │    └─ Users: ketua_lingkungan_stasi, stasi
```

### Database Schema

**Stasis table** (Core organizational unit)
```php
id, nama_stasi, kode_stasi, alamat, timestamps
Example: "Stasi Santo Petrus", "ST001"
```

**Lingkungan Stasis table** (Subdivisions under Stasi)
```php
id, stasi_id, nama_lingkungan_stasi, kode_lingkungan, timestamps
Example: stasi_id=1, nama="Lingkungan 1", kode="L01"
```

**Lingkungan Parokis table** (Subdivisions at Parish level)
```php
id, nama_lingkungan_paroki, kode_wilayah, timestamps
Example: nama="Wilayah A", kode="WA01"
```

### User Hierarchy Mapping

```php
// User can be assigned to:
- stasi_id (belongs to a Stasi)
- lingkungan_stasi_id (belongs to a LingkunganStasi within that Stasi)
- lingkungan_paroki_id (belongs to a LingkunganParoki)

// Based on role:
if (role === 'ketua_lingkungan_stasi') {
    // Must have: stasi_id + lingkungan_stasi_id
    // Access: Only their lingkungan_stasi's candidates
}
if (role === 'stasi') {
    // Must have: stasi_id
    // Access: All candidates in their stasi
}
if (role === 'ketua_lingkungan_paroki') {
    // Must have: lingkungan_paroki_id
    // Access: Ranking data for their lingkungan_paroki
}
if (role === 'paroki') {
    // Must have: lingkungan_paroki_id OR none
    // Access: All final ranking data
}
```

### Example User Creation (Seeder)

```php
// From database/seeders/DatabaseSeeder.php

// Ketua Lingkungan Stasi
User::create([
    'name' => 'Ketua Lingkungan Stasi',
    'email' => 'kls@example.com',
    'password' => bcrypt('password'),
    'role' => 'ketua_lingkungan_stasi',
    'stasi_id' => $stasi->id,
    'lingkungan_stasi_id' => $lingkunganStasi->id,
]);

// Stasi
User::create([
    'name' => 'Stasi',
    'email' => 'stasi@example.com',
    'password' => bcrypt('password'),
    'role' => 'stasi',
    'stasi_id' => $stasi->id,
]);

// Ketua Lingkungan Paroki
User::create([
    'name' => 'Ketua Lingkungan Paroki',
    'email' => 'klp@example.com',
    'password' => bcrypt('password'),
    'role' => 'ketua_lingkungan_paroki',
    'lingkungan_paroki_id' => $lingkunganParoki->id,
]);
```

---

## 📊 Database Schema (Complete Relationships)

### Core Tables

**users** (Extends Laravel)
```sql
id, name, email, password, role (enum), stasi_id (FK), 
lingkungan_paroki_id (FK), lingkungan_stasi_id (FK)

INDEX: idx_user_hierarchy (role, stasi_id, lingkungan_paroki_id, lingkungan_stasi_id)
```

**stasis** (Organizational units)
```sql
id, nama_stasi (unique), kode_stasi (unique), alamat, timestamps
```

**lingkungan_stasis** (Subdivisions under Stasi)
```sql
id, stasi_id (FK→stasis), nama_lingkungan_stasi, kode_lingkungan (unique), timestamps
```

**lingkungan_parokis** (Subdivisions under Paroki)
```sql
id, nama_lingkungan_paroki (unique), kode_wilayah (unique), timestamps
```

**calon_penerimas** (Candidates - Core entity)
```sql
id, bansos_period_id (FK), lingkungan_stasi_id (FK), stasi_id (FK),
nik (16), nama_lengkap, alamat_kristen, pendapatan_keluarga (decimal),
jumlah_tanggungan (int), status_tempat_tinggal (enum), status_hubungan (enum),
urgensi_tambahan_tekstual (text), saw_score (decimal), 
rank_global, rank_internal_stasi, status_alur (enum - 6 states),
is_penerima_sah (bool), nominal_bansos_disetujui (decimal), timestamps

INDEX: idx_calon_bansos_period_status, idx_calon_nik
GLOBAL SCOPE: TenantDataScope (filters by lingkungan_stasi_id)
```

**bansos_periods** (Rounds/Periods)
```sql
id, nama_periode, tahun, status_periode (enum), is_locked (bool),
locked_by (FK→users), locked_at (timestamp), timestamps
```

**saw_weights** (Ranking weights per period)
```sql
id, bansos_period_id (FK), criteria_id (FK), bobot (decimal 0-1), 
created_by (FK→users), timestamps

Example: Period 1, C1=0.40, C2=0.30, C3=0.15, C4=0.15
```

**saw_results** (Final rankings)
```sql
id, bansos_period_id (FK), calon_penerima_id (FK), 
score (decimal), rank (int), timestamps
```

**document_templates** (Letter templates)
```sql
id, nama, tipe_template, konten (longText), created_by (FK), timestamps
```

**generated_letters** (Generated documents)
```sql
id, document_template_id (FK), calon_penerima_id (FK),
nomor_surat (unique), konten_final (longText), 
phase1-4 specific columns, timestamps
```

**activity_logs** (Audit trail)
```sql
id, aksi, tipe_model, model_id, user_id (FK), 
deskripsi, data_berubah (json), timestamps
```

### Relationship Diagram

```
User (1) ──── (1) Stasi
              (1) LingkunganStasi
              (1) LingkunganParoki
              (n) GeneratedLetter [created_by]
              (n) ActivityLog [user_id]

Stasi (1) ──── (n) LingkunganStasi
             (n) CalonPenerima
             (n) User

LingkunganStasi (1) ──── (n) CalonPenerima
                        (n) User
                (1) Stasi

BansosPeriod (1) ──── (n) CalonPenerima
                     (n) SawWeight
                     (n) SawResult

CalonPenerima (1) ──── (1) BansosPeriod
                      (1) Stasi
                      (1) LingkunganStasi
                      (n) ActivityLog
                      (n) GeneratedLetter
                      (n) SawResult

LingkunganParoki (1) ──── (n) User
```

---

## 🔄 Workflow State Machine

### Candidate Status Flow (6 States)

```
┌─────────────┐
│   DRAFT     │ (Initial state)
└──────┬──────┘
       │ submit_to_stasi (by ketua_lingkungan_stasi)
       ▼
┌──────────────────────┐
│ DIAJUKAN_KE_STASI    │ (Waiting for stasi)
└──────┬──────┬────────┘
       │      │
       │      └─ reject_by_stasi ──► DITOLAK
       │         (by stasi)
       │
       └─ approve_by_stasi (by stasi)
          │
          ▼
┌──────────────────────────────┐
│  DISETUJUI_STASI             │ (Stasi approved)
└──────┬───────────────────────┘
       │ trigger_saw (automatic)
       ▼
┌──────────────────────────────┐
│ DIRANKING_LINGKUNGAN_PAROKI  │ (During SAW calculation)
└──────┬───────────────────────┘
       │ paroki_decision_final (by paroki)
       ├─► DISETUJUI_PAROKI (Approved)
       ├─► DITOLAK (Rejected)
       └─► Custom status via intervention
```

### Service Implementation

**File**: `app/Services/BansosWorkflowService.php`

```php
public function submitToStasi(int $calonId, int $userId): bool
    // draft → diajukan_ke_stasi
    // Logs activity

public function approveByStasi(int $calonId, int $userId): bool
    // diajukan_ke_stasi → disetujui_stasi
    // Logs activity

public function rejectData(int $calonId, string $reason, int $userId): bool
    // diajukan_ke_stasi → ditolak
    // Logs activity with reason

public function triggerSaw(int $periodId, ?int $userId): array
    // disetujui_stasi → diranking_lingkungan_paroki
    // Calls SawCalculationService for scoring

public function sendRankingToParoki(int $periodId, ?int $userId): bool
    // Locks period, prevents further modifications
```

---

## 🎯 API Architecture & Endpoints

### Endpoint Structure

**Prefix Pattern**: `/api/v1/{role-specific-path}`

### Authentication

```php
// Login endpoint (no auth required)
POST /api/v1/auth/login
Body: { email, password }
Returns: { user, token }

// Get current user
GET /api/v1/auth/me
Headers: Authorization: Bearer {token}

// Logout
POST /api/v1/auth/logout
Headers: Authorization: Bearer {token}
```

### Role-Based Endpoints

**Ketua Lingkungan Stasi** (Candidate Input)
```
GET  /v1/lingkungan-stasi/calon-penerima          [index]
POST /v1/lingkungan-stasi/calon-penerima          [store]
PUT  /v1/lingkungan-stasi/calon-penerima/{id}    [update]
DELETE /v1/lingkungan-stasi/calon-penerima/{id}  [destroy]
POST /v1/lingkungan-stasi/calon-penerima/{id}/ajukan [submit]
```

**Stasi** (Approval)
```
GET  /v1/stasi/calon-penerima-rekap               [index recap]
POST /v1/stasi/calon-penerima/{id}/approve        [approve]
POST /v1/stasi/calon-penerima/{id}/reject         [reject with reason]
```

**Ketua Lingkungan Paroki** (Ranking)
```
GET  /v1/lingkungan-paroki/saw/weights/{periodId}     [get weights]
POST /v1/lingkungan-paroki/saw/weights/{periodId}     [save weights]
GET  /v1/lingkungan-paroki/saw/preview/{periodId}     [preview results]
GET  /v1/lingkungan-paroki/saw/results/{periodId}     [get results]
POST /v1/lingkungan-paroki/proses-saw/{periodId}      [execute ranking]
POST /v1/lingkungan-paroki/kirim-ke-paroki/{periodId} [send to paroki]
```

**Paroki** (Final Decisions)
```
GET  /v1/paroki/ranking-results                   [all results]
GET  /v1/paroki/ranking/{periodId}                [results for period]
POST /v1/paroki/penerima/{id}/keputusan           [finalize decision]
POST /v1/paroki/surat-edaran/generate             [generate letter]
```

**Super Admin** (Master Data)
```
GET/POST/PUT/DELETE /v1/master/stasis             [manage stasis]
GET/POST/PUT/DELETE /v1/master/lingkungan-stasis  [manage lingkungan stasi]
GET/POST/PUT/DELETE /v1/master/lingkungan-parokis [manage lingkungan paroki]
GET/POST/PUT/DELETE /v1/master/bansos-periods     [manage periods]
GET/POST/PUT/DELETE /v1/master/users              [manage users]
```

---

## 🎨 Frontend Module Architecture

### Module Pattern (Class-Based)

Each module follows this structure:

```javascript
export class ModuleName {
    constructor() {
        this.crud = new CrudManager('/api-endpoint');  // API wrapper
        this.state = { /* module state */ };
    }

    async init(token, apiBase) {
        // Initialize with auth token and API base URL
        // Load initial data
    }

    render() {
        // Render UI to #content-region
        // Build HTML string, update DOM
    }

    attachEventListeners() {
        // Wire up event handlers
        // Search, filter, action buttons, forms
    }

    // Additional methods for business logic
}
```

### 4 Production Modules

#### 1. Ketua Lingkungan Stasi Module (`ketua-lingkungan-stasi.js` ~500 lines)

**File**: `resources/js/modules/ketua-lingkungan-stasi.js`

**Features**:
- Create new candidates (modal form)
- Search & filter by name, NIK, email, status
- Edit candidates (draft status only)
- Delete candidates (draft status only)
- Submit to Stasi (workflow action)
- Pagination (10 items/page)
- Status badges (color-coded)

**Key Methods**:
```javascript
loadCandidates()          // Fetch from API
render()                  // Render table + form
renderForm()              // Modal form HTML
attachEventListeners()    // Wire events
onSearchChange()          // Filter logic
onStatusFilter()          // Status filter
onSubmitCandidate()       // Workflow action
```

**Data Fields Displayed**:
- Nama Lengkap, NIK, Email, Pendapatan, Tanggungan, Status

#### 2. Stasi Module (`stasi.js` ~400 lines)

**File**: `resources/js/modules/stasi.js`

**Features**:
- View recap of submitted candidates
- Detail modal with full candidate info
- Approve/Reject actions
- Search & filter
- Pagination

**Key Methods**:
```javascript
loadRecap()               // Fetch submitted candidates
render()                  // Render table
showDetailModal()         // Show candidate details
approveCandidate()        // Approval action
rejectCandidate()         // Rejection action (with reason)
```

#### 3. Ketua Lingkungan Paroki Module (`ketua-lingkungan-paroki.js` ~480 lines)

**File**: `resources/js/modules/ketua-lingkungan-paroki.js`

**Features**:
- Period selection
- Configure SAW weights (C1, C2, C3, C4)
- Weight validation (total = 100%)
- Execute ranking
- View results table
- Download as CSV
- Send to Paroki

**Key Methods**:
```javascript
loadPeriods()             // Fetch available periods
loadWeights()             // Get current weights
saveWeights()             // Validate & save weights
executeSaw()              // Run ranking algorithm
downloadCsv()             // Export results
sendToParoki()            // Send results to next role
```

#### 4. Paroki Module (`paroki.js` ~420 lines)

**File**: `resources/js/modules/paroki.js`

**Features**:
- View final ranking results
- Decision modal for each candidate
- Multiple decision options
- Notes/reason field
- Generate circular letter
- Search & filter
- Decision status badges

**Key Methods**:
```javascript
loadResults()             // Fetch ranking results
render()                  // Render table
showDecisionModal()        // Decision dialog
finalizaDecision()         // Save decision
generateLetter()           // Generate document
```

### CRUD Helper Utility (`crud-helpers.js` ~270 lines)

**File**: `resources/js/crud-helpers.js`

Provides reusable CRUD operations for all modules:

```javascript
export default class CrudManager {
    constructor(endpoint) {}

    // Data fetching & API calls
    async fetchItems(token, apiBase)           // GET endpoint
    async createItem(data, token, apiBase)     // POST endpoint
    async updateItem(id, data, token, apiBase) // PUT endpoint
    async deleteItem(id, token, apiBase)       // DELETE endpoint

    // Filtering & Search
    setSearch(query)                            // Filter by query
    setFilter(field, value)                    // Filter by field
    getPaginatedItems()                        // Get current page items
    getTotalPages()                            // Calculate pages

    // Utilities
    static escapeHtml(text)                    // XSS protection
    static renderTableHeader(columns)          // Generate <thead>
    static renderTableRow(item, columns)       // Generate <tr>
    static renderPagination(page, totalPages)  // Generate pagination UI
    static formatDate(date)                    // Date formatting
}
```

---

## 🔐 Authorization & Security Patterns

### Role Middleware Pattern

**File**: `app/Http/Middleware/CheckRoleMiddleware.php`

```php
// Usage in routes
Route::middleware(['role:ketua_lingkungan_stasi,super_admin'])
    ->prefix('v1/lingkungan-stasi')
    ->group(function () {
        // Only these roles can access
    });
```

### Policy Authorization Pattern

**File**: `app/Policies/CalonPenerimaPolicy.php`

```php
class CalonPenerimaPolicy {
    public function update(User $user, CalonPenerima $calon): bool
    public function delete(User $user, CalonPenerima $calon): bool
    public function approve(User $user, CalonPenerima $calon): bool
    public function reject(User $user, CalonPenerima $calon): bool
}

// Usage in controller
$this->authorize('update', $calon);  // Throws AuthorizationException if fails
```

### Tenant Data Scope

**File**: `app/Scopes/TenantDataScope.php`

Applied globally to `CalonPenerima` model:

```php
protected static function boot()
{
    parent::boot();
    static::addGlobalScope(new TenantDataScope);
}

// Automatically filters queries by:
// - lingkungan_stasi_id for ketua_lingkungan_stasi
// - stasi_id for stasi
// - lingkungan_paroki_id for ketua_lingkungan_paroki
```

---

## 🧮 SAW Algorithm Implementation

### Simple Additive Weighting (SAW)

**File**: `app/Services/SawCalculationService.php`

**Formula**:
```
Score = (w1 × norm_c1) + (w2 × norm_c2) + (w3 × norm_c3) + (w4 × norm_c4)

Where:
- w = weight (configured by ketua_lingkungan_paroki)
- norm = normalized value (0-1)
```

**Criteria** (C1-C4):

| Criteria | Name | Type | Weight | Calculation |
|----------|------|------|--------|-------------|
| C1 | Pendapatan Keluarga | Cost | 40% | Lower is better |
| C2 | Jumlah Tanggungan | Benefit | 30% | Higher is better |
| C3 | Status Tempat Tinggal | Benefit | 15% | Sewa/Numpang better |
| C4 | Status Hubungan | Benefit | 15% | Lajang/Cerai better |

**Implementation Steps**:
1. Get all `disetujui_stasi` candidates for period
2. Extract C1-C4 values
3. Normalize values (0-1 range)
4. Apply weights
5. Calculate final scores
6. Rank by score (highest first)
7. Update `rank_global`, `rank_internal_stasi`, `status_alur`

---

## 📁 Key Files to Modify for New Features

### For "Ketua Lingkungan" Module Implementation

**Backend (Laravel)**:
1. **Model**: `app/Models/YourNewModel.php` - Define structure
2. **Migration**: `database/migrations/YYYY_MM_DD_create_table.php` - DB schema
3. **Controller**: `app/Http/Controllers/Api/YourController.php` - API endpoints
4. **Request**: `app/Http/Requests/StoreYourRequest.php` - Validation
5. **Route**: `routes/api.php` - Add new route group with middleware
6. **Policy**: `app/Policies/YourPolicy.php` (if needed) - Authorization
7. **Service**: `app/Services/YourService.php` (if needed) - Business logic

**Frontend (JavaScript)**:
1. **Module**: `resources/js/modules/your-module.js` - UI component
2. **App Integration**: Update `resources/js/app-modern.js` - Import & register module
3. **Views**: `resources/views/app.blade.php` - Add menu item if needed

### Critical Files for Role System

- **Role Definition**: `database/migrations/2026_05_24_000100_modify_users_table_add_fields.php`
- **Role Middleware**: `app/Http/Middleware/CheckRoleMiddleware.php`
- **User Model Relations**: `app/Models/User.php`
- **Hierarchy Models**: `app/Models/Stasi.php`, `LingkunganStasi.php`, `LingkunganParoki.php`
- **Routes**: `routes/api.php` (all role-based route groups)

---

## 🧪 Testing Patterns

### Test Locations

**Feature Tests**: `tests/Feature/`
- Test full API workflows
- Test authorization
- Test state transitions

**Unit Tests**: `tests/Unit/`
- Test service logic
- Test calculation algorithms

### Example Test Pattern

```php
// tests/Feature/WorkflowCalonPenerimaTest.php

class WorkflowCalonPenerimaTest extends TestCase
{
    public function test_ketua_lingkungan_stasi_dapat_membuat_calon()
    {
        $user = User::factory()->create(['role' => 'ketua_lingkungan_stasi']);
        $response = $this->actingAs($user)->postJson('/api/v1/lingkungan-stasi/calon-penerima', [
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Test',
            // ...
        ]);
        $this->assertTrue($response->json('success'));
    }
}
```

---

## 🚀 Existing Module Patterns to Follow

### Backend Pattern

```php
// Controllers/Api/YourModuleController.php
class YourModuleController extends Controller {
    use AuthorizesRequests;
    use RespondsWithApi;

    public function index(Request $request) {
        $user = $request->user();
        
        // Validate user hierarchy
        if ($user->role !== 'super_admin' && !$user->lingkungan_stasi_id) {
            return $this->error('User tidak terhubung ke lingkungan.', 422);
        }
        
        // Query with role-based filtering
        $items = Model::query()
            ->when($user->role !== 'super_admin', 
                fn($q) => $q->where('lingkungan_stasi_id', $user->lingkungan_stasi_id))
            ->get();
        
        return $this->success($items, 'Data berhasil diambil.');
    }
}
```

### Frontend Pattern

```javascript
export class YourModule {
    constructor() {
        this.crud = new CrudManager('/api/v1/your-endpoint');
        this.state = { token: null, apiBase: null };
    }

    async init(token, apiBase) {
        this.state.token = token;
        this.state.apiBase = apiBase;
        await this.loadData();
    }

    async loadData() {
        try {
            await this.crud.fetchItems(this.state.token, this.state.apiBase);
            this.render();
        } catch (error) {
            this.showError('Gagal memuat data');
        }
    }

    render() {
        const container = document.getElementById('content-region');
        // Build HTML and update DOM
    }

    attachEventListeners() {
        // Wire up event handlers
    }
}
```

---

## 📝 Current Test Coverage

All tests passing (19/19):

```
✅ Unit Tests (1)
   - ExampleTest
   - SawCalculationServiceTest

✅ Feature Tests (18)
   - ApiAuthAndCandidateTest (8 tests)
   - WorkflowCalonPenerimaTest (4 tests)
   - SawCalculationTest (1)
   - MasterDataTest (3)
   - ExampleTest (1)
   - Phase4 Letter Generation (1)
```

---

## 🎯 Key Implementation Insights

### 1. Multi-Tenancy via TenantDataScope
- `CalonPenerima` model applies automatic filtering
- Prevents IDOR vulnerabilities
- Filters by `lingkungan_stasi_id` for stasi users

### 2. Role-Based API Access
- Routes wrapped in `middleware(['role:role1,role2'])`
- Additional checks in controllers for super_admin bypass
- Authorization policies for fine-grained control

### 3. Workflow State Machine
- Single `status_alur` column with 6 states
- Transitions validated by `BansosWorkflowService`
- Activity logging on every state change

### 4. Frontend Module Registration
- Modules imported in `app-modern.js`
- Registered to menu items dynamically
- CRUD helper provides consistent data operations

### 5. SAW Algorithm
- Configurable weights per period
- Normalized scoring (0-1 range)
- Automatic candidate ranking on execution

---

## ✅ Checklist for "Ketua Lingkungan" Module Development

- [ ] Define database schema (migration)
- [ ] Create Model with relationships
- [ ] Create Controller with role middleware
- [ ] Create FormRequest for validation
- [ ] Define routes in `routes/api.php`
- [ ] Create Policy if custom authorization needed
- [ ] Add tests for authorization & workflow
- [ ] Create frontend module class
- [ ] Integrate module in `app-modern.js`
- [ ] Test end-to-end workflow
- [ ] Add menu item integration
- [ ] Document in module guide

---

**Last Updated**: June 6, 2026  
**Version**: 1.0 - Production Ready

# Ketua Lingkungan Module - Quick Reference & Implementation Guide

**Created**: June 6, 2026  
**Purpose**: Quick lookup for implementing the "ketua lingkungan" role-specific module

---

## 🎯 "Ketua Lingkungan" Role Overview

The **"Ketua Lingkungan"** (Environment/Ward Leader) is a generic term in the system that maps to two specific implementations:

### Current Implementations

1. **Ketua Lingkungan Stasi** (Ward leader at Stasi level)
   - Role: `ketua_lingkungan_stasi`
   - Module: Candidate Input & Management
   - API Prefix: `/v1/lingkungan-stasi`
   - Files: See section below

2. **Ketua Lingkungan Paroki** (Ward leader at Paroki level)
   - Role: `ketua_lingkungan_paroki`
   - Module: SAW Ranking Configuration
   - API Prefix: `/v1/lingkungan-paroki`
   - Files: See section below

---

## 📂 Key Implementation Files Reference

### For Ketua Lingkungan Stasi Module

#### Backend Files

| File | Purpose | Lines | Key Methods |
|------|---------|-------|-------------|
| `app/Http/Controllers/Api/KetuaLingkunganStasiController.php` | API endpoints for candidates | ~150 | `index()`, `store()`, `update()`, `destroy()`, `submitToStasi()` |
| `app/Http/Requests/StoreCalonPenerimaRequest.php` | Validation for create | ~50 | `rules()`, `messages()` |
| `app/Http/Requests/UpdateCalonPenerimaRequest.php` | Validation for update | ~50 | `rules()`, `messages()` |
| `app/Policies/CalonPenerimaPolicy.php` | Authorization rules | ~80 | `update()`, `delete()`, `approve()`, `reject()` |
| `app/Models/CalonPenerima.php` | Data model | ~60 | Relations to Stasi, LingkunganStasi, BansosPeriod |
| `app/Models/LingkunganStasi.php` | Lingkungan model | ~30 | Relations to Stasi, User, CalonPenerima |
| `routes/api.php` | Route definitions | ~200+ | Middleware: `role:ketua_lingkungan_stasi,super_admin` |

#### Frontend Files

| File | Purpose | Lines | Key Classes/Methods |
|------|---------|-------|---------------------|
| `resources/js/modules/ketua-lingkungan-stasi.js` | Main module | ~500 | `KetuaLingkunganStasiModule` class |
| `resources/js/crud-helpers.js` | CRUD utilities | ~270 | `CrudManager` class with CRUD methods |
| `resources/js/app-modern.js` | App shell | ~1000 | Module registration & routing |

#### Database Files

| File | Purpose | Table | Key Columns |
|------|---------|-------|-------------|
| `database/migrations/2026_05_24_000030_create_lingkungan_stasis_table.php` | Create lingkungan_stasis | lingkungan_stasis | id, stasi_id, nama_lingkungan_stasi, kode_lingkungan |
| `database/migrations/2026_05_24_000050_create_calon_penerimas_table.php` | Create candidates | calon_penerimas | id, lingkungan_stasi_id, stasi_id, nik, nama_lengkap, status_alur, ... |
| `database/migrations/2026_05_24_000100_modify_users_table_add_fields.php` | Add role/hierarchy | users | role (enum), stasi_id (FK), lingkungan_stasi_id (FK), ... |

---

## 🔧 Minimal Implementation Template

### 1. Create Database Migration

**File**: `database/migrations/2026_XX_XX_XXXXXX_create_your_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYourTable extends Migration
{
    public function up()
    {
        Schema::create('your_table', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lingkungan_stasi_id')
                ->constrained('lingkungan_stasis')
                ->cascadeOnDelete();
            $table->foreignId('stasi_id')
                ->constrained('stasis')
                ->cascadeOnDelete();
            
            // Your fields here
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('your_table');
    }
}
```

### 2. Create Model

**File**: `app/Models/YourModel.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YourModel extends Model
{
    use HasFactory;

    protected $table = 'your_table';
    protected $guarded = ['id'];

    // Relationships
    public function stasi()
    {
        return $this->belongsTo(Stasi::class, 'stasi_id');
    }

    public function lingkunganStasi()
    {
        return $this->belongsTo(LingkunganStasi::class, 'lingkungan_stasi_id');
    }
}
```

### 3. Create FormRequest Validation

**File**: `app/Http/Requests/StoreYourRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreYourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:150',
            'deskripsi' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
        ];
    }
}
```

### 4. Create Controller

**File**: `app/Http/Controllers/Api/YourModuleController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreYourRequest;
use App\Models\YourModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class YourModuleController extends Controller
{
    use AuthorizesRequests;
    use RespondsWithApi;

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'super_admin' && !$user->lingkungan_stasi_id) {
            return $this->error('User belum terhubung ke lingkungan stasi.', 422);
        }

        $items = YourModel::query()
            ->when($user->role !== 'super_admin', 
                fn($query) => $query->where('lingkungan_stasi_id', $user->lingkungan_stasi_id))
            ->latest()
            ->get();

        return $this->success($items, 'Data berhasil diambil.');
    }

    public function store(StoreYourRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        if ($user->role === 'super_admin') {
            if (!$request->filled('stasi_id') || !$request->filled('lingkungan_stasi_id')) {
                return $this->error('Super admin wajib memilih stasi dan lingkungan stasi.', 422);
            }
            $data['stasi_id'] = $request->integer('stasi_id');
            $data['lingkungan_stasi_id'] = $request->integer('lingkungan_stasi_id');
        } else {
            if (!$user->stasi_id || !$user->lingkungan_stasi_id) {
                return $this->error('User belum terhubung ke stasi dan lingkungan stasi.', 422);
            }
            $data['stasi_id'] = $user->stasi_id;
            $data['lingkungan_stasi_id'] = $user->lingkungan_stasi_id;
        }

        $item = YourModel::create($data);

        return $this->success($item, 'Data berhasil dibuat.', 201);
    }

    public function update(StoreYourRequest $request, $id)
    {
        $item = YourModel::findOrFail($id);
        $this->authorize('update', $item);  // Uses Policy
        
        $item->update($request->validated());

        return $this->success($item->fresh(), 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $item = YourModel::findOrFail($id);
        $this->authorize('delete', $item);  // Uses Policy
        
        $item->delete();

        return $this->success(null, 'Data berhasil dihapus.');
    }
}
```

### 5. Add Routes

**File**: `routes/api.php` (Add to appropriate section)

```php
// Your Module - Ketua Lingkungan Stasi
Route::middleware(['role:ketua_lingkungan_stasi,super_admin'])->prefix('v1/your-module')->group(function () {
    Route::get('/items', [YourModuleController::class, 'index']);
    Route::post('/items', [YourModuleController::class, 'store']);
    Route::put('/items/{id}', [YourModuleController::class, 'update']);
    Route::delete('/items/{id}', [YourModuleController::class, 'destroy']);
});
```

### 6. Create Frontend Module

**File**: `resources/js/modules/your-module.js`

```javascript
import CrudManager from '../crud-helpers.js';

export class YourModule {
    constructor() {
        this.crud = new CrudManager('/your-module/items');
        this.state = {
            token: null,
            apiBase: null,
            showForm: false,
            editingId: null,
        };
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
        if (!container) return;

        const items = this.crud.getPaginatedItems();

        let html = `
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold">Your Module</h1>
                    <button id="btn-tambah" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                        + Tambah
                    </button>
                </div>

                <!-- Search & Table -->
                <div class="bg-white rounded-lg p-4">
                    <input type="text" id="search-input" placeholder="Cari..." 
                           class="w-full px-3 py-2 border rounded-lg"
                           value="${CrudManager.escapeHtml(this.crud.state.searchQuery)}">
                </div>

                <div class="bg-white rounded-lg border overflow-hidden">
                    ${items.length > 0 ? `
                        <table class="w-full">
                            <thead class="bg-neutral-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Nama</th>
                                    <th class="px-4 py-2 text-left">Deskripsi</th>
                                    <th class="px-4 py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map(item => `
                                    <tr class="border-t">
                                        <td class="px-4 py-2">${CrudManager.escapeHtml(item.nama)}</td>
                                        <td class="px-4 py-2">${CrudManager.escapeHtml(item.deskripsi || '')}</td>
                                        <td class="px-4 py-2 text-center">
                                            <button class="edit-btn text-blue-600 hover:underline" data-id="${item.id}">Edit</button>
                                            <button class="delete-btn text-red-600 hover:underline ml-2" data-id="${item.id}">Hapus</button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    ` : `<div class="p-12 text-center text-neutral-600">Tidak ada data</div>`}
                </div>
            </div>
        `;

        container.innerHTML = html;
        this.attachEventListeners();
    }

    attachEventListeners() {
        document.getElementById('btn-tambah')?.addEventListener('click', () => {
            this.state.editingId = null;
            this.showForm();
        });

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.state.editingId = parseInt(e.target.dataset.id);
                this.showForm();
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = parseInt(e.target.dataset.id);
                if (confirm('Yakin ingin menghapus?')) {
                    await this.deleteItem(id);
                }
            });
        });

        document.getElementById('search-input')?.addEventListener('keyup', (e) => {
            this.crud.setSearch(e.target.value);
            this.render();
        });
    }

    showForm() {
        // Implement form modal
    }

    async deleteItem(id) {
        try {
            await this.crud.deleteItem(id, this.state.token, this.state.apiBase);
            this.showSuccess('Data berhasil dihapus');
            await this.loadData();
        } catch (error) {
            this.showError(error.message || 'Gagal menghapus data');
        }
    }

    showError(message) {
        alert(`Error: ${message}`);
    }

    showSuccess(message) {
        alert(message);
    }
}
```

### 7. Register in App Shell

**File**: `resources/js/app-modern.js` (Add import and registration)

```javascript
// Add import at top
import { YourModule } from './modules/your-module.js';

// In initializeApp() function, add to menuItems:
{
    label: 'Your Module',
    value: 'your-module',
    roles: ['ketua_lingkungan_stasi'],
    module: YourModule,
}
```

---

## 🔐 Authorization Rules Pattern

### Policy File (`app/Policies/YourPolicy.php`)

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\YourModel;

class YourPolicy
{
    public function update(User $user, YourModel $item): bool
    {
        // Only owner or super_admin can edit
        return $user->id === $item->created_by || $user->role === 'super_admin';
    }

    public function delete(User $user, YourModel $item): bool
    {
        // Cascade delete prevention
        return $user->role === 'super_admin';
    }
}
```

### Register Policy (`app/Providers/AuthServiceProvider.php`)

```php
use App\Models\YourModel;
use App\Policies\YourPolicy;

protected $policies = [
    YourModel::class => YourPolicy::class,
];
```

---

## 🧪 Testing Template

**File**: `tests/Feature/YourModuleTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stasi;
use App\Models\LingkunganStasi;
use App\Models\YourModel;
use Tests\TestCase;

class YourModuleTest extends TestCase
{
    public function test_ketua_lingkungan_stasi_dapat_membuat_item()
    {
        $stasi = Stasi::factory()->create();
        $lingkunganStasi = LingkunganStasi::factory()->create(['stasi_id' => $stasi->id]);
        $user = User::factory()->create([
            'role' => 'ketua_lingkungan_stasi',
            'stasi_id' => $stasi->id,
            'lingkungan_stasi_id' => $lingkunganStasi->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/your-module/items', [
            'nama' => 'Test Item',
            'deskripsi' => 'Test Description',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('Test Item', $response->json('data.nama'));
    }

    public function test_ketua_lingkungan_stasi_hanya_melihat_data_mereka()
    {
        $stasi1 = Stasi::factory()->create();
        $stasi2 = Stasi::factory()->create();
        
        $lingkunganStasi1 = LingkunganStasi::factory()->create(['stasi_id' => $stasi1->id]);
        $lingkunganStasi2 = LingkunganStasi::factory()->create(['stasi_id' => $stasi2->id]);

        $user1 = User::factory()->create([
            'role' => 'ketua_lingkungan_stasi',
            'stasi_id' => $stasi1->id,
            'lingkungan_stasi_id' => $lingkunganStasi1->id,
        ]);

        YourModel::factory()->create(['lingkungan_stasi_id' => $lingkunganStasi1->id]);
        YourModel::factory()->create(['lingkungan_stasi_id' => $lingkunganStasi2->id]);

        $response = $this->actingAs($user1)->getJson('/api/v1/your-module/items');

        $this->assertEquals(1, count($response->json('data')));
    }
}
```

---

## 📋 Comparison: Two Existing "Ketua Lingkungan" Modules

### Ketua Lingkungan Stasi vs Ketua Lingkungan Paroki

| Aspect | Ketua Lingkungan Stasi | Ketua Lingkungan Paroki |
|--------|----------------------|----------------------|
| **Hierarchy** | Under Stasi | Under Paroki |
| **Main Entity** | CalonPenerima (Candidates) | BansosPeriod (Periods) |
| **Key Actions** | CRUD candidates, Submit to Stasi | Configure weights, Execute SAW |
| **API Prefix** | `/v1/lingkungan-stasi` | `/v1/lingkungan-paroki` |
| **Controller** | KetuaLingkunganStasiController | KetuaLingkunganParokiController |
| **Module File** | ketua-lingkungan-stasi.js | ketua-lingkungan-paroki.js |
| **User FK** | lingkungan_stasi_id | lingkungan_paroki_id |
| **Workflow** | Input → Submit → Approve | Configure → Execute → Review |
| **Model** | CalonPenerima | SawWeight, SawResult |

---

## 🛠️ Common Gotchas & Solutions

### 1. User Hierarchy Not Set
```php
// ❌ Wrong - Will fail
if (!$user->lingkungan_stasi_id) {
    return $this->error('Belum di-setup');
}

// ✅ Correct - Handle both regular and super_admin
if ($user->role !== 'super_admin' && !$user->lingkungan_stasi_id) {
    return $this->error('Belum di-setup');
}
```

### 2. Forgetting AuthorizesRequests Trait
```php
// ❌ Wrong - authorize() won't work
class YourController extends Controller {
    public function update(Request $request, $id) {
        $this->authorize('update', $item);  // Error!
    }
}

// ✅ Correct
class YourController extends Controller {
    use AuthorizesRequests;  // Add this!
    
    public function update(Request $request, $id) {
        $this->authorize('update', $item);  // Works!
    }
}
```

### 3. Missing Foreign Key Constraints
```php
// ❌ Wrong - Will fail database insert
$item = YourModel::create(['nama' => 'Test']);

// ✅ Correct
$item = YourModel::create([
    'nama' => 'Test',
    'stasi_id' => $user->stasi_id,
    'lingkungan_stasi_id' => $user->lingkungan_stasi_id,
]);
```

### 4. Forgetting to Register Module
```php
// ❌ Wrong - Module won't show in menu
// resources/js/app-modern.js is missing the import or registration

// ✅ Correct
import { YourModule } from './modules/your-module.js';

// And in initializeApp():
menuItems.push({
    label: 'Your Module',
    value: 'your-module',
    roles: ['ketua_lingkungan_stasi'],
    module: YourModule,
});
```

---

## ✅ Implementation Checklist

Use this checklist when implementing a new "Ketua Lingkungan" module:

- [ ] **Database Setup**
  - [ ] Create migration file
  - [ ] Include `stasi_id` and `lingkungan_stasi_id` foreign keys
  - [ ] Add indexes for performance
  - [ ] Run migration: `php artisan migrate`

- [ ] **Backend Model & Validation**
  - [ ] Create Model with relationships
  - [ ] Create FormRequest classes (Store, Update)
  - [ ] Create Controller with role middleware
  - [ ] Create Policy for authorization
  - [ ] Add routes to `routes/api.php` with `role:ketua_lingkungan_stasi,super_admin`

- [ ] **Testing**
  - [ ] Write Feature tests for authorization
  - [ ] Test role-based filtering
  - [ ] Test policy authorization
  - [ ] Run tests: `php artisan test`

- [ ] **Frontend Module**
  - [ ] Create module class in `resources/js/modules/`
  - [ ] Implement `init()`, `render()`, `attachEventListeners()`
  - [ ] Use CrudManager for API calls
  - [ ] Add search/filter UI
  - [ ] Add form modal for CRUD operations

- [ ] **App Integration**
  - [ ] Import module in `app-modern.js`
  - [ ] Register to menuItems with role restrictions
  - [ ] Test menu item appears for correct roles
  - [ ] Test module loads and functions correctly

- [ ] **Documentation**
  - [ ] Update MODULES_USER_GUIDE.md
  - [ ] Update MODULES_DEVELOPER_GUIDE.md
  - [ ] Add API endpoint documentation

---

**Quick Links to Existing Code**:
- Ketua Lingkungan Stasi: `app/Http/Controllers/Api/KetuaLingkunganStasiController.php`
- Ketua Lingkungan Paroki: `app/Http/Controllers/Api/KetuaLingkunganParokiController.php`
- CRUD Helper: `resources/js/crud-helpers.js`
- Route Config: `routes/api.php` (lines ~70-85, ~145-165)

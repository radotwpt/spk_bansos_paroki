```markdown
# BLUEPRINT & ARSITEKTUR SISTEM: SPK BANSOS PAROKI (PWA-LARAVEL-SAW)
**Dokumen Panduan Teknis untuk Tim Pengembang (Production-Ready)**

---

## 1. ARSITEKTUR DATABASE & SKEMA MIGRASI

Arsitektur database dirancang menggunakan mesin penyimpanan InnoDB dengan pengindeksan ketat pada kunci asing (*foreign keys*) dan kolom pencarian untuk memastikan performa optimal pada relasi hierarkis Gereja Katolik (Paroki -> Lingkungan Paroki -> Stasi -> Lingkungan Stasi).


```

[Paroki]
│
├── [Lingkungan Paroki] (Kluster Koordinasi)
│
└── [Stasi]
│
└── [Lingkungan Stasi]
│
└── [Umat / Calon Penerima]

```

### 1.1 Skema Tabel Migrasi (Laravel Blueprint)

Berikut adalah definisi migrasi database lengkap tanpa pemotongan kode.

#### Tabel: `stasis`, `lingkungan_parokis`, `lingkungan_stasis`
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChurchStructureTables extends Migration
{
    public function up()
    {
        Schema::create('stasis', function (Blueprint $bluePrint) {
            $bluePrint->id();
            $bluePrint->string('nama_stasi', 100)->unique();
            $bluePrint->string('kode_stasi', 20)->unique();
            $bluePrint->text('alamat')->nullable();
            $bluePrint->timestamps();
        });

        Schema::create('lingkungan_parokis', function (Blueprint $bluePrint) {
            $bluePrint->id();
            $bluePrint->string('nama_lingkungan_paroki', 100)->unique();
            $bluePrint->string('kode_wilayah', 20)->unique();
            $bluePrint->timestamps();
        });

        Schema::create('lingkungan_stasis', function (Blueprint $bluePrint) {
            $bluePrint->id();
            $bluePrint->foreignId('stasi_id')->constrained('stasis')->onDelete('cascade');
            $bluePrint->string('nama_lingkungan_stasi', 100);
            $bluePrint->string('kode_lingkungan', 20)->unique();
            $bluePrint->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lingkungan_stasis');
        Schema::dropIfExists('lingkungan_parokis');
        Schema::dropIfExists('stasis');
    }
}

```

#### Tabel: `users` (Multi-Role Extension)

```php
class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $bluePrint) {
            $bluePrint->id();
            $bluePrint->string('name', 150);
            $bluePrint->string('email', 100)->unique();
            $bluePrint->string('password');
            $bluePrint->enum('role', ['super_admin', 'paroki', 'ketua_lingkungan_paroki', 'stasi', 'ketua_lingkungan_stasi']);
            
            // Nullable foreign keys tergantung kedudukan role user
            $bluePrint->foreignId('stasi_id')->nullable()->constrained('stasis')->onDelete('set null');
            $bluePrint->foreignId('lingkungan_paroki_id')->nullable()->constrained('lingkungan_parokis')->onDelete('set null');
            $bluePrint->foreignId('lingkungan_stasi_id')->nullable()->constrained('lingkungan_stasis')->onDelete('set null');
            
            $bluePrint->rememberToken();
            $bluePrint->timestamps();
            
            $bluePrint->index(['role', 'stasi_id', 'lingkungan_paroki_id', 'lingkungan_stasi_id'], 'idx_user_hierarchy');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}

```

#### Tabel: `bansos_periods` & `calon_penerimas`

```php
class CreateBansosAndCalonPenerimaTables extends Migration
{
    public function up()
    {
        Schema::create('bansos_periods', function (Blueprint $bluePrint) {
            $bluePrint->id();
            $bluePrint->string('nama_periode', 100); // Contoh: "Bansos Paskah 2026"
            $bluePrint->year('tahun');
            $bluePrint->enum('status_periode', ['aktif', 'proses_perankingan', 'selesai', 'arsip'])->default('aktif');
            $bluePrint->timestamps();
        });

        Schema::create('calon_penerimas', function (Blueprint $bluePrint) {
            $bluePrint->id();
            $bluePrint->foreignId('bansos_period_id')->constrained('bansos_periods')->onDelete('cascade');
            $bluePrint->foreignId('lingkungan_stasi_id')->constrained('lingkungan_stasis')->onDelete('cascade');
            $bluePrint->foreignId('stasi_id')->constrained('stasis')->onDelete('cascade');
            
            // Data Identitas (Enkripsi di layer aplikasi disarankan jika produksi skala besar)
            $bluePrint->string('nik', 16);
            $bluePrint->string('nama_lengkap', 150);
            $bluePrint->text('alamat_kristen');
            
            // Indikator Kuantitatif SAW (Raw Values)
            $bluePrint->decimal('pendapatan_keluarga', 12, 2);
            $bluePrint->integer('jumlah_tanggungan');
            $bluePrint->enum('status_tempat_tinggal', ['milik_sendiri', 'sewa', 'numpang']);
            $bluePrint->enum('status_hubungan', ['lajang', 'menikah', 'cerai']);
            
            // Parameter Kualitatif diluar SAW
            $bluePrint->text('urgensi_tambahan_tekstual'); 
            
            // Kolom Skor dan Hasil Perankingan
            $bluePrint->decimal('saw_score', 5, 4)->nullable()->default(0.0000);
            $bluePrint->integer('rank_global')->nullable();
            $bluePrint->integer('rank_internal_stasi')->nullable();
            
            // Workflow Status State Machine
            $bluePrint->enum('status_alur', [
                'draft', 
                'diajukan_ke_stasi', 
                'disetujui_stasi', 
                'diranking_lingkungan_paroki', 
                'disetujui_paroki', 
                'ditolak'
            ])->default('draft');
            
            // Hasil Eksekusi Paroki
            $bluePrint->boolean('is_penerima_sah')->default(false);
            $bluePrint->decimal('nominal_bansos_disetujui', 12, 2)->default(0.00);
            
            $bluePrint->timestamps();
            
            $bluePrint->index(['bansos_period_id', 'status_alur']);
            $bluePrint->index('nik');
        });
    }

    public function down()
    {
        Schema::dropIfExists('calon_penerimas');
        Schema::dropIfExists('bansos_periods');
    }
}

```

#### Tabel: `document_templates`, `generated_letters`, & `activity_logs`

```php
class CreateSystemUtilityTables extends Migration
{
    public function up()
    {
        Schema::create('document_templates', function (Blueprint $bluePrint) {
            $bluePrint->id();
            $bluePrint->enum('jenis_surat', ['permohonan_stasi', 'edaran_paroki']);
            $bluePrint->string('judul_template', 100);
            $bluePrint->longText('html_content'); // Menggunakan placeholder tag seperti {nama_stasi}, {daftar_penerima}
            $bluePrint->timestamps();
        });

        Schema::create('generated_letters', function (Blueprint $bluePrint) {
            $bluePrint->id();
            $bluePrint->foreignId('bansos_period_id')->constrained('bansos_periods')->onDelete('cascade');
            $bluePrint->foreignId('stasi_id')->nullable()->constrained('stasis')->onDelete('cascade');
            $bluePrint->enum('jenis_surat', ['permohonan_stasi', 'edaran_paroki']);
            $bluePrint->string('nomor_surat', 100)->unique();
            $bluePrint->longText('final_html_content');
            $bluePrint->string('pdf_file_path')->nullable();
            $bluePrint->foreignId('generated_by_user_id')->constrained('users');
            $bluePrint->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $bluePrint) {
            $bluePrint->id();
            $bluePrint->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $bluePrint->string('action_name', 100);
            $bluePrint->string('model_target', 50)->nullable();
            $bluePrint->unsignedBigInteger('model_id')->nullable();
            $bluePrint->json('payload_before')->nullable();
            $bluePrint->json('payload_after')->nullable();
            $bluePrint->string('ip_address', 45)->nullable();
            $bluePrint->text('user_agent')->nullable();
            $bluePrint->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('generated_letters');
        Schema::dropIfExists('document_templates');
    }
}

```

---

## 2. STRUKTUR FOLDER & CLAUSE CLEAN ARCHITECTURE (LARAVEL)

Proyek wajib menggunakan pendekatan pemisahan tanggung jawab (*Separation of Concerns*) dengan mengekstrak logika matematika SAW dan Alur Kerja (*Workflow*) keluar dari Controller menuju komponen *Service Layer*.

```
app/
├── Console/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── KetuaLingkunganStasiController.php
│   │       ├── StasiController.php
│   │       ├── KetuaLingkunganParokiController.php
│   │       └── ParokiController.php
│   ├── Middleware/
│   │   └── CheckRoleMiddleware.php
│   └── Requests/
│       ├── StoreCalonPenerimaRequest.php
│       └── WorkflowTransitionRequest.php
├── Models/
│   ├── CalonPenerima.php
│   ├── Stasi.php
│   ├── LingkunganStasi.php
│   ├── BansosPeriod.php
│   └── ActivityLog.php
├── Scopes/
│   └── TenantDataScope.php
└── Services/
    ├── SawCalculationService.php
    └── BansosWorkflowService.php

```

---

## 3. IMPLEMENTASI ALGORITMA SIMPLE ADDITIVE WEIGHTING (SAW)

### 3.1 Aturan Pembobotan Kriteria dan Pemetaan Nilai

| Kode | Kriteria | Sifat Kriteria | Nilai Parameter Kualitatif / Formula Kuantitatif | Bobot ($W$) |
| --- | --- | --- | --- | --- |
| **C1** | Pendapatan Keluarga | **Cost (Biaya)** | Menggunakan nilai rupiah riil. Semakin rendah nilai pendapatan, semakin tinggi prioritas penerimaan bantuan. | **40% (0.40)** |
| **C2** | Jumlah Tanggungan | **Benefit (Keuntungan)** | Jumlah jiwa dalam KK yang ditanggung riil (angka absolut). | **30% (0.30)** |
| **C3** | Status Tempat Tinggal | **Benefit (Keuntungan)** | - Numpang = Skor **3**<br>

<br>- Sewa = Skor **2**<br>

<br>- Milik Sendiri = Skor **1** | **15% (0.15)** |
| **C4** | Status Hubungan | **Benefit (Keuntungan)** | - Cerai (Janda/Duda) = Skor **3**<br>

<br>- Menikah = Skor **2**<br>

<br>- Lajang = Skor **1** | **15% (0.15)** |

### 3.2 Persamaan Matematika SAW

1. **Normalisasi Kriteria Benefit:**

$$R_{ij} = \frac{X_{ij}}{\max_i(X_{ij})}$$


2. **Normalisasi Kriteria Cost:**

$$R_{ij} = \frac{\min_i(X_{ij})}{X_{ij}}$$


3. **Perhitungan Nilai Preferensi Akhir ($V_i$):**

$$V_i = \sum_{j=1}^{n} W_j \cdot R_{ij}$$



### 3.3 Kode Service Layer: `SawCalculationService.php`

```php
namespace App\Services;

use App\Models\CalonPenerima;
use Illuminate\Support\Collection;

class SawCalculationService
{
    private array $weights = [
        'c1_pendapatan' => 0.40,
        'c2_tanggungan' => 0.30,
        'c3_tempat_tinggal' => 0.15,
        'c4_status_hubungan' => 0.15
    ];

    /**
     * Mengeksekusi kalkulasi SAW dari sekumpulan data Calon Penerima dalam satu periode.
     */
    public function calculate(int $periodId): Collection
    {
        // 1. Ambil seluruh data kandidat yang valid untuk diproses ranking
        $kandidats = CalonPenerima::where('bansos_period_id', $periodId)
            ->whereIn('status_alur', ['diajukan_ke_stasi', 'disetujui_stasi', 'diranking_lingkungan_paroki'])
            ->get();

        if ($kandidats->isEmpty()) {
            return collect();
        }

        // 2. Transformasi nilai kualitatif menjadi matriks keputusan numerik (X)
        $matrixX = $kandidats->map(function ($item) {
            return [
                'id' => $item->id,
                'c1' => (float) $item->pendapatan_keluarga,
                'c2' => (float) $item->jumlah_tanggungan,
                'c3' => $this->mapTempatTinggiToScore($item->status_tempat_tinggal),
                'c4' => $this->mapStatusHubunganToScore($item->status_hubungan),
            ];
        });

        // 3. Ekstraksi nilai Ekstrim (Max/Min) untuk pembagi normalisasi
        $minC1 = $matrixX->min('c1');
        $maxC2 = $matrixX->max('c2');
        $maxC3 = $matrixX->max('c3');
        $maxC4 = $matrixX->max('c4');

        // Proteksi pembagian dengan nol (zero-division bypass)
        $minC1 = $minC1 <= 0 ? 1.0 : $minC1;
        $maxC2 = $maxC2 <= 0 ? 1.0 : $maxC2;
        $maxC3 = $maxC3 <= 0 ? 1.0 : $maxC3;
        $maxC4 = $maxC4 <= 0 ? 1.0 : $maxC4;

        // 4. Tahap Normalisasi (Matriks R) & Perhitungan Nilai Preferensi Akhir (V)
        $calculatedScores = $matrixX->map(function ($row) use ($minC1, $maxC2, $maxC3, $maxC4) {
            // C1 bersifat Cost: min(x) / x
            $r1 = $minC1 / ($row['c1'] <= 0 ? 1 : $row['c1']);
            // C2, C3, C4 bersifat Benefit: x / max(x)
            $r2 = $row['c2'] / $maxC2;
            $r3 = $row['c3'] / $maxC3;
            $r4 = $row['c4'] / $maxC4;

            // Rumus preferensi V_i
            $v = ($this->weights['c1_pendapatan'] * $r1) +
                 ($this->weights['c2_tanggungan'] * $r2) +
                 ($this->weights['c3_tempat_tinggal'] * $r3) +
                 ($this->weights['c4_status_hubungan'] * $r4);

            return [
                'id' => $row['id'],
                'score' => round($v, 4)
            ];
        });

        // 5. Urutkan berdasarkan skor tertinggi (Descending)
        $rankedItems = $calculatedScores->sortByDesc('score')->values();

        // 6. Tulis kembali ke database (Bulk update atau iteratif terkontrol)
        foreach ($rankedItems as $index => $ranked) {
            $rankNumber = $index + 1;
            CalonPenerima::where('id', $ranked['id'])->update([
                'saw_score' => $ranked['score'],
                'rank_global' => $rankNumber,
                'status_alur' => 'diranking_lingkungan_paroki'
            ]);
        }

        return $rankedItems;
    }

    private function mapTempatTinggiToScore(string $value): float
    {
        return match ($value) {
            'numpang' => 3.0,
            'sewa' => 2.0,
            'milik_sendiri' => 1.0,
            default => 1.0,
        };
    }

    private function mapStatusHubunganToScore(string $value): float
    {
        return match ($value) {
            'cerai' => 3.0,
            'menikah' => 2.0,
            'lajang' => 1.0,
            default => 1.0,
        };
    }
}

```

---

## 4. WORKFLOW STATE MACHINE (ALUR PERSETUJUAN BERKAS)

Untuk mengamankan integritas data, status pengajuan diikat menggunakan aturan State Machine ketat. Data yang sudah naik level hirarki akan dikunci (*read-only*) secara otomatis bagi level di bawahnya.

```
 [Draft] ──(Ketu_Lingk_Stasi)──> [Diajukan ke Stasi] ──(Stasi)──> [Disetujui Stasi]
                                                                        │
 [Disetujui Paroki] <──(Paroki)── [Diranking Lingk_Paroki] <────────────┘

```

### 4.1 Tabel Transisi Keadaan (State Transitions)

| State Awal (*Current State*) | Aksi Pemicu (*Trigger*) | Pengguna Resmi (*Authorized Role*) | State Akhir (*Target State*) | Dampak Mutasi Data |
| --- | --- | --- | --- | --- |
| `draft` | `submit_to_stasi` | Ketua Lingkungan Stasi | `diajukan_ke_stasi` | Berkas dikunci untuk Ketua Lingkungan Stasi. |
| `diajukan_ke_stasi` | `approve_by_stasi` | Admin Stasi / Gereja | `disetujui_stasi` | Akumulasi pengajuan siap ditarik ke tingkat Paroki. |
| `disetujui_stasi` | `trigger_saw` | Ketua Lingkungan Paroki | `diranking_lingkungan_paroki` | Skor `saw_score` & `rank_global` terisi via Service. |
| `diranking_lingkungan_paroki` | `finalize_paroki` | Pastor/Admin Paroki | `disetujui_paroki` | `is_penerima_sah` menjadi `true`, nominal dana diturunkan. |
| *Semua State kecuali Akhir* | `reject_data` | Stasi / Paroki | `ditolak` | Alasan penolakan dicatat, proses berhenti. |

---

## 5. PWA (PROGRESSIVE WEB APP) & STRATEGI OFFLINE-FIRST

Ketua lingkungan sering melakukan verifikasi faktual *door-to-door* di area rawan sinyal. Oleh karena itu, aplikasi harus memiliki kemampuan penyimpanan luring sementara menggunakan IndexedDB dan sinkronisasi otomatis ketika koneksi internet kembali pulih (*Background Sync*).

### 5.1 Service Worker Core (`public/sw.js`)

```javascript
const CACHE_NAME = 'paroki-spk-pwa-v1';
const ASSETS_TO_CACHE = [
  '/',
  '/index.html',
  '/css/app.css',
  '/js/app.js',
  '/manifest.json',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png'
];

// Install Event
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
});

// Activate Event
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
});

// Fetch Event dengan Strategi Cache First, Fallback to Network
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return; // Skip POST/PUT/DELETE dari caching standard

  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      return cachedResponse || fetch(event.request).then(networkResponse => {
        return caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, networkResponse.clone());
          return networkResponse;
        });
      });
    }).catch(() => {
      if (event.request.headers.get('accept').includes('text/html')) {
        return caches.match('/index.html');
      }
    })
  );
});

// Background Sync untuk Pengiriman Data Calon Penerima Luring
self.addEventListener('sync', event => {
  if (event.tag === 'sync-calon-penerima') {
    event.waitUntil(flushOfflineQueueToServer());
  }
});

async function flushOfflineQueueToServer() {
  // Menggunakan library IdB sederhana untuk membaca IndexedDB lokal
  const db = await openIndexedDB();
  const tx = db.transaction('offline-mutations', 'readonly');
  const store = tx.objectStore('offline-mutations');
  const records = await store.getAll();

  for (const record of records) {
    try {
      const response = await fetch(record.url, {
        method: record.method,
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${record.token}`
        },
        body: JSON.stringify(record.payload)
      });

      if (response.ok) {
        const deleteTx = db.transaction('offline-mutations', 'readwrite');
        await deleteTx.objectStore('offline-mutations').delete(record.id);
        await deleteTx.done;
      }
    } catch (error) {
      console.error("Sinkronisasi gagal untuk record ID: " + record.id, error);
      break; // Hentikan loop jika server masih bermasalah
    }
  }
}

function openIndexedDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('ParokiPwaDb', 1);
    request.onupgradeneeded = event => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('offline-mutations')) {
        db.createObjectStore('offline-mutations', { keyPath: 'id', autoIncrement: true });
      }
    };
    request.onsuccess = event => resolve(event.target.result);
    request.onerror = event => reject(event.target.error);
  });
}

```

### 5.2 Web App Manifest (`public/manifest.json`)

```json
{
  "name": "SPK Bansos Lembaga Keagamaan Paroki",
  "short_name": "BansosParoki",
  "description": "Sistem Pendukung Keputusan Distribusi Bansos SAW Berbasis PWA",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#1e293b",
  "theme_color": "#0f172a",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ]
}

```

---

## 6. ARSITEKTUR REST API & ROUTING ENDPOINTS

Seluruh komunikasi front-end PWA dan backend Laravel diisolasi penuh menggunakan token berbasis *Laravel Sanctum*.

### 6.1 Berkas Rute API Lengkap (`routes/api.php`)

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KetuaLingkunganStasiController;
use App\Http\Controllers\Api\StasiController;
use App\Http\Controllers\Api\KetuaLingkunganParokiController;
use App\Http\Controllers\Api\ParokiController;

Route::post('/v1/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // 1. KELOMPOK FITUR: KETUA LINGKUNGAN STASI
    Route::middleware(['role:ketua_lingkungan_stasi,super_admin'])->prefix('lingkungan-stasi')->group(function () {
        Route::get('/calon-penerima', [KetuaLingkunganStasiController::class, 'index']);
        Route::post('/calon-penerima', [KetuaLingkunganStasiController::class, 'store']);
        Route::put('/calon-penerima/{id}', [KetuaLingkunganStasiController::class, 'update']);
        Route::delete('/calon-penerima/{id}', [KetuaLingkunganStasiController::class, 'destroy']);
        Route::post('/calon-penerima/{id}/ajukan', [KetuaLingkunganStasiController::class, 'submitToStasi']);
        Route::get('/aktivitas', [KetuaLingkunganStasiController::class, 'activityLogs']);
    });

    // 2. KELOMPOK FITUR: STASI / GEREJA
    Route::middleware(['role:stasi,super_admin'])->prefix('stasi')->group(function () {
        Route::get('/users-ketua', [StasiController::class, 'indexKetuaLingkungan']);
        Route::post('/users-ketua', [StasiController::class, 'storeKetuaLingkungan']);
        Route::put('/users-ketua/{id}', [StasiController::class, 'updateKetuaLingkungan']);
        Route::delete('/users-ketua/{id}', [StasiController::class, 'destroyKetuaLingkungan']);
        
        Route::get('/calon-penerima-rekap', [StasiController::class, 'indexCalonPenerima']);
        Route::post('/surat-permohonan/generate', [StasiController::class, 'generateSuratPermohonan']);
        Route::put('/template-surat', [StasiController::class, 'updateTemplateSurat']);
        Route::get('/logs', [StasiController::class, 'historyLogAndSubmissions']);
    });

    // 3. KELOMPOK FITUR: KETUA LINGKUNGAN PAROKI
    Route::middleware(['role:ketua_lingkungan_paroki,super_admin'])->prefix('lingkungan-paroki')->group(function () {
        Route::get('/stasi-accounts', [KetuaLingkunganParokiController::class, 'indexStasiAccounts']);
        Route::post('/stasi-accounts', [KetuaLingkunganParokiController::class, 'storeStasiAccount']);
        Route::get('/calon-penerima-global', [KetuaLingkunganParokiController::class, 'indexGlobalKandidat']);
        
        // Pemicu Algoritma SAW
        Route::post('/proses-saw/{periodId}', [KetuaLingkunganParokiController::class, 'executeSawRanking']);
        Route::post('/kirim-ke-paroki/{periodId}', [KetuaLingkunganParokiController::class, 'sendRankingToParoki']);
        Route::get('/logs', [KetuaLingkunganParokiController::class, 'activityLogs']);
    });

    // 4. KELOMPOK FITUR: PAROKI (PASTOR & DEWAN HARIAN)
    Route::middleware(['role:paroki,super_admin'])->prefix('paroki')->group(function () {
        Route::get('/ranking-data/{periodId}', [ParokiController::class, 'viewRankedData']);
        Route::post('/penerima/{id}/keputusan', [ParokiController::class, 'finalizeDecision']);
        Route::post('/surat-edaran/generate', [ParokiController::class, 'generateSuratEdaran']);
        Route::put('/template-edaran', [ParokiController::class, 'updateTemplateEdaran']);
        Route::get('/history-informasi', [ParokiController::class, 'historyInformasiLengkap']);
    });
});

```

---

## 7. SISTEM KEAMANAN (TENANT ISOLATION GLOBAL SCOPE)

Untuk memitigasi celah keamanan IDOR (*Indirect Object Reference*), di mana akun Ketua Lingkungan Stasi A bisa melihat atau memanipulasi data Lingkungan Stasi B dengan mengubah ID di URL, wajib diimplementasikan **Laravel Global Scope**.

### 7.1 Implementasi: `TenantDataScope.php`

```php
namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantDataScope implements Scope
{
    /**
     * Terapkan batasan query secara otomatis berdasarkan kedudukan struktur user yang login.
     */
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::hasUser()) {
            $user = Auth::user();

            switch ($user->role) {
                case 'ketua_lingkungan_stasi':
                    // Ketua lingkungan stasi hanya berhak mengakses data di lingkungan stasi miliknya
                    $builder->where('lingkungan_stasi_id', $user->lingkungan_stasi_id);
                    break;
                    
                case 'stasi':
                    // Admin stasi hanya berhak melihat data calon penerima yang berada di bawah stasinya
                    $builder->where('stasi_id', $user->stasi_id);
                    break;
                    
                case 'ketua_lingkungan_paroki':
                case 'paroki':
                case 'super_admin':
                    // Memiliki hak akses penuh lintasan regional data, tidak ditambahkan klausa where
                    break;
            }
        }
    }
}

```

### 7.2 Registrasi Pada Model: `CalonPenerima.php`

```php
namespace App\Models;

use App\Scopes\TenantDataScope;
use Illuminate\Database\Eloquent\Model;

class CalonPenerima extends Model
{
    protected $table = 'calon_penerimas';
    
    protected $guarded = ['id'];

    /**
     * Boot function untuk mengaktifkan TenantDataScope secara global setiap query dipanggil.
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TenantDataScope);
    }
    
    // Relasi Eloquent
    public function stasi()
    {
        return $this->belongsTo(Stasi::class, 'stasi_id');
    }

    public function lingkunganStasi()
    {
        return $this->belongsTo(LingkunganStasi::class, 'lingkungan_stasi_id');
    }
    
    public function period()
    {
        return $this->belongsTo(BansosPeriod::class, 'bansos_period_id');
    }
}

```

---

## 8. STRATEGI IMPLEMENTASI DAN INTERVENSI INTERAL

Programmer wajib memperhatikan ketentuan berikut terkait kolom **Urgensi Tambahan Tekstual** (`urgensi_tambahan_tekstual`):

1. **Eksklusi dari Variabel Matematika**: Jangan masukkan kolom teks ini ke dalam iterasi normalisasi ataupun perkalian bobot pada `SawCalculationService`.
2. **Penyajian Data pada UI**: Pada antarmuka pengguna (*Dashboard*) tingkat **Ketua Lingkungan Paroki** dan **Paroki**, teks urgensi ini harus diletakkan tepat di sebelah skor hasil akhir SAW.
3. **Fungsi Intervensi Manusia (Bypass Manual)**: Sistem harus memberikan kebebasan bagi Pastor Paroki atau Dewan Paroki untuk meloloskan seseorang yang memiliki skor SAW rendah apabila isi teks urgensi tersebut dinilai sangat krusial (misalnya: *"Anak sakit kritis butuh operasi segera"*), dengan cara menekan tombol `finalizeDecision` untuk mengubah nilai `is_penerima_sah` secara manual tanpa merusak integritas perhitungan matematis dari algoritma SAW itu sendiri.

```

```
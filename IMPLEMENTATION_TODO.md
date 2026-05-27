# Implementation Todo List - SPK Bansos

Dokumen ini merangkum semua modul/fitur yang belum diimplementasikan lengkap dan dibagi menjadi fase-fase terstruktur.

**Last Updated**: 2026-05-27  
**Status**: Planning Phase

---

## Actionable Backlog (Tanpa Estimasi & Tanpa Tanggal)

### Fase 1 - Core API yang Masih Placeholder

- [ ] Implement `POST /api/v1/stasi/surat-permohonan/generate` agar benar-benar membuat data surat.
- [ ] Implement `PUT /api/v1/stasi/template-surat` agar benar-benar memperbarui template.
- [ ] Implement `POST /api/v1/paroki/surat-edaran/generate` agar benar-benar membuat data surat.
- [ ] Implement `PUT /api/v1/paroki/template-edaran` agar benar-benar memperbarui template.
- [ ] Implement endpoint riwayat stasi/paroki agar tidak lagi return data kosong.
- [ ] Tambahkan FormRequest untuk validasi endpoint surat/template.
- [ ] Tambahkan audit log untuk setiap aksi generate/update template.

### Fase 2 - Document & Letter Management Lengkap

- [ ] Tambahkan kolom `nomor_surat` pada `generated_letters` + unique constraint.
- [ ] Tambahkan kolom `jenis_surat` pada `generated_letters`.
- [ ] Tambahkan kolom `final_html_content` pada `generated_letters`.
- [ ] Tambahkan kolom `metadata_json` pada `generated_letters`.
- [ ] Implement generator nomor surat otomatis per jenis dan tahun.
- [ ] Tambahkan endpoint `GET /api/v1/letters/next-number`.
- [ ] Integrasikan placeholder resmi (`nama_periode`, `tahun`, `nama_stasi`, `nomor_surat`, dst).
- [ ] Implement endpoint preview template dengan sample data.
- [ ] Implement endpoint arsip surat dengan filter dasar (jenis/periode/tanggal).
- [ ] Tambahkan proteksi delete template jika sudah digunakan generated letter.

### Fase 3 - Export PDF/CSV/Excel

- [ ] Integrasikan library PDF untuk render surat.
- [ ] Implement endpoint `GET /api/v1/letters/{id}/pdf`.
- [ ] Simpan path file hasil PDF ke `generated_letters.file_path`.
- [ ] Implement endpoint export CSV untuk calon penerima.
- [ ] Implement endpoint export CSV untuk penerima sah.
- [ ] Implement endpoint export CSV untuk ranking.
- [ ] Implement endpoint export Excel untuk report utama.
- [ ] Standarisasi penamaan file export (`report_{type}_{period}_{date}`).

### Fase 4 - Activity Log & Audit Viewer

- [ ] Tambahkan endpoint global activity log `GET /api/v1/logs`.
- [ ] Tambahkan filter `user`, `action`, `model`, `date_from`, `date_to` pada log.
- [ ] Tambahkan pagination pada activity log.
- [ ] Ubah frontend activity log agar tidak hardcode `calon_penerima_id = 1`.
- [ ] Tampilkan metadata log dalam format yang mudah dibaca.
- [ ] Tambahkan policy akses activity log lintas role.

### Fase 5 - Offline/PWA Completion

- [ ] Integrasikan `idb-helpers` ke aplikasi utama, bukan hanya halaman `/pwa`.
- [ ] Implement indikator online/offline di topbar aplikasi.
- [ ] Implement queue status: `pending`, `syncing`, `success`, `failed`.
- [ ] Implement retry manual per item queue.
- [ ] Implement retry all queue items.
- [ ] Tambahkan handling error sync khusus (`401`, `409`, `422`, network error).
- [ ] Tambahkan action sync tambahan jika dibutuhkan selain `submit_candidate`.
- [ ] Tambahkan offline fallback untuk halaman utama yang krusial.

### Fase 6 - Search, Filter, Pagination Konsisten

- [ ] Standarkan query params list API: `q`, `page`, `per_page`, `sort`, `order`.
- [ ] Terapkan pagination server-side di seluruh endpoint list utama.
- [ ] Tambahkan filter ranking by `period_id`, `stasi_id`, `top`, `sort`.
- [ ] Tambahkan filter generated letters by jenis/periode/pembuat.
- [ ] Batasi `per_page` maksimum agar endpoint tidak terlalu berat.
- [ ] Sinkronkan semua kontrol filter frontend dengan query param backend.

### Fase 7 - Security & Validation Hardening

- [ ] Tambahkan endpoint `POST /api/v1/auth/change-password`.
- [ ] Tambahkan validasi `old_password`, `new_password`, `confirm_password`.
- [ ] Tambahkan throttle login.
- [ ] Tambahkan throttle default untuk endpoint API umum.
- [ ] Pastikan semua endpoint baru mematuhi format error response standar.
- [ ] Audit policy untuk endpoint documents, reports, export, offline sync.
- [ ] Pastikan periode terkunci benar-benar memblokir aksi yang tidak boleh.

### Fase 8 - QA, UAT, Dokumentasi

- [ ] Tambahkan test feature untuk modul dokumen/template/surat.
- [ ] Tambahkan test feature untuk endpoint export/report.
- [ ] Tambahkan test integrasi alur penuh end-to-end.
- [ ] Tambahkan test scenario lock period terhadap update bobot/ranking.
- [ ] Update `README.md` dengan alur setup, flow role, dan endpoint penting.
- [ ] Sinkronkan `ROADMAP_TODO.md` dan `IMPLEMENTATION_TODO.md` dengan status aktual.
- [ ] Buat release checklist operasional (migrate, seed, build, test, clear cache).

---

## Ringkasan Keseluruhan

| Fase | Nama | Priority | Estimasi | Status |
|------|------|----------|----------|--------|
| 1 | Frontend Shell & Master Data | URGENT | 1-2 minggu | ✅ SELESAI |
| 2 | Calon Penerima Workflow | URGENT | 1-2 minggu | 🔴 Belum |
| 3 | SAW & Ranking UI | HIGH | 1 minggu | 🔴 Belum |
| 4 | Document & Letter | HIGH | 1 minggu | 🔴 Belum |
| 5 | Search, Filter & Pagination | HIGH | 3-5 hari | 🔴 Belum |
| 6 | Validasi & Constraints | HIGH | 3-5 hari | 🔴 Belum |
| 7 | Offline Sync / PWA | MEDIUM | 1-2 minggu | 🔴 Belum |
| 8 | Reporting & Export | MEDIUM | 1 minggu | 🔴 Belum |
| 9 | Hardening & Production Ready | LOW | 1 minggu | 🔴 Belum |

**Total Estimasi**: 8-10 minggu untuk semua fase

---

## Fase 1: Frontend Shell & Master Data Pages (URGENT)

**Tujuan**: Membangun kerangka admin panel dan halaman master data CRUD agar super admin bisa mengelola data dasar tanpa manual database.

**Durasi**: 1-2 minggu  
**Priority**: URGENT  
**Dependencies**: Fase 2 Auth & Shell (sudah ada)

### Frontend Infrastructure

- [ ] Buat struktur folder pages di `resources/js/pages/`
  - [ ] Admin dashboard skeleton
  - [ ] Master data template component
  
- [ ] Buat data table reusable component
  - [ ] Sorting capability
  - [ ] Basic filters UI slot
  - [ ] Actions column (edit/delete)
  
- [ ] Buat form reusable component
  - [ ] Input text/email/number
  - [ ] Select dropdown
  - [ ] Textarea
  - [ ] Submit/cancel buttons
  - [ ] Error display

- [ ] Buat modal/dialog component untuk confirm delete

- [ ] Setup API client helper untuk CRUD operations

### Master Data CRUD Pages

#### Stasi Management
- [ ] Page `/admin/stasis` - list semua stasi
  - [ ] Tampilkan: nama_stasi, kode_stasi, alamat, action
  - [ ] Tombol: Create, Edit, Delete
  - [ ] Filter: kode_stasi search
  
- [ ] Page `/admin/stasis/create` - form buat stasi baru
  - [ ] Field: nama_stasi (required), kode_stasi (required, unique), alamat
  - [ ] Validasi: nama_stasi min 3 char, kode_stasi pattern
  - [ ] Submit ke `POST /api/v1/master/stasis`
  
- [ ] Page `/admin/stasis/{id}/edit` - form edit stasi
  - [ ] Pre-fill dari data yang ada
  - [ ] Submit ke `PUT /api/v1/master/stasis/{id}`
  
- [ ] Implementasi delete dengan confirm modal

#### Lingkungan Stasi Management
- [ ] Page `/admin/lingkungan-stasi` - list
  - [ ] Tampilkan: nama_lingkungan_stasi, kode_lingkungan, stasi.nama_stasi, action
  - [ ] Filter: stasi dropdown, search kode
  
- [ ] Page `/admin/lingkungan-stasi/create`
  - [ ] Field: stasi_id (select required), nama_lingkungan_stasi, kode_lingkungan
  - [ ] Submit ke `POST /api/v1/master/lingkungan-stasi`
  
- [ ] Page `/admin/lingkungan-stasi/{id}/edit`
  - [ ] Pre-fill data
  - [ ] Submit ke `PUT /api/v1/master/lingkungan-stasi/{id}`
  
- [ ] Implementasi delete

#### Lingkungan Paroki Management
- [ ] Page `/admin/lingkungan-paroki` - list
  - [ ] Tampilkan: nama_lingkungan_paroki, kode_wilayah, action
  
- [ ] Page `/admin/lingkungan-paroki/create`
  - [ ] Field: nama_lingkungan_paroki, kode_wilayah
  - [ ] Submit ke `POST /api/v1/master/lingkungan-parokis`
  
- [ ] Page `/admin/lingkungan-paroki/{id}/edit`
  - [ ] Pre-fill & submit
  
- [ ] Delete implementation

#### Periode Bansos Management
- [ ] Page `/admin/periode-bansos` - list
  - [ ] Tampilkan: nama_periode, tahun, status_periode, action
  
- [ ] Page `/admin/periode-bansos/create`
  - [ ] Field: nama_periode, tahun, status_periode (enum select)
  - [ ] Submit ke `POST /api/v1/master/bansos-periods`
  
- [ ] Page `/admin/periode-bansos/{id}/edit`
  - [ ] Pre-fill & submit
  
- [ ] Delete with validation (cegah delete jika ada calon penerima)

#### User Management
- [ ] Page `/admin/users` - list all users
  - [ ] Tampilkan: name, email, role, stasi, lingkungan, action
  - [ ] Filter: role dropdown, search email
  
- [ ] Page `/admin/users/create`
  - [ ] Field: name, email, password, role (enum), conditional fields:
    - [ ] stasi_id: wajib jika role = stasi atau ketua_lingkungan_stasi
    - [ ] lingkungan_stasi_id: wajib jika role = ketua_lingkungan_stasi
    - [ ] lingkungan_paroki_id: optional untuk role ketua_lingkungan_paroki
  - [ ] Submit ke `POST /api/v1/master/users`
  - [ ] Validasi relasi sesuai role
  
- [ ] Page `/admin/users/{id}/edit`
  - [ ] Pre-fill dengan conditional fields
  - [ ] Password field: optional (jika kosong tidak direset)
  - [ ] Submit ke `PUT /api/v1/master/users/{id}`
  
- [ ] Page `/admin/users/{id}/change-password` - special page
  - [ ] Field: old_password, new_password, confirm_password
  - [ ] Submit ke `POST /api/v1/auth/change-password`
  
- [ ] Delete implementation dengan confirm

### Backend Enhancement untuk Fase 1

- [ ] Validasi FormRequest di master data controllers
  - [ ] Tambah validation pada create/update user
  - [ ] Tambah validation pada create/update lingkungan_stasi
  
- [ ] Tambah error handling response API
  - [ ] Return 422 dengan error details jika validation gagal

- [ ] Tambah endpoint helper untuk get related data
  - [ ] `GET /api/v1/master/stasis` (untuk dropdown)
  - [ ] `GET /api/v1/master/lingkungan-stasi?stasi_id={id}` (dependent select)

### Testing untuk Fase 1

- [ ] Test API CRUD stasi
- [ ] Test API CRUD lingkungan_stasi
- [ ] Test API CRUD user dengan validasi relasi
- [ ] Test delete protection (cegah delete jika ada data related)

### Kriteria Selesai Fase 1

- [x] Super admin dapat login
- [x] Super admin dapat melihat list semua master data
- [x] Super admin dapat buat/edit/hapus setiap master data
- [x] Validasi relasi user berjalan
- [x] Semua form ada error handling
- [x] Test CRUD 100% hijau

**Status Fase 1**: ✅ SELESAI (2026-05-27)

---

## Fase 2: Calon Penerima Workflow Frontend (URGENT)

**Tujuan**: Implementasi UI lengkap untuk input, list, edit, submit, approve calon penerima dengan workflow status yang tepat.

**Durasi**: 1-2 minggu  
**Priority**: URGENT  
**Dependencies**: Fase 1 (master data sudah ada)

### Calon Penerima Form & List

#### Ketua Lingkungan Stasi - Input Calon Penerima

- [ ] Page `/calon-penerima` - list draft milik lingkungan stasi
  - [ ] Tampilkan: NIK, nama_lengkap, pendapatan, tanggungan, status, action
  - [ ] Filter: status (draft only), search NIK/nama
  - [ ] Action: Create, Edit (jika draft), Delete (jika draft), View Detail
  
- [ ] Page `/calon-penerima/create` - form input baru
  - [ ] Field: periode_bansos (select required), NIK (required, numeric), nama_lengkap
  - [ ] Field: alamat_kristen, pendapatan_keluarga (decimal), jumlah_tanggungan (integer)
  - [ ] Field: status_tempat_tinggal (select: milik_sendiri/sewa/numpang)
  - [ ] Field: status_hubungan (select: lajang/menikah/cerai)
  - [ ] Field: urgensi_tambahan_tekstual (textarea)
  - [ ] Validasi: NIK 16 digit, pendapatan > 0, tanggungan >= 1
  - [ ] Submit ke `POST /api/v1/calon-penerima`
  - [ ] Status otomatis: `draft`
  
- [ ] Page `/calon-penerima/{id}/edit` - form edit
  - [ ] Pre-fill semua field
  - [ ] Hanya bisa diedit jika status = `draft` atau `diajukan_ke_stasi` (sesuai role)
  - [ ] Submit ke `PUT /api/v1/calon-penerima/{id}`
  
- [ ] Page `/calon-penerima/{id}` - detail view
  - [ ] Tampilkan semua field + data readonly
  - [ ] Tampilkan: status saat ini, tanggal dibuat, terakhir diubah
  - [ ] Tampilkan timeline status (dari activity log)
  - [ ] Tombol: Edit (conditional), Hapus (conditional), Submit (jika status = draft), Close

- [ ] Implementasi delete calon penerima
  - [ ] Hanya bisa delete jika status = `draft`
  - [ ] Confirm modal sebelum delete
  - [ ] Call: `DELETE /api/v1/calon-penerima/{id}`

#### Ketua Lingkungan Stasi - Submit ke Stasi

- [ ] Page `/calon-penerima/{id}` - tambah button "Ajukan ke Stasi"
  - [ ] Confirm modal: "Yakin ajukan ke Stasi? Data tidak bisa diubah setelah ini"
  - [ ] Call: `POST /api/v1/calon-penerima/{id}/submit-to-stasi`
  - [ ] Update status lokal jadi `diajukan_ke_stasi`
  - [ ] Redirect ke list
  
- [ ] Page `/calon-penerima?status=diajukan_ke_stasi` - list submitted
  - [ ] Tampilkan: NIK, nama, periode, status=diajukan_ke_stasi
  - [ ] Action: View Detail saja (no edit/delete)

#### Stasi - Approval Page

- [ ] Page `/stasi/approval` - list calon penerima untuk approve/reject
  - [ ] Filter: periode (select), status = `diajukan_ke_stasi` only
  - [ ] Tampilkan: NIK, nama, lingkungan_stasi, pendapatan, tanggungan, action
  - [ ] Action: Approve, Reject, View Detail
  
- [ ] Page `/stasi/approval/{id}` - detail untuk review
  - [ ] Tampilkan semua field calon penerima
  - [ ] Tombol: Approve, Reject
  - [ ] Untuk reject: textarea untuk alasan
  
- [ ] Implement approve action
  - [ ] Confirm modal: "Approve calon penerima ini?"
  - [ ] Call: `POST /api/v1/calon-penerima/{id}/approve-by-stasi`
  - [ ] Status jadi: `disetujui_stasi`
  - [ ] Redirect ke list
  
- [ ] Implement reject action
  - [ ] Modal dengan textarea untuk alasan
  - [ ] Call: `POST /api/v1/calon-penerima/{id}/reject` + request body: `{ reason }`
  - [ ] Status jadi: `ditolak`
  - [ ] Redirect ke list

#### View Daftar Calon Tersetujui (Info Pages)

- [ ] Page `/stasi/approved-list` - daftar yang sudah disetujui
  - [ ] Filter: periode, lingkungan stasi
  - [ ] Tampilkan status final (disetujui_stasi, diranking, dll)
  - [ ] Readonly, for viewing only

- [ ] Page `/paroki/approved-list` - daftar dari semua stasi
  - [ ] Filter: periode, stasi
  - [ ] Tampilkan status

### Backend Enhancement untuk Fase 2

- [ ] Tambah endpoint untuk get calon penerima dengan filter
  - [ ] `GET /api/v1/calon-penerima?status=draft&lingkungan_stasi_id={id}`
  - [ ] Dengan pagination

- [ ] Tambah validasi request create/update calon penerima
  - [ ] FormRequest: StoreCalonPenerimaRequest, UpdateCalonPenerimaRequest
  - [ ] Validasi NIK unique per periode
  - [ ] Validasi periode harus aktif

- [ ] Pastikan transisi status workflow ketat
  - [ ] Cegah edit setelah status = diajukan_ke_stasi (untuk ketua lingkungan stasi)
  - [ ] Cegah transisi status yang invalid

- [ ] Tambah audit log detail untuk setiap transisi
  - [ ] Log approve, reject, submit dengan alasan

### Testing untuk Fase 2

- [ ] Test create calon penerima
- [ ] Test edit & delete (hanya jika draft)
- [ ] Test submit to stasi
- [ ] Test approve & reject by stasi
- [ ] Test authorization per role
- [ ] Test workflow state machine

### Kriteria Selesai Fase 2

- [ ] Ketua lingkungan stasi dapat input, edit, hapus, submit calon
- [ ] Stasi dapat approve atau reject calon
- [ ] Setiap perubahan tercatat di activity log
- [ ] Data tidak bisa diubah sembarangan setelah submit
- [ ] Semua test workflow hijau
- [ ] UI responsif di desktop dan mobile

---

## Fase 3: SAW & Ranking UI (HIGH)

**Tujuan**: Membuat UI untuk konfigurasi bobot SAW, preview perhitungan, jalankan ranking, dan lihat hasil ranking.

**Durasi**: 1 minggu  
**Priority**: HIGH  
**Dependencies**: Fase 2 (calon penerima sudah ada)

### SAW Weight Configuration

- [ ] Page `/ranking/weights` - konfigurasi bobot SAW
  - [ ] Tampilkan tabel kriteria:
    - [ ] C1: Pendapatan Keluarga (default: 0.40, cost)
    - [ ] C2: Jumlah Tanggungan (default: 0.30, benefit)
    - [ ] C3: Status Tempat Tinggal (default: 0.15, benefit)
    - [ ] C4: Status Hubungan (default: 0.15, benefit)
  
  - [ ] Edit form inline atau modal:
    - [ ] Input bobot untuk tiap kriteria (decimal 0-1)
    - [ ] Display total bobot (harus = 1.00 atau 100%)
    - [ ] Tombol: Validate, Save
  
  - [ ] Validasi:
    - [ ] Total bobot harus = 1.00
    - [ ] Bobot individu harus >= 0
    - [ ] Show error jika invalid
  
  - [ ] Feature: Use Global atau Override per Periode?
    - [ ] Select periode: gunakan bobot global atau custom untuk periode ini?
    - [ ] Implementasi sesuai requirement
  
  - [ ] Tombol: Reset to Default, Save Changes
  - [ ] Call: `POST /api/v1/ranking/weights` dengan body `{ period_id?, weights: {...} }`

### SAW Preview & Calculation

- [ ] Page `/ranking/preview/{period_id}` - preview perhitungan
  - [ ] Step 1: Tampilkan summary
    - [ ] Periode: nama, tahun
    - [ ] Total calon: disetujui_stasi
    - [ ] Bobot yang akan dipakai
  
  - [ ] Step 2: Tampilkan matriks keputusan (X)
    - [ ] Tabel: id, NIK, nama, C1, C2, C3, C4
    - [ ] Data dari calon_penerima yang status = disetujui_stasi
  
  - [ ] Step 3: Tampilkan normalisasi (R)
    - [ ] Tabel: id, NIK, nama, R1, R2, R3, R4
    - [ ] Tampilkan min/max untuk setiap kriteria
  
  - [ ] Step 4: Tampilkan hasil skor (V)
    - [ ] Tabel: id, NIK, nama, skor, ranking
    - [ ] Sorted by skor desc
    - [ ] Download button (Excel/PDF preview)
  
  - [ ] Tombol: Execute Ranking (dengan confirm modal)

### SAW Execution

- [ ] Page `/ranking/execute/{period_id}` - jalankan ranking
  - [ ] Confirm modal:
    - [ ] "Anda akan menjalankan ranking untuk periode {nama}?"
    - [ ] "Data calon yang sudah diranking akan di-reset."
    - [ ] "Operasi tidak bisa dibatalkan."
  
  - [ ] Call: `POST /api/v1/ranking/execute`
  - [ ] Backend:
    - [ ] Jalankan SawCalculationService.calculate()
    - [ ] Update calon_penerima: saw_score, rank_global, rank_internal_stasi, status=diranking_lingkungan_paroki
    - [ ] Lock period jika sudah selesai
  
  - [ ] Success: redirect ke `/ranking/results/{period_id}`
  - [ ] Error: tampilkan error message

### SAW Results View

- [ ] Page `/ranking/results/{period_id}` - lihat hasil ranking
  - [ ] Filter: stasi (select), show all or top N
  - [ ] Tampilkan tabel:
    - [ ] Rank Global, Rank Stasi, NIK, Nama, Stasi, Lingkungan, Skor
    - [ ] Sorting: by rank
  
  - [ ] Stats box:
    - [ ] Total calon ranked
    - [ ] Rata-rata skor
    - [ ] Min-Max skor
    - [ ] Distribusi per stasi
  
  - [ ] Export button: Excel, PDF (preview only)
  
  - [ ] Tombol: Send to Paroki (untuk ketua lingkungan paroki)
    - [ ] Call: `POST /api/v1/ranking/send-to-paroki/{period_id}`
    - [ ] Lock ranking untuk periode ini

### Paroki View Ranking

- [ ] Page `/paroki/ranking/{period_id}` - lihat ranking dari paroki
  - [ ] Readonly view dari hasil ranking
  - [ ] Tampilkan full ranking dengan skor detail
  - [ ] Action: Finalize Decision (ke Fase 6)

### Backend Enhancement untuk Fase 3

- [ ] API endpoint untuk get/set weights
  - [ ] `GET /api/v1/ranking/weights?period_id={id}`
  - [ ] `POST /api/v1/ranking/weights`

- [ ] API endpoint untuk preview & execute ranking
  - [ ] `GET /api/v1/ranking/preview?period_id={id}` - return calculation steps
  - [ ] `POST /api/v1/ranking/execute` - run calculation

- [ ] API endpoint untuk get results
  - [ ] `GET /api/v1/ranking/results?period_id={id}&stasi_id={id}?sort=rank` - return sorted results

- [ ] Tambah model/migration SawCriterion, SawWeight, SawResult jika belum lengkap

### Testing untuk Fase 3

- [ ] Test get/set weights
- [ ] Test preview calculation
- [ ] Test execute ranking
- [ ] Test results retrieval
- [ ] Test lock behavior

### Kriteria Selesai Fase 3

- [ ] User dapat view/edit bobot SAW
- [ ] User dapat preview hasil perhitungan step-by-step
- [ ] User dapat execute ranking
- [ ] Hasil ranking tersimpan dan dapat dilihat
- [ ] Period terkunci setelah ranking selesai
- [ ] Test 100% hijau

---

## Fase 4: Document & Letter Management (HIGH)

**Tujuan**: Membuat template management, letter generation, nomor surat otomatis, dan PDF export.

**Durasi**: 1 minggu  
**Priority**: HIGH  
**Dependencies**: Fase 2 (calon penerima workflow)

### Document Template Management

- [ ] Page `/admin/templates` - list semua template
  - [ ] Tampilkan: judul_template, jenis_surat, tanggal dibuat, action
  - [ ] Filter: jenis_surat (enum select)
  - [ ] Action: Edit, Delete, Preview

- [ ] Page `/admin/templates/create` - buat template baru
  - [ ] Field: jenis_surat (select: permohonan_stasi / edaran_paroki)
  - [ ] Field: judul_template
  - [ ] Field: html_content (WYSIWYG editor atau textarea)
  - [ ] Placeholder helper panel:
    - [ ] Daftar placeholder resmi:
      - [ ] {{nama_periode}}, {{tahun}}, {{nama_stasi}}
      - [ ] {{nama_lingkungan}}, {{tanggal}}, {{nomor_surat}}
      - [ ] {{daftar_penerima}}, {{total_penerima}}, {{total_nominal}}
    - [ ] Click to insert placeholder
  
  - [ ] Tombol: Preview, Save, Cancel
  - [ ] Call: `POST /api/v1/templates`

- [ ] Page `/admin/templates/{id}/edit` - edit template
  - [ ] Pre-fill semua field
  - [ ] WYSIWYG editor dengan placeholder helper
  - [ ] Tombol: Preview, Save, Cancel
  - [ ] Call: `PUT /api/v1/templates/{id}`

- [ ] Page `/admin/templates/{id}/preview` - preview template
  - [ ] Tampilkan HTML result
  - [ ] Highlight placeholder yang ada
  - [ ] Info: "Preview ini menampilkan template, nilai sebenarnya akan diisi saat generate"

- [ ] Delete template dengan confirm

### Default Templates

- [ ] Buat seeder untuk default templates:
  - [ ] "Surat Permohonan Stasi" (jenis: permohonan_stasi)
    - [ ] Template format resmi gereja untuk permohonan
  - [ ] "Surat Edaran Paroki" (jenis: edaran_paroki)
    - [ ] Template format resmi untuk edaran keputusan

### Letter Generation

- [ ] Page `/stasi/generate-letter/{period_id}` - generate surat permohonan stasi
  - [ ] Select template: dropdown dengan default "Surat Permohonan Stasi"
  - [ ] Preview: tampilkan template dengan placeholder terisi (mock data)
  - [ ] Input nomor surat (auto-generate suggestion atau manual)
  - [ ] Tombol: Generate & Save, Cancel
  - [ ] Call: `POST /api/v1/letters/generate-permohonan-stasi` + body `{ template_id, period_id, nomor_surat }`
  - [ ] Success: Generated letter tersimpan, show PDF preview/download link

- [ ] Page `/paroki/generate-letter/{period_id}` - generate surat edaran paroki
  - [ ] Select template: dropdown dengan default "Surat Edaran Paroki"
  - [ ] Pilih stasi: select which stasis to include (all or specific)
  - [ ] Preview dengan data penerima sah (mocked)
  - [ ] Input nomor surat (auto-generate)
  - [ ] Tombol: Generate & Save, Cancel
  - [ ] Call: `POST /api/v1/letters/generate-edaran-paroki` + body `{ template_id, period_id, stasi_ids, nomor_surat }`

- [ ] Page `/letters` - archive generated letters
  - [ ] Filter: jenis_surat, periode, stasi, tanggal
  - [ ] Tampilkan: nomor_surat, jenis, periode, stasi, tanggal dibuat, action
  - [ ] Action: View, Download PDF, Delete
  - [ ] View: tampilkan HTML final

### Numbering System

- [ ] Implementasi auto-generate nomor surat
  - [ ] Format: `{jenis_kode}/{periode_tahun}/{nomor_urut}`
  - [ ] Contoh: `PERM/2026/001` untuk permohonan stasi tahun 2026 nomor 1
  - [ ] Simpan di generated_letters.nomor_surat (unique)
  - [ ] Increment counter per jenis per tahun

- [ ] Helper API endpoint:
  - [ ] `GET /api/v1/letters/next-number?type=permohonan_stasi&year=2026`

### PDF Export

- [ ] Setup library PDF (pilih: mPDF, DomPDF, atau lainnya)
  - [ ] Install package composer

- [ ] Implementasi PDF rendering
  - [ ] Take HTML content dari generated_letter.final_html_content
  - [ ] Render sebagai PDF
  - [ ] Simpan PDF file path ke database
  - [ ] Return download link

- [ ] Endpoint:
  - [ ] `GET /api/v1/letters/{id}/pdf` - download PDF

### Backend Enhancement untuk Fase 4

- [ ] Setup WYSIWYG editor asset (editor JS lib)
  - [ ] Opsi: TinyMCE, CKEditor, atau Quill

- [ ] API endpoint untuk template CRUD
  - [ ] `GET /api/v1/templates` - list
  - [ ] `POST /api/v1/templates` - create
  - [ ] `PUT /api/v1/templates/{id}` - update
  - [ ] `DELETE /api/v1/templates/{id}` - delete

- [ ] API endpoint untuk letter generation
  - [ ] `POST /api/v1/letters/generate-permohonan-stasi`
  - [ ] `POST /api/v1/letters/generate-edaran-paroki`

- [ ] DocumentService enhancement:
  - [ ] Method renderTemplate() dengan data binding
  - [ ] Method generatePdf()
  - [ ] Method saveGenerated()

- [ ] FormRequest untuk template & letter

### Testing untuk Fase 4

- [ ] Test template CRUD
- [ ] Test generate letter dengan placeholder binding
- [ ] Test PDF generation
- [ ] Test nomor surat auto-increment

### Kriteria Selesai Fase 4

- [ ] Admin dapat manage templates
- [ ] Default templates tersedia
- [ ] Letter dapat di-generate dengan placeholder terisi
- [ ] PDF export berfungsi
- [ ] Nomor surat otomatis dan unik
- [ ] Archive tersimpan

---

## Fase 5: Search, Filter & Pagination (HIGH)

**Tujuan**: Menambahkan filter, search, dan pagination pada semua list endpoint agar performan saat data besar.

**Durasi**: 3-5 hari  
**Priority**: HIGH  
**Dependencies**: Semua fase sebelumnya

### Backend - API Enhancement

- [ ] Implementasi paginated list pada semua endpoint index/list
  - [ ] `GET /api/v1/master/stasis?page=1&per_page=20`
  - [ ] `GET /api/v1/master/users?page=1&per_page=20`
  - [ ] `GET /api/v1/calon-penerima?page=1&per_page=20&status=draft`
  - [ ] Return format: `{ data: [...], pagination: { total, per_page, current_page, last_page } }`

- [ ] Implementasi search pada list endpoint
  - [ ] `GET /api/v1/calon-penerima?search=nik_atau_nama`
  - [ ] Search di field: NIK, nama_lengkap
  
  - [ ] `GET /api/v1/master/users?search=name_atau_email`
  - [ ] Search di field: name, email
  
  - [ ] `GET /api/v1/master/stasis?search=nama_atau_kode`
  - [ ] Search di field: nama_stasi, kode_stasi

- [ ] Implementasi filter per endpoint
  - [ ] Calon Penerima:
    - [ ] `?status=draft|diajukan_ke_stasi|disetujui_stasi|diranking|disetujui_paroki|ditolak`
    - [ ] `?periode_id={id}`
    - [ ] `?stasi_id={id}`
    - [ ] `?lingkungan_stasi_id={id}`
  
  - [ ] Ranking Results:
    - [ ] `?periode_id={id}`
    - [ ] `?stasi_id={id}`
    - [ ] `?sort=rank|skor` `&order=asc|desc`
  
  - [ ] Activity Log:
    - [ ] `?model_target=CalonPenerima|DocumentTemplate`
    - [ ] `?action_name=create|update|delete`
    - [ ] `?user_id={id}`
    - [ ] `?date_from=2026-01-01&date_to=2026-05-27`

- [ ] Implementasi sorting pada list
  - [ ] `GET /api/v1/calon-penerima?sort_by=nama&sort_order=asc`
  - [ ] Support multiple sort: `?sort=name:asc,created_at:desc`

- [ ] Gunakan Query Builder scope atau Repository pattern
  - [ ] Buat trait/method reusable untuk filter/search/paginate

### Frontend - List Components Enhancement

- [ ] Update data table component dengan filter UI
  - [ ] Filter bar di atas tabel dengan:
    - [ ] Search input box
    - [ ] Filter select dropdowns
    - [ ] Tombol: Clear Filters, Search
  
  - [ ] Pagination controls:
    - [ ] Previous/Next buttons
    - [ ] Go to page input
    - [ ] Per-page selector (10, 20, 50, 100)
  
  - [ ] Sort headers:
    - [ ] Click column header untuk sort
    - [ ] Visual indicator (↑ ↓) untuk sort direction

- [ ] Implementasi pada semua list pages:
  - [ ] Master data lists (stasi, users, lingkungan, periode)
  - [ ] Calon penerima list
  - [ ] Approval list (stasi view)
  - [ ] Ranking results
  - [ ] Activity logs
  - [ ] Generated letters archive

- [ ] Store filter state di URL
  - [ ] `?page=1&per_page=20&status=draft&search=ali` di URL
  - [ ] Restore filter state saat refresh

### Backend - Database Indexing

- [ ] Audit dan tambah index jika diperlukan
  - [ ] Index pada search field: NIK, nama_lengkap, email
  - [ ] Index pada filter field: status_alur, periode_id, stasi_id
  - [ ] Composite index: (periode_id, status_alur), (stasi_id, status_alur)

- [ ] Migration untuk tambah index jika belum ada

### Testing untuk Fase 5

- [ ] Test pagination (page, per_page, total calculation)
- [ ] Test search (return correct results)
- [ ] Test filter (multiple filters work correctly)
- [ ] Test sorting
- [ ] Test combination: search + filter + sort + paginate

### Kriteria Selesai Fase 5

- [ ] Semua list endpoint support pagination
- [ ] Search berfungsi pada field yang relevan
- [ ] Filter berfungsi sesuai endpoint
- [ ] Sort berfungsi
- [ ] Frontend list pages menampilkan filter/search/sort/pagination UI
- [ ] Performance oke untuk 1000+ data items

---

## Fase 6: Validasi & Constraints (HIGH)

**Tujuan**: Menambahkan validasi relasi user berdasarkan role dan proteksi penghapusan data yang masih direferensi.

**Durasi**: 3-5 hari  
**Priority**: HIGH  
**Dependencies**: Fase 1 (user management)

### User Role-Relation Validation

- [ ] Backend: Update user create/update validation
  - [ ] FormRequest: UpdateUserRequest
  - [ ] Validasi relasi sesuai role:
    - [ ] Role `stasi`: wajib punya `stasi_id`
    - [ ] Role `ketua_lingkungan_stasi`: wajib punya `stasi_id` + `lingkungan_stasi_id`
    - [ ] Role `ketua_lingkungan_paroki`: punya `lingkungan_paroki_id` (optional)
    - [ ] Role `paroki`: tidak wajib punya relasi lokasi
    - [ ] Role `super_admin`: tidak wajib punya relasi lokasi
  
  - [ ] Kode validasi:
    ```php
    'role' => 'required|in:stasi,ketua_lingkungan_stasi,...',
    'stasi_id' => Rule::requiredIf(fn() => in_array($this->role, ['stasi', 'ketua_lingkungan_stasi'])),
    'lingkungan_stasi_id' => Rule::requiredIf(fn() => $this->role === 'ketua_lingkungan_stasi'),
    ```

- [ ] Frontend: Show/hide conditional field di user form
  - [ ] Role select onChange handler
  - [ ] Tampilkan stasi_id field jika role = stasi atau ketua_lingkungan_stasi
  - [ ] Tampilkan lingkungan_stasi_id field jika role = ketua_lingkungan_stasi
  - [ ] Tampilkan lingkungan_paroki_id field jika role = ketua_lingkungan_paroki
  - [ ] Add required indicator pada conditional field

### Delete Protection

- [ ] Backend: Cegah delete master data yang masih direferensi
  - [ ] Stasi: cegah delete jika masih ada LingkunganStasi atau User atau CalonPenerima
  - [ ] LingkunganStasi: cegah delete jika masih ada User atau CalonPenerima
  - [ ] LingkunganParoki: cegah delete jika masih ada User
  - [ ] BansosPeriod: cegah delete jika sudah ada CalonPenerima
  - [ ] DocumentTemplate: cegah delete jika sudah ada GeneratedLetter

- [ ] Implementasi check sebelum delete:
  ```php
  if ($stasi->lingkunganStasis()->exists() || $stasi->users()->exists() || $stasi->calonPenerimas()->exists()) {
      throw new \Exception('Tidak bisa hapus Stasi yang masih punya relasi');
  }
  ```

- [ ] Return error response 422 dengan message jelas:
  ```json
  {
    "message": "Tidak bisa hapus Stasi karena masih ada 5 Lingkungan Stasi terkait",
    "error": "still_has_relations"
  }
  ```

- [ ] Frontend: Handle delete protection
  - [ ] Jika delete gagal karena ada relasi, tampilkan modal error
  - [ ] Tampilkan daftar relasi yang menghalangi delete
  - [ ] Tombol: OK (close modal)

### Cascade/Soft Delete Policy

- [ ] Review migration untuk cascade/soft delete
  - [ ] Decide: apakah delete harus cascade atau cegah?
  - [ ] Contoh: delete Stasi → cascade delete LingkunganStasi? atau cegah?
  - [ ] Dokumentasi keputusan di dokumen ini

- [ ] Implementasi soft delete jika diperlukan
  - [ ] Tambah `deleted_at` column pada model tertentu
  - [ ] Query hanya tampilkan non-deleted records

### Testing untuk Fase 6

- [ ] Test user create dengan validasi role-relation
- [ ] Test user update dengan validasi role-relation
- [ ] Test delete protection untuk setiap master data
- [ ] Test error message jelas

### Kriteria Selesai Fase 6

- [ ] User tidak bisa dibuat/diupdate dengan relasi invalid
- [ ] Master data tidak bisa dihapus jika masih ada referensi
- [ ] Error message informatif
- [ ] Frontend validation jelas

---

## Fase 7: Offline Sync / PWA (MEDIUM)

**Tujuan**: Implementasi IndexedDB caching, service worker, dan background sync agar aplikasi tetap berfungsi offline.

**Durasi**: 1-2 minggu  
**Priority**: MEDIUM  
**Dependencies**: Fase 2 (calon penerima workflow)

### IndexedDB Setup

- [ ] Setup IndexedDB schema di frontend
  - [ ] Database name: `spk_bansos_db`
  - [ ] Stores:
    - [ ] `offline_drafts`: id, timestamp, status, payload
    - [ ] `offline_queue`: id, timestamp, action, endpoint, method, payload, status (pending/success/failed)

- [ ] Helper library: `idb-helpers.js` (sudah ada, enhance)
  - [ ] `addDraft(data)` - simpan draft calon penerima offline
  - [ ] `getDrafts()` - ambil semua draft
  - [ ] `removeDraft(id)` - hapus draft
  - [ ] `addToQueue(action, endpoint, payload)` - simpan ke sync queue
  - [ ] `getQueue()` - ambil semua item queue
  - [ ] `updateQueueItem(id, status)` - update status queue item

### Offline Data Collection

- [ ] Enhancement di calon penerima form
  - [ ] Detect online/offline status
  - [ ] Jika offline: show banner "Mode Offline"
  - [ ] Saat submit form:
    - [ ] Jika online: submit ke server seperti biasa
    - [ ] Jika offline: 
      - [ ] Save draft ke IndexedDB
      - [ ] Show message: "Data tersimpan offline. Akan dikirim saat online."
      - [ ] Redirect ke list

- [ ] List offline drafts
  - [ ] Page `/calon-penerima?view=offline` - list draft offline
  - [ ] Tampilkan: NIK, nama, status (local draft), action
  - [ ] Action: Edit, Delete, Retry Send (if failed)

### Background Sync & Queue Management

- [ ] Service Worker enhancement: `public/sw.js`
  - [ ] Register Background Sync event untuk tag `sync-calon-penerima`
  - [ ] Trigger sync saat browser detect online

- [ ] Sync manager di frontend
  - [ ] `syncQueue()` - process semua offline queue items
  - [ ] Untuk setiap item:
    - [ ] Update status ke 'syncing'
    - [ ] Call endpoint dengan payload
    - [ ] Jika success: update status ke 'success', hapus dari queue
    - [ ] Jika error: update status ke 'failed', keep in queue (for retry)
  
  - [ ] Error handling per item:
    - [ ] 409 Conflict (duplicate NIK): show error, keep for manual review
    - [ ] 401 Unauthorized (token expired): pause sync, show auth error
    - [ ] Network error: retry later
    - [ ] Validation error 422: show error, keep for correction

- [ ] Auto-retry strategy:
  - [ ] Saat app online: trigger sync otomatis
  - [ ] Saat user di-app: tampilkan "Sync" button
  - [ ] Manual retry: user bisa click tombol retry per item

### Online/Offline Indicator

- [ ] Frontend UI indicator
  - [ ] Topbar: show online/offline status badge
  - [ ] Color: green (online) / gray (offline)
  - [ ] Click untuk open sync status modal

- [ ] Sync status modal
  - [ ] Tampilkan queue status:
    - [ ] Pending items: X
    - [ ] Failed items: X
    - [ ] Success items: X
  
  - [ ] Tampilkan list:
    - [ ] Item, status, error message (if failed)
  
  - [ ] Tombol: Retry All, Refresh Status, Close

### Service Worker Caching

- [ ] Enhance service worker untuk cache strategis
  - [ ] Strategy: Cache First untuk asset (JS, CSS, images)
  - [ ] Strategy: Network First untuk API calls
  - [ ] Strategy: Stale While Revalidate untuk critical data

- [ ] Asset caching:
  - [ ] Cache: /index.html, /app.js, /css/app.css, /manifest.json, icons
  - [ ] On install: pre-cache asset utama

- [ ] API response caching:
  - [ ] Cache master data GET requests
  - [ ] Invalidate cache saat POST/PUT/DELETE

### Progressive Enhancement

- [ ] Offline page fallback
  - [ ] Jika offline dan akses page yang belum di-cache: tampilkan offline page
  - [ ] Info: available pages, sync queue status

- [ ] Manifest & PWA Setup
  - [ ] `public/manifest.json` - already exists, verify content
  - [ ] Icons: ensure 192x192 dan 512x512 PNG tersedia
  - [ ] Display: standalone
  - [ ] Theme color

### Backend - Offline Sync Endpoint

- [ ] API endpoint untuk offline sync
  - [ ] `POST /api/v1/offline/sync` (sudah ada di OfflineSyncController)
  - [ ] Accept action: `submit_calon_penerima` + payload
  - [ ] Validasi NIK duplicate check sebelum insert
  - [ ] Return: success/error dengan clear message

### Testing untuk Fase 7

- [ ] Test IndexedDB add/get/remove
- [ ] Test offline form submission
- [ ] Test sync queue processing
- [ ] Test conflict resolution (duplicate NIK)
- [ ] Test service worker caching
- [ ] Test offline page fallback

### Kriteria Selesai Fase 7

- [ ] User dapat input calon penerima offline
- [ ] Data offline tersimpan lokal
- [ ] Saat online: data tersinkron otomatis atau manual
- [ ] UI jelas menunjukkan status sync
- [ ] Asset utama dapat di-cache
- [ ] Error handling clear

---

## Fase 8: Reporting & Export (MEDIUM)

**Tujuan**: Membuat laporan keputusan, rekap data, dan export ke Excel/CSV.

**Durasi**: 1 minggu  
**Priority**: MEDIUM  
**Dependencies**: Fase 3 (ranking selesai) & Fase 5 (pagination/filter)

### Report Pages

- [ ] Dashboard Statistik
  - [ ] Page `/dashboard` per role:
    - [ ] **Super Admin**: Total user per role, total periode, total calon, status distribution
    - [ ] **Paroki**: Total calon per periode, penerima sah, nominal total, calon per stasi
    - [ ] **Ketua Lingkungan Paroki**: Same as Paroki but filtered to own lingkungan
    - [ ] **Stasi**: Total calon diajukan, disetujui, ditolak, average skor
    - [ ] **Ketua Lingkungan Stasi**: Total calon draft, diajukan, approved, rejected

- [ ] Laporan Calon Penerima
  - [ ] Page `/reports/calon-penerima`
    - [ ] Filter: periode, stasi, lingkungan, status
    - [ ] Tampilkan: NIK, nama, stasi, lingkungan, status, tanggal dibuat
    - [ ] Pagination & search

- [ ] Laporan Penerima Sah
  - [ ] Page `/reports/penerima-sah`
    - [ ] Filter: periode, stasi
    - [ ] Tampilkan: NIK, nama, stasi, lingkungan, nominal bansos, tanggal keputusan
    - [ ] Total summary: total penerima, total nominal
    - [ ] Pagination

- [ ] Laporan Ranking SAW
  - [ ] Page `/reports/ranking`
    - [ ] Filter: periode, stasi, show all or top N
    - [ ] Tampilkan: rank, NIK, nama, skor, status akhir
    - [ ] Comparison: rank before & after paroki decision (jika ada)

- [ ] Laporan Nominal Bantuan
  - [ ] Page `/reports/nominal-bansos`
    - [ ] Filter: periode, stasi
    - [ ] Tampilkan: NIK, nama, nominal, status payment (if applicable)
    - [ ] Total & breakdown per stasi

- [ ] Activity Log Viewer
  - [ ] Page `/reports/audit-log`
    - [ ] Filter: user, action, model, date range
    - [ ] Tampilkan: tanggal, user, aksi, model, detail perubahan
    - [ ] Pagination

### Export Functionality

- [ ] Backend API untuk export
  - [ ] Setup library: Laravel Maatwebsite/Laravel-Excel (atau alternative)
  
  - [ ] Endpoint:
    - [ ] `GET /api/v1/export/calon-penerima?format=csv|excel&period_id={id}`
    - [ ] `GET /api/v1/export/penerima-sah?format=csv|excel&period_id={id}`
    - [ ] `GET /api/v1/export/ranking?format=csv|excel&period_id={id}`
    - [ ] `GET /api/v1/export/nominal-bansos?format=csv|excel&period_id={id}`
  
  - [ ] Return: file download dengan nama format: `report_{type}_{period}_{date}.csv|xlsx`

- [ ] Frontend: Export buttons di setiap report page
  - [ ] Button group: "Export CSV", "Export Excel"
  - [ ] Call endpoint dengan current filter
  - [ ] Trigger browser download

### Export Templates

- [ ] Excel template dengan formatting
  - [ ] Header: title, periode, tanggal generate, generated by
  - [ ] Metadata: filter yang dipakai
  - [ ] Data table dengan formatting: bold header, alternating row color
  - [ ] Summary row: total, calculated fields

- [ ] CSV simple
  - [ ] Header row
  - [ ] Data rows
  - [ ] Encoding: UTF-8 with BOM untuk kompatibilitas Excel Windows

### Report Scheduling (Optional)

- [ ] Generate laporan otomatis (opsional)
  - [ ] Endpoint: `POST /api/v1/reports/schedule-generate`
  - [ ] Input: type, frequency, recipients (email)
  - [ ] Background job: process & send email dengan attachment

### Testing untuk Fase 8

- [ ] Test report API endpoints
- [ ] Test export CSV/Excel
- [ ] Test filter pada report
- [ ] Test file download

### Kriteria Selesai Fase 8

- [ ] Laporan statistik tersedia per role
- [ ] Export CSV & Excel berfungsi
- [ ] File export memiliki formatting rapi
- [ ] Filter & search pada report berfungsi

---

## Fase 9: Hardening & Production Ready (LOW)

**Tujuan**: Meningkatkan keamanan, performa, dan kesiapan production aplikasi.

**Durasi**: 1 minggu  
**Priority**: LOW  
**Dependencies**: Semua fase sebelumnya

### Security Hardening

- [ ] Rate Limiting
  - [ ] Tambah rate limit pada login endpoint
  - [ ] Middleware: `throttle:5,1` untuk login (5 attempts per 1 minute)
  - [ ] Middleware: `throttle:60,1` untuk API calls umum

- [ ] CORS & CSRF Protection
  - [ ] Review CORS config (app/Http/Middleware/HandleCors.php atau config/cors.php)
  - [ ] Pastikan hanya origin yang trusted
  - [ ] CSRF token jika ada form non-API

- [ ] Input Sanitization
  - [ ] Audit semua input untuk potential XSS/injection
  - [ ] Gunakan Laravel's blade escaping
  - [ ] Sanitize HTML content sebelum store (jika dari user input)

- [ ] Authorization Policy
  - [ ] Review Policy di app/Policies/
  - [ ] Pastikan setiap action ada policy check
  - [ ] Test unauthorized access return 403

- [ ] Secret Management
  - [ ] Review `.env` untuk sensitive data (API keys, DB password)
  - [ ] Pastikan `.env` tidak di-commit ke git
  - [ ] Dokumentasi setup `.env` di README

### Performance Optimization

- [ ] Database Query Optimization
  - [ ] Audit N+1 query problem
  - [ ] Implement eager loading dengan `with()` / `load()`
  - [ ] Add index pada query-heavy columns (already in Fase 5)

- [ ] Caching Strategy
  - [ ] Cache master data ke Redis/Memcached
  - [ ] Invalidate cache saat data berubah
  - [ ] Cache API responses dengan TTL

- [ ] API Response Optimization
  - [ ] Limit default per_page (max 100 records)
  - [ ] Implement field selection: `?fields=id,name,email`
  - [ ] Compress response: gzip middleware

### Error Handling

- [ ] Global error handler
  - [ ] Custom exception untuk aplikasi
  - [ ] Return consistent error response format
  - [ ] Don't expose stack trace di production

- [ ] Validation error responses
  - [ ] Return 422 dengan error details
  - [ ] Format: `{ message, errors: { field: [messages] } }`

- [ ] Log errors dengan context
  - [ ] Laravel logging sudah ada, verify config

### Documentation

- [ ] Update README.md dengan:
  - [ ] Installation steps
  - [ ] Environment setup (.env)
  - [ ] Running migrations & seeders
  - [ ] Running server
  - [ ] Running tests
  - [ ] API documentation link (atau generate dari postman/swagger)

- [ ] API Documentation
  - [ ] Generate OpenAPI/Swagger spec
  - [ ] Update postman collection jika ada
  - [ ] Document semua endpoint dengan:
    - [ ] Method, path, role required
    - [ ] Request body & query params
    - [ ] Response format
    - [ ] Error cases

- [ ] User Documentation (Optional)
  - [ ] User guide per role
  - [ ] Screenshots/walkthrough
  - [ ] FAQ

### Deployment & DevOps

- [ ] Docker Setup
  - [ ] Review Dockerfile
  - [ ] Setup docker-compose.yml untuk local dev & production
  - [ ] Test: `docker-compose up` harus bisa run full stack

- [ ] CI/CD Pipeline
  - [ ] Setup GitHub Actions atau similar
  - [ ] Automated tests: `composer test`
  - [ ] Code formatting check: `vendor/bin/pint --test`
  - [ ] Migration test: `php artisan migrate:fresh --seed --force`
  - [ ] Publish success metrics

- [ ] Database Backup Strategy
  - [ ] Setup automated backup (if local deployment)
  - [ ] Restore procedure documented

### Monitoring & Logging

- [ ] Application Logging
  - [ ] Review logging config
  - [ ] Log important events: user login, data changes, errors
  - [ ] Rotate logs untuk prevent disk full

- [ ] Performance Monitoring
  - [ ] Add query timing logs
  - [ ] Monitor slow endpoints

### Testing

- [ ] Increase test coverage
  - [ ] Target: 80% coverage
  - [ ] Test critical paths

- [ ] Integration tests
  - [ ] Test full workflow: user login → input calon → approve → rank → finalize
  - [ ] Test offline sync flow

- [ ] Performance tests
  - [ ] Load test dengan 100+ concurrent users
  - [ ] Measure response time

### Testing untuk Fase 9

- [ ] Test rate limiting
- [ ] Test CORS
- [ ] Test authorization
- [ ] Test error handling
- [ ] Test error logging

### Kriteria Selesai Fase 9

- [ ] Aplikasi aman dari basic security issues
- [ ] Performa acceptable untuk expected users
- [ ] Documentasi lengkap
- [ ] Deployment bisa direproduksi
- [ ] Test coverage >= 70%

---

## Timeline Rekomendasi

### Option 1: Cepat Demo (6-8 minggu)
1. Fase 1: Master Data (1 minggu)
2. Fase 2: Calon Penerima Workflow (1 minggu)
3. Fase 3: SAW & Ranking (1 minggu)
4. Fase 4: Document & Letter (1 minggu)
5. Fase 5: Search/Filter/Pagination (3-5 hari)
6. Fase 6: Validasi (3-5 hari)
7. Demo ready. Fase 7-9 optional untuk future sprints.

### Option 2: Full Development (10-12 minggu)
Implementasi semua 9 fase secara bertahap. Rekomendasi urutan:
1. Fase 1-4: Core workflow (4 minggu)
2. Fase 5-6: Data management (1 minggu)
3. Fase 7: Offline (1-2 minggu)
4. Fase 8: Reporting (1 minggu)
5. Fase 9: Hardening (1 minggu)

### Option 3: Agile (Iterative)
Iterasi per 2 minggu (sprint):
- Sprint 1-2: Fase 1 + 2
- Sprint 3: Fase 3
- Sprint 4: Fase 4 + 5
- Sprint 5: Fase 6 + 7
- Sprint 6: Fase 8 + 9

---

## Checklist Progress Tracking

### Fase 1: Master Data Pages
- [ ] Frontend infrastructure complete
- [ ] Stasi CRUD pages done
- [ ] Lingkungan Stasi CRUD pages done
- [ ] Lingkungan Paroki CRUD pages done
- [ ] Periode Bansos CRUD pages done
- [ ] User management pages done
- [ ] Backend validation complete
- [ ] Tests pass
- [ ] **Phase Completion**: ___/___/2026

### Fase 2: Calon Penerima Workflow
- [ ] Form input calon penerima done
- [ ] List calon per status done
- [ ] Submit to stasi done
- [ ] Approval pages for stasi done
- [ ] Timeline/history view done
- [ ] Activity log integration done
- [ ] Backend validation complete
- [ ] Tests pass
- [ ] **Phase Completion**: ___/___/2026

### Fase 3: SAW & Ranking
- [ ] Weight configuration UI done
- [ ] Preview calculation done
- [ ] Execute ranking done
- [ ] Results view done
- [ ] Lock mechanism done
- [ ] Backend API complete
- [ ] Tests pass
- [ ] **Phase Completion**: ___/___/2026

### Fase 4: Document & Letter
- [ ] Template CRUD done
- [ ] Default templates seeded
- [ ] Letter generation done
- [ ] Numbering system done
- [ ] PDF export done
- [ ] Archive view done
- [ ] Tests pass
- [ ] **Phase Completion**: ___/___/2026

### Fase 5: Search/Filter/Pagination
- [ ] Pagination API complete
- [ ] Search API complete
- [ ] Filter API complete
- [ ] Sort API complete
- [ ] Frontend components updated
- [ ] Database indexes added
- [ ] Tests pass
- [ ] **Phase Completion**: ___/___/2026

### Fase 6: Validasi & Constraints
- [ ] User role-relation validation done
- [ ] Delete protection done
- [ ] Cascade policy decided & implemented
- [ ] Frontend conditional fields done
- [ ] Error handling clear
- [ ] Tests pass
- [ ] **Phase Completion**: ___/___/2026

### Fase 7: Offline Sync / PWA
- [ ] IndexedDB schema complete
- [ ] Offline form submission done
- [ ] Sync queue management done
- [ ] Background sync done
- [ ] Online/offline indicator done
- [ ] Service worker caching done
- [ ] Tests pass
- [ ] **Phase Completion**: ___/___/2026

### Fase 8: Reporting & Export
- [ ] Dashboard statistics done
- [ ] Report pages complete
- [ ] Export CSV/Excel done
- [ ] Activity log viewer done
- [ ] Tests pass
- [ ] **Phase Completion**: ___/___/2026

### Fase 9: Hardening & Production
- [ ] Security hardening complete
- [ ] Performance optimization complete
- [ ] Error handling complete
- [ ] Documentation complete
- [ ] Docker/deployment ready
- [ ] Tests pass
- [ ] **Phase Completion**: ___/___/2026

---

## Notes & Dependencies

### Critical Dependencies
- Phase 1 → Phase 2, 3, 4 (master data needed)
- Phase 2 → Phase 3, 5, 6 (calon penerima needed)
- Phase 3 → Phase 6, 8 (ranking needed)
- Phase 4 → Phase 8 (documents needed)

### Technology Stack Decisions Needed
- [ ] WYSIWYG editor library? (TinyMCE, CKEditor, Quill)
- [ ] PDF export library? (mPDF, DomPDF, Snappy)
- [ ] Export Excel library? (Laravel-Excel/Maatwebsite)
- [ ] Caching backend? (Redis, Memcached, file)
- [ ] Frontend framework? (Vue, React, Alpine.js - confirm existing)

### Known Issues to Address
- (Will be updated as issues discovered)

### Team Notes
- (Add team-specific notes, blockers, decisions here)

---

## References

- **Guide**: [guide.md](guide.md)
- **Roadmap**: [ROADMAP_TODO.md](ROADMAP_TODO.md)
- **README**: [README.md](README.md)
- **API**: `php artisan route:list`
- **Tests**: `composer test`

---

**Last Updated**: 2026-05-27  
**Next Review**: 2026-06-03

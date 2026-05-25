# Roadmap Todo Penyelesaian SPK Bansos

Dokumen ini merangkum pekerjaan yang perlu dilakukan agar project SPK Bansos berkembang dari fondasi backend awal menjadi aplikasi operasional yang siap dipakai oleh tiap role.

## Prinsip Pengerjaan

- Jangan menghapus fitur, endpoint, migration, view, atau file yang sudah ada tanpa alasan kuat.
- Gunakan migration Laravel sebagai sumber utama struktur database.
- Pertahankan alur role yang sudah dirancang:
  - `super_admin`
  - `paroki`
  - `ketua_lingkungan_paroki`
  - `stasi`
  - `ketua_lingkungan_stasi`
- Setiap fase besar sebaiknya disertai test minimal.
- Setelah perubahan backend, jalankan:

```bash
composer test
php artisan route:list
vendor/bin/pint --test
```

Pada Windows, perintah Pint bisa memakai:

```bash
vendor\bin\pint --test
```

## Fase 1: Fondasi Operasional

### Tujuan

Memastikan backend, database, auth, role, dan response API stabil sebagai dasar semua fitur berikutnya.

### Todo

- [x] Pastikan seluruh struktur database utama tersedia sebagai migration Laravel.
- [x] Jadikan `schema.sql` sebagai referensi/export, bukan sumber utama schema.
- [x] Sinkronkan isi `schema.sql` dengan migration terbaru jika masih dipakai sebagai dokumentasi.
- [x] Lengkapi seed data demo untuk semua role:
  - [x] `super_admin`
  - [x] `paroki`
  - [x] `ketua_lingkungan_paroki`
  - [x] `stasi`
  - [x] `ketua_lingkungan_stasi`
- [x] Tambahkan endpoint `/api/v1/auth/me` untuk mengambil user login.
- [x] Pastikan endpoint logout menghapus token aktif dengan aman.
- [x] Tentukan format response API standar:
  - [x] response sukses memakai `message` dan `data`
  - [x] response validasi memakai `message` dan `errors`
  - [x] response gagal memakai HTTP status yang tepat
- [x] Terapkan format response standar ke endpoint penting.
- [x] Audit semua endpoint agar memiliki validasi request yang jelas.
- [x] Audit semua endpoint agar memiliki middleware role yang sesuai.
- [x] Audit akses tenant/hierarki data:
  - [x] ketua lingkungan stasi hanya melihat data lingkungan stasinya
  - [x] stasi hanya melihat data stasinya
  - [x] paroki dapat melihat data final sesuai kebutuhan
  - [x] super admin dapat mengakses semua data
- [x] Lengkapi policy untuk model penting.
- [x] Tambahkan test authorization dasar per role.

### Kriteria Selesai

- [x] `php artisan migrate:fresh --seed` berhasil dari database kosong.
- [x] `composer test` hijau.
- [x] Semua endpoint utama muncul di `php artisan route:list`.
- [x] Login, logout, dan ambil user login berjalan.
- [x] User dengan role salah mendapat response `403`.

## Fase 2: Auth dan Shell Frontend

### Tujuan

Membangun kerangka aplikasi frontend agar user bisa login, melihat menu sesuai role, dan memakai aplikasi dari satu layout utama.

### Todo

- [x] Buat halaman login.
- [x] Hubungkan form login ke `/api/v1/auth/login`.
- [x] Simpan token secara konsisten di frontend.
- [x] Buat helper API client untuk request dengan bearer token.
- [x] Buat mekanisme logout dari frontend.
- [x] Buat layout aplikasi utama:
  - [x] sidebar/menu
  - [x] topbar user
  - [x] area konten
  - [x] state loading
  - [x] state error
  - [x] state empty
- [x] Buat guard frontend agar halaman hanya bisa diakses setelah login.
- [x] Buat menu berdasarkan role user.
- [x] Buat dashboard ringkas per role.
- [x] Tampilkan informasi user login:
  - [x] nama
  - [x] email
  - [x] role
  - [x] stasi/lingkungan jika ada
- [x] Tangani token expired atau token invalid.

### Kriteria Selesai

- [x] User dapat login dari browser.
- [x] User melihat menu sesuai role.
- [x] User dapat logout.
- [x] User tanpa token diarahkan ke login.
- [x] UI dasar nyaman dipakai di desktop dan mobile.

## Fase 3: Master Data

### Tujuan

Menyediakan pengelolaan data dasar agar sistem tidak bergantung pada seeder/manual database.

### Todo

- [ ] CRUD Stasi.
- [ ] CRUD Lingkungan Stasi.
- [ ] CRUD Lingkungan Paroki.
- [ ] CRUD Periode Bansos.
- [ ] CRUD User.
- [ ] CRUD Role atau setidaknya manajemen role user.
- [ ] Tambahkan filter/search untuk setiap master data.
- [ ] Tambahkan pagination untuk list master data.
- [ ] Tambahkan validasi relasi user:
  - [ ] user role `stasi` wajib punya `stasi_id`
  - [ ] user role `ketua_lingkungan_stasi` wajib punya `stasi_id` dan `lingkungan_stasi_id`
  - [ ] user role `paroki` tidak wajib punya `stasi_id`
  - [ ] user role `ketua_lingkungan_paroki` disesuaikan dengan struktur paroki
- [ ] Cegah penghapusan master data yang masih dipakai.
- [ ] Buat halaman frontend untuk setiap master data.
- [ ] Tambahkan test API master data.

### Progress

- [x] CRUD Stasi.
- [x] CRUD Lingkungan Stasi.
- [x] CRUD Lingkungan Paroki.
- [x] CRUD Periode Bansos.
- [x] CRUD User.
- [x] CRUD Role atau setidaknya manajemen role user.
- [x] Tambahkan filter/search untuk setiap master data.
- [x] Tambahkan pagination untuk list master data.
- [x] Tambahkan validasi relasi user:
  - [x] user role `stasi` wajib punya `stasi_id`
  - [x] user role `ketua_lingkungan_stasi` wajib punya `stasi_id` dan `lingkungan_stasi_id`
  - [x] user role `paroki` tidak wajib punya `stasi_id`
  - [x] user role `ketua_lingkungan_paroki` disesuaikan dengan struktur paroki
- [x] Cegah penghapusan master data yang masih dipakai.
- [x] Buat halaman frontend untuk setiap master data (skeleton admin-master di SPA).
- [x] Tambahkan test API master data (tests/Feature/MasterDataTest.php).

### Status Implementasi (2026-05-26)

- **Ringkasan**: Model, migrasi, dan seeder demo untuk master data utama sudah tersedia. Namun API CRUD, validasi relasi user saat create/update, halaman frontend master data, filter/search/pagination, proteksi delete, dan test API belum diimplementasikan.

- **Yang sudah tersedia di kode**:
  - Model `Stasi` dan migrasi [database/migrations/2026_05_24_000010_create_stasis_table.php](database/migrations/2026_05_24_000010_create_stasis_table.php#L1) serta seeder demo.
  - Model `LingkunganStasi` dan migrasi [database/migrations/2026_05_24_000030_create_lingkungan_stasis_table.php](database/migrations/2026_05_24_000030_create_lingkungan_stasis_table.php#L1).
  - Model `LingkunganParoki` dan migrasi [database/migrations/2026_05_24_000020_create_lingkungan_parokis_table.php](database/migrations/2026_05_24_000020_create_lingkungan_parokis_table.php#L1).
  - Model `BansosPeriod` dan migrasi [database/migrations/2026_05_24_000040_create_bansos_periods_table.php](database/migrations/2026_05_24_000040_create_bansos_periods_table.php#L1).
  - Penambahan kolom `role`, `stasi_id`, `lingkungan_paroki_id`, `lingkungan_stasi_id` pada tabel `users` ([database/migrations/2026_05_24_000100_modify_users_table_add_fields.php](database/migrations/2026_05_24_000100_modify_users_table_add_fields.php#L1)).

- **Yang belum / perlu dikerjakan**:
  - Implementasi API CRUD (index/show/store/update/destroy) untuk semua master data.
  - Validasi relasi user pada create/update user sesuai role (mis. role `stasi` wajib `stasi_id`).
  - Filter/search dan pagination pada endpoints list.
  - Proteksi penghapusan item yang masih direferensi (cek relasi sebelum delete).
  - Halaman frontend untuk manajemen master data.
  - Test API untuk CRUD master data.

- **Rekomendasi langkah implementasi singkat**:
  1. Tambah controller API resource untuk masing-masing master data di `app/Http/Controllers/Api/Master/`.
  2. Tambah FormRequest untuk validasi input.
  3. Daftarkan routes di `routes/api.php` di bawah middleware `auth:sanctum` dan `role:super_admin`.
  4. Tambah Feature tests di `tests/Feature/MasterDataTest.php`.
  5. Implement frontend pages setelah API stabil.

- **Catatan**: Saya hanya memperbarui dokumen roadmap untuk mencerminkan status saat ini. Saya tidak mengubah kode aplikasi.

### Kriteria Selesai

- [ ] Super admin dapat mengelola seluruh master data dari UI.
- [ ] Validasi relasi user berjalan.
- [ ] List master data mendukung search dan pagination.
- [ ] Test CRUD dasar hijau.

## Fase 4: Workflow Calon Penerima

### Tujuan

Membuat alur pendataan dan verifikasi calon penerima bansos dapat digunakan secara nyata.

### Todo

- [ ] Buat form input calon penerima untuk ketua lingkungan stasi.
- [ ] Lengkapi field calon penerima:
  - [ ] periode bansos
  - [ ] NIK
  - [ ] nama lengkap
  - [ ] alamat
  - [ ] pendapatan keluarga
  - [ ] jumlah tanggungan
  - [ ] status tempat tinggal
  - [ ] status hubungan
  - [ ] urgensi tambahan
- [ ] Buat list draft calon penerima milik lingkungan stasi.
- [ ] Buat fitur edit calon penerima selama status masih `draft`.
- [ ] Buat fitur hapus calon penerima selama status masih `draft`.
- [ ] Buat fitur submit calon penerima ke stasi.
- [ ] Buat halaman rekap calon penerima untuk stasi.
- [ ] Buat fitur approve oleh stasi.
- [ ] Buat fitur reject oleh stasi dengan alasan.
- [ ] Simpan alasan penolakan.
- [ ] Buat riwayat perubahan status.
- [ ] Tampilkan timeline status di detail calon penerima.
- [ ] Tambahkan audit log yang lebih informatif:
  - [ ] aktor
  - [ ] aksi
  - [ ] status sebelum
  - [ ] status sesudah
  - [ ] waktu
  - [ ] catatan/alasan
- [ ] Batasi transisi status agar tidak loncat alur.
- [ ] Tambahkan test workflow:
  - [ ] `draft` ke `diajukan_ke_stasi`
  - [ ] `diajukan_ke_stasi` ke `disetujui_stasi`
  - [ ] `diajukan_ke_stasi` ke `ditolak`
  - [ ] larangan edit setelah submit
  - [ ] larangan approve oleh role yang salah

### Kriteria Selesai

- [ ] Ketua lingkungan stasi dapat input, edit, hapus, dan submit calon.
- [ ] Stasi dapat approve atau reject.
- [ ] Setiap perubahan status tercatat.
- [ ] Data tidak bisa diubah sembarangan setelah masuk proses.

## Fase 5: SAW dan Ranking

### Tujuan

Membuat metode SAW lebih matang, transparan, dapat dikonfigurasi, dan dapat diaudit.

### Todo

- [ ] Buat tabel kriteria SAW.
- [ ] Buat tabel bobot kriteria.
- [ ] Tentukan apakah bobot berlaku global atau per periode.
- [ ] Buat seeder kriteria default:
  - [ ] pendapatan keluarga
  - [ ] jumlah tanggungan
  - [ ] status tempat tinggal
  - [ ] status hubungan
- [ ] Buat halaman konfigurasi bobot.
- [ ] Validasi total bobot.
- [ ] Buat preview matriks keputusan.
- [ ] Buat preview normalisasi.
- [ ] Buat preview hasil skor sebelum disimpan.
- [ ] Jalankan ranking per periode.
- [ ] Simpan hasil ranking secara audit-able:
  - [ ] nilai mentah
  - [ ] nilai normalisasi
  - [ ] bobot yang dipakai
  - [ ] skor akhir
  - [ ] ranking
- [ ] Buat ranking global.
- [ ] Buat ranking per stasi.
- [ ] Tambahkan fitur lock hasil ranking setelah dikirim ke paroki.
- [ ] Cegah perubahan bobot setelah hasil ranking dikunci.
- [ ] Tambahkan test end-to-end perhitungan SAW.

### Kriteria Selesai

- [ ] Admin/role terkait dapat mengatur bobot.
- [ ] Sistem menampilkan detail perhitungan SAW.
- [ ] Ranking dapat diproses per periode.
- [ ] Hasil ranking bisa dijelaskan dan diaudit.

## Fase 6: Keputusan Final Paroki

### Tujuan

Membuat paroki dapat menetapkan penerima bansos resmi berdasarkan hasil ranking.

### Todo

- [ ] Buat halaman paroki untuk melihat ranking.
- [ ] Tambahkan filter ranking:
  - [ ] periode
  - [ ] stasi
  - [ ] status
  - [ ] penerima sah/belum
- [ ] Buat fitur pilih penerima sah.
- [ ] Buat input nominal bansos.
- [ ] Buat fitur finalisasi keputusan.
- [ ] Buat fitur tolak kandidat dengan alasan.
- [ ] Cegah perubahan setelah periode selesai.
- [ ] Beri akses override terbatas untuk super admin jika diperlukan.
- [ ] Buat halaman daftar penerima sah.
- [ ] Buat summary keputusan:
  - [ ] total calon
  - [ ] total penerima sah
  - [ ] total nominal bantuan
  - [ ] distribusi penerima per stasi
- [ ] Tambahkan test finalisasi keputusan.

### Kriteria Selesai

- [ ] Paroki dapat menetapkan penerima sah.
- [ ] Nominal bantuan tersimpan.
- [ ] Status final tidak mudah berubah tanpa izin.
- [ ] Ringkasan keputusan tersedia.

## Fase 7: Dokumen dan Surat

### Tujuan

Membuat fitur dokumen menjadi siap pakai untuk surat permohonan, surat edaran, dan arsip.

### Todo

- [ ] Rapikan model template dokumen.
- [ ] Pastikan nama field template konsisten dengan migration dan service.
- [ ] Buat template default:
  - [ ] surat permohonan stasi
  - [ ] surat edaran paroki
  - [ ] berita acara/rekap penerima jika dibutuhkan
- [ ] Buat editor template sederhana.
- [ ] Buat daftar placeholder resmi:
  - [ ] `{{nama_periode}}`
  - [ ] `{{tahun}}`
  - [ ] `{{nama_stasi}}`
  - [ ] `{{nama_lingkungan}}`
  - [ ] `{{tanggal}}`
  - [ ] `{{nomor_surat}}`
  - [ ] `{{daftar_penerima}}`
  - [ ] `{{total_penerima}}`
  - [ ] `{{total_nominal}}`
- [ ] Buat nomor surat otomatis.
- [ ] Buat preview surat sebelum generate.
- [ ] Render HTML final surat.
- [ ] Simpan arsip generated letter.
- [ ] Tambahkan fitur download/export PDF.
- [ ] Tambahkan fitur lihat ulang surat yang sudah dibuat.
- [ ] Tambahkan test render template.
- [ ] Tambahkan test generate surat.

### Kriteria Selesai

- [ ] Template dapat dibuat dan diedit.
- [ ] Surat dapat dipreview.
- [ ] Surat final tersimpan sebagai arsip.
- [ ] Surat bisa diunduh sebagai PDF.

## Fase 8: PWA dan Offline Sync

### Tujuan

Membuat aplikasi tetap berguna saat koneksi tidak stabil, terutama untuk input calon penerima.

### Todo

- [ ] Buat indikator status online/offline di UI.
- [ ] Simpan draft calon penerima ke IndexedDB saat offline.
- [ ] Tampilkan daftar data yang menunggu sync.
- [ ] Tambahkan tombol sync manual.
- [ ] Jalankan retry sync otomatis saat koneksi kembali online.
- [ ] Simpan status sync per item:
  - [ ] menunggu
  - [ ] sedang dikirim
  - [ ] berhasil
  - [ ] gagal
- [ ] Tangani error sync:
  - [ ] periode tidak aktif
  - [ ] user tidak punya lingkungan
  - [ ] data tidak valid
  - [ ] token expired
  - [ ] duplikasi NIK
- [ ] Buat strategi konflik data.
- [ ] Tambahkan service worker caching untuk asset utama.
- [ ] Pastikan halaman utama tetap bisa dibuka saat offline.
- [ ] Tambahkan test endpoint offline sync.

### Kriteria Selesai

- [ ] User dapat mengisi data saat offline.
- [ ] Data offline tersimpan lokal.
- [ ] Data tersinkron otomatis saat online.
- [ ] UI menampilkan status sync dengan jelas.

## Fase 9: Laporan dan Export

### Tujuan

Menyediakan laporan keputusan dan rekap data untuk kebutuhan administrasi.

### Todo

- [ ] Buat dashboard statistik per role.
- [ ] Buat laporan calon penerima per periode.
- [ ] Buat laporan penerima sah per periode.
- [ ] Buat laporan ranking SAW.
- [ ] Buat laporan nominal bantuan.
- [ ] Tambahkan filter laporan:
  - [ ] periode
  - [ ] stasi
  - [ ] lingkungan
  - [ ] status
  - [ ] penerima sah/belum
- [ ] Tambahkan export CSV.
- [ ] Tambahkan export Excel jika library sudah dipilih.
- [ ] Tambahkan export PDF rekap.
- [ ] Buat halaman audit log.
- [ ] Tambahkan filter audit log:
  - [ ] user
  - [ ] aksi
  - [ ] tanggal
  - [ ] model target
- [ ] Tambahkan test laporan dasar.

### Kriteria Selesai

- [ ] Paroki/stasi dapat melihat laporan sesuai aksesnya.
- [ ] Data laporan bisa difilter.
- [ ] Data laporan bisa diexport.
- [ ] Audit log dapat ditelusuri.

## Fase 10: Hardening dan Production Readiness

### Tujuan

Membuat aplikasi lebih aman, mudah dirawat, dan siap dijalankan di lingkungan nyata.

### Todo

- [ ] Rapikan error handling API.
- [ ] Tambahkan rate limiting login.
- [ ] Tambahkan policy lengkap untuk semua model penting.
- [ ] Pastikan semua list besar memakai pagination.
- [ ] Pastikan semua input penting tervalidasi.
- [ ] Review keamanan token frontend.
- [ ] Review penggunaan CORS dan Sanctum.
- [ ] Tambahkan logging aktivitas penting.
- [ ] Tambahkan backup/restore database jika target deployment lokal.
- [ ] Rapikan Docker setup.
- [ ] Uji deployment Docker.
- [ ] Uji deployment lokal/XAMPP jika memang target pengguna memakai XAMPP.
- [ ] Lengkapi README operasional:
  - [ ] instalasi
  - [ ] konfigurasi `.env`
  - [ ] migration
  - [ ] seeding
  - [ ] menjalankan server
  - [ ] menjalankan test
  - [ ] akun demo
- [ ] Tambahkan CI yang menjalankan:
  - [ ] `composer test`
  - [ ] `vendor/bin/pint --test`
  - [ ] `php artisan migrate:fresh --seed --force`
- [ ] Review performa query penting.
- [ ] Tambahkan index database jika ada query yang berat.

### Kriteria Selesai

- [ ] Aplikasi dapat dipasang dari nol memakai instruksi README.
- [ ] Test dan format check berjalan di CI.
- [ ] Deployment lokal/Docker berhasil.
- [ ] Risiko keamanan dasar sudah ditangani.

## Urutan Pengerjaan yang Disarankan

Urutan ini dipilih agar aplikasi cepat terlihat hidup, lalu diperkuat secara bertahap.

1. Fase 1: Fondasi Operasional
2. Fase 2: Auth dan Shell Frontend
3. Fase 4: Workflow Calon Penerima
4. Fase 5: SAW dan Ranking
5. Fase 6: Keputusan Final Paroki
6. Fase 7: Dokumen dan Surat
7. Fase 3: Master Data
8. Fase 8: PWA dan Offline Sync
9. Fase 9: Laporan dan Export
10. Fase 10: Hardening dan Production Readiness

## Catatan Implementasi

- Master data dapat dikerjakan setelah workflow awal karena seed minimal sudah tersedia.
- Jika project perlu cepat demo, prioritaskan:
  - login
  - dashboard role
  - input calon penerima
  - approve stasi
  - proses SAW
  - finalisasi paroki
- Jika project perlu cepat stabil untuk production, prioritaskan:
  - policy dan authorization
  - migration fresh
  - test workflow
  - audit log
  - dokumentasi deployment

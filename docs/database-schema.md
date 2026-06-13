# Rancangan Schema Database SPK Bansos Paroki

Dokumen ini merangkum schema awal untuk sistem pendukung keputusan distribusi bantuan sosial di lingkungan paroki.

Keputusan rancangan:

- Tidak memakai master umat. Data calon penerima diinput ulang per periode bantuan.
- Satu periode bantuan hanya untuk bantuan tunai.
- Satu nomor KK hanya boleh memiliki satu calon penerima dalam periode yang sama.
- Variabel urgensi tidak masuk perhitungan SAW dan dipakai sebagai bahan diskusi manual Paroki.
- Data disabilitas masuk sebagai kriteria SAW bertipe benefit.
- Bobot SAW berlaku global dan disimpan dengan versi agar histori ranking bisa diaudit.

## Struktur Organisasi dan Akses

### `roles`

Master role sistem:

- `ketua_lingkungan_stasi`
- `stasi`
- `paroki`
- `super_admin`

### `parokis`

Master paroki. Satu paroki dapat memiliki banyak stasi.

### `stasis`

Master stasi yang berada di bawah satu paroki.

### `lingkungans`

Master lingkungan yang berada di bawah satu stasi.

### `users`

User sistem. Setiap user memiliki satu role dan dapat dikaitkan ke level organisasi:

- `paroki_id` untuk user paroki
- `stasi_id` untuk user stasi
- `lingkungan_id` untuk ketua lingkungan stasi
- super admin boleh tidak terikat ke organisasi tertentu

## Pengajuan Bantuan

### `periode_bantuans`

Periode atau gelombang bantuan, misalnya Bantuan Natal 2026 atau Bantuan Paskah 2027.

Jenis bantuan dikunci sebagai `tunai`. Tabel ini juga menyimpan nominal default, total anggaran, kuota, dan rencana tanggal penyaluran.

Status periode:

- `draft`
- `open`
- `closed`
- `ranking`
- `finalized`
- `archived`

### `calon_penerimas`

Data calon penerima bantuan yang dikumpulkan oleh Ketua Lingkungan Stasi.

Tidak ada tabel master umat. Karena itu data calon penerima menyimpan detail identitas dan kondisi ekonomi langsung di tabel ini.

Field penting untuk SAW:

- `monthly_income`
- `dependents_count`
- `housing_status`
- `housing_status_score`
- `has_disability`
- `disability_score`

Field pendukung keputusan manual:

- `urgency_note`
- `disability_note`
- `economic_condition_note`
- `stasi_validation_note`
- `paroki_decision_note`

Aturan integritas:

- `periode_bantuan_id + nik` unik
- `periode_bantuan_id + nomor_kk` unik

Aturan kedua mencegah lebih dari satu orang dalam KK yang sama diajukan pada periode bantuan yang sama.

Status pengajuan:

- `draft`
- `submitted_to_stasi`
- `revision_requested`
- `approved_by_stasi`
- `sent_to_paroki`
- `ranked`
- `under_discussion`
- `approved_final`
- `rejected`

### `validasi_logs`

Riwayat perubahan status dan catatan validasi tiap calon penerima.

## Surat Permohonan

### `document_templates`

Template dokumen, termasuk template surat permohonan dari stasi.

### `surat_permohonans`

Surat permohonan yang digenerate oleh user Stasi untuk dikirim ke Paroki.

### `surat_permohonan_items`

Pivot daftar calon penerima yang masuk dalam satu surat permohonan.

## Metode SAW

### `saw_criteria`

Kriteria SAW awal:

- `pendapatan`: cost
- `jumlah_tanggungan`: benefit
- `status_tempat_tinggal`: benefit
- `disabilitas`: benefit

### `saw_weight_versions`

Versi bobot SAW global. Versi aktif awal:

- `pendapatan`: 35
- `jumlah_tanggungan`: 30
- `status_tempat_tinggal`: 20
- `disabilitas`: 15

Saat bobot berubah, sistem membuat versi baru. Hasil ranking lama tetap menunjuk versi bobot yang dipakai saat kalkulasi.

### `saw_weight_items`

Detail bobot per kriteria dalam satu versi bobot.

### `saw_criterion_options`

Opsi skor untuk kriteria kategorikal, terutama status tempat tinggal:

- `milik_sendiri`: 1
- `kontrak`: 2
- `menumpang`: 3
- `tidak_tetap`: 4

Opsi skor status disabilitas:

- `tidak`: 1
- `ya`: 2

### `saw_results`

Hasil ranking per periode bantuan. Nilai normalisasi dan snapshot kalkulasi disimpan agar histori keputusan tidak berubah ketika bobot kriteria diedit di kemudian hari.

Tabel ini juga menyimpan `saw_weight_version_id` untuk audit versi bobot yang dipakai.

## Keputusan Final

### `penerima_bantuans`

Keputusan akhir Paroki setelah ranking SAW dan diskusi internal.

Karena bantuan berupa tunai, tabel ini menyimpan nominal bantuan, metode pembayaran, status penyaluran, dan waktu penyaluran.

Status final:

- `selected`
- `waiting_list`
- `not_selected`

Ranking SAW bersifat pendukung, bukan penentu mutlak. Keputusan final tetap dapat mempertimbangkan `urgency_note` dan diskusi internal Paroki.

## Laporan dan Audit

### `report_exports`

Riwayat laporan yang digenerate sistem:

- `rekap_calon_per_stasi`
- `rekap_penerima_final`
- `hasil_ranking_saw`
- `surat_permohonan_pdf`
- `berita_acara_paroki`
- `riwayat_penerima_bantuan`

### `audit_logs`

Audit umum untuk aktivitas penting:

- perubahan data calon penerima
- perubahan status pengajuan
- generate surat
- proses ranking
- keputusan final
- export laporan

## Privasi NIK dan Nomor KK

NIK dan nomor KK tetap disimpan di `calon_penerimas`, tetapi akses tampilannya dibatasi di layer aplikasi:

- Ketua Lingkungan Stasi dapat melihat data lingkungannya
- Stasi dapat melihat data stasinya
- Paroki melihat data tanpa NIK/KK penuh
- Super Admin mengatur sistem, namun akses detail identitas bisa diaudit melalui `audit_logs`

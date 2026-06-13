# Modul Stasi - Implementasi Lengkap

## Deskripsi
Modul Stasi adalah antarmuka frontend lengkap untuk user dengan role `stasi` yang bertugas memvalidasi, menyetujui, atau menolak pengajuan calon penerima bantuan dari Ketua Lingkungan.

## Fitur Utama

### 1. Dashboard Stasi
**Lokasi**: Home page saat login dengan role Stasi  
**Menampilkan**:
- Jumlah Pengajuan Masuk (submitted_to_stasi)
- Jumlah Menunggu Aksi (submitted_to_stasi + revision_requested)
- Jumlah Sudah Disetujui (approved_by_stasi)
- Jumlah Ditolak (rejected)

**Quick Action Cards**:
- Masuk → Link ke "Pengajuan Masuk"
- Disetujui → Link ke "Sudah Disetujui"
- Ditolak → Link ke "Ditolak"

### 2. Menu Navigasi Stasi
**Sidebar Menu**:
- Dashboard (with badge count)
- Pengajuan Masuk (with badge for new submissions)
- Disetujui (with badge for approved)
- Ditolak (with badge for rejected)
- Profil & Akses
- Logout

### 3. Queue Views
#### Pengajuan Masuk (submitted_to_stasi)
- Lihat semua calon yang baru diajukan dari Ketua Lingkungan
- Tabel dengan: Nama, NIK, Penghasilan, Status
- Search & filter untuk memudahkan pencarian
- Klik candidate untuk membuka detail

#### Disetujui (approved_by_stasi)
- Lihat semua calon yang sudah divalidasi dan disetujui
- Tombol "Kirim ke Paroki" untuk forward ke tingkat paroki
- Status track untuk setiap calon

#### Ditolak (rejected)
- Lihat semua calon yang ditolak beserta alasannya
- Catatan stasi visible untuk referensi

### 4. Approval Workflow

#### Di Candidate Detail View
Ketika Stasi membuka candidate dengan status "submitted_to_stasi", tersedia 3 tombol:

1. **Setujui**
   - Dialog confirmation (tanpa note field)
   - API: POST `/calon-penerimas/{id}/transition/approve-by-stasi`
   - Hasil: status berubah ke `approved_by_stasi`

2. **Minta Revisi**
   - Modal dialog dengan text field untuk catatan
   - API: POST `/calon-penerimas/{id}/transition/request-revision` + notes
   - Hasil: status berubah ke `revision_requested`, note dikirim ke Ketua Lingkungan
   - Ketua Lingkungan melihat di queue "Perlu Revisi"

3. **Tolak**
   - Modal dialog dengan text field untuk alasan penolakan
   - API: POST `/calon-penerimas/{id}/transition/reject` + notes
   - Hasil: status berubah ke `rejected`, alasan disimpan

#### Setelah Approval
- Dashboard metrics update real-time
- Badge di sidebar menu berubah
- Refresh otomatis ke queue view

### 5. Detail Calon
**Menampilkan**:
- Data identitas lengkap (NIK, KK, kepala keluarga, dll)
- Skor kebutuhan (dari 0-100)
- Catatan lapangan (urgensi, kondisi ekonomi, disabilitas)
- Alur status (timeline dari draft hingga sekarang)
- **Catatan Stasi** (jika ada dari validasi sebelumnya)

## Frontend Changes

### File: resources/js/app.js

#### State Management
```javascript
state.tablePagination.submitted_stasi = 1;
state.tablePagination.approved_stasi = 1;
state.tablePagination.rejected_stasi = 1;
state.stasiApprovalNote = '';
state.stasiApprovalAction = null;
```

#### Role Detection
```javascript
function isStasiUser() {
    return state.user?.role?.name === 'stasi';
}
```

#### New Render Functions
- `renderStasiDashboardView()` - Dashboard khusus Stasi
- `renderStasiQueueView(status, title)` - Generic queue view

#### New Helper Functions
- `showStasiApprovalDialog(action, candidateId)` - Show modal
- `confirmStasiAction()` - Handle API call & refresh

#### Event Handlers
- `approve-stasi-candidate` - Trigger approval flow
- `revise-stasi-candidate` - Trigger revision flow
- `reject-stasi-candidate` - Trigger rejection flow
- `send-to-paroki-candidate` - Send approved to Paroki
- `confirm-stasi-action` - Confirm modal action

### File: resources/css/app.css

#### Modal Dialog Styles
```css
.modal-overlay    /* Full-screen backdrop */
.modal-dialog     /* Card container */
.modal-header     /* Title bar */
.modal-body       /* Content area */
.modal-footer     /* Action buttons */
```

## Backend Integration

### API Endpoints (Existing - No changes needed)

**Transition Candidate**
```
POST /api/v1/calon-penerimas/{id}/transition/{action}
Body: { notes: "optional notes" }
```

**Supported Actions (from backend)**:
- `approve-by-stasi` - Stasi approves
- `request-revision` - Stasi requests revision
- `reject` - Stasi rejects
- `send-to-paroki` - Stasi sends to Paroki

### Database (No changes needed)
Existing columns already support:
- `status` - Tracks alur workflow
- `stasi_validation_note` - Stores Stasi comments
- `validated_by` - Who validated
- `validated_at` - When validated

## Testing Checklist

### Setup
1. Run migrations & seeders
2. Login as `stasi1@spk-bansos.local` / `stasi12345`

### Dashboard
- [ ] Dashboard shows 4 metrics cards
- [ ] Pipeline cards visible and clickable
- [ ] Sidebar shows Stasi-specific menu
- [ ] Badge counts update correctly

### Pengajuan Masuk Queue
- [ ] List shows submitted_to_stasi candidates
- [ ] Search/filter works
- [ ] Click candidate opens detail view
- [ ] Detail view shows 3 action buttons

### Approval - Setujui
- [ ] Click "Setujui" button
- [ ] Simple confirmation dialog appears
- [ ] Click confirm changes status to approved_by_stasi
- [ ] Candidate moves to "Disetujui" queue
- [ ] Badge updates

### Approval - Minta Revisi
- [ ] Click "Minta Revisi" button
- [ ] Modal with text field appears
- [ ] Enter note text
- [ ] Click confirm sends to API
- [ ] Status changes to revision_requested
- [ ] Ketua Lingkungan sees it in "Perlu Revisi"

### Approval - Tolak
- [ ] Click "Tolak" button
- [ ] Modal with red style appears
- [ ] Enter rejection reason
- [ ] Click confirm marks as rejected
- [ ] Candidate appears in "Ditolak" queue

### Disetujui Queue
- [ ] List shows approved_by_stasi candidates
- [ ] Detail view shows "Kirim ke Paroki" button
- [ ] Click sends to Paroki (status: sent_to_paroki)

### Ditolak Queue
- [ ] List shows rejected candidates
- [ ] Can see rejection reason in detail
- [ ] No action buttons available

### Backward Compatibility
- [ ] Ketua Lingkungan login still shows original menu
- [ ] Ketua Lingkungan can still input & submit
- [ ] Dashboard unchanged for Ketua Lingkungan
- [ ] No errors in browser console

## Build & Deployment

### Build Frontend
```bash
npm run build
```
Expected output:
- No JavaScript errors
- CSS: 31+ KB
- JS: 98+ KB

### Deploy
1. Run migrations (if any DB schema changes)
2. Run seeders (ensures test data)
3. Build frontend (`npm run build`)
4. Clear cache: `php artisan cache:clear`

## Known Limitations & Future Enhancements

### Current
- Modal dialog uses vanilla JavaScript (no framework)
- Approval notes are single text field (no rich text)
- No batch approval workflow (individual only)
- No email notifications to Ketua Lingkungan on revision/rejection

### Future
1. Batch approval workflow (select multiple, approve together)
2. Email notifications
3. Advanced filter by Lingkungan, date range, score range
4. Export to Excel/PDF
5. Revision history tracking
6. Task assignment & due dates

## Architecture Notes

### Role-Based Rendering
- All views check `isKetuaLingkunganUser()` or `isStasiUser()`
- Navigation items conditionally rendered
- Action buttons shown only for authorized roles
- Backend policies enforce permissions

### State Flow
```
Login → Auth/ME → Load Data → Render Dashboard
                         ↓
                  User sees role-appropriate menu
                         ↓
                  Navigate to queue → Filter candidates
                         ↓
                  Click candidate → Detail view
                         ↓
                  Choose action (approve/revise/reject)
                         ↓
                  Modal dialog → Confirm
                         ↓
                  API call → Success → Refresh data
```

### Modal Dialog Flow
```
Action Button Click
    ↓
Show Modal (showStasiApprovalDialog)
    ↓
Enter note (if needed)
    ↓
Confirm Button Click (confirmStasiAction)
    ↓
API POST request with action + notes
    ↓
Success → Remove modal → Refresh data
Error → Show error message → Keep modal open
```

## Support & Troubleshooting

### Issue: Stasi menu not showing
**Solution**: Check user role is exactly `stasi` (not `stasi_admin`, etc.)

### Issue: Approval buttons not showing
**Solution**: Verify candidate.status === 'submitted_to_stasi'

### Issue: Modal not appearing
**Solution**: Check browser console for JavaScript errors

### Issue: Notes not saved
**Solution**: Verify notes field is populated before clicking confirm

### Issue: API error when approving
**Solution**: Check backend authorization policy allows action

# 📖 SPK Bansos Modules - User Guide

## 🎯 Quick Start

### Prerequisites
- ✅ Backend API running at `http://localhost:8000/api/v1`
- ✅ Frontend dev server running (`npm run dev`)
- ✅ User authenticated with valid role

### Supported Roles
1. **super_admin** - System administrator
2. **ketua_lingkungan_stasi** - Lingkungan Stasi leader (candidate input)
3. **stasi** - Stasi reviewer (approval)
4. **ketua_lingkungan_paroki** - Lingkungan Paroki leader (ranking)
5. **paroki** - Paroki decision maker (finalization)

---

## 🏛️ Module 1: Ketua Lingkungan Stasi (Candidate Management)

### Purpose
Manage candidate input for each Lingkungan Stasi with full CRUD capabilities.

### Menu Path
**Dashboard** → **Calon Penerima**

### Features

#### 📋 List View
- Display all candidates with pagination (10 per page)
- Search candidates by: Nama, NIK, Email, Telepon, Alamat
- Filter by status:
  - **Draft** - Not yet submitted
  - **Diajukan ke Stasi** - Submitted, awaiting review
  - **Disetujui Stasi** - Approved by Stasi
  - **Ditolak Stasi** - Rejected by Stasi

#### ➕ Add Candidate
1. Click **"+ Tambah Calon"** button
2. Fill form:
   - Nama Lengkap (required)
   - NIK (required, unique)
   - Email (optional)
   - Telepon (optional)
   - Alamat (required)
3. Click **"Simpan"**

#### ✏️ Edit Candidate
1. Click **"Edit"** button on any Draft candidate
2. Update fields as needed
3. Click **"Perbarui"**

#### 📤 Submit to Stasi
1. Click **"Submit"** button (only available for Draft status)
2. Confirm submission dialog
3. Status changes to **"Diajukan ke Stasi"**

#### 🗑️ Delete Candidate
1. Click **"Hapus"** button
2. Confirm deletion dialog
3. Candidate removed from system

### Status Flow
```
Draft → Diajukan ke Stasi → [Stasi Review] → Disetujui/Ditolak
```

### Example Workflow
1. **Create** new candidate (status: Draft)
2. **Edit** if needed before submission
3. **Submit** when ready (status: Diajukan ke Stasi)
4. Wait for Stasi review
5. View result (approved/rejected)

---

## 👥 Module 2: Stasi (Candidate Review & Approval)

### Purpose
Review candidates submitted by Lingkungan Stasi and make approval decisions.

### Menu Path
**Dashboard** → **Rekap Stasi**

### Features

#### 📊 Recap View
- Display all candidates submitted to Stasi
- Search by: Nama, NIK, Status
- Filter by status: Diajukan, Disetujui, Ditolak
- Pagination (10 per page)

#### 👁️ View Details
1. Click **"Lihat"** button on any candidate
2. Detail modal shows:
   - Full candidate information
   - Lingkungan Stasi origin
   - Current status
   - Timeline (created, updated dates)

#### ✅ Approve Candidate
1. Click **"Lihat"** to open detail
2. Click **"Setujui"** button (only for "Diajukan" status)
3. Confirm approval dialog
4. Status changes to **"Disetujui Stasi"**
5. Success message displays

#### ❌ Reject Candidate
1. Click **"Lihat"** to open detail
2. Click **"Tolak"** button (only for "Diajukan" status)
3. Confirm rejection dialog
4. Status changes to **"Ditolak Stasi"**
5. Success message displays

### Decision Flow
```
Diajukan ke Stasi → [Review] → Disetujui Stasi (continues to ranking)
                             → Ditolak Stasi (removed from process)
```

### Example Workflow
1. **Access** Rekap Stasi view
2. **Filter** by "Diajukan" status to see pending reviews
3. **View** candidate details
4. **Approve** or **Reject** based on criteria
5. Monitor progress as approved candidates move to next stage

---

## 📊 Module 3: Ketua Lingkungan Paroki (SAW Ranking)

### Purpose
Configure SAW weights and execute ranking algorithm for candidate selection.

### Menu Path
**Dashboard** → **Proses SAW**

### Features

#### 🔄 Period Selection
1. Select **Bansos Period** from dropdown
2. System loads existing weights if available
3. Statistics update based on period

#### ⚖️ Configure Weights
Four evaluation criteria with default weights:

| Criteria | Code | Default | Type | Description |
|----------|------|---------|------|-------------|
| Penghasilan Bulanan | C1 | 40% | Cost | Lower is better |
| Jumlah Tanggungan | C2 | 30% | Benefit | Higher is better |
| Status Kesehatan | C3 | 15% | Benefit | Better health preferred |
| Kondisi Tempat Tinggal | C4 | 15% | Benefit | Better condition preferred |

**Important**: Total weight must equal **100%**

#### 🔧 Weight Management
1. Adjust weight percentages in form
2. Real-time total calculation
3. Valid range: 0.00 - 1.00 per criterion
4. System validates total equals 1.0

#### 💾 Save Weights
1. Click **"Simpan Bobot"** to persist weights
2. Success message confirms save
3. Weights used for current and future rankings

#### 🔄 Reset to Default
1. Click **"Reset Default"**
2. Weights revert to: C1=40%, C2=30%, C3=15%, C4=15%

#### ▶️ Execute Ranking
1. Configure and save weights
2. Click **"▶ Jalankan Ranking"**
3. Confirm execution dialog
4. System processes SAW algorithm:
   - Normalizes criteria values
   - Applies weights
   - Calculates scores
   - Generates ranking
5. Results display in table (Rank, Nama, Skor)

#### 📥 Download Results
1. After ranking execution
2. Click **"📥 Unduh Hasil"**
3. CSV file downloads with ranking data
4. Can import to Excel for further analysis

#### 📨 Send to Paroki
1. After ranking execution
2. Click **"✉️ Kirim ke Paroki"**
3. Confirm sending dialog
4. Results transmitted to Paroki role
5. Success notification

### SAW Algorithm
```
Normalized_Value = (Value - Min) / (Max - Min)
Score = Σ (Normalized_Value × Weight)
Ranking = Sort by Score (Descending)
```

### Example Workflow
1. **Select** Bansos period
2. **Adjust** weights based on policy (e.g., emphasize health)
3. **Save** weights
4. **Execute** ranking algorithm
5. **Review** results table
6. **Send** to Paroki for finalization
7. **Download** for records

---

## 🏆 Module 4: Paroki (Decision Finalization)

### Purpose
Review final ranking and make final approval decisions for bansos recipients.

### Menu Path
**Dashboard** → **Ranking**

### Features

#### 📊 Ranking Results
- Display final SAW ranking results
- Show rank (1-N), nama, NIK, skor
- Search by: Nama, NIK
- Filter by decision status: Pending, Disetujui, Ditolak, Bersyarat
- Pagination (10 per page)

#### 🎯 Decision Status
Four possible decisions:

| Status | Badge | Action | Description |
|--------|-------|--------|-------------|
| Pending | Gray | Putuskan | Not yet decided |
| Disetujui (Approved) | Green | - | Approved for bansos |
| Ditolak (Rejected) | Red | - | Not approved |
| Bersyarat (Conditional) | Yellow | - | Approved with conditions |

#### 💬 Make Decision
1. Click **"Putuskan"** on any candidate
2. Decision modal opens with:
   - Candidate information
   - Three decision options
   - Notes field for reason
3. Select decision:
   - ✓ Disetujui (Approved)
   - ⚠ Bersyarat (Conditional)
   - ✗ Ditolak (Rejected)
4. Add notes explaining decision
5. Click **"Simpan Keputusan"**
6. System updates decision and notifies backend

#### 📄 Generate Surat Edaran
1. Review all decisions (ensure at least one approved)
2. Click **"📄 Buat Surat Edaran"**
3. Confirm generation dialog
4. System generates official circular letter:
   - Lists all approved recipients
   - References ranking
   - Official Paroki letterhead
   - Signed/sealed format
5. PDF auto-downloads for distribution

### Decision Flow
```
Pending → [Decision] → Disetujui (Bansos approved)
                    → Bersyarat (With conditions)
                    → Ditolak (Not approved)
                    ↓
              Generate Surat Edaran (official announcement)
```

### Example Workflow
1. **Review** final ranking results
2. **Filter** by "Pending" to see undecided candidates
3. **Click** "Putuskan" for each candidate
4. **Select** decision (approve/reject/conditional)
5. **Add** reasoning in notes field
6. **Save** decision
7. **Generate** surat edaran for approved recipients
8. **Download** official letter for distribution

---

## 🔄 Complete End-to-End Workflow

### Day 1: Candidate Input (Ketua Lingkungan Stasi)
```
1. Create candidates (draft status)
2. Edit as needed
3. Submit to Stasi
```

### Day 2: Candidate Review (Stasi)
```
1. Access Rekap Stasi
2. Review candidate details
3. Approve or reject each candidate
```

### Day 3: Ranking Execution (Ketua Lingkungan Paroki)
```
1. Select Bansos period
2. Configure SAW weights
3. Execute ranking
4. Download and send to Paroki
```

### Day 4: Decision Finalization (Paroki)
```
1. Review final ranking
2. Make approval decisions
3. Generate surat edaran
4. Distribute official letter
```

---

## 📱 UI Navigation Tips

### Search & Filter Pattern (All Modules)
```
┌─────────────────────────────────────┐
│ Search Input | Filter Dropdown | Reset |
└─────────────────────────────────────┘
```
- **Search**: Real-time across multiple fields
- **Filter**: Combine with search (AND logic)
- **Reset**: Clear search and filters

### Pagination Pattern
```
[← Previous] [1] [2] [3] [Next →]
```
- 10 items per page
- Click page number to jump
- Previous/Next for sequential navigation

### Modal Form Pattern
```
┌────────────────────────────────┐
│ × Modal Title                  │
├────────────────────────────────┤
│ Field 1: [Input]               │
│ Field 2: [Input]               │
│ ...                            │
├────────────────────────────────┤
│ [Cancel] [Confirm Action]      │
└────────────────────────────────┘
```
- Dark semi-transparent backdrop
- White card centered on screen
- Confirm/cancel buttons
- Close via X button or backdrop click

---

## ⚠️ Common Issues & Solutions

### Issue: Module not loading
**Solution**: 
- Verify user has correct role
- Check browser console for errors
- Ensure API server running
- Clear browser cache

### Issue: Search not working
**Solution**:
- Check field is searchable (all text fields are)
- Ensure data exists in that field
- Try partial matches
- Use Reset to clear filters

### Issue: Cannot submit/approve
**Solution**:
- Check current status allows action
- Verify user has correct role
- Ensure required fields filled
- Check for validation errors

### Issue: Weight validation error
**Solution**:
- Ensure total weights = 1.0 (100%)
- Sum display shows current total
- Adjust weights to exactly 1.0
- Use Reset Default if unsure

### Issue: Ranking not executing
**Solution**:
- Save weights first before executing
- Ensure valid period selected
- Verify at least 1 approved candidate
- Check API connectivity

---

## 🔐 Security Features

- ✅ Role-based access control (RBAC)
- ✅ XSS protection (HTML escaping)
- ✅ CSRF protection (API token validation)
- ✅ Rate limiting on API calls
- ✅ Input validation on forms
- ✅ Confirmation dialogs for critical actions
- ✅ Audit logging of all changes

---

## 📊 Data Consistency

- **Atomic Operations**: Each action saved completely or not at all
- **Validation**: All inputs validated before sending
- **Error Handling**: Clear error messages if operations fail
- **Offline Support**: Limited - critical actions require network
- **Data Sync**: Immediate reflection after successful operation

---

## 📞 Support & Troubleshooting

### Log Locations
- **Browser Console**: `F12` or `Cmd+Option+I`
- **Network Tab**: Check API requests
- **Application Tab**: View stored data (localStorage, IndexedDB)

### API Endpoint Reference
```
POST   /auth/login                                  (Authentication)
GET    /lingkungan-stasi/calon-penerima            (List candidates)
POST   /lingkungan-stasi/calon-penerima            (Create)
PUT    /lingkungan-stasi/calon-penerima/{id}       (Update)
DELETE /lingkungan-stasi/calon-penerima/{id}       (Delete)
POST   /lingkungan-stasi/calon-penerima/{id}/ajukan (Submit)
GET    /stasi/calon-penerima-rekap                 (Recap)
POST   /stasi/calon-penerima/{id}/approve          (Approve)
POST   /stasi/calon-penerima/{id}/reject           (Reject)
GET    /bansos-periods                             (Periods)
GET    /lingkungan-paroki/saw/weights/{id}         (Get weights)
POST   /lingkungan-paroki/saw/weights/{id}         (Save weights)
POST   /lingkungan-paroki/saw/execute/{id}         (Execute ranking)
POST   /lingkungan-paroki/saw/send-to-paroki/{id}  (Send results)
GET    /paroki/ranking-results                     (Final results)
POST   /paroki/penerima/{id}/keputusan             (Decision)
POST   /paroki/surat-edaran/generate               (Generate letter)
```

---

**Last Updated**: 2024
**Version**: 1.0 - Production Ready

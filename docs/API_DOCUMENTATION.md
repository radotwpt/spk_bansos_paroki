# SPK Bansos API Documentation

## Overview
**SPK Bansos** adalah Sistem Pendukung Keputusan (Decision Support System) untuk distribusi bantuan sosial di lingkungan Paroki Katolik. Sistem menggunakan algoritma SAW (Simple Additive Weighting) untuk menentukan prioritas penerima bantuan berdasarkan kriteria ekonomi dan sosial.

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
API menggunakan **Laravel Sanctum** untuk token-based authentication.

### Login
```
POST /auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password",
  "device_name": "Mobile App"
}

Response:
{
  "success": true,
  "data": {
    "token_type": "Bearer",
    "access_token": "token_string",
    "user": {
      "id": 1,
      "name": "User Name",
      "email": "user@example.com",
      "role": { "name": "paroki", "label": "Paroki" },
      ...
    }
  }
}
```

### Get Current User
```
GET /auth/me
Authorization: Bearer {access_token}
```

### Logout
```
POST /auth/logout
Authorization: Bearer {access_token}
```

---

## API Endpoints

### 1. USER MANAGEMENT

#### List Users
```
GET /users?role_id=1&search=keyword&per_page=15
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `role_id`: Filter by role ID
- `search`: Search by name or email
- `per_page`: Items per page (default: 15)

#### Create User
```
POST /users
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "role_id": 1,
  "paroki_id": 1,
  "stasi_id": 1,
  "lingkungan_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "081234567890",
  "position_title": "Ketua Lingkungan",
  "is_active": true,
  "password": "securepassword123"
}
```

#### Get User Details
```
GET /users/{id}
Authorization: Bearer {access_token}
```

#### Update User
```
PUT /users/{id}
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "Updated Name",
  "email": "newemail@example.com",
  "phone": "089876543210",
  "is_active": true,
  "password": "newpassword123" // optional, leave empty to keep current
}
```

#### Delete User
```
DELETE /users/{id}
Authorization: Bearer {access_token}
```

---

### 2. MASTER DATA (Generic CRUD)

#### Get All Roles
```
GET /master-data/roles
Authorization: Bearer {access_token}
```

#### List Master Data
```
GET /master-data/{resource}
Authorization: Bearer {access_token}

Resources: parokis, stasis, lingkungans, periode-bantuans, document-templates
```

**Query Parameters:**
- `search`: Search by name or code
- `per_page`: Items per page
- `paroki_id`, `stasi_id`, `status`, `type`, `is_active`: Filters

#### Create Master Data
```
POST /master-data/{resource}
Authorization: Bearer {access_token}
Content-Type: application/json

Example - Create Paroki:
{
  "code": "PAROKI001",
  "name": "Paroki Santo Petrus",
  "leader_name": "Romo Alexander",
  "phone": "081234567890",
  "address": "Jl. Gereja No. 1, Jakarta",
  "is_active": true
}
```

#### Get Master Data Detail
```
GET /master-data/{resource}/{id}
Authorization: Bearer {access_token}
```

#### Update Master Data
```
PUT /master-data/{resource}/{id}
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "Updated Name",
  "is_active": true
}
```

#### Delete Master Data
```
DELETE /master-data/{resource}/{id}
Authorization: Bearer {access_token}
```

---

### 3. CALON PENERIMA (Beneficiary Candidates)

#### List Candidates
```
GET /calon-penerimas
Authorization: Bearer {access_token}

Query Parameters:
- periode_bantuan_id: Filter by assistance period
- paroki_id: Filter by parish
- stasi_id: Filter by sub-parish
- lingkungan_id: Filter by community
- status: Filter by status
- search: Search by name or registration number
- per_page: Items per page (default: 15)
```

#### Create Candidate
```
POST /calon-penerimas
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "periode_bantuan_id": 1,
  "paroki_id": 1,
  "stasi_id": 1,
  "lingkungan_id": 1,
  "registration_number": "REG001",
  "name": "Budi Santoso",
  "nik": "1234567890123456",
  "nomor_kk": "1234567890123456",
  "family_head_name": "Budi Santoso",
  "place_of_birth": "Jakarta",
  "date_of_birth": "1985-06-15",
  "gender": "laki_laki",
  "address": "Jl. Merpati No. 5",
  "phone": "081234567890",
  "occupation": "Pedagang",
  "monthly_income": 1500000,
  "dependents_count": 4,
  "housing_status": "menumpang",
  "has_disability": false,
  "urgency_note": "Kondisi ekonomi sangat sulit",
  "economic_condition_note": "Penghasilan tidak stabil",
  "status": "draft"
}
```

#### Get Candidate Details
```
GET /calon-penerimas/{id}
Authorization: Bearer {access_token}
```

#### Update Candidate
```
PUT /calon-penerimas/{id}
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "name": "Updated Name",
  "address": "New Address",
  "monthly_income": 2000000,
  "dependents_count": 5,
  ...
}
```

#### Delete Candidate
```
DELETE /calon-penerimas/{id}
Authorization: Bearer {access_token}
```

#### Workflow Transition
```
POST /calon-penerimas/{id}/transition/{action}
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "notes": "Catatan untuk transisi status"
}

Actions:
- submit-to-stasi: Serahkan ke Stasi
- request-revision: Minta revisi
- approve-by-stasi: Setujui oleh Stasi
- send-to-paroki: Kirim ke Paroki
- mark-under-discussion: Tandai sedang dibahas
- reject: Tolak permohonan
```

---

### 4. SAW RANKING & DECISION MAKING

#### Calculate Ranking
```
POST /ranking/calculate
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "periode_bantuan_id": 1,
  "saw_weight_version_id": 1 // optional, uses active version if not provided
}

Response:
{
  "success": true,
  "data": {
    "periodo_bantuan_id": 1,
    "total_candidates_ranked": 50,
    "weight_version_used": "V1_2026",
    "calculated_at": "2026-06-06T10:30:00Z",
    "results": [
      {
        "rank": 1,
        "candidate": { "id": 1, "name": "Budi", "nik": "...", "status": "ranked" },
        "score": 0.876,
        "normalized_scores": {
          "income": 0.9,
          "dependents": 0.8,
          "housing": 0.95,
          "disability": 0.75
        }
      },
      ...
    ]
  }
}
```

#### Get Ranking Results
```
GET /ranking/results/{periode_bantuan_id}
Authorization: Bearer {access_token}

Query Parameters:
- per_page: Items per page (default: 15)
```

#### Finalize Ranking
```
POST /ranking/finalize/{periode_bantuan_id}
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "approved_count": 30,
  "notes": "Hasil akhir periode Juni"
}
```

#### Get SAW Weights
```
GET /ranking/weights/{periode_bantuan_id}
Authorization: Bearer {access_token}

Response:
{
  "success": true,
  "data": {
    "version_id": 1,
    "version_code": "V1_2026",
    "is_active": true,
    "is_locked": false,
    "weights": [
      {
        "criterion_id": 1,
        "criterion_code": "monthly_income",
        "criterion_name": "Penghasilan Bulanan",
        "weight": 35,
        "type": "cost"
      },
      ...
    ]
  }
}
```

#### Update SAW Weights
```
PUT /ranking/weights/{periode_bantuan_id}
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "weights": [
    { "criterion_id": 1, "weight": 40 },
    { "criterion_id": 2, "weight": 30 },
    { "criterion_id": 3, "weight": 20 },
    { "criterion_id": 4, "weight": 10 }
  ]
}
```

---

### 5. PENERIMA BANTUAN (Final Beneficiaries)

#### List Beneficiaries
```
GET /penerima-bantuans
Authorization: Bearer {access_token}

Query Parameters:
- periode_bantuan_id: Filter by period (required)
- status: selected|waiting_list|not_selected|disbursed
- search: Search by name or NIK
- per_page: Items per page
```

#### Get Beneficiary Details
```
GET /penerima-bantuans/{id}
Authorization: Bearer {access_token}
```

#### Update Beneficiary
```
PUT /penerima-bantuans/{id}
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "aid_amount": 3000000,
  "payment_method": "bank_transfer",
  "bank_account_number": "1234567890",
  "disbursement_date": "2026-06-30",
  "notes": "Pembayaran via transfer bank"
}
```

#### Mark as Disbursed
```
POST /penerima-bantuans/{id}/mark-disbursed
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "disbursement_date": "2026-06-30",
  "disbursement_notes": "Bantuan telah diserahkan"
}
```

---

### 6. REPORTS & EXPORTS

#### Get Candidate List Report
```
GET /reports/candidate-list/{periode_bantuan_id}
Authorization: Bearer {access_token}

Query Parameters:
- format: json|csv (default: json)
```

#### Get Ranking Results Report
```
GET /reports/ranking-results/{periode_bantuan_id}
Authorization: Bearer {access_token}

Query Parameters:
- format: json|csv
- limit: Limit number of results
```

#### Get Beneficiary List Report
```
GET /reports/beneficiaries/{periode_bantuan_id}
Authorization: Bearer {access_token}

Query Parameters:
- format: json|csv
- status: Filter by status
```

#### Generate Surat Permohonan
```
POST /reports/surat-permohonan/{periode_bantuan_id}
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "stasi_id": 1,
  "title": "Permohonan Bantuan Periode Juni 2026"
}
```

#### Export Report
```
GET /reports/export/{reportType}/{periode_bantuan_id}
Authorization: Bearer {access_token}

Query Parameters:
- format: csv|xlsx|pdf (default: csv)

Report Types:
- candidate-list
- ranking-results
- beneficiaries
```

---

### 7. DASHBOARD & ANALYTICS

#### Get Period Summary
```
GET /dashboard/summary/{periode_bantuan_id}
Authorization: Bearer {access_token}

Response:
{
  "success": true,
  "data": {
    "period": { "id": 1, "code": "...", "name": "...", "status": "...", ... },
    "candidates": {
      "total": 100,
      "by_status": { "draft": 10, "submitted_to_stasi": 20, ... }
    },
    "beneficiaries": {
      "total": 50,
      "by_status": { "selected": 30, "waiting_list": 20 },
      "disbursed": 15
    },
    "financial": {
      "total_budget": 150000000,
      "amount_disbursed": 45000000,
      "remaining_budget": 105000000,
      "average_aid_amount": 900000,
      "budget_utilization_percent": 30.0
    }
  }
}
```

#### Get Period Statistics
```
GET /dashboard/statistics/{periode_bantuan_id}
Authorization: Bearer {access_token}

Response includes:
- economic_statistics: income, dependents, disability data
- housing_distribution: distribution by housing status
- gender_distribution: distribution by gender
- age_distribution: age groups
- workflow_statistics: candidates at each workflow stage
- ranking_statistics: score distribution
- top_applicant_areas: top lingkungans by application count
```

---

## Error Responses

### Standard Error Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

### Common Status Codes
- `200`: OK - Request successful
- `201`: Created - Resource created successfully
- `400`: Bad Request - Invalid input
- `401`: Unauthorized - Missing/invalid token
- `403`: Forbidden - User not authorized
- `404`: Not Found - Resource not found
- `422`: Unprocessable Entity - Validation error
- `500`: Internal Server Error - Server error

---

## Role-Based Access Control

| Endpoint | Super Admin | Paroki | Stasi | Lingkungan Leader |
|----------|:---:|:---:|:---:|:---:|
| Users CRUD | ✅ | ❌ | ❌ | ❌ |
| Master Data CRUD | ✅ | ✅ | ❌ | ❌ |
| Create Candidate | ✅ | ❌ | ❌ | ✅ |
| Approve Candidate (Stasi) | ✅ | ❌ | ✅ | ❌ |
| Approve Candidate (Paroki) | ✅ | ✅ | ❌ | ❌ |
| Calculate Ranking | ✅ | ✅ | ❌ | ❌ |
| Update SAW Weights | ✅ | ✅ | ❌ | ❌ |
| View All Data | ✅ | ✅ | ✅ | ✅ |
| Generate Reports | ✅ | ✅ | ✅ | ✅ |

---

## Data Constraints

### Unique Constraints
- One candidate per NIK (National ID) per assistance period
- One candidate per KK (Family Card) per assistance period
- One user per email address

### Workflow Status
```
Draft
  ↓
Submitted to Stasi ← → Revision Requested
  ↓
Approved by Stasi
  ↓
Sent to Paroki
  ↓
Ranked
  ↓
Under Discussion
  ↓
Approved Final / Rejected
```

### Assistance Period Status
```
Draft → Open → Closed → Ranking → Finalized → Archived
```

---

## Examples

### Example 1: Complete Workflow
1. Lingkungan leader creates candidate (draft)
2. Lingkungan leader submits to Stasi
3. Stasi coordinator reviews and approves
4. Stasi sends to Paroki
5. Paroki coordinates with others (under discussion)
6. Paroki calculates SAW ranking
7. Paroki approves beneficiaries
8. Finance team marks aid as disbursed

### Example 2: Get Candidate with Full Workflow
```bash
# Get candidate
GET /calon-penerimas/1

# Check current status
# Get available transitions based on user role

# Perform transition if authorized
POST /calon-penerimas/1/transition/submit-to-stasi
{
  "notes": "Pengajuan dari lingkungan"
}

# Check new status
GET /calon-penerimas/1
```

---

## Rate Limiting
- Default: 60 requests per minute
- Authenticated endpoints: Same rate limit

---

## Version History
- **v1** (Current): Initial API version with full CRUD, ranking, reporting

---

## Support & Troubleshooting

### Common Issues

**401 Unauthorized**
- Ensure token is included in Authorization header: `Authorization: Bearer {token}`
- Token may have expired; request new token via login

**403 Forbidden**
- User role doesn't have access to this resource
- Check role-based access control table above

**422 Validation Error**
- Review validation rules in error response
- Ensure required fields are provided
- Check field data types and constraints

**500 Server Error**
- Contact system administrator
- Check server logs for detailed error information

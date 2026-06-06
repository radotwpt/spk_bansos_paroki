# 🚀 SPK Bansos Setup & Run Guide

## ✅ Status: Ready to Launch

Project sudah dalam kondisi siap dengan:
- ✅ Dependencies installed (composer, npm)
- ✅ `.env` configured
- ✅ Modules already built (4 production-ready modules)
- ✅ Documentation complete

---

## 🔧 Setup Steps

### Step 1: Generate App Key (jika belum)
```bash
php artisan key:generate
```

### Step 2: Setup Database & Run Migrations
```bash
php artisan migrate --force
```

### Step 3: Seed Database dengan Test Data
```bash
php artisan db:seed
```

Ini akan create test users untuk setiap role:
- **super_admin** - Super Administrator
- **ketua_lingkungan_stasi** - Ketua Lingkungan Stasi
- **stasi** - Stasi Reviewer
- **ketua_lingkungan_paroki** - Ketua Lingkungan Paroki
- **paroki** - Paroki Decision Maker

---

## 🚀 Menjalankan Project

### Option A: Automated (Recommended - Windows)
```bash
# Terminal 1 - Backend
php artisan serve

# Terminal 2 (buka tab terminal baru)
npm run dev
```

### Option B: Combined (if using concurrently)
```bash
composer run dev
```

---

## 📊 Expected Output

### Terminal 1 (Backend - port 8000)
```
Starting Laravel development server: http://127.0.0.1:8000
[timestamp] Listening on http://127.0.0.1:8000
```

### Terminal 2 (Frontend - port 5173)
```
VITE v7.0.7  ready in 800 ms

➜  Local:   http://localhost:5173/
➜  press h to show help
```

---

## 🌐 Access Application

### Frontend
- **URL**: http://localhost:5173
- **Features**: All 4 modules ready
- **Login**: Use any test user credential below

### Backend API
- **URL**: http://localhost:8000/api/v1
- **Auth**: Bearer token (from login)
- **Endpoints**: 15+ API endpoints ready

---

## 👤 Test User Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@example.com | password |
| Ketua Lingkungan Stasi | kls@example.com | password |
| Stasi | stasi@example.com | password |
| Ketua Lingkungan Paroki | klp@example.com | password |
| Paroki | paroki@example.com | password |

---

## 📖 Module Access

After login, you'll see these modules based on your role:

### 🏛️ Ketua Lingkungan Stasi
**Dashboard** → **Calon Penerima**
- Full CRUD for candidates
- Search, filter, pagination
- Submit to Stasi

### 👥 Stasi
**Dashboard** → **Rekap Stasi**
- Review candidates
- Approve/reject workflow
- Detail view

### 📊 Ketua Lingkungan Paroki
**Dashboard** → **Proses SAW**
- Configure weights (C1-C4)
- Execute ranking
- Download results

### 🏆 Paroki
**Dashboard** → **Ranking**
- View final ranking
- Make decisions
- Generate surat edaran

---

## 🧪 Quick Test

### After Login
1. Navigate to your role's module
2. Try search feature
3. Try filter feature
4. Test CRUD operations (if available)
5. Check pagination
6. Verify notifications work

---

## 📊 Database Info

### SQLite (Default)
- **Location**: `database/database.sqlite`
- **Auto-created** on first migration
- **Perfect for**: Development & testing

### Tables Created
- users
- calon_penerimaes
- saw_weights
- saw_results
- activity_logs
- bansos_periods
- lingkungan_stasi
- lingkungan_paroki
- stasi
- paroki
- sessions
- cache

---

## 🛠️ Troubleshooting

### Issue: Port 8000 already in use
```bash
# Use different port
php artisan serve --port=8001
```

### Issue: Port 5173 already in use
```bash
# Vite will auto-select next available port
npm run dev
```

### Issue: Database locked
```bash
# Delete sqlite database and recreate
rm database/database.sqlite
php artisan migrate --force
php artisan db:seed
```

### Issue: "Class not found" error
```bash
# Regenerate autoloader
composer dump-autoload
```

---

## 📁 Key Directories

```
resources/js/
├── app-modern.js              ← Main app shell
├── crud-helpers.js            ← Reusable utilities
└── modules/
    ├── ketua-lingkungan-stasi.js
    ├── stasi.js
    ├── ketua-lingkungan-paroki.js
    └── paroki.js

resources/views/
└── app.blade.php              ← Main template

app/Http/Controllers/
└── API/*.php                  ← API endpoints

database/
├── migrations/
├── seeders/
└── factories/
```

---

## ✅ Checklist

- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan db:seed`
- [ ] Terminal 1: `php artisan serve`
- [ ] Terminal 2: `npm run dev`
- [ ] Open http://localhost:5173
- [ ] Login with test user
- [ ] Navigate to your module
- [ ] Test search/filter/pagination
- [ ] Verify all features working

---

## 📞 Next Steps

1. **Test Login**: Login with each role
2. **Explore Modules**: Test each module workflow
3. **Check Documentation**: See MODULES_USER_GUIDE.md
4. **Review Code**: See MODULES_DEVELOPER_GUIDE.md
5. **Deploy**: When ready, follow deployment guide

---

## 🎯 Success Indicators

✅ Both servers running without errors  
✅ Frontend loads at http://localhost:5173  
✅ Can login successfully  
✅ Menu displays based on role  
✅ Modules load and respond  
✅ Search/filter/pagination work  
✅ No console errors  
✅ API calls successful (check Network tab)  

---

**Ready to proceed?** Follow the setup steps above.

For detailed usage info, see [MODULES_USER_GUIDE.md](MODULES_USER_GUIDE.md)

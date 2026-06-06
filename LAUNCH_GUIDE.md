# 🚀 Panduan Menjalankan SPK Bansos Project

## 📋 Prerequisites Checklist

Pastikan sudah terinstall:
- ✅ PHP 8.2+ (`php -v`)
- ✅ Composer (`composer -v`)
- ✅ Node.js & npm (`node -v` & `npm -v`)
- ✅ MySQL 8.0+ atau gunakan SQLite
- ✅ Git (`git -v`)

---

## 🔧 Setup Database (Pilih Salah Satu)

### Option A: MySQL (Recommended)

#### 1. Create Database
```bash
mysql -u root -p
```

```sql
CREATE DATABASE spk_bansos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### 2. Verify .env Configuration
Buka `.env` dan pastikan:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spk_bansos
DB_USERNAME=root
DB_PASSWORD=
```

#### 3. Run Migrations & Seed
```bash
php artisan migrate --force
php artisan db:seed
```

### Option B: SQLite (Simple Development)

#### 1. Update .env
```env
DB_CONNECTION=sqlite
# Comment out these lines:
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=spk_bansos
# DB_USERNAME=root
# DB_PASSWORD=
```

#### 2. Run Migrations & Seed
```bash
php artisan migrate --force
php artisan db:seed
```

---

## 🚀 Menjalankan Project

### Method 1: Separate Terminals (Recommended)

**Terminal 1 - Backend Server**
```bash
php artisan serve
```

Expected output:
```
Laravel development server started: http://127.0.0.1:8000
```

**Terminal 2 - Frontend Dev Server** (buka tab/window baru)
```bash
npm run dev
```

Expected output:
```
VITE v7.0.7  ready in 800 ms
➜  Local:   http://localhost:5173/
```

### Method 2: Single Command (if concurrently installed)

```bash
composer run dev
```

---

## 🌐 Akses Application

1. **Buka Browser**: http://localhost:5173
2. **Lihat Login Screen**: Berisi form email & password
3. **Login dengan Test User**: (lihat credentials di bawah)
4. **Dashboard Loaded**: Sesuai dengan role user

---

## 👤 Test User Credentials

Gunakan credentials ini untuk login:

| Role | Email | Password | Module |
|------|-------|----------|--------|
| Super Admin | admin@example.com | password | Dashboard |
| Ketua Lingkungan Stasi | kls@example.com | password | Calon Penerima (CRUD) |
| Stasi | stasi@example.com | password | Rekap Stasi (Approve/Reject) |
| Ketua Lingkungan Paroki | klp@example.com | password | Proses SAW (Ranking) |
| Paroki | paroki@example.com | password | Ranking (Decisions) |

---

## ✨ Testing Modules

Setelah login, test setiap fitur:

### 🏛️ Ketua Lingkungan Stasi
1. Ke **Calon Penerima** menu
2. Klik **+ Tambah Calon** 
3. Isi form dengan data
4. Klik **Simpan**
5. Test **Search** & **Filter**
6. Klik **Submit** untuk kirim ke Stasi

### 👥 Stasi  
1. Ke **Rekap Stasi** menu
2. Lihat list calon yang disubmit
3. Klik **Lihat** untuk detail
4. Klik **Setujui** atau **Tolak**
5. Confirm dialog

### 📊 Ketua Lingkungan Paroki
1. Ke **Proses SAW** menu
2. Pilih **Periode Bansos**
3. Adjust bobot C1-C4 (total harus 100%)
4. Klik **Simpan Bobot**
5. Klik **Jalankan Ranking**
6. Lihat hasil ranking
7. Klik **Kirim ke Paroki**

### 🏆 Paroki
1. Ke **Ranking** menu
2. Lihat hasil ranking final
3. Klik **Putuskan** untuk setiap candidate
4. Pilih keputusan (Disetujui/Ditolak/Bersyarat)
5. Tambah catatan
6. Klik **Simpan Keputusan**
7. Klik **Buat Surat Edaran** untuk generate letter

---

## 🧪 Quick Test Checklist

- [ ] Backend server running di http://127.0.0.1:8000
- [ ] Frontend server running di http://localhost:5173
- [ ] Bisa akses login page
- [ ] Bisa login dengan test user
- [ ] Dashboard loading sesuai role
- [ ] Menu items sesuai role
- [ ] Module loading (Search, Filter, Pagination working)
- [ ] Form submission working
- [ ] Notifications appearing (success/error)
- [ ] No console errors

---

## 📱 Browser DevTools

Untuk debug, buka DevTools:

**Windows/Linux**: `F12` atau `Ctrl+Shift+I`  
**Mac**: `Cmd+Option+I`

### Tabs to Check:
- **Console**: Lihat untuk errors
- **Network**: Lihat API calls
- **Application**: 
  - localStorage (auth token)
  - IndexedDB (offline data)

---

## 🔄 API Testing

Test API endpoints:

```bash
# Get auth token
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Use token in subsequent requests
curl http://127.0.0.1:8000/api/v1/lingkungan-stasi/calon-penerima \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📊 Database Info

### MySQL
- **Host**: 127.0.0.1
- **Port**: 3306
- **Database**: spk_bansos
- **User**: root
- **Access**: 
  ```bash
  mysql -u root -p spk_bansos
  ```

### SQLite
- **File**: `database/database.sqlite`
- **Size**: Auto-managed
- **Portability**: Bisa di-backup sebagai single file

---

## ❌ Troubleshooting

### Backend won't start
```bash
# Check port 8000 is free
netstat -ano | findstr :8000

# Use different port
php artisan serve --port=8001

# Check PHP errors
php -l artisan
```

### Frontend won't compile
```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -r node_modules package-lock.json
npm install

# Try dev again
npm run dev
```

### Database errors
```bash
# Check MySQL running
mysql -u root -p -e "SELECT 1;"

# Recreate database
mysql -u root -p < database/schema.sql

# Reset migrations
php artisan migrate:reset
php artisan migrate:fresh --seed
```

### Authentication issues
```bash
# Check app key exists
cat .env | grep APP_KEY

# Generate if missing
php artisan key:generate

# Clear cached config
php artisan config:clear
php artisan cache:clear
```

### Vite port conflict
```bash
# Vite auto-selects next port
npm run dev

# Or specify port
npm run dev -- --port 5174
```

---

## 📚 Documentation

Untuk info lebih lanjut, baca:

1. **User Guide** (untuk end users)
   - Cara menggunakan setiap module
   - Workflow lengkap
   - Troubleshooting

2. **Developer Guide** (untuk developers)
   - Architecture
   - API reference
   - Creating new modules
   - Code patterns

3. **Quick Reference** (quick lookup)
   - File locations
   - Common commands
   - API endpoints

---

## ✅ Production Readiness

Project sudah production-ready dengan:

✅ 4 fully functional modules  
✅ Complete CRUD operations  
✅ Search & pagination  
✅ Error handling  
✅ Responsive design  
✅ XSS protection  
✅ CSRF protection  
✅ Role-based access  
✅ Well-documented code  
✅ Comprehensive guides  

---

## 🎯 Next Steps

1. ✅ Setup database
2. ✅ Run migrations
3. ✅ Start backend (`php artisan serve`)
4. ✅ Start frontend (`npm run dev`)
5. ✅ Open http://localhost:5173
6. ✅ Login dan explore modules
7. ✅ Read MODULES_USER_GUIDE.md untuk workflow detail

---

## 💡 Tips & Tricks

**Hot Reload**: 
- Backend: Auto-reload saat file .php berubah
- Frontend: Auto-reload saat file .js/.css berubah

**Database Reset**:
```bash
php artisan migrate:fresh --seed
```

**Clear All Cache**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**Tinker (Interactive Shell)**:
```bash
php artisan tinker
```

**Check Routes**:
```bash
php artisan route:list
```

---

## 📞 Support

Jika ada error:

1. Baca pesan error di console
2. Cek Browser DevTools (F12)
3. Cek Backend error logs
4. Baca MODULES_USER_GUIDE.md
5. Baca MODULES_DEVELOPER_GUIDE.md

---

**Status**: ✅ Ready to Run  
**Estimated Setup Time**: 5 minutes  
**Estimated Run Time**: < 30 seconds  

---

Ready to launch? Follow steps di atas! 🚀

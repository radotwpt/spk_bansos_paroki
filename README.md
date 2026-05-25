<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## SPK Bansos

Project ini adalah aplikasi Sistem Pendukung Keputusan (SPK) bantuan sosial berbasis Laravel 12. Fokus utamanya adalah pendataan calon penerima dari lingkungan stasi, approval bertahap di stasi, proses ranking memakai metode SAW, keputusan final paroki, pembuatan surat berbasis template, dan dukungan PWA/offline queue.

### Alur utama

- Ketua Lingkungan Stasi membuat dan mengajukan data calon penerima.
- Stasi meninjau rekap calon penerima dan menyetujui data yang valid.
- Ketua Lingkungan Paroki menjalankan perankingan SAW per periode bansos.
- Paroki melihat ranking, menetapkan penerima sah, dan mengelola surat/template.
- PWA dapat menyimpan payload offline lalu mengirim ulang lewat endpoint sync.

### Fondasi backend yang tersedia

- API Laravel terdaftar di prefix `/api/v1`.
- Auth API menggunakan Laravel Sanctum token.
- Middleware `role` membatasi akses berdasarkan peran pengguna.
- Model domain: `Stasi`, `LingkunganStasi`, `LingkunganParoki`, `BansosPeriod`, `CalonPenerima`, `DocumentTemplate`, `GeneratedLetter`, dan `ActivityLog`.
- Migration inti sudah mencakup struktur hierarki wilayah, periode bansos, calon penerima, template dokumen, surat hasil generate, log aktivitas, dan token Sanctum.
- Test feature mencakup login API dan pembuatan calon penerima oleh `ketua_lingkungan_stasi`.

### Perintah verifikasi cepat

```bash
composer test
php artisan route:list
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Project Setup (SPK Bansos)

Follow these steps to configure this project for local development.

1. Install PHP dependencies

```bash
composer install
```

2. Install frontend deps

```bash
npm install
```

3. Copy env and generate app key

```bash
cp .env.example .env
php artisan key:generate
```

4. (Optional) Install Laravel Sanctum for API token auth

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
```

Add `SANCTUM_STATEFUL_DOMAINS` to `.env` if you will use SPA cookies (example: `localhost:5173`).

5. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

### Running with Docker

Build and run the app + MySQL via Docker Compose:

```bash
docker-compose up -d --build
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

### CI

A GitHub Actions workflow is included at `.github/workflows/ci.yml` to run migrations and tests on push/PR.


6. Serve the application

```bash
php artisan serve
```

If you encounter missing `composer` or `php` commands, install PHP and Composer and ensure they are on your PATH.

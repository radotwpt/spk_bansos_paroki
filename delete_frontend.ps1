# Script untuk menghapus semua file frontend dari project SPK Bansos Laravel
# Jalankan dengan: powershell -ExecutionPolicy Bypass -File delete_frontend.ps1

$base = Split-Path -Parent $MyInvocation.MyCommand.Path

$targets = @(
    "resources\css",
    "resources\js",
    "resources\views",
    "public\build",
    "public\manifest.json",
    "public\sw.js",
    "tailwind.config.js",
    "vite.config.js",
    "package.json",
    "package-lock.json"
)

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host " Menghapus file frontend Laravel..." -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

foreach ($t in $targets) {
    $fullPath = Join-Path $base $t
    if (Test-Path $fullPath) {
        Remove-Item -Recurse -Force $fullPath
        Write-Host "[DELETED] $t" -ForegroundColor Green
    } else {
        Write-Host "[SKIP]    $t (tidak ditemukan)" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host " Selesai! Silakan hapus file ini." -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

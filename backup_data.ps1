# Farm App Data Backup Script
# Run this regularly to backup your data
# Usage: Right-click -> Run with PowerShell

$date = Get-Date -Format "yyyy-MM-dd_HH-mm"
$backupDir = "C:\xampp\htdocs\farmapp\database\backups"
$backupFile = "$backupDir\farmapp_backup_$date.sql"

# Create backup directory if it doesn't exist
if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir | Out-Null
}

# Run mysqldump to export ALL data
$mysqldump = "C:\xampp\mysql\bin\mysqldump.exe"
& $mysqldump -u root --single-transaction --routines --triggers farmapp_db | Out-File -FilePath $backupFile -Encoding UTF8

if (Test-Path $backupFile) {
    $size = [math]::Round((Get-Item $backupFile).Length / 1KB, 2)
    Write-Host "Backup saved: $backupFile ($size KB)" -ForegroundColor Green
    Write-Host "To restore: cmd /c `"mysql -u root farmapp_db < $backupFile`"" -ForegroundColor Cyan
} else {
    Write-Host "Backup FAILED!" -ForegroundColor Red
}

# Keep only last 10 backups
Get-ChildItem $backupDir -Filter "*.sql" | Sort-Object LastWriteTime -Descending | Select-Object -Skip 10 | Remove-Item -Force
Write-Host "Old backups cleaned up." -ForegroundColor Yellow

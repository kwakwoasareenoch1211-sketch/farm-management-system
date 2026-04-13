@echo off
:: FarmApp Automatic Database Backup
:: Runs daily via Windows Task Scheduler

set BACKUP_DIR=C:\xampp\htdocs\farmapp\database\backups
set MYSQLDUMP=C:\xampp\mysql\bin\mysqldump.exe
set DATE_STR=%date:~10,4%-%date:~4,2%-%date:~7,2%_%time:~0,2%-%time:~3,2%
set DATE_STR=%DATE_STR: =0%
set BACKUP_FILE=%BACKUP_DIR%\farmapp_%DATE_STR%.sql

:: Create backup directory if needed
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

:: Run backup
"%MYSQLDUMP%" -u root --single-transaction --routines --triggers farmapp_db > "%BACKUP_FILE%"

:: Log result
echo %date% %time% - Backup saved: %BACKUP_FILE% >> "%BACKUP_DIR%\backup_log.txt"

:: Keep only last 14 backups (delete older ones)
for /f "skip=14 delims=" %%F in ('dir /b /o-d "%BACKUP_DIR%\farmapp_*.sql"') do del "%BACKUP_DIR%\%%F"

echo Backup complete: %BACKUP_FILE%

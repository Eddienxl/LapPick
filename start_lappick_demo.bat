@echo off
title LapPick SPK — One-Click UAS Launcher
setlocal

set "WINGET_PHP=C:\Users\ahmdf\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe"
if exist "%WINGET_PHP%\php.exe" (
    set "PATH=%WINGET_PHP%;%PATH%"
)
set "ROOT_DIR=%~dp0"

echo ============================================================================
echo   LapPick SPK (AHP + TOPSIS) — UAS Semester 7 Demo Launcher
echo   Ahmad Fadli Pratama (NIM. 12350111030) - UIN Suska Riau
echo ============================================================================
echo.

REM First-time bootstrap check for fresh GitHub clones
if not exist "%ROOT_DIR%lappick-laravel\.env" (
    echo [Setup] Creating lappick-laravel\.env from .env.example ...
    copy /Y "%ROOT_DIR%lappick-laravel\.env.example" "%ROOT_DIR%lappick-laravel\.env" >nul
    pushd "%ROOT_DIR%lappick-laravel"
    php artisan key:generate
    popd
)
if not exist "%ROOT_DIR%lappick-laravel\database\database.sqlite" (
    echo [Setup] Initializing SQLite database & seeding 949 laptops ...
    type nul > "%ROOT_DIR%lappick-laravel\database\database.sqlite"
    pushd "%ROOT_DIR%lappick-laravel"
    php artisan migrate --seed --force
    popd
)

echo [1/3] Checking running ports (8000 for Laravel, 8501 for Streamlit)...
netstat -ano | findstr ":8000 " | findstr "LISTENING" >nul
if errorlevel 1 (
    echo   Starting Laravel Web App at http://127.0.0.1:8000 ...
    start "LapPick Laravel Server (8000)" /D "%ROOT_DIR%lappick-laravel" cmd /k "php artisan serve --host=127.0.0.1 --port=8000"
) else (
    echo   [OK] Port 8000 already listening.
)

netstat -ano | findstr ":8501 " | findstr "LISTENING" >nul
if errorlevel 1 (
    echo   Starting Streamlit Analysis Lab at http://localhost:8501 ...
    where py >nul 2>&1
    if not errorlevel 1 (
        start "LapPick Streamlit Lab (8501)" /D "%ROOT_DIR%lab-streamlit" cmd /k "py -m streamlit run app.py --server.headless true --server.port 8501"
    ) else (
        start "LapPick Streamlit Lab (8501)" /D "%ROOT_DIR%lab-streamlit" cmd /k "python -m streamlit run app.py --server.headless true --server.port 8501"
    )
) else (
    echo   [OK] Port 8501 already listening.
)

echo.
echo [2/3] Waiting for services to warm up...
timeout /t 4 /nobreak >nul

echo [3/3] Opening LapPick Web App & Streamlit Analysis Lab in default browser...
start "" "http://127.0.0.1:8000/"
start "" "http://localhost:8501/"

echo.
echo ============================================================================
echo   Ready!
echo   - Laravel Web App       : http://127.0.0.1:8000
echo   - Streamlit Lab         : http://localhost:8501
echo   - Laporan UAS Live HTML : "%ROOT_DIR%Laporan_UAS_SPK_LapPick.html"
echo ============================================================================
pause

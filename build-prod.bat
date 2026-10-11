@echo off
setlocal EnableExtensions DisableDelayedExpansion

set "SOURCE=F:\Git\ptt"
set "OUTPUT=F:\Git\release"
set "TEMP=%OUTPUT%\temp"
set "DEST=%TEMP%\ptt"
set "PACKAGE=%OUTPUT%\PTT-X-YYZ.zip"
set "FAILED=0"

echo ==========================================
echo PTT - budowanie wersji produkcyjnej
echo ==========================================

if not exist "%SOURCE%\laravel\artisan" (
    echo BLAD: Nie znaleziono pliku laravel\artisan.
    exit /b 1
)
if not exist "%SOURCE%\laravel\composer.lock" (
    echo BLAD: Brak composer.lock - wymagany do powtarzalnego buildu.
    exit /b 1
)
if not exist "%SOURCE%\index.php" (
    echo BLAD: Brak glownego index.php.
    exit /b 1
)
where composer >nul 2>&1
if errorlevel 1 (
    echo BLAD: Composer nie jest dostepny w PATH.
    exit /b 1
)
where php >nul 2>&1
if errorlevel 1 (
    echo BLAD: PHP nie jest dostepne w PATH.
    exit /b 1
)

if not exist "%OUTPUT%" mkdir "%OUTPUT%"
if errorlevel 1 exit /b 1

if exist "%TEMP%\" (
    echo [1] Usuwanie poprzedniej kopii roboczej...
    rmdir /s /q "%TEMP%"
    if exist "%TEMP%\" (
        echo BLAD: Nie mozna usunac katalogu tymczasowego.
        exit /b 1
    )
)
mkdir "%DEST%"
if errorlevel 1 exit /b 1

echo [2] Kopiowanie aplikacji...
robocopy "%SOURCE%" "%DEST%" /E /R:1 /W:1 /XD .git .github .vscode node_modules public tests /XF .gitattributes .gitignore .gitkeep robots.txt phpstan.neon phpstan.neon.dist .env.example gulpfile.js _ide_helper.php phpunit.xml phpspec.yml README.md CHANGELOG.md
if errorlevel 8 goto error

echo [3] Kopiowanie zasobow publicznych...
for %%D in (css js fonts apk) do (
    if exist "%SOURCE%\laravel\public\%%D\" (
        robocopy "%SOURCE%\laravel\public\%%D" "%DEST%\%%D" /E /R:1 /W:1
        if errorlevel 8 goto error
    )
)

echo [4] Instalowanie zaleznosci produkcyjnych...
pushd "%DEST%\laravel"
if errorlevel 1 goto error
call composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
if errorlevel 1 (
    popd
    goto error
)
popd

echo [5] Czyszczenie cache i logow w kopii...
if exist "%DEST%\laravel\bootstrap\cache\" (
    del /f /q "%DEST%\laravel\bootstrap\cache\*.php" >nul 2>&1
)
if exist "%DEST%\laravel\storage\logs\" (
    del /f /q "%DEST%\laravel\storage\logs\*.log" >nul 2>&1
)
if exist "%DEST%\laravel\storage\framework\cache\data\" (
    rmdir /s /q "%DEST%\laravel\storage\framework\cache\data"
)
mkdir "%DEST%\laravel\storage\framework\cache\data" 2>nul
if exist "%DEST%\laravel\storage\framework\views\" (
    del /f /q "%DEST%\laravel\storage\framework\views\*.php" >nul 2>&1
)
if exist "%DEST%\laravel\storage\framework\sessions\" (
    for /f "delims=" %%F in ('dir /b /a-d "%DEST%\laravel\storage\framework\sessions" 2^>nul') do del /f /q "%DEST%\laravel\storage\framework\sessions\%%F" >nul 2>&1
)

echo [6] Pakowanie ZIP...
if exist "%PACKAGE%" del /f /q "%PACKAGE%"
if exist "%PACKAGE%" goto error
powershell -NoProfile -ExecutionPolicy Bypass -Command "Add-Type -AssemblyName System.IO.Compression.FileSystem; [System.IO.Compression.ZipFile]::CreateFromDirectory('%TEMP%', '%PACKAGE%', [System.IO.Compression.CompressionLevel]::Optimal, $false)"
if errorlevel 1 goto error

echo [7] Weryfikacja paczki...
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; Add-Type -AssemblyName System.IO.Compression.FileSystem; $z=[System.IO.Compression.ZipFile]::OpenRead('%PACKAGE%'); try { $names=@($z.Entries | ForEach-Object { $_.FullName.Replace('\','/') }); $required=@('ptt/index.php','ptt/.htaccess','ptt/laravel/.env','ptt/laravel/vendor/autoload.php','ptt/laravel/artisan'); foreach($n in $required) { if($names -cnotcontains $n) { throw ('Brak w ZIP: '+$n) } }; if(@($names | Where-Object { $_ -match '(^|/)(\.git|\.github|\.vscode|public|phpstan\.neon)(/|$)' }).Count -gt 0) { throw 'W ZIP sa wykluczone pliki lub katalogi' }; Write-Host 'OK: wymagane pliki sa w ZIP' } finally { $z.Dispose() }"
if errorlevel 1 goto error

echo [8] Usuwanie katalogu tymczasowego...
rmdir /s /q "%TEMP%"
if exist "%TEMP%\" (
    echo UWAGA: Nie udalo sie usunac katalogu tymczasowego.
    exit /b 1
)
echo.
echo GOTOWE: %PACKAGE%
exit /b 0

:error
echo.
echo BLAD: Nie udalo sie przygotowac wersji produkcyjnej.
if exist "%PACKAGE%" del /f /q "%PACKAGE%" >nul 2>&1
if exist "%TEMP%\" rmdir /s /q "%TEMP%"
exit /b 1

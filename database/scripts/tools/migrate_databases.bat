@echo off
echo Starting database migration...

REM Set MySQL paths
set MYSQL_PATH=C:\xampp\mysql\bin
set MYSQL_USER=root
set MYSQL_PASS=
set TARGET_DB=apsdreamhome

REM Create backup directory
if not exist "db_backups" mkdir db_backups

REM List of source databases
set SOURCE_DBS=aps_dream_home apsdreamhome apsdreamhomes

REM Loop through each source database
for %%D in (%SOURCE_DBS%) do (
    echo.
    echo Processing database: %%D
    echo ------------------------------
    
    REM Check if source database exists
    "%MYSQL_PATH%\mysql.exe" -u%MYSQL_USER% -e "SHOW DATABASES LIKE '%%D'" | findstr /r /c:"^%%D" >nul
    if errorlevel 1 (
        echo Database %%D does not exist, skipping...
    ) else (
        echo Exporting structure and data from %%D...
        
        REM Export structure without DROP TABLE and with IF NOT EXISTS
        "%MYSQL_PATH%\mysqldump.exe" --no-data -u%MYSQL_USER% --skip-add-drop-table --skip-triggers --compact %%D > "db_backups\%%D_structure.sql"
        
        REM Export data without structure
        "%MYSQL_PATH%\mysqldump.exe" --no-create-info --skip-triggers -u%MYSQL_USER% --compact %%D > "db_backups\%%D_data.sql"
        
        echo Importing into %TARGET_DB%...
        
        REM Import structure
        if exist "db_backups\%%D_structure.sql" (
            "%MYSQL_PATH%\mysql.exe" -u%MYSQL_USER% %TARGET_DB% < "db_backups\%%D_structure.sql"
            if errorlevel 1 (
                echo Error importing structure from %%D
            ) else (
                echo Successfully imported structure from %%D
            )
        )
        
        REM Import data
        if exist "db_backups\%%D_data.sql" (
            "%MYSQL_PATH%\mysql.exe" -u%MYSQL_USER% %TARGET_DB% < "db_backups\%%D_data.sql"
            if errorlevel 1 (
                echo Error importing data from %%D
            ) else (
                echo Successfully imported data from %%D
            )
        )
        
        REM Clean up
        if exist "db_backups\%%D_structure.sql" del "db_backups\%%D_structure.sql"
        if exist "db_backups\%%D_data.sql" del "db_backups\%%D_data.sql"
    )
)

echo.
echo Database migration completed!
pause

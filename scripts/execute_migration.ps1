# PowerShell Script to Execute MySQL Migration Queries
# This script executes the enhanced_password_migration.sql file

# Configuration - Using values from config.php
$MySQLHost = "localhost"
$MySQLUser = "root"
$MySQLPass = ""
$MySQLDB = "realestatephp"
$MySQLPort = "3306"

# Path to the SQL file
$SQLFilePath = "$PSScriptRoot\DATABASE FILE\enhanced_password_migration.sql"

# Check if the SQL file exists
if (-not (Test-Path $SQLFilePath)) {
    Write-Host "Error: SQL file not found at $SQLFilePath" -ForegroundColor Red
    exit 1
}

# Function to execute MySQL commands
function Execute-MySQLQuery {
    param (
        [string]$Query,
        [string]$Database = $MySQLDB
    )
    
    try {
        # Create MySQL arguments
        $MySQLArgs = @(
            "-h", $MySQLHost,
            "-u", $MySQLUser,
            "-P", $MySQLPort,
            $Database,
            "-e", $Query
        )
        
        # Add password if provided
        if ($MySQLPass) {
            $MySQLArgs += @("-p$MySQLPass")
        }
        
        # Execute MySQL command
        $result = & mysql $MySQLArgs 2>&1
        
        # Check for errors
        if ($LASTEXITCODE -ne 0) {
            Write-Host "Error executing query: $result" -ForegroundColor Red
            return $false
        }
        
        return $true
    }
    catch {
        Write-Host "Exception: $_" -ForegroundColor Red
        return $false
    }
}

# Function to execute a SQL file
function Execute-SQLFile {
    param (
        [string]$FilePath,
        [string]$Database = $MySQLDB
    )
    
    try {
        # Create MySQL arguments for source command
        $MySQLArgs = @(
            "-h", $MySQLHost,
            "-u", $MySQLUser,
            "-P", $MySQLPort
        )
        
        # Add password if provided
        if ($MySQLPass) {
            $MySQLArgs += @("-p$MySQLPass")
        }
        
        $MySQLArgs += @($Database)
        
        # Execute MySQL command with source
        $result = Get-Content $FilePath | & mysql $MySQLArgs 2>&1
        
        # Check for errors
        if ($LASTEXITCODE -ne 0) {
            Write-Host "Error executing SQL file: $result" -ForegroundColor Red
            return $false
        }
        
        return $true
    }
    catch {
        Write-Host "Exception: $_" -ForegroundColor Red
        return $false
    }
}

# Function to split SQL file into separate statements
function Split-SQLFile {
    param (
        [string]$FilePath
    )
    
    $content = Get-Content -Path $FilePath -Raw
    
    # Replace DELIMITER statements with markers
    $content = $content -replace "DELIMITER \\\\//", "-- DELIMITER_START"
    $content = $content -replace "DELIMITER ;", "-- DELIMITER_END"
    
    # Split by semicolons outside of procedures
    $inProcedure = $false
    $statements = @()
    $currentStatement = ""
    
    foreach ($line in ($content -split "`r`n")) {
        if ($line -match "-- DELIMITER_START") {
            $inProcedure = $true
            if ($currentStatement.Trim()) {
                $statements += $currentStatement.Trim()
                $currentStatement = ""
            }
            $currentStatement += "$line`r`n"
        }
        elseif ($line -match "-- DELIMITER_END") {
            $inProcedure = $false
            $currentStatement += "$line`r`n"
            $statements += $currentStatement.Trim()
            $currentStatement = ""
        }
        elseif ($inProcedure) {
            $currentStatement += "$line`r`n"
        }
        else {
            if ($line -match ";\s*$") {
                $currentStatement += "$line`r`n"
                $statements += $currentStatement.Trim()
                $currentStatement = ""
            }
            else {
                $currentStatement += "$line`r`n"
            }
        }
    }
    
    # Add any remaining statement
    if ($currentStatement.Trim()) {
        $statements += $currentStatement.Trim()
    }
    
    return $statements
}

# Main execution
Write-Host "Starting MySQL migration..." -ForegroundColor Cyan

# Check if MySQL is available
try {
    $mysqlVersion = & mysql --version 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Error: MySQL client not found. Please ensure MySQL is installed and in your PATH." -ForegroundColor Red
        exit 1
    }
    Write-Host "Using $mysqlVersion" -ForegroundColor Green
}
catch {
    Write-Host "Error: MySQL client not found. Please ensure MySQL is installed and in your PATH." -ForegroundColor Red
    exit 1
}

# Test database connection
Write-Host "Testing database connection..." -ForegroundColor Cyan
$testConnection = Execute-MySQLQuery -Query "SELECT 'Connection successful' AS Status;"
if (-not $testConnection) {
    Write-Host "Error: Could not connect to MySQL database. Please check your credentials." -ForegroundColor Red
    exit 1
}
Write-Host "Database connection successful." -ForegroundColor Green

# Check if user_backup table exists
Write-Host "Checking if user_backup table exists..." -ForegroundColor Cyan
$tableExists = Execute-MySQLQuery -Query "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$MySQLDB' AND table_name = 'user_backup';"
if (-not $tableExists) {
    Write-Host "Warning: user_backup table does not exist. Creating backup of user table..." -ForegroundColor Yellow
    $createBackup = Execute-MySQLQuery -Query "CREATE TABLE IF NOT EXISTS user_backup AS SELECT * FROM user;"
    if (-not $createBackup) {
        Write-Host "Error: Failed to create user_backup table. Please check if the user table exists." -ForegroundColor Red
        exit 1
    }
    Write-Host "Created user_backup table successfully." -ForegroundColor Green
}

# Execute the SQL file
Write-Host "Executing SQL migration file..." -ForegroundColor Cyan

# Method 1: Try direct execution first
$directExecution = Execute-SQLFile -FilePath $SQLFilePath
if ($directExecution) {
    Write-Host "SQL migration executed successfully using direct method." -ForegroundColor Green
}
else {
    Write-Host "Direct execution failed. Trying statement-by-statement execution..." -ForegroundColor Yellow
    
    # Method 2: Split and execute statement by statement
    $statements = Split-SQLFile -FilePath $SQLFilePath
    $totalStatements = $statements.Count
    $successCount = 0
    
    for ($i = 0; $i -lt $totalStatements; $i++) {
        $statement = $statements[$i]
        if ($statement -match "^\s*--" -or $statement -match "^\s*$") {
            # Skip comments and empty lines
            $successCount++
            continue
        }
        
        Write-Host "Executing statement $($i+1) of $totalStatements..." -ForegroundColor Cyan
        
        # Handle DELIMITER blocks specially
        if ($statement -match "-- DELIMITER_START") {
            # Extract the procedure or function definition
            $procedureDefinition = $statement -replace "-- DELIMITER_START", "DELIMITER //"
            $procedureDefinition = $procedureDefinition -replace "-- DELIMITER_END", "DELIMITER ;"
            
            # Write to a temporary file
            $tempFile = [System.IO.Path]::GetTempFileName()
            $procedureDefinition | Out-File -FilePath $tempFile -Encoding utf8
            
            # Execute the temporary file
            $result = Execute-SQLFile -FilePath $tempFile
            Remove-Item -Path $tempFile -Force
            
            if ($result) {
                $successCount++
                Write-Host "Procedure/function created successfully." -ForegroundColor Green
            }
            else {
                Write-Host "Error creating procedure/function." -ForegroundColor Red
            }
        }
        else {
            # Regular statement
            $result = Execute-MySQLQuery -Query $statement
            if ($result) {
                $successCount++
            }
            else {
                Write-Host "Failed statement: $statement" -ForegroundColor Red
            }
        }
    }
    
    Write-Host "Completed $successCount of $totalStatements statements successfully." -ForegroundColor Cyan
    
    if ($successCount -eq $totalStatements) {
        Write-Host "SQL migration completed successfully." -ForegroundColor Green
    }
    else {
        Write-Host "SQL migration completed with errors. Please check the output above." -ForegroundColor Yellow
    }
}

# Call the migration procedure
Write-Host "Calling migration procedure..." -ForegroundColor Cyan
$callProcedure = Execute-MySQLQuery -Query "CALL migrate_user_data_with_password_handling();"
if ($callProcedure) {
    Write-Host "Migration procedure executed successfully." -ForegroundColor Green
    
    # Check for users needing password reset
    Write-Host "Checking for users needing password reset..." -ForegroundColor Cyan
    $resetProcedure = Execute-MySQLQuery -Query "CALL reset_migrated_passwords();"
    if ($resetProcedure) {
        Write-Host "Password reset check completed. Please check the output for users needing password reset." -ForegroundColor Green
    }
    else {
        Write-Host "Error checking for password resets." -ForegroundColor Red
    }
}
else {
    Write-Host "Error executing migration procedure." -ForegroundColor Red
}

Write-Host "Migration process completed." -ForegroundColor Cyan
param(
    [Parameter(Mandatory = $true)]
    [string]$BackupZip,

    [string]$ProjectRoot = (Resolve-Path "$PSScriptRoot\..\..").Path,

    [switch]$ConfirmarRestauracao
)

$ErrorActionPreference = "Stop"

if (-not $ConfirmarRestauracao) {
    throw @"
Restauração bloqueada por segurança.

Execute novamente incluindo:

-ConfirmarRestauracao

ATENÇÃO: o banco atual será sobrescrito pelos dados do backup.
"@
}

function Read-EnvFile {
    param([string]$Path)

    $result = @{}

    if (-not (Test-Path $Path)) {
        throw "Arquivo .env não encontrado em: $Path"
    }

    Get-Content $Path | ForEach-Object {
        $line = $_.Trim()

        if (
            $line -eq "" -or
            $line.StartsWith("#")
        ) {
            return
        }

        $parts = $line -split "=", 2

        if ($parts.Count -ne 2) {
            return
        }

        $key = $parts[0].Trim()
        $value = $parts[1].Trim()

        if (
            ($value.StartsWith('"') -and $value.EndsWith('"')) -or
            ($value.StartsWith("'") -and $value.EndsWith("'"))
        ) {
            $value = $value.Substring(1, $value.Length - 2)
        }

        $result[$key] = $value
    }

    return $result
}

function Find-CommandPath {
    param([string[]]$Names)

    foreach ($name in $Names) {
        $cmd = Get-Command $name -ErrorAction SilentlyContinue

        if ($cmd) {
            return $cmd.Source
        }
    }

    return $null
}

if (-not (Test-Path $BackupZip)) {
    throw "Backup não encontrado: $BackupZip"
}

$envData = Read-EnvFile (Join-Path $ProjectRoot ".env")

$dbHost = $envData["DB_HOST"]
$dbPort = $envData["DB_PORT"]
$dbName = $envData["DB_NAME"]
$dbUser = $envData["DB_USER"]
$dbPass = $envData["DB_PASS"]

if (-not $dbHost) { $dbHost = "127.0.0.1" }
if (-not $dbPort) { $dbPort = "3306" }

$mysqlExe = Find-CommandPath @(
    "mariadb",
    "mysql"
)

if (-not $mysqlExe) {
    throw "Não encontrei mariadb ou mysql no PATH."
}

$tempDir = Join-Path `
    ([System.IO.Path]::GetTempPath()) `
    ("syn_restore_" + [guid]::NewGuid().ToString("N"))

New-Item `
    -ItemType Directory `
    -Path $tempDir | Out-Null

try {
    Expand-Archive `
        -Path $BackupZip `
        -DestinationPath $tempDir `
        -Force

    $dbFile = Join-Path $tempDir "database.sql"

    if (-not (Test-Path $dbFile)) {
        throw "database.sql não existe no backup."
    }

    Write-Host "Restaurando banco..." -ForegroundColor Yellow

    $oldMysqlPwd = $env:MYSQL_PWD

    try {
        if ($null -ne $dbPass -and $dbPass -ne "") {
            $env:MYSQL_PWD = $dbPass
        } else {
            Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
        }

        Get-Content `
            -Raw `
            -Path $dbFile |
            & $mysqlExe `
                "--host=$dbHost" `
                "--port=$dbPort" `
                "--user=$dbUser" `
                $dbName

        if ($LASTEXITCODE -ne 0) {
            throw "Falha ao restaurar banco. Código: $LASTEXITCODE"
        }
    }
    finally {
        if ($null -eq $oldMysqlPwd) {
            Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
        } else {
            $env:MYSQL_PWD = $oldMysqlPwd
        }
    }

    $uploadsBackup = Join-Path $tempDir "uploads"
    $uploadsTarget = Join-Path $ProjectRoot "public\uploads"

    if (Test-Path $uploadsBackup) {
        Write-Host "Restaurando uploads..." -ForegroundColor Yellow

        if (Test-Path $uploadsTarget) {
            Remove-Item `
                -Path $uploadsTarget `
                -Recurse `
                -Force
        }

        Copy-Item `
            -Path $uploadsBackup `
            -Destination $uploadsTarget `
            -Recurse `
            -Force
    }

    Write-Host ""
    Write-Host "Restauração concluída." -ForegroundColor Green
}
finally {
    if (Test-Path $tempDir) {
        Remove-Item `
            -Path $tempDir `
            -Recurse `
            -Force
    }
}

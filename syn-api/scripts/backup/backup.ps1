param(
    [string]$ProjectRoot = (Resolve-Path "$PSScriptRoot\..\..").Path
)

$ErrorActionPreference = "Stop"

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

$envPath = Join-Path $ProjectRoot ".env"
$envData = Read-EnvFile $envPath

$dbHost = $envData["DB_HOST"]
$dbPort = $envData["DB_PORT"]
$dbName = $envData["DB_NAME"]
$dbUser = $envData["DB_USER"]
$dbPass = $envData["DB_PASS"]

if (-not $dbHost) { $dbHost = "127.0.0.1" }
if (-not $dbPort) { $dbPort = "3306" }

if (-not $dbName) {
    throw "DB_NAME não está definido no .env"
}

if (-not $dbUser) {
    throw "DB_USER não está definido no .env"
}

$dumpExe = Find-CommandPath @(
    "mariadb-dump",
    "mysqldump"
)

if (-not $dumpExe) {
    throw @"
Não encontrei mariadb-dump ou mysqldump no PATH.

Instale/adicone as ferramentas do MariaDB/MySQL ao PATH
antes de executar o backup.
"@
}

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupRoot = Join-Path $ProjectRoot "storage\backups"
$workDir = Join-Path $backupRoot "syn_$timestamp"
$zipFile = Join-Path $backupRoot "syn_$timestamp.zip"

New-Item -ItemType Directory -Force -Path $workDir | Out-Null

$dbFile = Join-Path $workDir "database.sql"
$uploadsDir = Join-Path $ProjectRoot "public\uploads"
$uploadsCopy = Join-Path $workDir "uploads"

Write-Host "Criando backup do banco..." -ForegroundColor Cyan

$oldMysqlPwd = $env:MYSQL_PWD

try {
    if ($null -ne $dbPass -and $dbPass -ne "") {
        $env:MYSQL_PWD = $dbPass
    } else {
        Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    }

    $args = @(
        "--host=$dbHost",
        "--port=$dbPort",
        "--user=$dbUser",
        "--single-transaction",
        "--routines",
        "--triggers",
        "--events",
        "--default-character-set=utf8mb4",
        "--result-file=$dbFile",
        $dbName
    )

    & $dumpExe @args

    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao criar dump do banco. Código: $LASTEXITCODE"
    }
}
finally {
    if ($null -eq $oldMysqlPwd) {
        Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    } else {
        $env:MYSQL_PWD = $oldMysqlPwd
    }
}

if (-not (Test-Path $dbFile)) {
    throw "O arquivo database.sql não foi criado."
}

if ((Get-Item $dbFile).Length -le 0) {
    throw "O arquivo database.sql foi criado vazio."
}

Write-Host "Copiando uploads..." -ForegroundColor Cyan

if (Test-Path $uploadsDir) {
    Copy-Item `
        -Path $uploadsDir `
        -Destination $uploadsCopy `
        -Recurse `
        -Force
} else {
    New-Item `
        -ItemType Directory `
        -Path $uploadsCopy `
        -Force | Out-Null
}

$manifest = [ordered]@{
    sistema = "SYN"
    criado_em = (Get-Date).ToString("o")
    banco = $dbName
    host = $dbHost
    porta = $dbPort
    arquivo_banco = "database.sql"
    pasta_uploads = "uploads"
}

$manifest |
    ConvertTo-Json -Depth 3 |
    Set-Content `
        -Path (Join-Path $workDir "manifest.json") `
        -Encoding UTF8

Write-Host "Compactando backup..." -ForegroundColor Cyan

Compress-Archive `
    -Path (Join-Path $workDir "*") `
    -DestinationPath $zipFile `
    -Force

Remove-Item `
    -Path $workDir `
    -Recurse `
    -Force

$hash = Get-FileHash `
    -Path $zipFile `
    -Algorithm SHA256

$hashFile = "$zipFile.sha256"

"$($hash.Hash)  $(Split-Path $zipFile -Leaf)" |
    Set-Content `
        -Path $hashFile `
        -Encoding ASCII

Write-Host ""
Write-Host "Backup concluído." -ForegroundColor Green
Write-Host "Arquivo: $zipFile"
Write-Host "SHA256 : $($hash.Hash)"

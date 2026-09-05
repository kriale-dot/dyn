param(
    [Parameter(Mandatory = $true)]
    [string]$BackupZip
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path $BackupZip)) {
    throw "Backup não encontrado: $BackupZip"
}

$hashFile = "$BackupZip.sha256"

if (-not (Test-Path $hashFile)) {
    throw "Arquivo SHA256 não encontrado: $hashFile"
}

$expected = (
    Get-Content $hashFile |
    Select-Object -First 1
).Split(" ")[0].Trim()

$actual = (
    Get-FileHash `
        -Path $BackupZip `
        -Algorithm SHA256
).Hash

if (
    $actual.ToUpperInvariant()
    -ne
    $expected.ToUpperInvariant()
) {
    throw "SHA256 inválido. O backup pode estar corrompido."
}

$tempDir = Join-Path `
    ([System.IO.Path]::GetTempPath()) `
    ("syn_verify_" + [guid]::NewGuid().ToString("N"))

New-Item -ItemType Directory -Path $tempDir | Out-Null

try {
    Expand-Archive `
        -Path $BackupZip `
        -DestinationPath $tempDir `
        -Force

    $dbFile = Join-Path $tempDir "database.sql"

    if (
        -not (Test-Path $dbFile) -or
        (Get-Item $dbFile).Length -le 0
    ) {
        throw "database.sql ausente ou vazio."
    }

    if (-not (Test-Path (Join-Path $tempDir "manifest.json"))) {
        throw "manifest.json ausente."
    }

    Write-Host "Backup íntegro e estruturalmente válido." -ForegroundColor Green
}
finally {
    if (Test-Path $tempDir) {
        Remove-Item -Path $tempDir -Recurse -Force
    }
}

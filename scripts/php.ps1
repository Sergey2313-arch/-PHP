# Uses the isolated PHP configuration prepared for this computer, when present.
# All arguments are passed directly to PHP (for example: .\scripts\php.ps1 artisan test).
$ErrorActionPreference = 'Stop'
$projectDirectory = Split-Path $PSScriptRoot -Parent
$previousPhpRc = $env:PHPRC
try {
    $localIni = Join-Path $projectDirectory '.local\php.ini'
    if (Test-Path -LiteralPath $localIni) { $env:PHPRC = $localIni }
    Push-Location $projectDirectory
    try { & php @args; $phpExitCode = $LASTEXITCODE } finally { Pop-Location }
} finally { $env:PHPRC = $previousPhpRc }
exit $phpExitCode

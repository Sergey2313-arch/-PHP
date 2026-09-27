# Only an explicitly named isolated test database can be used here.
param([string] $Database = 'it_service_desk_testing')
$ErrorActionPreference = 'Stop'
if ($Database -notmatch '_testing$') { throw 'The test database name must end with _testing.' }
$projectDirectory = Split-Path $PSScriptRoot -Parent
$previousDatabase = $env:DB_DATABASE
$previousConnection = $env:DB_CONNECTION
$previousPhpRc = $env:PHPRC
try {
    $env:DB_CONNECTION = 'mysql'
    $env:DB_DATABASE = $Database
    $localIni = Join-Path $projectDirectory '.local\php.ini'
    if (Test-Path -LiteralPath $localIni) { $env:PHPRC = $localIni }
    Push-Location $projectDirectory
    try { & php artisan test @args; $testExitCode = $LASTEXITCODE } finally { Pop-Location }
} finally {
    $env:DB_DATABASE = $previousDatabase
    $env:DB_CONNECTION = $previousConnection
    $env:PHPRC = $previousPhpRc
}
exit $testExitCode

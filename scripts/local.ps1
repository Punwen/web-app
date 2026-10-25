param(
    [ValidateSet('start','stop','status')][string]$Action = 'start',
    [switch]$KeepAlive
)
$ErrorActionPreference = 'Stop'
$projectRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$runtimeRoot = Join-Path $projectRoot '.local'
$runtimeFile = Join-Path $runtimeRoot 'runtime.json'
$phpCandidates = @('C:\php\php.exe', 'C:\xampp\php\php.exe')
$phpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($phpCommand) { $phpCandidates = @($phpCommand.Source) + $phpCandidates }
$phpExe = $phpCandidates | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1
if (-not $phpExe) { throw 'PHP was not found. Set its path in scripts/local.ps1 or follow docs/LOCAL_SETUP.md.' }
function Test-LocalPort([int]$Port) {
    $client = New-Object Net.Sockets.TcpClient
    try { $task = $client.ConnectAsync('127.0.0.1', $Port); return ($task.Wait(600) -and $client.Connected) }
    catch { return $false }
    finally { $client.Dispose() }
}
function Test-OwnedWebsite {
    $instanceFile = Join-Path $runtimeRoot 'web-instance'
    if (-not (Test-Path -LiteralPath $instanceFile)) { return $false }
    $expected = (Get-Content -LiteralPath $instanceFile -Raw).Trim()
    $response = $null
    try {
        $request = [Net.HttpWebRequest]::Create('http://127.0.0.1:8088/index.php')
        $request.Timeout = 3000
        try { $response = $request.GetResponse() } catch [Net.WebException] { $response = $_.Exception.Response }
        return ($response -and $response.Headers['X-ReStart-Instance'] -eq $expected)
    } catch { return $false } finally { if ($response) { $response.Close() } }
}
function Get-OwnedPhp($State) {
    if (-not $State -or -not $State.phpPid) { return $null }
    $process = Get-Process -Id $State.phpPid -ErrorAction SilentlyContinue
    if ($process -and $process.ProcessName -eq 'php' -and $process.StartTime.ToUniversalTime().Ticks.ToString() -eq $State.phpStartTicks) { return $process }
    return $null
}
$state = if (Test-Path -LiteralPath $runtimeFile) { Get-Content -LiteralPath $runtimeFile -Raw | ConvertFrom-Json } else { $null }
if ($Action -eq 'status') {
    Write-Output ('Website port8088: ' + (Test-LocalPort 8088))
    Write-Output ('Database port33079: ' + (Test-LocalPort 33079))
    exit 0
}
if ($Action -eq 'stop') {
    if (Test-LocalPort 33079) {
        & $phpExe (Join-Path $PSScriptRoot 'admin.php') shutdown
        if ($LASTEXITCODE -ne 0) { throw 'Refusing to stop an unverified database.' }
        for ($attempt=0; $attempt -lt 40 -and (Test-LocalPort 33079); $attempt++) { Start-Sleep -Milliseconds 250 }
    }
    $ownedPhp = Get-OwnedPhp $state
    if ($ownedPhp) { Stop-Process -Id $ownedPhp.Id; Write-Output 'ReStart website stopped.' }
    elseif ((Test-OwnedWebsite) -and $state.keepAlive) {
        Set-Content -LiteralPath (Join-Path $runtimeRoot 'stop-web-request') -Value (Get-Content -LiteralPath (Join-Path $runtimeRoot 'web-instance') -Raw) -Encoding ASCII
        for ($attempt=0; $attempt -lt 40 -and (Test-LocalPort 8088); $attempt++) { Start-Sleep -Milliseconds 250 }
        if (Test-LocalPort 8088) { throw 'The verified website did not stop. Close its launcher window.' }
        Write-Output 'ReStart website stopped.'
    }
    elseif (Test-OwnedWebsite) { throw 'Website process is inaccessible; close its PHP server window before retrying.' }

    exit 0
}
New-Item -ItemType Directory -Force -Path $runtimeRoot | Out-Null
if (-not (Test-LocalPort 33079)) {
    $mysqlCandidates = @('C:\xampp\mysql\bin\mysqld.exe')
    $mysqlInstallRoot = 'C:\Program Files\MySQL'
    if (Test-Path -LiteralPath $mysqlInstallRoot) {
        $mysqlCandidates += @(Get-ChildItem -LiteralPath $mysqlInstallRoot -Directory | ForEach-Object { Join-Path $_.FullName 'bin\mysqld.exe' })
    }
    $mysqlExe = $mysqlCandidates | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1
    if (-not $mysqlExe) { throw 'MySQL was not found. Follow the XAMPP setup in docs/LOCAL_SETUP.md.' }
    if ($mysqlExe -like '*xampp*') { throw 'For XAMPP/MariaDB use the manual setup in docs/LOCAL_SETUP.md. This isolated launcher initializes Oracle MySQL only.' }
    $mysqlBase = Split-Path (Split-Path $mysqlExe -Parent) -Parent
    $dataDir = Join-Path $runtimeRoot 'mysql'
    if (-not (Test-Path -LiteralPath (Join-Path $dataDir 'mysql'))) {
        if ((Test-Path -LiteralPath $dataDir) -and @(Get-ChildItem -LiteralPath $dataDir -Force).Count -gt 0) { throw 'Data directory is not empty. Initialization refused to protect existing data.' }
        New-Item -ItemType Directory -Force -Path $dataDir | Out-Null
        & $mysqlExe --no-defaults --initialize-insecure "--basedir=$mysqlBase" "--datadir=$dataDir" --console
        if ($LASTEXITCODE -ne 0) { throw 'MySQL initialization failed.' }
    }
    $mysqlArgs = @('--no-defaults', ('--basedir="' + $mysqlBase + '"'), ('--datadir="' + $dataDir + '"'), '--port=33079', '--bind-address=127.0.0.1', '--mysqlx=OFF', '--console')
    $mysqlProcess = Start-Process -FilePath $mysqlExe -ArgumentList $mysqlArgs -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runtimeRoot 'mysql-out.log') -RedirectStandardError (Join-Path $runtimeRoot 'mysql-error.log')
    & $phpExe (Join-Path $PSScriptRoot 'provision.php')
    if ($LASTEXITCODE -ne 0) { throw 'Database setup failed; see .local/mysql-error.log.' }
} else {
    & $phpExe (Join-Path $PSScriptRoot 'admin.php') verify
    if ($LASTEXITCODE -ne 0) { throw 'Port33079 is occupied by another or inaccessible service; it was not changed.' }
}
if (-not (Test-Path -LiteralPath (Join-Path $projectRoot 'config.local.php'))) { throw 'config.local.php is missing. Follow the setup guide.' }
$phpProcess = Get-OwnedPhp $state
if (Test-LocalPort 8088) {
    if (-not (Test-OwnedWebsite)) { throw 'Port8088 is occupied by another process; it was not stopped.' }
    Write-Output 'ReStart is already running: http://127.0.0.1:8088/'
} else {
    $instanceFile = Join-Path $runtimeRoot 'web-instance'
    if (-not (Test-Path -LiteralPath $instanceFile)) { [Guid]::NewGuid().ToString('N') | Set-Content -LiteralPath $instanceFile -Encoding ASCII }
    Set-Content -LiteralPath (Join-Path $runtimeRoot 'stop-web-request') -Value '' -Encoding ASCII
    $oldConfig = [Environment]::GetEnvironmentVariable('RESTART_CONFIG','Process')
    try {
        [Environment]::SetEnvironmentVariable('RESTART_CONFIG',(Join-Path $projectRoot 'config.local.php'),'Process')
        $argsList = @('-S','127.0.0.1:8088','-t',('"' + $projectRoot + '"'),('"' + (Join-Path $PSScriptRoot 'router.php') + '"'))
        $phpProcess = Start-Process -FilePath $phpExe -ArgumentList $argsList -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $runtimeRoot 'php-out.log') -RedirectStandardError (Join-Path $runtimeRoot 'php-error.log')
    } finally { [Environment]::SetEnvironmentVariable('RESTART_CONFIG',$oldConfig,'Process') }
    @{ phpPid=$phpProcess.Id; phpStartTicks=$phpProcess.StartTime.ToUniversalTime().Ticks.ToString(); keepAlive=[bool]$KeepAlive } | ConvertTo-Json | Set-Content -LiteralPath $runtimeFile -Encoding UTF8
    $ready = $false
    for ($attempt=0; $attempt -lt 30; $attempt++) {
        if (Test-LocalPort 8088) { $ready=$true; break }
        Start-Sleep -Milliseconds 200
    }
    if (-not $ready) { throw 'Website failed to start; see .local/php-error.log.' }
    Write-Output 'ReStart is ready: http://127.0.0.1:8088/'
}
if ($KeepAlive -and $phpProcess) {
    $stopFile = Join-Path $runtimeRoot 'stop-web-request'
    while (-not $phpProcess.HasExited) {
        if ((Test-Path -LiteralPath $stopFile) -and (Get-Content -LiteralPath $stopFile -Raw).Trim() -eq (Get-Content -LiteralPath (Join-Path $runtimeRoot 'web-instance') -Raw).Trim()) {
            Stop-Process -Id $phpProcess.Id
            Set-Content -LiteralPath $stopFile -Value '' -Encoding ASCII
            break
        }
        Start-Sleep -Milliseconds 250
    }
}




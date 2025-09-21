# PowerShell wrapper to run predict_ts.py from this folder
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$py = Get-Command py -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $py) { $py = Get-Command python -ErrorAction SilentlyContinue | Select-Object -First 1 }
if (-not $py) { $py = Get-Command python3 -ErrorAction SilentlyContinue | Select-Object -First 1 }
if (-not $py) {
    Write-Error "No Python interpreter found. Install Python or use a virtualenv and ensure 'py' or 'python' is in PATH."
    exit 1
}
& $py.Path "$scriptDir\predict_ts.py" @Args
exit $LASTEXITCODE

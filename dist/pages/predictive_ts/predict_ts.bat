@echo off
rem Windows batch wrapper for running predict_ts.py reliably
set SCRIPT_DIR=%~dp0
set PY_EXE=

rem Prefer py launcher, then python, then python3
for %%p in (py python python3) do (
  where %%p >nul 2>&1 && set PY_EXE=%%p
)

if "%PY_EXE%"=="" (
  echo No Python interpreter found in PATH. Please install Python and ensure 'py' or 'python' is available.
  exit /b 1
)

"%PY_EXE%" "%SCRIPT_DIR%predict_ts.py" %*
exit /b %errorlevel%

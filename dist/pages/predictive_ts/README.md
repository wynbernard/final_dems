Prophet time-series predictor

Files:

- `predict_ts.py` - Prophet-based time-series predictor per evacuation location. Applies MinMax scaling to the target before fitting Prophet and inverse-transforms predictions.
- `requirements.txt` - Dependencies for the script.

Usage:

1. Install dependencies in the Python environment that will run the script:

```powershell
python -m pip install -r "C:\xampp\htdocs\FInal_DEMS\dist\pages\predictive_ts\requirements.txt"
```

2. Run the script:

```powershell
python "C:\xampp\htdocs\FInal_DEMS\dist\pages\predictive_ts\predict_ts.py" --periods 7 --freq D
```

On Windows you can use the provided wrappers instead of calling /usr/bin/env python3 which will not work on Windows:

```powershell
# Run via batch wrapper (double-click or from powershell)
"C:\xampp\htdocs\FInal_DEMS\dist\pages\predictive_ts\predict_ts.bat" --periods 7 --freq D

# Or via PowerShell wrapper
powershell -ExecutionPolicy Bypass -File "C:\xampp\htdocs\FInal_DEMS\dist\pages\predictive_ts\predict_ts.ps1" --periods 7 --freq D
```

3. Output:

- CSV: `dist/pages/predictive_ts/output/prophet_predictions_YYYYmmdd_HHMMSS.csv`
- DB: table `predictive_result` is created/overwritten with latest predictions.

Notes:

- Prophet installation can be tricky on some hosts (compilation). If installing fails, consider using the lighter Ridge-based `predict_scaled.py` approach.
- If you prefer not to overwrite `predictive_result`, edit `persist_results` to append with timestamps.

Troubleshooting: "The system cannot find the path specified"

- If you see an error mentioning `/usr/bin/env python3` on Windows, that's coming from an interpreter selection that uses a Unix shebang; use the `.bat` or `.ps1` wrappers or call `python` directly from PowerShell / CMD.
- If the script fails to create the `output/` directory, check folder permissions for the webserver or user running the script. The script prints `BASE`, `OUTPUT`, `cwd`, and `python_executable` at startup to help debug.

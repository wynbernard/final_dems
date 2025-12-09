# Environment Setup Guide

## 🔐 Security: Database Credentials Configuration

This project now uses environment variables for sensitive configuration instead of hardcoded credentials.

---

## Quick Setup

### Step 1: Create `.env` File

Create a `.env` file in the project root directory (`c:\xampp\htdocs\final_dems\.env`):

```env
# Database Configuration
DB_HOST=srv1322.hstgr.io
DB_USER=u520834156_userDEMS
DB_PASS=5YnY61~U~Hz
DB_NAME=u520834156_DBDems

# Local Development (uncomment to use)
# DB_HOST=localhost
# DB_USER=root
# DB_PASS=
# DB_NAME=f_dems

# Encryption Key (generate a random 32-byte hex string)
# Generate with: php -r "echo bin2hex(random_bytes(32));"
ENCRYPTION_KEY=your-secret-key-here-change-this-to-random-value

# Python Database Configuration (same as above)
PYTHON_DB_HOST=srv1322.hstgr.io
PYTHON_DB_USER=u520834156_userDEMS
PYTHON_DB_PASS=5YnY61~U~Hz
PYTHON_DB_NAME=u520834156_DBDems
```

### Step 2: Generate Encryption Key

Run this command to generate a secure encryption key:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Copy the output and replace `ENCRYPTION_KEY` in your `.env` file.

### Step 3: Install Python Dependencies

If using Python scripts, install the required package:

```bash
cd python
pip install python-dotenv
```

Or install all requirements:

```bash
pip install -r requirements.txt
```

---

## How It Works

### PHP Files

The `database/conn.php` file now:
1. Loads environment variables from `.env` file (via `env_loader.php`)
2. Falls back to system environment variables if `.env` doesn't exist
3. Uses safe defaults for local development

**Files Updated:**
- `database/conn.php` - Now uses `getenv()` to read credentials
- `database/env_loader.php` - Helper to load `.env` file

### Python Files

The Python scripts now:
1. Use `python-dotenv` to load `.env` file
2. Read from environment variables
3. Fall back to defaults if not set

**Files Updated:**
- `python/predictor/run_predictor.py` - Uses `load_dotenv()`
- `python/predictor/sarimax_framework.py` - Uses `load_dotenv()`

---

## Security Notes

✅ **DO:**
- Keep `.env` file local (never commit to git)
- Use different credentials for development/production
- Generate strong encryption keys
- Restrict file permissions on `.env` (chmod 600)

❌ **DON'T:**
- Commit `.env` to version control
- Share `.env` file publicly
- Use weak encryption keys
- Hardcode credentials in source code

---

## Verification

### Test PHP Connection

Create a test file `test_db.php`:

```php
<?php
require_once 'database/conn.php';
echo "Database connection successful!";
?>
```

### Test Python Connection

```bash
cd python/predictor
python -c "from sarimax_framework import SarimaxPredictor; import os; from dotenv import load_dotenv; load_dotenv(); print('Environment loaded successfully')"
```

---

## Troubleshooting

### PHP: "Database connection failed"

1. Check `.env` file exists in project root
2. Verify credentials are correct
3. Check file permissions (should be readable)
4. Ensure `database/env_loader.php` is included

### Python: "ModuleNotFoundError: No module named 'dotenv'"

```bash
pip install python-dotenv
```

### Environment variables not loading

1. Check `.env` file is in correct location (project root)
2. Verify syntax (no spaces around `=`)
3. Check for typos in variable names
4. Restart your web server/Python interpreter

---

## Production Deployment

For production:

1. **Create `.env` file on server** with production credentials
2. **Set file permissions:** `chmod 600 .env`
3. **Verify `.env` is in `.gitignore`** (already done)
4. **Never commit `.env` to repository**
5. **Use different credentials** than development

---

## Migration from Hardcoded Credentials

If you're migrating from the old hardcoded credentials:

1. ✅ Credentials moved to `.env` - **DONE**
2. ✅ `.gitignore` updated - **DONE**
3. ✅ PHP files updated - **DONE**
4. ✅ Python files updated - **DONE**
5. ⚠️ **YOU NEED TO:** Create `.env` file with your credentials
6. ⚠️ **YOU NEED TO:** Generate and set `ENCRYPTION_KEY`

---

## Next Steps

After setting up `.env`:

1. Update `database/encryption.php` to use `ENCRYPTION_KEY` from environment
2. Test all database connections
3. Test Python scripts
4. Verify no credentials are exposed in logs or error messages

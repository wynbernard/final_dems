# URL Parameter Encryption

This application uses encrypted URL parameters to protect sensitive data like `disaster_id` and `location_id`.

## How It Works

The encryption is implemented using PHP's OpenSSL library with AES-256-CBC cipher.

### Files Modified

1. **database/encryption.php** - Encryption helper functions
2. **dist/pages/admin_page/idps_user.php** - Main page with encrypted URL parameters

### Usage

When displaying URLs or form values:
```php
// Encrypt an ID for display
$encrypted = encrypt_for_display($id);

// Use in HTML
echo '<option value="' . htmlspecialchars($encrypted) . '">Name</option>';
```

When reading from URL:
```php
// Decrypt from URL parameter
$decrypted = decrypt_from_url($_GET['location_id']);
```

### Security Note

**Important:** Change the encryption key in `database/encryption.php`:

```php
define('ENCRYPTION_KEY', 'your-secret-key-here-change-this-to-random-value');
```

Generate a strong random key for production use.

### Implementation Details

- Uses AES-256-CBC encryption
- Each encryption includes a unique IV (Initialization Vector)
- Base64 encoded for URL-safe transmission
- ID values are encrypted before appearing in URLs
- ID values are decrypted when reading from GET parameters

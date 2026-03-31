# Record Loss Button Fix

## Changes Made

### 1. Fixed .htaccess
Added PT (passthrough) flag to preserve POST method:
```apache
RewriteRule ^ index.php [QSA,L,PT]
```

### 2. Fixed LossWriteoffController Redirects
Changed all redirects from `/losses` to use `BASE_URL`:
```php
header('Location: ' . rtrim(BASE_URL, '/') . '/losses');
```

### 3. Added Debug Logging
Added error logging to `recordMortality()` method to track:
- Request method
- POST data
- Mortality ID
- Success/failure

## Testing Steps

### Step 1: Check Route Configuration
Run this command:
```bash
php test_record_loss_route.php
```

Expected output: All checks should pass ✓

### Step 2: Access Diagnostic Page
Open in browser:
```
http://localhost/farmapp/diagnose_route.php
```

This page will:
- Show your environment configuration
- List all losses routes
- Provide a test form
- Show where to check logs

### Step 3: Test the Button
1. Go to: `http://localhost/farmapp/mortality`
2. Click "Record Loss" button on any mortality record
3. Check what happens:
   - Should redirect to `/farmapp/losses`
   - Should create a loss record

### Step 4: Check Logs
If still getting 404, check these log files:
- `C:\xampp\apache\logs\error.log`
- `C:\xampp\php\logs\php_error_log`

Look for lines containing "recordMortality"

## Common Issues

### Issue 1: Apache Not Restarting
Solution: Restart Apache from XAMPP Control Panel

### Issue 2: Browser Cache
Solution: Hard refresh (Ctrl+Shift+R) or clear browser cache

### Issue 3: .htaccess Not Being Read
Check:
1. Apache config has `AllowOverride All`
2. mod_rewrite is enabled
3. .htaccess file is in the correct location (farmapp root)

### Issue 4: POST Method Not Preserved
The PT flag in .htaccess should fix this. If not:
1. Check Apache version
2. Try adding [P] flag instead of [PT]
3. Check if mod_proxy is enabled

## Verification

After clicking "Record Loss":
1. You should be redirected to `/farmapp/losses`
2. A new loss record should appear in the losses table
3. The mortality record should be marked as recorded

## Debug Output

The controller now logs:
```
recordMortality called - Method: POST
POST data: Array([mortality_id] => 7)
Mortality ID: 7
Record result: success
```

Check PHP error log for these messages.

## Alternative Solution

If the issue persists, we can:
1. Change the button to use GET instead of POST
2. Add the mortality_id as a URL parameter
3. Update the route to handle GET requests

Let me know if you need this alternative approach.

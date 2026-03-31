# URL Access Guide

## Correct URLs

Your application is installed in `/farmapp/` subdirectory, so all URLs must include `/farmapp/`:

### ✓ Correct URLs:
- `http://localhost/farmapp/` - Home/Dashboard
- `http://localhost/farmapp/losses` - Losses page
- `http://localhost/farmapp/feed` - Feed page
- `http://localhost/farmapp/feed/items` - Feed items
- `http://localhost/farmapp/poultry` - Poultry dashboard
- `http://localhost/farmapp/batches` - Batches

### ✗ Wrong URLs (will give 404):
- `http://localhost/losses` ❌
- `http://localhost/feed` ❌
- `http://localhost/poultry` ❌

## Why This Happens

The `.htaccess` file has:
```apache
RewriteBase /farmapp/
```

This means Apache expects all requests to include `/farmapp/` in the path.

## All Available Routes

```
/farmapp/                    - Dashboard
/farmapp/login               - Login
/farmapp/logout              - Logout
/farmapp/poultry             - Poultry Operations
/farmapp/financial           - Financial Dashboard
/farmapp/economic            - Economic Dashboard
/farmapp/sales               - Sales Dashboard
/farmapp/batches             - Batches
/farmapp/feed                - Feed Records
/farmapp/feed/items          - Feed Items Management
/farmapp/mortality           - Mortality Records
/farmapp/vaccination         - Vaccination Records
/farmapp/medication          - Medication Records
/farmapp/egg-production      - Egg Production
/farmapp/weights             - Weight Records
/farmapp/expenses            - Expenses
/farmapp/capital             - Capital
/farmapp/investments         - Investments
/farmapp/liabilities         - Liabilities
/farmapp/losses              - Losses & Write-offs
/farmapp/customers           - Customers
/farmapp/users               - Users
/farmapp/settings            - Settings
/farmapp/reports             - Reports
```

## Testing

To test if a route works, always use the full URL:
```
http://localhost/farmapp/losses
```

Not:
```
http://localhost/losses  ❌
```

## If Still Getting 404

1. Make sure Apache mod_rewrite is enabled
2. Make sure `.htaccess` file exists in `/xampp/htdocs/farmapp/`
3. Make sure you're using the correct URL with `/farmapp/` prefix
4. Clear browser cache
5. Restart Apache

## Quick Test

Open browser and go to:
```
http://localhost/farmapp/losses
```

Should work! ✓

# Railway.app Deployment - Easiest Free Option

Deploy your farm management system to Railway.app in 10 minutes. Works perfectly with PHP + MySQL!

## Why Railway.app?

- ✅ **$5 FREE credit** per month (enough for demo)
- ✅ **One-click deploy** from GitHub
- ✅ **MySQL support** (no conversion needed!)
- ✅ **Automatic SSL**
- ✅ **Super easy** setup
- ✅ **Modern dashboard**

## 📋 Quick Deployment (10 Minutes Total)

### Step 1: Sign Up (1 minute)

1. **Go to**: https://railway.app
2. **Click**: "Login" → "Login with GitHub"
3. **Authorize** Railway to access your repositories
4. **Done!** You're in the dashboard

✅ **Checkpoint**: Logged into Railway dashboard

---

### Step 2: Create New Project (2 minutes)

1. **Click**: "New Project"
2. **Select**: "Deploy from GitHub repo"
3. **Choose**: `farm-management-system`
4. **Click**: "Deploy Now"
5. **Wait**: Railway will try to deploy (it will fail - that's okay!)

✅ **Checkpoint**: Project created

---

### Step 3: Add MySQL Database (2 minutes)

1. **In your project**, click "New"
2. **Select**: "Database" → "Add MySQL"
3. **Wait**: 30 seconds for database creation
4. **Click** on MySQL service
5. **Go to**: "Variables" tab
6. **Copy** these values:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`

✅ **Checkpoint**: MySQL database created

---

### Step 4: Configure Your Service (3 minutes)

1. **Click** on your web service (farm-management-system)
2. **Go to**: "Settings" tab
3. **Add Start Command**:
   ```
   php -S 0.0.0.0:$PORT
   ```
4. **Go to**: "Variables" tab
5. **Add these environment variables**:
   ```
   DB_HOST = (paste MYSQLHOST value)
   DB_PORT = (paste MYSQLPORT value)
   DB_NAME = (paste MYSQLDATABASE value)
   DB_USER = (paste MYSQLUSER value)
   DB_PASS = (paste MYSQLPASSWORD value)
   ```

✅ **Checkpoint**: Service configured

---

### Step 5: Update Config to Use Environment Variables (2 minutes)

We need to update your Config.php to read from Railway's environment variables.

**Update `app/config/Config.php`:**

```php
<?php
// Database Configuration
// Use environment variables if available (Railway), otherwise use local values
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'farmapp_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Base URL - auto-detect or use environment variable
if (getenv('RAILWAY_STATIC_URL')) {
    define('BASE_URL', 'https://' . getenv('RAILWAY_STATIC_URL') . '/');
} else {
    define('BASE_URL', 'http://localhost/farmapp/');
}

// Application Settings
define('APP_NAME', 'Farm Management System');
define('APP_VERSION', '1.0.0');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
```

**Push to GitHub:**

```bash
git add app/config/Config.php
git commit -m "Add Railway environment variable support"
git push origin master
```

✅ **Checkpoint**: Config updated and pushed

---

### Step 6: Import Database (3 minutes)

1. **In Railway**, click on your MySQL database
2. **Go to**: "Data" tab
3. **Click**: "Connect" to get connection command
4. **OR use Railway's built-in query tool**:
   - Click "Query"
   - Copy/paste your SQL from `database/rebuild_complete.sql`
   - Click "Run"
   - Then copy/paste from `database/users.sql`
   - Click "Run"

**Alternative - Use MySQL Workbench:**
1. Download MySQL Workbench
2. Connect using Railway's connection details
3. Import `rebuild_complete.sql`
4. Import `users.sql`

✅ **Checkpoint**: Database populated

---

### Step 7: Generate Domain & Deploy (2 minutes)

1. **Click** on your web service
2. **Go to**: "Settings" tab
3. **Scroll to**: "Networking"
4. **Click**: "Generate Domain"
5. **Copy** your URL (e.g., `farmapp-production.up.railway.app`)
6. **Go to**: "Deployments" tab
7. **Click**: "Redeploy" (if needed)
8. **Wait**: 2-3 minutes for deployment

✅ **Checkpoint**: Service deployed with public URL

---

### Step 8: Test Your Site (1 minute)

1. **Visit**: Your Railway URL
2. **You should see**: Login page
3. **Login**:
   - Username: `admin`
   - Password: `admin123`
4. **Test**: Navigate around, create a batch, etc.

✅ **Checkpoint**: Site is live and working!

---

## 🎉 Deployment Complete!

Your farm management system is now live at:
**https://farmapp-production.up.railway.app**

### What You Get (FREE):

- ✅ $5 credit per month
- ✅ ~500 hours of runtime
- ✅ MySQL database included
- ✅ Automatic SSL
- ✅ Custom domain support
- ✅ Automatic deploys from GitHub

### Usage Monitoring:

- Check your usage in Railway dashboard
- $5/month is enough for:
  - Demo site with moderate traffic
  - Portfolio showcase
  - Testing and development

### If You Need More:

- Add payment method for $5/month minimum
- Or use for demo only (stays within free credit)

---

## Troubleshooting

**If deployment fails:**
1. Check logs in "Deployments" tab
2. Verify environment variables are set
3. Make sure database is running

**If site shows error:**
1. Check database connection in logs
2. Verify SQL files were imported
3. Check that Config.php was updated

**If database import fails:**
1. Use Railway's query tool
2. Or connect via MySQL Workbench
3. Import tables one by one if needed

---

## Update Your GitHub README

Add this to your README.md:

```markdown
## 🌐 Live Demo

**Live Site**: https://farmapp-production.up.railway.app

**Demo Credentials:**
- Username: `admin`
- Password: `admin123`

*Hosted on Railway.app - Free tier*
```

---

## Next Steps

1. ✅ Share your live URL
2. ✅ Add to portfolio
3. ✅ Update LinkedIn
4. ✅ Show to potential employers/clients

**Your site is now live and accessible to anyone!**

Need help with any step? Let me know!

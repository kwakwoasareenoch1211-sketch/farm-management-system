# Login System Setup

## Database Setup

Run this SQL file to create the users table:

```bash
mysql -u root farmapp_db < database/users.sql
```

Or manually execute the SQL in phpMyAdmin/MySQL Workbench.

## Default Credentials

- **Username:** admin
- **Password:** admin123

## How It Works

1. **Router** (`app/core/Router.php`) - Checks if route requires authentication
2. **Auth** (`app/core/Auth.php`) - Handles session management and auth checks
3. **User Model** (`app/models/User.php`) - Authenticates users against database
4. **AuthController** (`app/controllers/AuthController.php`) - Handles login/logout
5. **Login View** (`app/views/auth/login.php`) - Beautiful login form

## Protected Routes

All routes except `/login` and `/` (POST) require authentication. Unauthenticated users are redirected to `/login?error=required`.

## Testing

1. Visit: `http://localhost/farmapp/`
2. You'll be redirected to login
3. Enter: admin / admin123
4. You'll be redirected to admin dashboard

## Security Features

✅ Password hashing with bcrypt  
✅ Session regeneration on login (prevents session fixation)  
✅ Prepared statements (prevents SQL injection)  
✅ Active user check  
✅ Last login tracking

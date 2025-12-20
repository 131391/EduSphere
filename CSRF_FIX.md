# CSRF 419 Error Fix Guide

## Issue
Getting "419 PAGE EXPIRED" error even on GET requests to `/login`.

## Possible Causes

1. **Session Cookie Not Being Set**
   - Browser blocking cookies
   - Domain mismatch (127.0.0.1 vs localhost)
   - Session driver not working

2. **CSRF Token Mismatch**
   - Token expired
   - Session not persisting
   - Multiple tabs with different sessions

3. **Browser Cache**
   - Old page cached with expired token
   - Form being auto-submitted

## Solutions

### Solution 1: Clear Browser Data
1. Open browser DevTools (F12)
2. Go to Application/Storage tab
3. Clear all cookies for `127.0.0.1`
4. Clear cache
5. Hard refresh (Ctrl+F5)

### Solution 2: Use localhost instead of 127.0.0.1
Try accessing: `http://localhost:8000/login` instead of `http://127.0.0.1:8000/login`

### Solution 3: Check Session Configuration
Verify in `.env`:
```
SESSION_DRIVER=database
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
```

### Solution 4: Test Session
```bash
php8.2 artisan tinker
>>> session(['test' => 'value']);
>>> session('test');
```

### Solution 5: Temporary CSRF Bypass (Development Only)
In `app/Http/Middleware/VerifyCsrfToken.php`, temporarily add:
```php
protected $except = [
    'login',  // Only for testing!
];
```

**⚠️ Remove this in production!**

### Solution 6: Check if Sessions Table Has Data
```bash
php8.2 artisan tinker
>>> DB::table('sessions')->count();
```

If count is 0, sessions aren't being saved.

## Quick Test

1. Open browser in **Incognito/Private mode**
2. Go to: `http://localhost:8000/login` (not 127.0.0.1)
3. Check browser console for errors
4. Check Network tab - look for session cookie being set

## If Still Not Working

Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

Then try to login and see what errors appear.


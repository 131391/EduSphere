# CSS/Assets Not Loading - Fix Guide

## Problem
CSS and JavaScript files are showing 404 errors because Vite dev server is not running.

## Solution

### Option 1: Run Vite Dev Server (Recommended for Development)

```bash
# Terminal 1: Start Laravel server
php8.2 artisan serve

# Terminal 2: Start Vite dev server
npm run dev
```

Vite will run on `http://localhost:5173` and serve your assets with hot reload.

### Option 2: Build Assets for Production

If you don't want to run the dev server, build the assets:

```bash
npm run build
```

This creates compiled assets in `public/build/` that work without Vite running.

### Option 3: Use CDN for Tailwind (Quick Fix)

If you just want to see the page working quickly, you can temporarily use Tailwind CDN:

```blade
<!-- In layouts/app.blade.php, replace @vite with: -->
<script src="https://cdn.tailwindcss.com"></script>
```

## Verify It's Working

1. Check browser console - no 404 errors
2. Styles should be applied (blue buttons, proper spacing)
3. Page should look styled, not plain HTML

## Common Issues

### Issue: "Vite manifest not found"
**Solution**: Run `npm run dev` or `npm run build`

### Issue: "Connection refused to localhost:5173"
**Solution**: Make sure Vite dev server is running (`npm run dev`)

### Issue: Tailwind classes not working
**Solution**: 
1. Make sure `npm run dev` is running
2. Check `tailwind.config.js` includes your view files
3. Verify `resources/css/app.css` has Tailwind directives


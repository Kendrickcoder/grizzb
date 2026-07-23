# Pharmacy Management System — Split Frontend/Backend

Your hosting doesn't run PHP, so this version is split into two independent
pieces that talk to each other over the internet:

```
frontend/   → pure HTML/CSS/JS. Upload this to your current static host.
backend/    → PHP + MySQL API. Needs a DIFFERENT host that supports PHP.
```

This split is unavoidable, not a workaround: a static host can never
connect to a MySQL database directly (browsers can't hold DB credentials
safely). The backend has to run somewhere with PHP support.

## 1. Get a PHP + MySQL host for the backend

Any of these work — you just need PHP 8+ and a MySQL database:
- A cheap shared host (Hostinger, Namecheap, etc.)
- A free-tier PHP host (InfinityFree, 000webhost)
- A small VPS (DigitalOcean, etc.)

## 2. Set up the database

Run `backend/sql/schema.sql` against it (phpMyAdmin, your host's SQL tool, or):
```bash
mysql -h YOUR_DB_HOST -u YOUR_DB_USER -p YOUR_DB_NAME < backend/sql/schema.sql
```

Create your first admin account (registration only ever creates staff accounts):
```bash
php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"
```
```sql
INSERT INTO users (fullname, username, password, role)
VALUES ('Admin User', 'admin', 'PASTE_THE_HASH_HERE', 'admin');
```

## 3. Deploy the backend

Upload the `backend/` folder's contents to your PHP host. Set these
environment variables there (hosting panel → Environment Variables, or
an `.htaccess` with `SetEnv`):
```
DB_HOST=your-db-host
DB_PORT=3306
DB_NAME=pharmacy
DB_USER=your_db_user
DB_PASS=your_db_password
```
Confirm it's live by visiting `https://your-backend-domain.com/api/me.php`
in a browser — you should get back `{"error":"Not logged in"}` (that's
correct, it means the API is running).

## 4. Point the frontend at your backend

Open `frontend/assets/js/api.js` and edit the first line:
```js
const API_BASE = "https://your-backend-domain.com/api";
```
That's the only code change needed — every page uses this one file.

## 5. Deploy the frontend

Upload everything in `frontend/` to your static host (drag-and-drop,
FTP, git deploy — whatever your host uses for static files). Visit your
site — it should redirect to `login.html`.

## How auth works across two domains

Login returns a token, stored in the browser's `localStorage`. Every
API call sends it as `Authorization: Bearer <token>`. We use this
instead of cookies because cookies don't reliably cross two different
domains (browsers increasingly block third-party cookies) — a header
token has no such problem. Both sites should be served over HTTPS.

## If something doesn't connect

- Blank page / network error in the browser console → check
  `API_BASE` in `api.js` matches your backend's real URL exactly
  (including `https://` and no trailing slash).
- CORS error in the console → your backend host may be stripping
  headers; confirm `includes/cors.php` is being reached (view
  `api/me.php` directly in a browser to test).
- "Database connection failed" → double check the `DB_*` environment
  variables are actually set on the backend host, and that your DB
  host allows remote/external connections if the app and DB aren't on
  the same server.

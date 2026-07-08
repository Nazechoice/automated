# Setup Guide (Automobile Management System)

## 1) Create DB
- Create a MySQL database user with permissions.
- Import `setup.sql` into MySQL.

**Default database name:** `automates`

## 2) Configure app
Edit:
- `app/config/config.php`

Set:
- `db.host`
- `db.name`
- `db.user`
- `db.pass`

## 3) Web entry
Point your web server document root to:
- `public/`

If using XAMPP, ensure `public/index.php` is accessible.

## 4) Admin login (first run)
- Email: `admin@automates.local`
- Password: **Admin@1234** (the initial seed hash may require password reset depending on environment)

## 5) Recommended: enable mod_rewrite
For clean URLs, enable Apache rewrite and configure `public/.htaccess`.

## 6) Seed additional sample data
After first login, use admin Vehicle Management to add vehicles/categories.


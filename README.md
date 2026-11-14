# Point-of-Sale System

A CodeIgniter 4 point-of-sale application for managing products, customers, staff accounts, inventory, and sales.

## Features

- Session-based staff authentication and protected management routes
- Product CRUD with stock tracking and validated image uploads
- Customer and staff account management
- Secure password hashing and duplicate-username prevention
- Transactional sales with row locking and automatic stock deduction
- Walk-in or registered-customer sales
- Sales history and dashboard summaries
- Complete customer portal with registration and login
- Customer product catalog, cart, checkout, and order history
- Staff-facing online order fulfillment workflow
- Database-backed sessions for reliable authentication and carts across multiple instances
- Database-backed authentication throttling shared across instances
- CSRF-protected forms and escaped view output

## Technology

- PHP 8.2+
- CodeIgniter 4
- MySQL or MariaDB
- HTML and CSS
- Composer

## Local installation

```powershell
composer install
Copy-Item env .env
```

Edit `.env` and configure these values for your machine:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
database.default.hostname = localhost
database.default.database = pos_system
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
seed.adminPassword = 'replace-with-a-unique-development-password'
```

Create the database, then initialize it:

```powershell
php spark migrate
php spark db:seed DefaultDataSeeder
```

Run the development server:

```powershell
php spark serve
```

Open `http://localhost:8080`.

Customer storefront: `http://localhost:8080/shop`

Product catalog: `http://localhost:8080/shop/products`

Customers create their own accounts from the storefront. Portal checkout currently uses cash on pickup.

## Development login

The seeded username is `admin`. Its password is the value you set in `seed.adminPassword` before running the seeder. The seeder rejects passwords shorter than 12 characters. Never reuse this development password in production.

## Uploads

Product images are stored in `public/uploads/products`. Staff avatars are stored in `public/uploads/avatars`. Both directories must be writable by the web server. Uploads are limited to 2 MB and validated as JPG, PNG, or WEBP images.

For Apache/XAMPP deployment, configure the document root to the project's `public` directory so application source and environment files are not web-accessible.

## Verification commands

```powershell
php spark migrate:status
php spark routes
composer test
```

## End-to-end verification

The E2E workflow uses a disposable database whose name must end in `_e2e`. It verifies staff authentication, product image storage, customer creation, transactional sales, customer registration, checkout, order fulfillment and cancellation, CSRF rejection, and database sessions.

Create and initialize the disposable database in PowerShell:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "DROP DATABASE IF EXISTS pos_system_e2e; CREATE DATABASE pos_system_e2e CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

$env:E2E_APP_BASE_URL = 'http://127.0.0.1:8099/'
$env:E2E_DB_HOST = '127.0.0.1'
$env:E2E_DB_DATABASE = 'pos_system_e2e'
$env:E2E_DB_USERNAME = 'root'
$env:E2E_DB_PASSWORD = ''
$env:E2E_DB_DRIVER = 'MySQLi'
$env:E2E_DB_PORT = '3306'
$env:E2E_ADMIN_PASSWORD = 'E2E-Admin-Password-123!'
$env:E2E_MEDIA_STORAGE = 'database'

php spark migrate --all
php -d auto_prepend_file=tests/e2e/bootstrap_env.php spark db:seed DefaultDataSeeder
php -S 127.0.0.1:8099 tests/e2e/router.php
```

In a second terminal with `E2E_APP_BASE_URL` set, run:

```powershell
php tests/e2e/run.php
C:\xampp\mysql\bin\mysql.exe -u root -e "DROP DATABASE IF EXISTS pos_system_e2e;"
```

Never point this workflow at development or production data.

## Railway deployment

The root `Dockerfile` builds a production FrankenPHP/Caddy image, installs Composer dependencies without development packages, serves the `public` directory, listens on Railway's `$PORT`, and makes `writable` accessible. Sessions and uploaded media are stored in MySQL so they survive stateless container redeploys.

### 1. Create Railway services

1. Create a Railway project.
2. Add a MySQL service using Railway's MySQL template.
3. Add an application service from this GitHub repository.
4. Confirm the application service detects the root `Dockerfile`.

### 2. Configure application variables

Add these variables to the application service. Replace `MySQL` in each reference if the database service has a different name.

```ini
CI_ENVIRONMENT=production
MYSQLHOST=${{MySQL.MYSQLHOST}}
MYSQLPORT=${{MySQL.MYSQLPORT}}
MYSQLUSER=${{MySQL.MYSQLUSER}}
MYSQLPASSWORD=${{MySQL.MYSQLPASSWORD}}
MYSQLDATABASE=${{MySQL.MYSQLDATABASE}}
cookie_secure=true
media_storage=database
```

The application maps the Railway `MYSQL*` variables to CodeIgniter's MySQLi connection. Do not use the public database URL from inside Railway.

After generating a Railway domain, `RAILWAY_PUBLIC_DOMAIN` is used automatically. For a custom domain, explicitly set:

```ini
app_baseURL=https://your-domain.example/
```

Do not upload the local `.env` file or copy local XAMPP credentials into Railway.

### 3. Configure deployment settings

In the application service settings, configure:

- Builder: Dockerfile
- Dockerfile path: `Dockerfile`
- Start command: leave empty so the Docker `CMD` is used
- Pre-deploy command: `php spark migrate --all`
- Pre-deploy timeout: 300 seconds
- Healthcheck path: `/health`
- Healthcheck timeout: 300 seconds
- Restart policy: On Failure

The pre-deploy migration runs inside Railway's private network and prevents a release from starting if a migration fails. Do not run `DefaultDataSeeder` in production.

### 4. Deploy and verify

Deploy from the Railway dashboard or with the CLI:

```powershell
railway login
railway link
railway up
railway logs
```

Verify the generated domain and health endpoint:

```text
https://your-domain.example/health
```

The endpoint returns `200` only when the application can connect to MySQL.

### 5. Provision the first administrator

Temporarily add `ADMIN_USERNAME`, `ADMIN_FULL_NAME`, and a sealed `ADMIN_PASSWORD` variable to the application service. The password must contain 12-72 characters. Then run inside the deployed container:

```powershell
railway ssh -- php spark admin:create
```

The command refuses weak passwords and duplicate usernames. Remove the three temporary administrator variables immediately afterward. Never run the sample-data seeder in production.

### 6. Production verification

After deployment, verify:

- staff login and logout;
- protected-route redirects;
- product, customer, and staff CRUD;
- image and avatar uploads;
- sale stock deduction and insufficient-stock rejection;
- customer registration, cart, and checkout;
- order status transitions and cancellation stock restoration;
- database-backed sessions across a redeploy;
- `/health` and Railway logs.

Use Railway deployment rollback for application regressions. Database migrations must be reviewed separately before manually running a migration rollback.

The `.env` file is ignored by Git and Docker and must never be committed. Production secrets belong only in Railway service variables.

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

Customers create their own accounts from the storefront. Portal checkout currently uses cash on pickup.

## Development login

- Username: `admin`
- Password: `admin123`

Change this password before using the system outside local development.

## Uploads

Product images are stored in `public/uploads/products`. Staff avatars are stored in `public/uploads/avatars`. Both directories must be writable by the web server. Uploads are limited to 2 MB and validated as JPG, PNG, or WEBP images.

For Apache/XAMPP deployment, configure the document root to the project's `public` directory so application source and environment files are not web-accessible.

## Verification commands

```powershell
php spark migrate:status
php spark routes
composer test
```

The `.env` file is ignored by Git and must never be committed. Production deployments should use HTTPS, a non-development environment, strong database credentials, and a changed administrator password.

You are helping me build a complete Point-of-Sale System using CodeIgniter 4, PHP, MySQL, HTML, CSS, and JavaScript.

## Verified progress (2026-11-12)

- [x] CodeIgniter 4 project and Composer dependencies installed.
- [x] Environment and MySQL connection configured locally.
- [x] Users, products, customers, and sales migrations executed.
- [x] Default administrator, products, and customers seeded.
- [x] Models, authentication filter, explicit routes, and reusable layout created.
- [x] Authentication, dashboard, product, customer, staff, and sales controllers/views created.
- [x] CSRF enabled and state-changing logout/delete routes restricted to POST.
- [x] Product and avatar uploads validate size, extension, MIME type, and image content.
- [x] Sale writes and stock deductions use a transaction with row locking.
- [x] Deletion of records referenced by sales is handled safely.
- [x] PHP syntax, route table, database migrations, and seed data verified.
- [x] Project-specific README and baseline unit tests added.
- [x] Sales history search and pagination added.
- [x] Field-level validation feedback and structured phone validation added.
- [x] Upload directories hardened against PHP/script execution under Apache.
- [x] Login and authenticated sales search verified through local HTTP requests.
- [x] Customer registration and authentication portal added.
- [x] Public product catalog and session cart added.
- [x] Transactional customer checkout with stock locking added.
- [x] Customer order history and order details added.
- [x] Staff online-order fulfillment module added.
- [x] Customer profile and password management added.
- [x] Professional customer landing page and dedicated catalog added.
- [x] Customer portal verified end to end with stock restoration after test cleanup.
- [ ] Complete browser-based CRUD, authentication, upload, and sales workflow testing.
- [ ] Add broader automated feature and database tests.


Project requirements:

1. Product Management
- Add products
- View product list
- Edit products
- Delete or archive products
- Fields: name, price, stock quantity, product image, created date
- Validate all fields
- Allow only safe image uploads
- Store uploaded files with generated filenames
- Display product images properly

2. Customer Management
- Add customers
- View customers
- Edit customers
- Delete customers
- Fields: full name, email, phone, created date
- Validate names, email addresses, and phone numbers

3. Staff/User Management
- Add staff accounts
- View staff accounts
- Edit staff accounts
- Delete staff accounts
- Fields: username, full name, password, avatar, created date
- Store passwords using password_hash()
- Never display plain-text passwords
- Allow safe avatar uploads
- Prevent duplicate usernames

4. Authentication
- Create a login page
- Authenticate users using username and password
- Use PHP password_verify()
- Store the logged-in user ID and name in the session
- Protect all management pages using an authentication filter
- Redirect unauthenticated users to the login page
- Create logout functionality
- Prevent users from accessing protected pages after logout

5. Record Sale
- Create a sales page
- A logged-in staff member selects a product
- The staff member may optionally select a customer
- The staff member enters a quantity
- Calculate total price automatically or on the server
- Check if the requested quantity is greater than available stock
- Reject the sale with a clear error message if stock is insufficient
- Decrease product stock after a successful sale
- Record product, customer, staff member, quantity, total price, and date
- Use a database transaction so the sale and stock deduction succeed or fail together

6. Sales History
- Display all previous sales
- Show product name
- Show customer name or “Walk-in Customer”
- Show staff member name
- Show quantity
- Show total price
- Show sale date
- Add pagination or search if practical

Database tables:

products:
- id INT AUTO_INCREMENT PRIMARY KEY
- name VARCHAR(100) NOT NULL
- price DECIMAL(10,2) NOT NULL
- stock_quantity INT NOT NULL DEFAULT 0
- image VARCHAR(255) NULL
- created_at DATETIME NOT NULL
- updated_at DATETIME NULL

customers:
- id INT AUTO_INCREMENT PRIMARY KEY
- full_name VARCHAR(100) NOT NULL
- email VARCHAR(100) NOT NULL
- phone VARCHAR(20) NULL
- created_at DATETIME NOT NULL
- updated_at DATETIME NULL

users:
- id INT AUTO_INCREMENT PRIMARY KEY
- username VARCHAR(50) NOT NULL UNIQUE
- full_name VARCHAR(100) NOT NULL
- password VARCHAR(255) NOT NULL
- avatar VARCHAR(255) NULL
- created_at DATETIME NOT NULL
- updated_at DATETIME NULL

sales:
- id INT AUTO_INCREMENT PRIMARY KEY
- product_id INT NOT NULL
- customer_id INT NULL
- sold_by INT NOT NULL
- quantity INT NOT NULL
- total_price DECIMAL(10,2) NOT NULL
- created_at DATETIME NOT NULL
- updated_at DATETIME NULL

Relationships:
- sales.product_id references products.id
- sales.customer_id references customers.id
- sales.sold_by references users.id
- customer_id must be nullable because a sale may be made to a walk-in customer

Important security requirements:

- Use CodeIgniter 4 MVC architecture.
- Use explicit routes.
- Do not use unsafe SQL string concatenation.
- Use CodeIgniter Models and Query Builder.
- Validate all submitted data on the server.
- Escape output in views.
- Use CSRF protection for forms.
- Use authentication filters for protected routes.
- Validate uploaded files using image validation, MIME validation, extension validation, and file-size validation.
- Store uploaded files using generated random names.
- Do not allow uploaded files to execute as PHP.
- Do not expose database passwords or secrets in GitHub.
- Use .env for database credentials.
- Add .env to .gitignore.
- Use database transactions for recording sales.
- Use CodeIgniter’s built-in migration and seeding system.
- Avoid putting business logic directly inside views.
- Keep controllers, models, filters, and views organized.

Recommended project structure:

app/
├── Config/
│   ├── Database.php
│   ├── Filters.php
│   └── Routes.php
├── Controllers/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── ProductsController.php
│   ├── CustomersController.php
│   ├── UsersController.php
│   └── SalesController.php
├── Filters/
│   └── AuthFilter.php
├── Models/
│   ├── ProductModel.php
│   ├── CustomerModel.php
│   ├── UserModel.php
│   └── SaleModel.php
├── Database/
│   ├── Migrations/
│   └── Seeds/
└── Views/
    ├── layouts/
    │   └── main.php
    ├── auth/
    │   └── login.php
    ├── dashboard/
    │   └── index.php
    ├── products/
    ├── customers/
    ├── users/
    └── sales/

public/
├── uploads/
│   ├── products/
│   └── avatars/
└── assets/

Development phases:

PHASE 1 — Project Setup
1. Check whether PHP, Composer, and MySQL are installed.
2. Create a new CodeIgniter 4 project using Composer.
3. Configure the .env file.
4. Configure the application base URL.
5. Configure the MySQL database connection.
6. Start the development server.
7. Confirm that the default CodeIgniter page loads correctly.
8. Initialize Git.
9. Create a proper .gitignore file.
10. Create a README.md file.

PHASE 2 — Database
1. Create migrations for users, products, customers, and sales.
2. Add primary keys, indexes, timestamps, and foreign keys.
3. Make customer_id nullable in the sales table.
4. Create seeders.
5. Add one default staff account using a securely hashed password.
6. Add sample products and customers.
7. Run the migrations.
8. Run the seeders.
9. Confirm that all tables and relationships work.

PHASE 3 — Models
Create the following models:
- ProductModel
- CustomerModel
- UserModel
- SaleModel

Each model must:
- Define the correct table name
- Define the primary key
- Define allowedFields
- Enable timestamps where appropriate
- Use validation rules
- Avoid exposing password fields unnecessarily

Create useful methods such as:
- getAvailableProducts()
- getSalesWithDetails()
- getProductStock()
- getDashboardTotals()

PHASE 4 — Layout and UI
1. Create a reusable main layout.
2. Add a navigation bar.
3. Add sidebar or navigation links for:
   - Dashboard
   - Products
   - Customers
   - Staff
   - Record Sale
   - Sales History
   - Logout
4. Display the logged-in user’s name.
5. Add success and error flash messages.
6. Make the interface responsive.
7. Use a clean POS design.
8. Do not place database queries inside views.

PHASE 5 — Authentication
1. Create AuthController.
2. Create login view.
3. Validate username and password.
4. Query the user by username.
5. Verify the password using password_verify().
6. Store the user ID and full name in the session.
7. Create logout.
8. Create AuthFilter.
9. Apply AuthFilter to all protected pages.
10. Test login, invalid login, logout, and direct access to protected routes.

PHASE 6 — Product Management
Create:
- Product list page
- Add product page
- Edit product page
- Delete or archive action

Validation:
- Name is required and maximum 100 characters
- Price must be numeric and greater than zero
- Stock quantity must be an integer and cannot be negative
- Image must be optional during editing
- Image must be a valid JPG, JPEG, PNG, or WEBP file
- Limit image size
- Generate a random filename
- Remove the old image when replacing or deleting it when appropriate

PHASE 7 — Customer Management
Create:
- Customer list
- Add customer
- Edit customer
- Delete customer

Validation:
- Full name is required
- Email must be valid
- Phone is optional
- Use server-side validation
- Display validation errors beside the form fields

PHASE 8 — Staff Management
Create:
- Staff list
- Add staff
- Edit staff
- Delete staff

Rules:
- Username must be unique
- Password is required when creating an account
- Password may remain unchanged during editing
- Password must be hashed before saving
- Avatar upload must be validated safely
- Do not display passwords
- Prevent deleting the currently logged-in user unless there is a safe confirmation or restriction

PHASE 9 — Sales Workflow
Create:
- Record sale form
- Sales history page
- Sale details query

Record sale process:
1. Confirm that the user is logged in.
2. Validate the selected product.
3. Validate the optional customer.
4. Validate that quantity is a positive integer.
5. Start a database transaction.
6. Retrieve the product.
7. Check the current stock.
8. Reject the transaction if quantity is greater than stock.
9. Calculate total_price as product price multiplied by quantity.
10. Insert the sale record.
11. Decrease the product stock.
12. Complete the transaction.
13. Show a success message.
14. Roll back if any database operation fails.

Important:
- Do not trust price values submitted by the browser.
- Always retrieve the product price from the database.
- Do not allow zero or negative quantities.
- Do not allow selling more than available stock.
- Use database transactions.

PHASE 10 — Dashboard
Display:
- Total products
- Total customers
- Total staff
- Total sales
- Total sales amount
- Low-stock products
- Recent sales

PHASE 11 — Validation and Security Testing
Test:
- Empty form submissions
- Invalid email
- Negative price
- Negative stock
- Invalid image files
- Oversized files
- Duplicate usernames
- Wrong login credentials
- Accessing protected pages while logged out
- Selling zero quantity
- Selling negative quantity
- Selling more than available stock
- Deleting records connected to sales
- CSRF-protected forms
- XSS-safe output
- SQL injection-safe queries

PHASE 12 — Documentation and Deployment
Create a README.md containing:
- Project title
- Project description
- Features
- Technologies used
- System requirements
- Installation instructions
- Database setup instructions
- Migration and seeder commands
- Default login credentials for development only
- Upload folder instructions
- Local development instructions
- Deployment instructions
- Security notes
- Screenshots section
- GitHub repository information

Before finishing:
- Run all migrations successfully.
- Run all seeders successfully.
- Test every route.
- Test all CRUD operations.
- Test the complete sale workflow.
- Test stock deduction.
- Test insufficient stock rejection.
- Test login protection.
- Fix all errors and warnings.
- Make sure the project can be cloned and installed on another computer.
- Make sure no passwords or private configuration files are committed.
- Give me the exact commands used.
- Explain every file created or modified.
- Do not skip required files.
- If a decision is unclear, ask me before making a major architectural change.
- Work in small phases and verify each phase before continuing.

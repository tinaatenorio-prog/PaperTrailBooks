# Paper Trail Books

PGM3002 Cloud Computing — Assessment 3 group project.

## Current development status

The database-backed catalogue, title/author search, session-based shopping
cart, customer registration, login and logout have been tested locally
through XAMPP.

Customer registration, login and logout were merged into main through
pull request #2 (merge commit 7277ae4).

Post-merge PHP syntax checks and browser checks passed locally on
19 November 2026. Browser checks covered catalogue display and title
search, login, logout, cart preservation through login/logout, and
registration-page loading. They did not create another account.

Verified local catalogue behaviours:
- Display six fictional demonstration books.
- Search by author.
- Search by title.
- Display a message when no books match.
- Return to the full catalogue using Show all books.

Verified local shopping-cart behaviours:
- Add available books from the catalogue.
- Update book quantities.
- Calculate book subtotals and the cart book total.
- Reject quantities exceeding available stock without changing the saved cart.
- Remove individual books.
- Display an empty-cart message after the last book is removed.
- Display a disabled Out of stock button for an unavailable book.

The shopping-cart changes were merged into main through pull request #1.
Post-merge cart and catalogue checks passed locally on 18 November 2026.

The cart is a demonstration only. Adding a book does not reserve stock.
No orders or payments are processed.

Verified local customer-account behaviours:
- Register a fictional customer and store a password hash.
- Assign the customer role during public registration.
- Reject a duplicate email without creating another account.
- Reject mismatched passwords without creating another account.
- Reject an incorrect login password.
- Sign in with the registered customer's correct credentials.
- Display the signed-in customer's name and role on the login page.
- Sign out using the Logout button.
- Remain signed out when reopening the login page after logout.
- Preserve the session cart through login and logout.

These account checks passed locally on 18 November 2026.
Login and logout do not process orders or payments.

The presence of other pages in this repository does not establish that
their functionality is complete or tested.

AWS deployment and cloud testing are not yet verified.

## Local setup using XAMPP on Windows

These instructions describe a fresh setup. If the catalogue already works
on your machine, do not reimport the database or replace your configuration.

### 1. Prepare the project

Place the project in:

C:\xampp\htdocs\PaperTrailBooks

Start Apache and MySQL using the XAMPP Control Panel.

### 2. Prepare the database

1. Open http://localhost/phpmyadmin/
2. Create an empty database named paper_trail_books using
   utf8mb4_unicode_ci collation.
3. Select the paper_trail_books database.
4. Open the Import tab.
5. Select database/paper_trail_books.sql and run the import.

The SQL file creates the books table and inserts six fictional records.
It does not create the database or a database user.

Import into an empty database. If a books table already exists, stop and
check the existing database rather than deleting or overwriting it.

### 3. Configure the local connection

Copy config.example.php to a new file named config.local.php
in the project root.

Keep config.example.php unchanged as a placeholder-only template.

In config.local.php, enter your own:
- Database host.
- Database port.
- Database name.
- Database username.
- Database password.

The template uses 127.0.0.1 and port 3306. Adjust these if your local
database service uses different settings.

db.php reads config.local.php. The example file is not loaded directly.

Never commit actual credentials. config.local.php is excluded by .gitignore.

### 4. Open the catalogue

Visit:

http://localhost/PaperTrailBooks/catalogue.php

These addresses assume the default Apache HTTP port and the folder
name shown above. Adjust the address if your local configuration differs.

## Customer-account database setup

These additional steps apply to both a fresh setup and an existing
catalogue database. Do not reimport database/paper_trail_books.sql into
an existing working database.

### 1. Back up and apply the migration

1. Export the whole paper_trail_books database through phpMyAdmin.
2. Save the SQL backup outside the Git repository.
3. Check that the backup contains the books table definition and its data.
4. Select paper_trail_books in phpMyAdmin.
5. Check whether a users table already exists. If it does, stop and inspect
   the existing schema; do not delete it or rerun this migration.
6. Import database/migrations/001_create_users.sql.
7. Confirm that books is unchanged and users has the expected columns.

The migration creates users only. It does not insert customer or
administrator accounts, modify books, or grant database permissions.

### 2. Check application database permissions

The PHP application uses the database account configured locally in
config.local.php. This database account is separate from the customer
and administrator roles stored in users.

The current features require:
- SELECT on books for the catalogue and search.
- SELECT on users for login.
- INSERT on users for registration.

The tested local account is papertrail_app@localhost. It already had
SELECT on paper_trail_books.*. Registration required this additional
table-scoped grant, executed through a database-administration connection:

```sql
GRANT INSERT
ON `paper_trail_books`.`users`
TO 'papertrail_app'@'localhost';
```

This example assumes that the account already exists and has the
required SELECT permissions. For another installation, identify the
actual application account and host before applying grants.

Do not switch the application's connection to root or grant
ALL PRIVILEGES to bypass a permission error. Current features do not
require UPDATE, DELETE, CREATE, ALTER or DROP permissions.

Review database permissions before testing any new database-backed
feature.

### 3. Create and use a fictional account

Open:

http://localhost/PaperTrailBooks/register.php

Register fictional details using an example.com email address and a
test-only password. For this local demonstration, use 15-72 ASCII
characters for the password. Never reuse a real password.

Registration creates a customer account but does not sign the customer in.

Open:

http://localhost/PaperTrailBooks/login.php

After successful login, this page displays the signed-in customer's
name, role and Logout button. Logout preserves the demonstration cart.

No pre-created account or shared password is supplied by the migration.

## Basic local checks

| Action | Expected result |
|---|---|
| Open the catalogue | Six books displayed |
| Search for Maya | Two books by Maya Bennett |
| Search for Lantern | The Lantern Bookshop only |
| Search for zzznomatch | No-results message and no book cards |
| Click Show all books | Empty search field and all six books |
| Add The Lantern Bookshop and set quantity to 2 | Book subtotal AUD $49.90 |
| Add Gardens of Tomorrow once as well | Cart book total AUD $71.90 |
| Change The Lantern Bookshop quantity to 13 | Stock-limit message; saved quantity stays at 2 and total stays AUD $71.90 |
| Remove The Lantern Bookshop | Gardens of Tomorrow remains; total AUD $22.00 |
| Remove the final book | Your cart is empty message |
| View Learning PHP Step by Step in the catalogue | Disabled Out of stock button |
| Register a new fictional customer | Success message; one new users row with role customer and a password hash |
| Register the same email again | Duplicate-email message; account count unchanged |
| Register with different password and confirmation values | Password-mismatch message; account count unchanged |
| Log in with an incorrect password | Invalid email or password message; not signed in |
| Log in with correct customer credentials | Signed-in name, customer role and Logout button displayed |
| Check a populated cart after login | Same titles, quantities and total |
| Click Logout and reopen login.php | Login form displayed; no signed-in identity |
| Check the cart after logout | Same titles, quantities and total |

To check PHP syntax from the project folder in PowerShell:

```powershell
C:\xampp\php\php.exe -l catalogue.php
C:\xampp\php\php.exe -l db.php
C:\xampp\php\php.exe -l config.example.php
C:\xampp\php\php.exe -l cart-session.php
C:\xampp\php\php.exe -l cart.php
C:\xampp\php\php.exe -l auth.php
C:\xampp\php\php.exe -l register.php
C:\xampp\php\php.exe -l login.php
C:\xampp\php\php.exe -l logout.php
```

Syntax checks do not replace functional testing.

## Data and deployment limitations

- All book records are fictional demonstration data.
- Displayed prices are in AUD.
- Do not add real customer or payment information.
- These are local development instructions, not an AWS deployment guide.
- Cloud configuration, credentials and testing must be addressed separately.
- Customer accounts must contain fictional details and test-only passwords.
- Administrator book management remains a static demonstration; a stored
  role does not establish implemented administrator authorisation.
- Profile editing, password reset, email verification, account deletion
  and login throttling are not implemented.
- Customer order history, checkout and payment processing are not implemented.
- Sessions currently use local HTTP settings. HTTPS cookie settings and
  shared session storage must be reviewed before cloud deployment.
- Authentication is an assessment prototype, not a production-ready service.
- Customer-account functionality has not been tested on AWS.
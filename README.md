# Paper Trail Books

PGM3002 Cloud Computing — Assessment 3 group project.

## Current development status

The database-backed catalogue and title/author search have been tested
locally through XAMPP.

Verified local behaviours:
- Display six fictional demonstration books.
- Search by author.
- Search by title.
- Display a message when no books match.
- Return to the full catalogue using Show all books.

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

## Basic local checks

| Action | Expected result |
|---|---|
| Open the catalogue | Six books displayed |
| Search for Maya | Two books by Maya Bennett |
| Search for Lantern | The Lantern Bookshop only |
| Search for zzznomatch | No-results message and no book cards |
| Click Show all books | Empty search field and all six books |

To check PHP syntax from the project folder in PowerShell:

```powershell
C:\xampp\php\php.exe -l catalogue.php
C:\xampp\php\php.exe -l db.php
C:\xampp\php\php.exe -l config.example.php
```

Syntax checks do not replace functional testing.

## Data and deployment limitations

- All book records are fictional demonstration data.
- Displayed prices are in AUD.
- Do not add real customer or payment information.
- These are local development instructions, not an AWS deployment guide.
- Cloud configuration, credentials and testing must be addressed separately.
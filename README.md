# Shelton Tool-Hire

Shelton Tool-Hire is a PHP-based web application for managing a tool rental business. It provides a customer-facing catalogue, rental booking flow, review submission, and an admin panel for managing inventory and moderating reviews.

## Project overview

This project is a prototype built for a small tool hire company. It allows users to:

- Browse available tools in a catalogue
- View tool details and pricing
- Use a rental cost calculator for date-based pricing
- Register and log in
- Submit reviews for tools
- View their rental history

Administrators can:

- Manage tools and inventory
- Review and approve or reject customer reviews
- View dashboard statistics
- Manage rental status

## Features

### Customer-facing features

- Homepage with featured tools
- Tool catalogue with search and filtering
- Tool detail pages
- Rental calculator
- Registration and login
- Review submission
- My rentals page

### Admin features

- Admin login
- Dashboard with summary statistics
- Manage tools
- Manage rentals
- Moderate reviews

## Tech stack

- PHP 8+
- MySQL / MariaDB
- HTML, CSS, Bootstrap 5
- PDO for database access
- Session-based authentication
- Password hashing using `password_hash()`

## Project structure

- `admin/` – admin-only pages and management workflows
- `assets/` – CSS and static assets
- `config/` – database connection settings
- `docs/` – system design and testing documentation
- `includes/` – shared header, footer, and helper functions
- `sql/` – database schema and seed data
- root PHP files – public pages and request handlers

## Database

The database schema is stored in `sql/schema.sql`.

Main tables:

- `categories`
- `tools`
- `users`
- `reviews`
- `review_ratings`
- `review_comments`
- `moderator_actions`
- `rentals`

## Setup instructions

### 1. Install and run Apache + MySQL

Use XAMPP or another local PHP/MySQL stack.

### 2. Create the database

Import `sql/schema.sql` into MySQL.

Example:

```bash
mysql -u root -p < sql/schema.sql
```

### 3. Configure database connection

Edit `config/database.php` if your MySQL credentials differ.

### 4. Start the project

Place the project in your local web server root, for example:

- `D:/xampp/htdocs/tools_hire`

Open the app in the browser:

- `http://localhost/tools_hire`

## Default accounts

The schema currently seeds these accounts:

- Admin: `admin` / `admin123`
- Customer: `customer` / `customer123`

## Security notes

- Passwords are stored using `password_hash()`
- SQL queries use PDO prepared statements
- Session-based authentication is used for protected pages

## Testing

Testing documentation is available in `docs/TEST_PLAN.md`.

## Documentation

Project design and requirements are documented in `docs/SYSTEM_DESIGN.md`.

## Future improvements

- Payment integration for online booking
- Live inventory updates
- Email notifications
- Improved admin reporting
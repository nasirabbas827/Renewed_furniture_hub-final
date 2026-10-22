# Renewed Furniture Hub

A PHP and MySQL furniture marketplace with buyer, seller and admin panels.

## Features

- Search furniture by category, condition, location and price
- Seller listings and furniture images
- Buyer orders and payment pages
- Reviews and complaint handling

## Technology

PHP, MySQL, HTML, CSS and JavaScript.

## Local setup

Clone the repository:

```bash
git clone https://github.com/nasirabbas827/Renewed_furniture_hub-final.git
cd Renewed_furniture_hub-final
```

1. Install a local PHP web server and MySQL, such as XAMPP.
2. Place the project in the server's document root, or configure a virtual host pointing to this folder.
3. Import `Database/furniture_db.sql` using phpMyAdmin or your MySQL client.
4. Update the database connection in `config.php` and in role-specific copies of `config.php` to match your local database name and credentials.
5. Start Apache and MySQL, then open `http://localhost/Renewed_furniture_hub-final/`.

## Repository layout

| Path | Purpose |
|---|---|
| `Database/` | SQL database export |
| `admin/` | Role-specific pages |
| `buyer/` | Role-specific pages |
| `config.php` | Database connection configuration |
| `css/` | Project resources |
| `images/` | Project resources |
| `index.php` | Web entry page |
| `seller/` | Role-specific pages |
| `uploads/` | Project resources |

## Development notes

Setup steps describe local development. Configure your own database, mail and API credentials where applicable. Review debug settings and replace development secrets before deploying. The application was not executed as part of this documentation update.

Maintained by [Nasir Abbas](https://github.com/nasirabbas827).

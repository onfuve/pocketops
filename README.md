# Pocket Business

A pocket-sized web app for managing sales, purchases, invoices, orders, leads, and contacts — in Persian with Shamsi (Jalali) calendar. Built for growth: MySQL-backed and ready for multi-user use later.

## Features (planned)

| Module | Description |
|--------|-------------|
| **Contacts** | Business address book: customers, counterparts, suppliers — full profiles, delivery labels |
| **Products** | What you sell / what you buy — product and service catalog |
| **Invoices** | Issue invoices on the go — Shamsi dates, print |
| **Orders** | Track orders (from suppliers or for customers) |
| **Leads** | Submit leads from calls/chat — later assign to team |

- **Locale:** Persian (fa), Shamsi calendar, Asia/Tehran timezone  
- **DB:** MySQL (`pocket_business`) — ready for multi-user and scaling  
- **UI:** Mobile-first (pocket / on-the-go)

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm (for Vite)
- MySQL 8.x (create database: `pocket_business`)

## Setup

```bash
# Clone or cd into project
cd pocket-business

# Install PHP dependencies
composer install

# Copy env and configure MySQL
cp .env.example .env
php artisan key:generate

# Edit .env: DB_DATABASE=pocket_business, DB_USERNAME, DB_PASSWORD

# Create DB (if not exists)
# mysql -e "CREATE DATABASE pocket_business CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Install frontend deps (optional, for Vite)
npm install && npm run build

# Run dev server
php artisan serve
```

Visit `http://localhost:8000`.

## Project structure

```
app/
├── Modules/           # Feature modules
│   ├── Contacts/      # Address book (customers, suppliers, counterparts)
│   ├── Products/      # What you sell / buy
│   ├── Invoices/     # Invoicing on the go
│   ├── Orders/       # Order tracking
│   └── Leads/        # Lead capture & assignment
├── Http/
├── Models/
└── ...
```

## License

Private / side project.

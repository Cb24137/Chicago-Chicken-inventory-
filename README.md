# Chicago-Chicken-inventory-

Food & Beverage Inventory Management System for Chicago Chicken City (Laravel 12).

## Database setup (XAMPP MySQL)

The project uses **MySQL from XAMPP only**. Each team member has their own local databases; nobody shares database files through GitHub.

### 1. Create the two databases

1. Open the **XAMPP Control Panel** and start **MySQL** (and Apache if you want phpMyAdmin).
2. Open phpMyAdmin at <http://localhost/phpmyadmin>, choose **New**, and create two databases with collation `utf8mb4_unicode_ci`:
   - `chicago_chicken_inventory` - the app's data
   - `chicago_chicken_inventory_test` - used only by the automated tests (it is emptied on every test run)

### 2. Point Laravel at MySQL

If you have no `.env` yet, copy the template and create an app key:

```bash
copy .env.example .env
php artisan key:generate
```

Then check the database lines in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chicago_chicken_inventory
DB_USERNAME=root
DB_PASSWORD=
```

These are the XAMPP defaults. If your MySQL uses another port (see the XAMPP Control Panel or `C:\xampp\mysql\bin\my.ini`) or you gave `root` a password, change `DB_PORT` / `DB_PASSWORD` to match.

### 3. Install the packages and create the tables

```bash
composer install
php artisan config:clear
php artisan migrate
```

`composer install` adds the PHP packages, including the PDF library used by the report exports (`barryvdh/laravel-dompdf`).
`php artisan migrate` only runs migrations you have not run yet, so it is safe to repeat after every `git pull`.
Never run `php artisan migrate:fresh`, `migrate:reset` or `db:wipe` on a database whose records you want to keep - they delete everything.

### 4. Add the demonstration data (optional)

```bash
php artisan db:seed --class=ChicagoChickenDemoSeeder
```

Adds realistic records to all four modules. It never changes existing records, and running it again adds nothing new.

### 5. Create your first admin account

A new database has no accounts. Create an admin with Tinker (the password is hashed automatically):

```bash
php artisan tinker
> App\Models\User::forceCreate(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'choose-a-password', 'role' => 'admin']);
```

The admin can then create staff accounts from **Staff Management**.

### 6. Run the tests

```bash
php artisan test
```

Tests always use the separate `chicago_chicken_inventory_test` database (set in `phpunit.xml`) and refuse to run against any database whose name does not end in `_test`, so they never touch your real data.

### If MySQL is not running

The app shows a "Cannot connect to the MySQL database" page. Start MySQL in the XAMPP Control Panel and refresh.

## Dashboards, reports and notifications

- **Executive dashboard (administrators)** - a management overview for a reporting period (this month, last month, last 3 or 6 months, this year, or a custom range), compared with the same length of time before it (e.g. 1-14 Oct vs 1-14 Sep):
  - key figures: inventory value, purchase expenditure, estimated waste cost, pending purchase orders, suppliers and operational alerts
  - charts: monthly purchase expenditure, inventory value by category, waste cost trend and supplier purchase distribution
  - management insights that state their numbers and the data they come from, important notifications, report shortcuts and staff management
- **Staff dashboard** - today's work: stock to reorder, orders to receive, deliveries close to expiry, quick actions and recent activity. Every figure on both dashboards is read from the database.
- **Reports & Analytics** - Inventory, Purchases, Suppliers, Storage, Waste & Expiry and Operational Summary reports with date ranges and filters. Each report can be exported as **CSV** (opens in Excel) or **PDF**, or printed. Staff can open the Inventory, Storage and Waste reports; administrators can open all six.
  - *Inventory value* is the current stock quantity × recorded unit price (a snapshot for today).
  - *Spent* counts received orders only, *committed* is pending and ordered orders, and cancelled orders are never counted as spending.
  - Waste cost is an estimate: quantity x the current unit price of the inventory item with the same name.
- **Notifications** - the bell in the top bar and the Notifications page. Alerts are shown in the app only; no emails or text messages are sent. Nobody is notified about their own changes.

| Alert | Who receives it |
| --- | --- |
| Low stock / out of stock (once per drop, re-armed after restocking) | Everyone |
| Delivery approaching or past its expiry date | Everyone |
| Purchase order received | Everyone |
| Purchase order created, placed or cancelled | Administrators |
| Waste recorded | Administrators |
| Staff account added or removed | Administrators |

**Expiry alerts** use the expiry date entered when a purchase order is received (or on the order form). They are sent `INVENTORY_EXPIRY_ALERT_DAYS` days before that date (3 by default, set in `.env`).

**Daily check** - XAMPP does not run Laravel's scheduler, so low-stock and expiry alerts that are due are checked automatically on the first dashboard visit each day. You can also run the check yourself:

```bash
php artisan inventory:check-alerts
```

### Team rules

- Never commit `.env` or database exports (`*.sql`) - they are git-ignored because they contain passwords and private data.
- Changes to the database structure go in a new migration file; do not edit migrations that teammates have already run.
- Shared demo data belongs in a seeder (`database/seeders`), not in a database file.

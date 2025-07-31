# Sales Reporting & Analytics Dashboard

A full-stack Laravel application with CRUD API endpoints and web-based dashboard for managing customers, products, and sales data.

## Features

- **RESTful API** for Customers, Products, and Sales
- **Web-based CRUD interface** with Bootstrap UI
- **Interactive Dashboard** with Chart.js visualizations
- **Database seeding** with fake data
- **MySQL/SQLite support**

## Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL (XAMPP/WAMP/MAMP) or SQLite
- Node.js (optional, for frontend assets)

## Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd sales-dashboard
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
Copy the environment file:
```bash
cp .env.example .env
```

### 4. Configure Database
Edit `.env` file with your database settings:

**For MySQL (XAMPP):**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_dashboard
DB_USERNAME=root
DB_PASSWORD=
```

**For SQLite:**
```
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Create Database
- **MySQL**: Create database `sales_dashboard` in phpMyAdmin
- **SQLite**: The database file will be created automatically

### 7. Run Migrations and Seed Data
```bash
php artisan migrate:fresh --seed
```

This will:
- Create all database tables
- Seed 100 customers, 20 products, and 100 sales records

### 8. Start the Development Server
```bash
php artisan serve
```

Visit: `http://localhost:8000`

## Usage

### Web Interface

1. **Dashboard**: Visit `/` to see sales analytics charts
2. **Customers**: Visit `/customers` to manage customer data
3. **Products**: Visit `/products` to manage product catalog
4. **Sales**: Visit `/sales` to manage sales transactions

### API Endpoints

All API endpoints are prefixed with `/api/`:

#### Customers
- `GET /api/customers` - List all customers
- `GET /api/customers/{id}` - Get specific customer
- `POST /api/customers` - Create new customer
- `PUT /api/customers/{id}` - Update customer
- `DELETE /api/customers/{id}` - Delete customer

#### Products
- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get specific product
- `POST /api/products` - Create new product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product

#### Sales
- `GET /api/sales` - List all sales
- `GET /api/sales/{id}` - Get specific sale
- `POST /api/sales` - Create new sale
- `PUT /api/sales/{id}` - Update sale
- `DELETE /api/sales/{id}` - Delete sale

### API Testing with Postman

1. Import the `Sales_Dashboard_API.postman_collection.json` file
2. Set the `base_url` variable to `http://localhost:8000`
3. Test all CRUD operations

## Database Schema

### Customers Table
- `id` (Primary Key)
- `name` (String)
- `region` (String)
- `gender` (Enum: male/female)
- `created_at`, `updated_at` (Timestamps)

### Products Table
- `id` (Primary Key)
- `name` (String)
- `category` (String)
- `price` (Decimal)
- `created_at`, `updated_at` (Timestamps)

### Sales Table
- `id` (Primary Key)
- `customer_id` (Foreign Key)
- `product_id` (Foreign Key)
- `quantity` (Integer)
- `sale_date` (Date)
- `created_at`, `updated_at` (Timestamps)

## Dashboard Features

The dashboard displays:
- **Sales by Product Category** (Bar Chart)
- **Sales by Region** (Pie Chart)
- **Sales Over Time** (Line Chart)

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure MySQL service is running (XAMPP)
   - Check database credentials in `.env`
   - Run `php artisan config:cache`

2. **Migration Errors**
   - Clear cache: `php artisan config:clear`
   - Reset migrations: `php artisan migrate:fresh`

3. **Permission Issues**
   - Ensure storage and bootstrap/cache directories are writable

### Commands Reference

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Reset database
php artisan migrate:fresh --seed

# View routes
php artisan route:list

# Check application status
php artisan about
```

## Project Structure

```
sales-dashboard/
├── app/
│   ├── Http/Controllers/
│   │   ├── CustomerController.php (API)
│   │   ├── CustomerWebController.php (Web)
│   │   ├── ProductController.php (API)
│   │   ├── ProductWebController.php (Web)
│   │   ├── SaleController.php (API)
│   │   ├── SaleWebController.php (Web)
│   │   └── DashboardController.php
│   └── Models/
│       ├── Customer.php
│       ├── Product.php
│       └── Sale.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/views/
│   ├── dashboard.blade.php
│   ├── customers/
│   ├── products/
│   ├── sales/
│   └── layouts/
└── routes/
    ├── api.php
    └── web.php
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues and questions, please check the troubleshooting section or create an issue in the repository. 
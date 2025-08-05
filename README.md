# 📊 Sales Dashboard

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![Vite](https://img.shields.io/badge/Vite-7.0-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Active-brightgreen?style=for-the-badge)]()

A comprehensive sales analytics dashboard built with Laravel, featuring real-time reporting, customer management, product tracking, and advanced analytics with interactive charts.

## 🚀 Features

### 📈 Analytics & Reporting
- **Real-time Sales Dashboard** with key performance indicators
- **Advanced Filtering** by date range, category, region, customer demographics
- **Interactive Charts** showing revenue trends, top products, and regional performance
- **Conversion Rate Analytics** with multi-factor calculations
- **Export Functionality** for reports and data

### 👥 Customer Management
- **Customer CRUD Operations** with demographic tracking
- **Regional Analysis** by customer location
- **Age and Gender Segmentation** for targeted insights
- **Customer Purchase History** tracking

### 📦 Product Management
- **Product Catalog** with categories and pricing
- **Inventory Tracking** with quantity management
- **Product Performance Analytics** showing top-selling items
- **Category-based Analysis** for product insights

### 💰 Sales Tracking
- **Sales Transaction Management** with detailed records
- **Revenue Calculation** with product price integration
- **Order Value Analysis** and trend tracking
- **Sales Performance Metrics** and KPIs

### 🔌 API Integration
- **RESTful API** endpoints for all entities
- **Postman Collection** included for easy testing
- **JSON Response Format** for frontend integration
- **Public API Access** for external integrations

## 🛠️ Technology Stack

| Component | Technology | Version |
|-----------|------------|---------|
| **Backend Framework** | Laravel | 12.0 |
| **PHP Version** | PHP | 8.2+ |
| **Frontend Styling** | Tailwind CSS | 4.0 |
| **Build Tool** | Vite | 7.0 |
| **Database** | SQLite/MySQL | - |
| **Testing** | PHPUnit | 11.5 |

## 📋 Prerequisites

- **PHP** 8.2 or higher
- **Composer** for dependency management
- **Node.js** and **npm** for frontend assets
- **Web Server** (Apache/Nginx) or Laravel's built-in server

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd sales-dashboard
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration
```bash
# Create SQLite database (or configure MySQL in .env)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

### 5. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 6. Start the Server
```bash
# Using Laravel's built-in server
php artisan serve

# Or using the development script
composer run dev
```

Visit `http://localhost:8000` to access the dashboard.

## 📚 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Available Endpoints

#### Products
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/products` | List all products |
| `POST` | `/products` | Create new product |
| `GET` | `/products/{id}` | Get specific product |
| `PUT` | `/products/{id}` | Update product |
| `DELETE` | `/products/{id}` | Delete product |

#### Customers
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/customers` | List all customers |
| `POST` | `/customers` | Create new customer |
| `GET` | `/customers/{id}` | Get specific customer |
| `PUT` | `/customers/{id}` | Update customer |
| `DELETE` | `/customers/{id}` | Delete customer |

#### Sales
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/sales` | List all sales |
| `POST` | `/sales` | Create new sale |
| `GET` | `/sales/{id}` | Get specific sale |
| `PUT` | `/sales/{id}` | Update sale |
| `DELETE` | `/sales/{id}` | Delete sale |

#### Analytics
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/sales/reports` | Get sales reports |
| `GET` | `/sales/export` | Export sales data |
| `GET` | `/sales/chart-data` | Get chart data |

### API Request Examples

#### Create Product
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Sample Product",
    "category": "Electronics",
    "price": 99.99,
    "quantity": 100
  }'
```

#### Create Customer
```bash
curl -X POST http://localhost:8000/api/customers \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "123-456-7890",
    "address": "123 Main St",
    "city": "New York",
    "state": "NY",
    "zip_code": "10001",
    "country": "USA",
    "region": "Northeast",
    "age": 30,
    "gender": "Male"
  }'
```

#### Create Sale
```bash
curl -X POST http://localhost:8000/api/sales \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "product_id": 1,
    "quantity": 5,
    "sale_date": "2024-01-15"
  }'
```

## 📊 Dashboard Features

### Key Metrics
- **Total Revenue** - Real-time calculation from sales data
- **Total Orders** - Number of sales transactions
- **Average Order Value** - Revenue per transaction
- **Conversion Rate** - Multi-factor performance metric

### Interactive Charts
- **Revenue Trends** - Time-based revenue visualization
- **Top Products** - Best-selling items analysis
- **Sales by Category** - Product category performance
- **Regional Performance** - Geographic sales analysis

### Advanced Filtering
- **Date Range** - Custom period selection
- **Category Filter** - Product category filtering
- **Region Filter** - Geographic filtering
- **Customer Demographics** - Age and gender segmentation
- **Amount Ranges** - Revenue-based filtering

## 🧪 Testing

### Run Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=ProductTest
```

### API Testing with Postman
Import the included `Sales_Dashboard_API.postman_collection.json` file into Postman for easy API testing.

## 📁 Project Structure

```
sales-dashboard/
├── app/
│   ├── Http/Controllers/     # API and Web controllers
│   ├── Models/              # Eloquent models
│   └── Providers/           # Service providers
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── views/              # Blade templates
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript files
├── routes/
│   ├── api.php            # API routes
│   └── web.php            # Web routes
└── tests/                 # Test files
```

## 🔧 Configuration

### Environment Variables
Key environment variables in `.env`:

```env
APP_NAME="Sales Dashboard"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### Database Configuration
The application supports both SQLite (default) and MySQL. Update the database configuration in `.env` for MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sales_dashboard
DB_USERNAME=root
DB_PASSWORD=
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

If you encounter any issues or have questions:

1. Check the [Issues](../../issues) page for existing solutions
2. Create a new issue with detailed information
3. Contact the development team

## 🙏 Acknowledgments

- **Laravel Team** for the amazing framework
- **Tailwind CSS** for the utility-first CSS framework
- **Vite** for the fast build tool
- **Bootstrap Icons** for the icon set

---

<div align="center">

**Made with ❤️ using Laravel and Tailwind CSS**

[![GitHub stars](https://img.shields.io/github/stars/your-username/sales-dashboard?style=social)](https://github.com/your-username/sales-dashboard)
[![GitHub forks](https://img.shields.io/github/forks/your-username/sales-dashboard?style=social)](https://github.com/your-username/sales-dashboard)
[![GitHub issues](https://img.shields.io/github/issues/your-username/sales-dashboard)](https://github.com/your-username/sales-dashboard/issues)

</div> 
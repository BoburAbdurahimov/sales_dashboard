# Sales Dashboard API Documentation

## Overview
The Sales Dashboard API provides RESTful endpoints for managing customers, products, sales, and generating reports. The API is built with Laravel and follows REST conventions.

## Base URL
```
http://localhost:8000/api
```

## Authentication
The API uses **Bearer Token Authentication** with Laravel Sanctum. All protected endpoints require a valid bearer token in the Authorization header.

### Authentication Flow
1. **Register** a new user account
2. **Login** to get an access token
3. **Use the token** in the Authorization header for all subsequent requests
4. **Logout** to invalidate the token

### Authentication Endpoints

#### Register User
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

**Response:**
```json
{
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "1|abc123def456...",
    "message": "User registered successfully"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
    },
    "token": "1|abc123def456...",
    "message": "Login successful"
}
```

#### Get User Profile
```http
GET /api/auth/user
Authorization: Bearer 1|abc123def456...
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer 1|abc123def456...
```

### Using Bearer Tokens
For all protected endpoints, include the bearer token in the Authorization header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

## Endpoints

### Customers

#### Get All Customers
```http
GET /api/customers
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone_number": "+1234567890",
    "age": 30,
    "gender": "male",
    "region": "North America",
    "address": "123 Main St, City, State 12345",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
]
```

#### Get Customer by ID
```http
GET /api/customers/{id}
Authorization: Bearer YOUR_TOKEN_HERE
```

#### Create Customer
```http
POST /api/customers
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "phone_number": "+1234567890",
  "age": 30,
  "gender": "male",
  "region": "North America",
  "address": "123 Main St, City, State 12345"
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters, min 2 characters
- `email`: required, valid email, unique, max 255 characters
- `phone_number`: required, string, max 20 characters, min 10 characters
- `age`: required, integer, min 1, max 120
- `gender`: required, one of: male, female, other
- `region`: required, string, max 100 characters
- `address`: required, string, max 500 characters

#### Update Customer
```http
PUT /api/customers/{id}
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "phone_number": "+1234567891",
  "age": 25,
  "gender": "female",
  "region": "Europe",
  "address": "456 Oak Ave, City, Country 67890"
}
```

#### Delete Customer
```http
DELETE /api/customers/{id}
Authorization: Bearer YOUR_TOKEN_HERE
```

### Products

#### Get All Products
```http
GET /api/products
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "iPhone 15 Pro",
    "category": "Electronics",
    "price": "999.99",
    "quantity": 50,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
]
```

#### Get Product by ID
```http
GET /api/products/{id}
Authorization: Bearer YOUR_TOKEN_HERE
```

#### Create Product
```http
POST /api/products
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "name": "iPhone 15 Pro",
  "category": "Electronics",
  "price": 999.99,
  "quantity": 50
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters, min 2 characters, unique
- `category`: required, string, max 100 characters
- `price`: required, numeric, min 0.01, max 999999.99
- `quantity`: required, integer, min 0, max 999999

#### Update Product
```http
PUT /api/products/{id}
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "name": "iPhone 15 Pro Max",
  "category": "Electronics",
  "price": 1199.99,
  "quantity": 25
}
```

#### Delete Product
```http
DELETE /api/products/{id}
Authorization: Bearer YOUR_TOKEN_HERE
```

### Sales

#### Get All Sales
```http
GET /api/sales
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:**
```json
[
  {
    "id": 1,
    "customer_id": 1,
    "product_id": 1,
    "quantity": 2,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "region": "North America"
    },
    "product": {
      "id": 1,
      "name": "iPhone 15 Pro",
      "category": "Electronics",
      "price": "999.99"
    }
  }
]
```

#### Get Sale by ID
```http
GET /api/sales/{id}
Authorization: Bearer YOUR_TOKEN_HERE
```

#### Create Sale
```http
POST /api/sales
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "customer_id": 1,
  "product_id": 1,
  "quantity": 2
}
```

**Validation Rules:**
- `customer_id`: required, exists in customers table
- `product_id`: required, exists in products table
- `quantity`: required, integer, min 1

**Notes:**
- The system automatically checks if the product has sufficient stock
- If stock is insufficient, returns 422 error with details
- When a sale is created, the product quantity is automatically reduced

#### Update Sale
```http
PUT /api/sales/{id}
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "customer_id": 1,
  "product_id": 1,
  "quantity": 3
}
```

**Notes:**
- The system handles stock adjustments automatically
- If quantity increases, checks for sufficient stock
- If quantity decreases, restores the difference to stock
- If product changes, handles stock transfers between products

#### Delete Sale
```http
DELETE /api/sales/{id}
Authorization: Bearer YOUR_TOKEN_HERE
```

**Notes:**
- When a sale is deleted, the product quantity is automatically restored

### Reports

#### Get Sales Report
```http
GET /api/sales/reports?date_from=2024-01-01&date_to=2024-12-31&category=Electronics&region=North America
Authorization: Bearer YOUR_TOKEN_HERE
```

**Query Parameters:**
- `date_from` (optional): Start date for filtering (YYYY-MM-DD)
- `date_to` (optional): End date for filtering (YYYY-MM-DD)
- `category` (optional): Filter by product category
- `region` (optional): Filter by customer region

**Response:**
```json
[
  {
    "id": 1,
    "customer_id": 1,
    "product_id": 1,
    "quantity": 2,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "region": "North America"
    },
    "product": {
      "id": 1,
      "name": "iPhone 15 Pro",
      "category": "Electronics",
      "price": "999.99"
    }
  }
]
```

#### Export Sales Report
```http
GET /api/sales/export?date_from=2024-01-01&date_to=2024-12-31&category=Electronics&region=North America
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:**
Returns a CSV file with the following columns:
- Order ID
- Customer Name
- Customer Email
- Customer Age
- Customer Gender
- Customer Region
- Product Name
- Category
- Quantity
- Unit Price
- Total Amount
- Date

## Error Responses

### Authentication Errors (401)
```json
{
  "message": "Unauthenticated."
}
```

### Validation Errors (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "quantity": ["The quantity must be at least 1."]
  }
}
```

### Not Found (404)
```json
{
  "message": "No query results for model [App\\Models\\Sale] 999"
}
```

### Insufficient Stock (422)
```json
{
  "error": "Insufficient stock. Available: 5, Requested: 10"
}
```

## Postman Collection

A complete Postman collection is available in the file `Sales_Dashboard_API.postman_collection.json`. This collection includes:

- **Authentication endpoints** (Login, Register, Logout, Get Profile)
- All CRUD operations for Customers, Products, and Sales
- Report generation and export endpoints
- Sample request bodies with proper validation
- Environment variables for easy configuration
- **Bearer token authentication** for all protected endpoints

### Import Instructions:
1. Open Postman
2. Click "Import" button
3. Select the `Sales_Dashboard_API.postman_collection.json` file
4. Set the `base_url` variable to your local server (default: `http://localhost:8000`)
5. **Set up authentication:**
   - First, use the "Register" endpoint to create a user
   - Then use the "Login" endpoint to get a token
   - Copy the token from the response and set it as the `auth_token` variable
   - All subsequent requests will automatically use the bearer token

### Authentication Setup in Postman:
1. **Register a user** using the Register endpoint
2. **Login** using the Login endpoint
3. **Copy the token** from the response
4. **Set the `auth_token` variable** in the collection
5. **All requests** will now include the Authorization header automatically

## Recent Changes

### 1. Authentication Added
- **Laravel Sanctum** installed for API authentication
- **Bearer token authentication** implemented
- **Protected routes** require valid authentication
- **AuthController** created with login, register, logout, and user profile endpoints

### 2. Postman Collection Updated
- **Authentication section** added with login, register, logout, and user profile endpoints
- **Bearer token headers** added to all protected endpoints
- **Environment variables** for `auth_token` added
- **Updated documentation** with authentication flow

### 3. API Security Enhanced
- **All CRUD operations** now require authentication
- **Reports and exports** require valid tokens
- **Proper error responses** for unauthenticated requests

## Setup Instructions

1. **Install Dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Start Development Server:**
   ```bash
   php artisan serve
   ```

5. **Import Postman Collection:**
   - Import `Sales_Dashboard_API.postman_collection.json` into Postman
   - Set the `base_url` variable to `http://localhost:8000`
   - Register a user and login to get a token
   - Set the `auth_token` variable with your token

## Testing the API

1. **Authentication:**
   - Register a user using the POST `/api/auth/register` endpoint
   - Login using the POST `/api/auth/login` endpoint
   - Copy the token from the response

2. **Test Protected Endpoints:**
   - Add the token to the Authorization header: `Bearer YOUR_TOKEN`
   - Test all CRUD operations for customers, products, and sales
   - Test report generation and export functionality

3. **Test Error Handling:**
   - Try accessing protected endpoints without authentication
   - Test validation errors with invalid data
   - Test insufficient stock scenarios

## Notes

- **All API endpoints** now require bearer token authentication
- **Authentication tokens** are managed by Laravel Sanctum
- **Tokens can be revoked** using the logout endpoint
- **User profile** can be retrieved using the `/api/auth/user` endpoint
- **Error messages** are descriptive and helpful for debugging
- **CSV exports** include comprehensive customer and product information 
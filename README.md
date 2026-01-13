# E-Commerce Shopping Cart Application

A full-stack e-commerce shopping cart application built with Laravel (backend) and React (frontend) with Tailwind CSS styling.

## Features

- User authentication (register/login) using Laravel Sanctum
- Product browsing with stock information
- Shopping cart functionality (add, update quantities, remove items)
- User-specific cart (stored in database, not session/localStorage)
- Low stock notification system (email alerts to admin when stock is low)
- Daily sales report (scheduled job that runs every evening)

## Tech Stack

### Backend
- Laravel 12
- Laravel Sanctum (API authentication)
- Laravel Breeze (authentication scaffolding)
- SQLite database (default)
- Queue system for background jobs
- Scheduled tasks for daily reports

### Frontend
- React 19
- Tailwind CSS
- Axios for API calls
- Vite for build tooling

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm or yarn

### Backend Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd E_commerce_laravel
```

2. Install PHP dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations and seeders:
```bash
php artisan migrate
php artisan db:seed
```

This will create:
- An admin user: `admin@example.com` / `password`
- Sample products with various stock quantities

6. Configure email settings in `.env`:
```env
SENDGRID_API_KEY=your_sendgrid_api_key_here
MAIL_TO_ADDRESS=recipient@example.com
MAIL_FROM_NAME=E-commerce System
```

**Note**: All emails are sent from `aninda@evra.solutions`. The recipient address is configured via `MAIL_TO_ADDRESS` environment variable.

7. Start the Laravel development server:
```bash
php artisan serve
```

8. Start the queue worker (for background jobs):
```bash
php artisan queue:work
```

### Frontend Setup

1. Navigate to the frontend directory:
```bash
cd frontend
```

2. Install dependencies:
```bash
npm install
```

3. Create a `.env` file in the frontend directory:
```env
VITE_API_URL=http://localhost:8000/api
```

4. Start the development server:
```bash
npm run dev
```

The frontend will be available at `http://localhost:5173` (or the port Vite assigns).

## Usage

1. **Register/Login**: Create a new account or login with existing credentials
2. **Browse Products**: View all available products with prices and stock information
3. **Add to Cart**: Select quantity and add products to your cart
4. **Manage Cart**: Update quantities or remove items from your cart
5. **View Cart**: Click the "Cart" button in the navigation to view your cart

## API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (requires authentication)
- `GET /api/user` - Get authenticated user (requires authentication)
- `POST /api/forgot-password` - Request password reset
- `POST /api/reset-password` - Reset password
- `GET /api/verify-email/{id}/{hash}` - Verify email address
- `POST /api/email/verification-notification` - Resend email verification

### Products
- `GET /api/products` - Get all products
- `GET /api/products/{id}` - Get a specific product

### Cart (requires authentication)
- `GET /api/cart` - Get user's cart items
- `POST /api/cart` - Add item to cart
- `PUT /api/cart/{id}` - Update cart item quantity
- `DELETE /api/cart/{id}` - Remove item from cart

### Orders (requires authentication)
- `POST /api/orders` - Create a new order (checkout)
- `GET /api/orders` - Get user's orders
- `GET /api/orders/{id}` - Get a specific order

## Background Jobs

### Low Stock Notification
When a product's stock quantity falls to 10 or below, a job is automatically dispatched to send an email notification using SendGrid. The email is sent from `aninda@evra.solutions` to the address specified in `MAIL_TO_ADDRESS` environment variable.

### Daily Sales Report
A scheduled job runs every evening at 6:00 PM to generate and email a daily sales report using SendGrid. The email is sent from `aninda@evra.solutions` to the address specified in `MAIL_TO_ADDRESS` environment variable. The report includes:
- All products sold that day
- Quantity sold for each product
- Revenue generated per product

To manually trigger the daily sales report:
```bash
php artisan sales:send-daily-report
```

## Database Structure

- **users**: User accounts
- **products**: Product catalog (name, price, stock_quantity)
- **cart_items**: Shopping cart items (linked to users and products)
- **orders**: Order records
- **order_items**: Order line items

## Testing

Run the test suite:
```bash
php artisan test
```

## Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Set up a process manager (like Supervisor) for the queue worker
5. Set up a cron job for the scheduler:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

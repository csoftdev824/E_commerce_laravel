# Setup Instructions

## Quick Start

### 1. Backend Setup

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Start server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work
```

### 2. Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Create .env file
echo "VITE_API_URL=http://localhost:8000/api" > .env

# Start development server
npm run dev
```

## Default Credentials

- **Admin User**: admin@example.com / password
- **Test User**: You can register a new account through the UI

## Testing Features

1. **Low Stock Notification**: 
   - Update a product's stock_quantity to 10 or below
   - Check that a job is dispatched (check `php artisan queue:work` output)
   - Email will be sent to admin@example.com

2. **Daily Sales Report**:
   - Manually trigger: `php artisan sales:send-daily-report`
   - Or wait for scheduled run at 6:00 PM daily

## GitHub Setup

1. Initialize git repository:
```bash
git init
git add .
git commit -m "Initial commit: E-commerce shopping cart application"
```

2. Create a new repository on GitHub

3. Add remote and push:
```bash
git remote add origin <your-repo-url>
git branch -M main
git push -u origin main
```

4. Add collaborator:
   - Go to repository settings
   - Click "Collaborators"
   - Add @dylanmichaelryan

## Environment Variables

### Backend (.env)
- `APP_URL`: Application URL
- `FRONTEND_URL`: Frontend URL (for CORS)
- `DB_CONNECTION`: Database connection (sqlite by default)
- `QUEUE_CONNECTION`: Queue driver (database by default)
- `SENDGRID_API_KEY`: SendGrid API key for sending emails (required for email notifications)
- `MAIL_TO_ADDRESS`: Email address to receive notifications (required for email notifications)
- `MAIL_FROM_NAME`: Name to send emails from (default: E-commerce System)

### Frontend (frontend/.env)
- `VITE_API_URL`: Backend API URL

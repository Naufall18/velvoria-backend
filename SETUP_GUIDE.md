# 🚀 LuxeMart Backend - Setup Guide

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2+** - [Download PHP](https://www.php.net/downloads)
- **Composer 2.6+** - [Download Composer](https://getcomposer.org/download/)
- **Docker Desktop** - [Download Docker](https://www.docker.com/products/docker-desktop/)
- **Git** - [Download Git](https://git-scm.com/downloads)

## Quick Start (5 Minutes)

### 1. Clone Repository

```bash
git clone https://github.com/Naufall18/luxemart-backend.git
cd luxemart-backend
```

### 2. Install Laravel

Since this is a fresh repository, you need to create a new Laravel project:

```bash
# Option A: Create new Laravel project
composer create-project laravel/laravel .

# Option B: Or if you want to use this structure
composer install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Start Docker Services

```bash
# Start PostgreSQL, Redis, MongoDB, etc.
docker-compose up -d

# Wait for services to be healthy (30 seconds)
docker-compose ps
```

### 5. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### 6. Start Development Server

```bash
# Start Laravel server
php artisan serve

# Server will run at: http://localhost:8000
```

## Alternative: Using Laravel Sail

Laravel Sail provides a Docker-based development environment:

```bash
# Install Sail
composer require laravel/sail --dev

# Install Sail with PostgreSQL, Redis, Meilisearch
php artisan sail:install --with=pgsql,redis,meilisearch

# Start Sail
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Seed database
./vendor/bin/sail artisan db:seed
```

## Project Initialization

### Create Laravel Project Structure

```bash
# Create controllers
php artisan make:controller Api/AuthController
php artisan make:controller Api/UserController
php artisan make:controller Api/ProductController
php artisan make:controller Api/VendorController
php artisan make:controller Api/OrderController

# Create models
php artisan make:model User -m
php artisan make:model Product -m
php artisan make:model Vendor -m
php artisan make:model Order -m

# Create migrations
php artisan make:migration create_products_table
php artisan make:migration create_vendors_table
php artisan make:migration create_orders_table
```

## Essential Packages Installation

```bash
# Authentication
composer require laravel/sanctum

# Permissions
composer require spatie/laravel-permission

# API Resources
composer require spatie/laravel-query-builder

# Payment Gateways
composer require stripe/stripe-php

# Search
composer require laravel/scout
composer require meilisearch/meilisearch-php

# Image Processing
composer require intervention/image

# Real-time
composer require pusher/pusher-php-server
```

## Configuration

### Database Configuration (.env)

```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=luxemart
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### Redis Configuration (.env)

```env
REDIS_HOST=localhost
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Mail Configuration (.env)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

## Testing

```bash
# Run tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter=AuthTest
```

## Troubleshooting

### Port Already in Use

```bash
# Use different port
php artisan serve --port=8001
```

### Permission Issues

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Next Steps

1. ✅ Setup complete
2. 📖 Read API documentation
3. 🔧 Configure payment gateways
4. 🚀 Start building features

## Support

- 📧 Email: support@luxemart.com
- 📚 Docs: https://docs.luxemart.com
- 🐛 Issues: [GitHub Issues](https://github.com/Naufall18/luxemart-backend/issues)

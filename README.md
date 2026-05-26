<div align="center">

# 🏗️ LuxeMart Backend

### Premium Multi-Vendor Marketplace - Laravel API

[![Laravel](https://img.shields.io/badge/Laravel-11+-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Redis](https://img.shields.io/badge/Redis-7+-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)

**Enterprise-grade Laravel API powering luxury e-commerce at scale**

[Features](#-key-features) • [Architecture](#-architecture) • [Quick Start](#-quick-start) • [API Docs](#-api-documentation)

</div>

---

## 🎯 Overview

LuxeMart Backend is a **production-ready Laravel API** designed for high-performance luxury marketplace operations. Built with Laravel 11 and modern PHP practices, it handles millions of transactions with enterprise-grade reliability.

### Why Laravel?

- 🚀 **Rapid Development** - Elegant syntax and powerful features
- 🔒 **Secure** - Built-in security features and best practices
- ⚡ **Fast** - Optimized performance with caching and queues
- 📦 **Rich Ecosystem** - Thousands of packages available
- 🧪 **Testable** - Comprehensive testing tools
- 🐳 **Cloud-Ready** - Docker & Laravel Sail support

---

## ✨ Key Features

### 🏪 Multi-Vendor Marketplace
- Complete vendor onboarding and management
- Commission-based revenue model
- Vendor analytics and reporting
- Multi-store support

### 🛍️ Advanced E-Commerce
- Product catalog with variants
- Smart search with Laravel Scout
- Real-time inventory management
- Dynamic pricing and promotions

### 💳 Payment Processing
- Multiple payment gateways (Stripe, Midtrans, Xendit)
- Secure payment handling
- Automated payout system
- Refund management

### 📦 Order Management
- Real-time order tracking
- Multi-vendor order splitting
- Shipping integration
- Return and refund workflows

### 💬 Communication
- Real-time chat with Laravel Echo
- Push notifications (FCM)
- Email notifications (Queue)
- SMS alerts (Twilio)

### 📺 Live Shopping
- Live streaming integration (Agora)
- Real-time chat during streams
- Product showcase
- Interactive features

### 📊 Analytics & Insights
- Business intelligence dashboard
- Sales analytics
- User behavior tracking
- Performance metrics

---

## 🏗️ Architecture

### Tech Stack

| Category | Technology |
|----------|-----------|
| **Framework** | Laravel 11+ |
| **Language** | PHP 8.2+ |
| **Database** | PostgreSQL 15+ |
| **Cache** | Redis 7+ |
| **Search** | Laravel Scout + Meilisearch |
| **Queue** | Redis Queue |
| **Real-time** | Laravel Echo + Pusher |
| **Storage** | AWS S3 / MinIO |
| **Container** | Docker, Laravel Sail |
| **Testing** | PHPUnit, Pest |

---

## 🚀 Quick Start

### Prerequisites

```bash
PHP >= 8.2
Composer >= 2.6
Docker >= 20.0.0
```

### Installation

```bash
# Clone repository
git clone https://github.com/Naufall18/luxemart-backend.git
cd luxemart-backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Start Docker services
docker-compose up -d

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Start development server
php artisan serve
```

### Using Laravel Sail

```bash
# Install Sail
composer require laravel/sail --dev
php artisan sail:install

# Start Sail
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Seed database
./vendor/bin/sail artisan db:seed
```

---

## 📁 Project Structure

```
luxemart-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Auth/
│   │   │   │   ├── User/
│   │   │   │   ├── Product/
│   │   │   │   ├── Vendor/
│   │   │   │   ├── Order/
│   │   │   │   └── ...
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   └── Events/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Feature/
│   └── Unit/
├── docker-compose.yml
├── .env.example
└── README.md
```

---

## 🔧 Development

### Artisan Commands

```bash
# Run development server
php artisan serve

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run queue worker
php artisan queue:work

# Run scheduler
php artisan schedule:work
```

### Code Quality

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=AuthTest

# Code coverage
php artisan test --coverage

# PHP CS Fixer
./vendor/bin/pint

# PHPStan
./vendor/bin/phpstan analyse
```

---

## 🐳 Docker & Sail

### Docker Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Rebuild containers
docker-compose up -d --build
```

### Sail Commands

```bash
# Start Sail
./vendor/bin/sail up -d

# Run artisan commands
./vendor/bin/sail artisan migrate

# Run composer
./vendor/bin/sail composer install

# Run tests
./vendor/bin/sail test

# Access container
./vendor/bin/sail shell
```

---

## 📊 API Documentation

Interactive API documentation available at:
- **Swagger UI**: http://localhost:8000/api/documentation
- **Postman Collection**: `/docs/postman/`

### API Endpoints

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/user/profile
GET    /api/products
POST   /api/orders
...
```

---

## 🔐 Security

- ✅ Laravel Sanctum authentication
- ✅ Role-based access control (Spatie Permission)
- ✅ Rate limiting
- ✅ Input validation
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ CORS configuration
- ✅ Encrypted sensitive data

---

## 📈 Performance

- ⚡ Redis caching
- ⚡ Database query optimization
- ⚡ Eager loading
- ⚡ Queue jobs
- ⚡ Horizon for queue monitoring
- ⚡ Octane for performance boost

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific suite
php artisan test --testsuite=Feature

# Parallel testing
php artisan test --parallel
```

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📄 License

MIT License - see [LICENSE](LICENSE) file.

---

## 👥 Team

- **Backend Lead**: [Your Name]
- **Laravel Developer**: [Name]
- **DevOps Engineer**: [Name]

---

## 📞 Support

- 📧 Email: support@luxemart.com
- 📚 Docs: https://docs.luxemart.com
- 🐛 Issues: [GitHub Issues](https://github.com/Naufall18/luxemart-backend/issues)

---

<div align="center">

**Built with ❤️ using Laravel**

⭐ Star us on GitHub — it motivates us a lot!

</div>

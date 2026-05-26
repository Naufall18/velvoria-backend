<div align="center">

# 🏗️ LuxeMart Backend

### Premium Multi-Vendor Marketplace - Microservices Architecture

[![Node.js](https://img.shields.io/badge/Node.js-18+-339933?style=for-the-badge&logo=node.js&logoColor=white)](https://nodejs.org/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.0+-3178C6?style=for-the-badge&logo=typescript&logoColor=white)](https://www.typescriptlang.org/)
[![Express](https://img.shields.io/badge/Express-4.18+-000000?style=for-the-badge&logo=express&logoColor=white)](https://expressjs.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![MongoDB](https://img.shields.io/badge/MongoDB-6+-47A248?style=for-the-badge&logo=mongodb&logoColor=white)](https://www.mongodb.com/)
[![Redis](https://img.shields.io/badge/Redis-7+-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)

**Enterprise-grade backend system powering luxury e-commerce at scale**

[Features](#-key-features) • [Architecture](#-architecture) • [Quick Start](#-quick-start) • [Documentation](#-documentation)

</div>

---

## 🎯 Overview

LuxeMart Backend is a **production-ready microservices platform** designed for high-performance luxury marketplace operations. Built with modern technologies and best practices, it handles millions of transactions with enterprise-grade reliability.

### Why LuxeMart Backend?

- 🚀 **Scalable** - Microservices architecture for horizontal scaling
- 🔒 **Secure** - Enterprise-grade security with JWT, RBAC, and encryption
- ⚡ **Fast** - Redis caching, optimized queries, and CDN integration
- 🔄 **Real-time** - WebSocket support for live updates
- 📊 **Observable** - Comprehensive logging, monitoring, and analytics
- 🐳 **Cloud-Ready** - Docker & Kubernetes deployment ready

---

## ✨ Key Features

### 🏪 Multi-Vendor Marketplace
- Complete vendor onboarding and management
- Commission-based revenue model
- Vendor analytics and reporting
- Multi-store support

### 🛍️ Advanced E-Commerce
- Product catalog with variants
- Smart search with Elasticsearch
- Real-time inventory management
- Dynamic pricing and promotions

### 💳 Payment Processing
- Multiple payment gateways (Stripe, PayPal, Midtrans)
- Secure payment handling
- Automated payout system
- Refund management

### 📦 Order Management
- Real-time order tracking
- Multi-vendor order splitting
- Shipping integration
- Return and refund workflows

### 💬 Communication
- Real-time chat system
- Push notifications (FCM)
- Email notifications
- SMS alerts

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

### Microservices Overview

```
┌─────────────────────────────────────────────────────────────┐
│                      API Gateway (Kong)                      │
│                    Load Balancer & Routing                   │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
┌───────▼────────┐   ┌───────▼────────┐   ┌───────▼────────┐
│  Auth Service  │   │  User Service  │   │Product Service │
│   Port 3001    │   │   Port 3002    │   │   Port 3003    │
└────────────────┘   └────────────────┘   └────────────────┘
        │                     │                     │
┌───────▼────────┐   ┌───────▼────────┐   ┌───────▼────────┐
│ Vendor Service │   │ Order Service  │   │Payment Service │
│   Port 3004    │   │   Port 3005    │   │   Port 3006    │
└────────────────┘   └────────────────┘   └────────────────┘
        │                     │                     │
┌───────▼────────┐   ┌───────▼────────┐   ┌───────▼────────┐
│Shipping Service│   │ Review Service │   │  Chat Service  │
│   Port 3007    │   │   Port 3008    │   │   Port 3009    │
└────────────────┘   └────────────────┘   └────────────────┘
        │                     │                     │
┌───────▼────────┐   ┌───────▼────────┐   ┌───────▼────────┐
│Live Shop Svc   │   │ Notify Service │   │Analytics Svc   │
│   Port 3010    │   │   Port 3011    │   │   Port 3012    │
└────────────────┘   └────────────────┘   └────────────────┘
```

### Technology Stack

| Category | Technology |
|----------|-----------|
| **Runtime** | Node.js 18+ |
| **Language** | TypeScript 5.0+ |
| **Framework** | Express.js 4.18+ |
| **API Gateway** | Kong / Nginx |
| **Message Queue** | RabbitMQ |
| **Databases** | PostgreSQL 15+, MongoDB 6+ |
| **Cache** | Redis 7+ |
| **Search** | Elasticsearch 8+ |
| **Real-time** | Socket.io |
| **Container** | Docker, Kubernetes |
| **Monitoring** | Prometheus, Grafana |
| **Logging** | Winston, ELK Stack |

---

## 🚀 Quick Start

### Prerequisites

```bash
node >= 18.0.0
npm >= 9.0.0
docker >= 20.0.0
docker-compose >= 2.0.0
```

### Installation

```bash
# Clone repository
git clone https://github.com/Naufall18/luxemart-backend.git
cd luxemart-backend

# Install dependencies
npm install

# Setup environment
cp .env.example .env
# Edit .env with your configuration

# Start infrastructure (databases, cache, etc.)
docker-compose up -d

# Run database migrations
npm run migrate

# Seed initial data
npm run seed

# Start development server
npm run dev
```

### Docker Quick Start

```bash
# Build and start all services
docker-compose up --build

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

---

## 📁 Project Structure

```
luxemart-backend/
├── services/                    # Microservices
│   ├── auth-service/           # Authentication & Authorization
│   ├── user-service/           # User management
│   ├── product-service/        # Product catalog
│   ├── vendor-service/         # Vendor operations
│   ├── order-service/          # Order processing
│   ├── payment-service/        # Payment handling
│   ├── shipping-service/       # Logistics
│   ├── review-service/         # Reviews & ratings
│   ├── chat-service/           # Real-time messaging
│   ├── live-shopping-service/  # Live streaming
│   ├── notification-service/   # Notifications
│   └── analytics-service/      # Analytics
├── api-gateway/                # API Gateway configuration
├── shared/                     # Shared utilities
│   ├── types/                  # TypeScript types
│   ├── utils/                  # Helper functions
│   ├── constants/              # Constants
│   └── middleware/             # Shared middleware
├── infrastructure/             # Infrastructure as Code
│   ├── docker/                 # Dockerfiles
│   ├── kubernetes/             # K8s manifests
│   └── terraform/              # Terraform configs
├── scripts/                    # Utility scripts
├── docs/                       # Documentation
├── tests/                      # Integration tests
├── docker-compose.yml          # Docker Compose config
├── package.json                # Dependencies
└── README.md                   # This file
```

---

## 🔧 Development

### Running Services

```bash
# Run all services
npm run dev

# Run specific service
npm run dev:auth
npm run dev:user
npm run dev:product

# Run with watch mode
npm run dev:watch
```

### Code Quality

```bash
# Lint code
npm run lint

# Fix linting issues
npm run lint:fix

# Format code
npm run format

# Type check
npm run type-check
```

### Testing

```bash
# Run all tests
npm test

# Run tests with coverage
npm run test:coverage

# Run specific service tests
npm run test:auth

# Run integration tests
npm run test:integration

# Run e2e tests
npm run test:e2e
```

### Database Operations

```bash
# Create new migration
npm run migration:create -- AddUsersTable

# Run migrations
npm run migration:run

# Revert last migration
npm run migration:revert

# Seed database
npm run seed
```

---

## 🐳 Docker & Kubernetes

### Docker Commands

```bash
# Build all services
docker-compose build

# Start services
docker-compose up -d

# View logs
docker-compose logs -f [service-name]

# Stop services
docker-compose down

# Remove volumes
docker-compose down -v
```

### Kubernetes Deployment

```bash
# Apply configurations
kubectl apply -f infrastructure/kubernetes/

# Check deployments
kubectl get deployments

# Check pods
kubectl get pods

# View logs
kubectl logs -f deployment/auth-service

# Scale service
kubectl scale deployment auth-service --replicas=3
```

---

## 📊 Monitoring & Observability

### Available Dashboards

- **Prometheus**: http://localhost:9090
- **Grafana**: http://localhost:3000
- **Kibana**: http://localhost:5601
- **RabbitMQ**: http://localhost:15672

### Health Checks

```bash
# Check service health
curl http://localhost:3001/health

# Check all services
npm run health:check
```

---

## 🔐 Security

- ✅ JWT-based authentication
- ✅ Role-based access control (RBAC)
- ✅ Rate limiting
- ✅ Input validation & sanitization
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ CORS configuration
- ✅ Helmet.js security headers
- ✅ Encrypted sensitive data
- ✅ API key management

---

## 📈 Performance

- ⚡ Redis caching layer
- ⚡ Database query optimization
- ⚡ Connection pooling
- ⚡ Load balancing
- ⚡ Horizontal scaling
- ⚡ CDN integration
- ⚡ Gzip compression
- ⚡ Response pagination

---

## 📝 API Documentation

Interactive API documentation available at:
- **Swagger UI**: http://localhost:3000/api-docs
- **Postman Collection**: `/docs/postman/`

---

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md).

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📄 License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file.

---

## 👥 Team

- **Backend Lead**: [Your Name]
- **DevOps Engineer**: [Name]
- **Database Admin**: [Name]

---

## 📞 Support

- 📧 Email: support@luxemart.com
- 💬 Slack: #luxemart-backend
- 📚 Docs: https://docs.luxemart.com
- 🐛 Issues: [GitHub Issues](https://github.com/Naufall18/luxemart-backend/issues)

---

<div align="center">

**Built with ❤️ by LuxeMart Team**

⭐ Star us on GitHub — it motivates us a lot!

</div>

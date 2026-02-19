# 🚀 Digilians Admin Portal

**A Modern, Scalable Laravel 11 Admin Portal with Real-time Notifications**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-yellow.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-green.svg)](STATUS.md)

---

## 📋 Table of Contents

- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Documentation](#documentation)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

---

## ✨ Features

### Core Features
- ✅ **Live Notifications** - Real-time WebSocket notifications with fallback to AJAX polling
- ✅ **RBAC System** - Role-Based Access Control with 4 roles and 14 permissions
- ✅ **Bilingual Support** - Full Arabic & English localization with RTL/LTR
- ✅ **User Management** - Complete user CRUD with profile management
- ✅ **Audit Logs** - Comprehensive activity tracking and logging
- ✅ **Dashboard** - Real-time metrics and analytics widgets
- ✅ **Performance** - Optimized with caching and database indexing

### Advanced Features
- 🔔 **Desktop Notifications** - Browser notification API integration
- 🎨 **Velzon Template** - Modern Bootstrap 5 admin template
- 📊 **Data Export** - CSV export with streaming for large datasets
- 🔐 **Security** - CSRF protection, XSS prevention, and secure authentication
- 📱 **Responsive Design** - Mobile-friendly interface
- 🌙 **Dark Mode Ready** - Theme customization support

### Upcoming Features
- 🎨 **Theme Customization** - Per-user UI customization (4-5 days)
- 📊 **Profile Analytics** - User activity analytics with charts (3-4 days)
- 📄 **PDF Reports** - Interactive PDF generation (5-7 days)

---

## 💻 System Requirements

- **PHP:** 8.2 or higher
- **Composer:** 2.x
- **Node.js:** 18.x or higher
- **NPM:** 9.x or higher
- **MySQL:** 8.0 or higher
- **Redis:** 6.x or higher (optional, for caching)
- **Laravel Reverb:** For WebSocket support

---

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-org/digilians-admin.git
cd digilians-admin
```

### 2. Install Dependencies
```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Add broadcasting configuration
cat .env.broadcasting >> .env
```

### 4. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE digilians CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```

### 5. Storage Setup
```bash
# Create storage link
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
```

### 6. Compile Assets
```bash
# Development
npm run dev

# Production
npm run build
```

---

## ⚙️ Configuration

### Broadcasting (WebSocket)

Update your `.env` file:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=digilians-app
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@digilians.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Queue Configuration

```env
QUEUE_CONNECTION=database
# or
QUEUE_CONNECTION=redis
```

---

## 🎯 Usage

### Starting the Application

```bash
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Start Laravel Reverb (WebSocket server)
php artisan reverb:start

# Terminal 3: Start queue worker
php artisan queue:work

# Terminal 4: Start scheduler (optional)
php artisan schedule:work

# Terminal 5: Compile assets in watch mode (development)
npm run dev
```

### Default Credentials

| Role | Email | Password |
|:-----|:------|:---------|
| Super Admin | admin@digilians.com | password |
| Editor | editor@digilians.com | password |
| Regular User | user@digilians.com | password |

**⚠️ Change these credentials immediately in production!**

---

## 📚 Documentation

### Available Documentation

- **[FINAL_AUDIT_REPORT.md](FINAL_AUDIT_REPORT.md)** - Complete system audit
- **[COMPLETE_LIVE_NOTIFICATIONS_REPORT.md](COMPLETE_LIVE_NOTIFICATIONS_REPORT.md)** - Live notifications implementation
- **[COMPREHENSIVE_IMPROVEMENT_PLAN.md](COMPREHENSIVE_IMPROVEMENT_PLAN.md)** - Future enhancements roadmap
- **[FRONTEND_FIX_REPORT.md](FRONTEND_FIX_REPORT.md)** - Frontend fixes and improvements

### Quick Links

- [API Documentation](docs/API.md)
- [User Guide](docs/USER_GUIDE.md)
- [Developer Guide](docs/DEVELOPER_GUIDE.md)
- [Deployment Guide](docs/DEPLOYMENT.md)

---

## 🧪 Testing

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Test Live Notifications

```bash
php artisan tinker

>>> $user = \Modules\Users\Domain\Models\User::first();
>>> $user->notify(new \Illuminate\Notifications\DatabaseNotification([
...     'title' => 'Test Notification',
...     'message' => 'This is a test notification',
...     'priority' => 'high'
... ]));
```

Expected result: Notification appears instantly in navbar without page refresh.

---

## 🚢 Deployment

### Production Deployment Checklist

- [ ] Update `.env` for production
  ```env
  APP_ENV=production
  APP_DEBUG=false
  ```

- [ ] Optimize application
  ```bash
  php artisan optimize
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

- [ ] Compile production assets
  ```bash
  npm run build
  ```

- [ ] Set up supervisor for queue workers
  ```bash
  sudo cp deployment/supervisor/digilians-worker.conf /etc/supervisor/conf.d/
  sudo supervisorctl reread
  sudo supervisorctl update
  sudo supervisorctl start digilians-worker:*
  ```

- [ ] Configure web server (Nginx/Apache)
- [ ] Set up SSL certificate
- [ ] Configure firewall
- [ ] Set up automated backups
- [ ] Configure monitoring

### Server Requirements

**Recommended Specifications:**
- **CPU:** 2+ cores
- **RAM:** 4GB minimum, 8GB recommended
- **Storage:** 20GB minimum
- **Bandwidth:** 100Mbps

---

## 🏗️ Architecture

### Modular Structure

```
app/
├── Modules/
│   ├── Core/           # Core functionality
│   ├── Users/          # User management
│   └── Settings/       # System settings
├── Events/             # Application events
├── Observers/          # Model observers
└── Providers/          # Service providers
```

### Key Technologies

- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Vite, Bootstrap 5, Velzon Template
- **Real-time:** Laravel Reverb, Laravel Echo, Pusher JS
- **Database:** MySQL 8.0+
- **Caching:** Redis (optional)
- **Queue:** Database/Redis

---

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Use meaningful commit messages

---

## 📊 Project Status

### Current Version: 2.0.0

| Component | Status | Coverage |
|:----------|:------:|:--------:|
| Live Notifications | ✅ Complete | 100% |
| RBAC System | ✅ Complete | 100% |
| Localization | ✅ Complete | 100% |
| UI/UX | ✅ Complete | 100% |
| Performance | ✅ Optimized | 95% |
| Documentation | ✅ Complete | 100% |

### Roadmap

- **v2.1.0** - Theme Customization (4-5 days)
- **v2.2.0** - Profile Analytics (3-4 days)
- **v2.3.0** - Advanced Features (5-7 days)

---

## 📞 Support

For support, please contact:

- **Email:** support@digilians.com
- **Documentation:** [docs.digilians.com](https://docs.digilians.com)
- **Issue Tracker:** [GitHub Issues](https://github.com/your-org/digilians-admin/issues)

---

## 📄 License

This project is proprietary software. All rights reserved.

Copyright © 2026 Digilians. Unauthorized copying, modification, or distribution is prohibited.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Velzon](https://themesbrand.com/velzon/) - Admin Template
- [Spatie](https://spatie.be) - Laravel Permissions Package
- All contributors and supporters

---

## 📈 Statistics

![GitHub last commit](https://img.shields.io/github/last-commit/your-org/digilians-admin)
![GitHub issues](https://img.shields.io/github/issues/your-org/digilians-admin)
![GitHub pull requests](https://img.shields.io/github/issues-pr/your-org/digilians-admin)

---

**Made with ❤️ by the Digilians Team**

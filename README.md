# 🐔 Farm Management System

A comprehensive web-based farm management system designed for poultry farming operations. Built with PHP MVC architecture, featuring real-time financial tracking, unified operations management, and business intelligence.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)
![License](https://img.shields.io/badge/license-Proprietary-red.svg)

---

## ✨ Features

### 🐓 Poultry Management
- **Batch Tracking** - Complete lifecycle management for poultry batches
- **Feed Recording** - Direct entry system (no inventory lookups)
- **Medication Tracking** - Record treatments with direct entry
- **Vaccination Management** - Track vaccinations and due dates
- **Mortality Recording** - Monitor and analyze mortality rates
- **Weight Tracking** - Record and analyze bird weights
- **Egg Production** - Track daily egg production

### 💰 Financial Management
- **Real-time Expense Tracking** - All expenses calculated in real-time
- **Automatic Liability Management** - Unpaid expenses auto-create liabilities
- **Capital & Investment Tracking** - Monitor capital contributions and investments
- **Profit/Loss Calculations** - Automated P&L statements
- **Financial Traceability** - Complete audit trail for all transactions

### 📊 Reports & Analytics
- Batch performance reports
- Financial reports (P&L, expenses, revenue)
- Business health indicators
- Decision intelligence recommendations
- Forecasting and projections

### 👥 User Management
- Multi-user support
- Role-based access control
- Secure authentication system

---

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx with mod_rewrite enabled
- Composer (optional)

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/farm-management-system.git
cd farm-management-system
```

2. **Create database**
```bash
mysql -u root -p -e "CREATE DATABASE farmapp_db"
mysql -u root -p farmapp_db < database/rebuild_complete.sql
mysql -u root -p farmapp_db < database/users.sql
```

3. **Configure application**
```php
// app/config/Config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'farmapp_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('BASE_URL', 'http://yourdomain.com/');
```

4. **Set permissions**
```bash
chmod 755 app/
chmod 644 .htaccess
```

5. **Access the application**
```
URL: http://yourdomain.com/
Username: admin
Password: admin123
```

⚠️ **Change default password immediately after first login!**

---

## 📁 Project Structure

```
farm-management-system/
├── app/
│   ├── config/          # Configuration files
│   ├── controllers/     # Request handlers
│   ├── models/          # Database logic
│   ├── views/           # UI templates
│   ├── core/            # Framework core
│   └── Router/          # Route definitions
├── database/            # SQL files
├── .htaccess           # URL rewriting
└── index.php           # Application entry point
```

---

## 🎯 Key Highlights

### Unified System (No Inventory Lookups)
Unlike traditional systems, our unified approach eliminates the need for separate inventory management:
- **Direct Entry**: Enter feed, medication, and vaccination details directly
- **No Setup Required**: No need to create inventory items first
- **Faster Workflow**: Reduced steps for data entry
- **Real-time Tracking**: All costs tracked immediately

### Automatic Liability Management
- Unpaid expenses automatically create liabilities
- Real-time outstanding calculations (principal - payments)
- No manual liability creation needed
- Complete financial transparency

### Real-time Calculations
- All totals calculated from database in real-time
- No cached data or stale information
- Accurate financial metrics always
- Transparent calculation methods

---

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ (MVC Architecture)
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5, HTML5, CSS3, JavaScript
- **Security**: PDO with prepared statements, bcrypt password hashing
- **Architecture**: Custom MVC framework with RESTful routing

---

## 📊 Database Schema

### Core Tables
- `farms` - Farm information
- `animal_batches` - Poultry batches
- `users` - System users

### Operations
- `feed_records` - Feed usage tracking
- `medication_records` - Medication tracking
- `vaccination_records` - Vaccination tracking
- `mortality_records` - Mortality data
- `weight_records` - Weight measurements
- `egg_production_records` - Egg production

### Financial
- `expenses` - All expenses
- `liabilities` - Loans and obligations
- `capital` - Capital contributions
- `investments` - Investment tracking
- `sales` - Sales records

---

## 🔒 Security Features

- Session-based authentication
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection
- CSRF protection
- Role-based access control

---

## 📖 Documentation

- [Deployment Guide](DEPLOYMENT_SUMMARY.md)
- [Quick Start Guide](QUICK_START.md)
- [API Documentation](URL_ACCESS_GUIDE.md)
- [Financial Tracking Guide](EXPENSE_TRACKING_GUIDE.md)

---

## 🧪 Testing

All critical systems have been tested:
- ✅ Feed recording (direct entry)
- ✅ Medication tracking
- ✅ Vaccination management
- ✅ Expense calculations
- ✅ Liability management
- ✅ Financial dashboards
- ✅ Report generation
- ✅ User authentication

---

## 🤝 Contributing

This is a proprietary system. For contributions or feature requests, please contact the development team.

---

## 📝 License

Proprietary - All rights reserved

---

## 👨‍💻 Development Team

**Farm Management Team**  
Version: 1.0.0  
Release Date: March 31, 2026

---

## 📞 Support

For support, bug reports, or feature requests:
- Email: kwakwoasareenoch1211@gmail.com
- GitHub Issues: [Report a bug](https://github.com/kwakwoasareenoch1211-sketch/farm-management-system/issues)

---

## 🎉 Changelog

### Version 1.0.0 (March 31, 2026)
- ✅ Initial production release
- ✅ Unified system (no inventory lookups)
- ✅ Expense-liability integration
- ✅ Real-time financial calculations
- ✅ Complete poultry management
- ✅ Comprehensive reporting
- ✅ User authentication
- ✅ Multi-farm support

---

## ⚡ Performance

- Optimized database queries
- Indexed foreign keys
- Real-time calculations (no caching overhead)
- Responsive Bootstrap 5 UI
- Minimal JavaScript dependencies

---

## 🌟 Screenshots

Screenshots coming soon! The system includes:
- Clean, modern login interface
- Comprehensive dashboard with real-time statistics
- Poultry operations management interface
- Financial tracking and reporting dashboards
- Batch management with lifecycle tracking
- Simple, direct-entry forms for feed, medication, and vaccination

---

**Built with ❤️ for modern farm management**

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Cashflow Management System

A comprehensive cashflow management system for cooperative organizations with role-based access control.

## 🚀 Quick Setup

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration
Update your `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cashflow_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations & Seeders
```bash
php artisan migrate:fresh --seed
```

### 5. Start Development Server
```bash
php artisan serve
```

## 👥 User Roles & Access

### 🔐 Admin Users
- **Full system access**
- **Email:** admin@coop.com | **Password:** admin123
- **Email:** it.manager@coop.com | **Password:** admin123

**Features:**
- Dashboard overview
- GL Account setup
- User management
- System configuration

### 🏢 Head Office Users
- **Multi-branch oversight**
- **Email:** john.smith@coop.com | **Password:** head123
- **Email:** maria.garcia@coop.com | **Password:** head123
- **Email:** robert.johnson@coop.com | **Password:** head123

**Features:**
- Dashboard with consolidated metrics
- File upload management
- Cashflow data management
- Branch oversight

### 🏪 Branch Users
- **Branch-specific access (read-only)**
- **Email:** ana.santos@makati.coop.com | **Password:** branch123
- **Email:** luz.cruz@qc.coop.com | **Password:** branch123
- **Email:** carmen.lim@cebu.coop.com | **Password:** branch123
- **Email:** elena.rodriguez@davao.coop.com | **Password:** branch123
- **Email:** patricia.gomez@baguio.coop.com | **Password:** branch123
- **Email:** sofia.hernandez@iloilo.coop.com | **Password:** branch123

**Features:**
- Branch-specific dashboard
- Read-only cashflow view
- Branch data filtering

## 📊 Sample Data

The seeder creates:
- **6 Branches** (Makati, Quezon City, Cebu, Davao, Baguio, Iloilo)
- **2 Admin Users**
- **3 Head Office Users**
- **12 Branch Users** (2 per branch)
- **36 Cashflow Files** (6 months × 6 branches)
- **576 Cashflow Entries** (16 accounts × 36 files)

## 🏗️ System Architecture

### Database Structure
```
users
├── id, name, email, password, role, branch_id, status
├── Relationships: branch (BelongsTo)

branches
├── id, name, head_id
├── Relationships: head (BelongsTo), users (HasMany)

cashflow_files
├── id, file_name, file_path, original_name, year, month
├── branch_id, uploaded_by, status, description
├── Relationships: branch (BelongsTo), cashflows (HasMany)

cashflows
├── id, cashflow_file_id, branch_id, year, month
├── account_code, account_name, account_type, cashflow_category
├── actual_amount, projection_percentage, projected_amount, total
├── Relationships: branch (BelongsTo), cashflowFile (BelongsTo)
```

### Role-Based Access Control
- **Admin:** Full system access
- **Head:** Multi-branch oversight, file management
- **Branch:** Branch-specific read-only access

### Security Features
- Role-based middleware
- Branch isolation for branch users
- CSRF protection
- Input validation

## 🎨 UI Features

- **Responsive Design** - Works on desktop and mobile
- **Bootstrap 5** - Modern, clean interface
- **DataTables** - Interactive tables with sorting/filtering
- **Bootstrap Icons** - Consistent iconography
- **Toast Notifications** - User feedback
- **Modal Dialogs** - Clean form interactions

## 🔧 Development

### Key Controllers
- `Admin/DashboardController` - Admin dashboard
- `Admin/SetupController` - GL Account setup
- `Admin/UsersController` - User management
- `Head/DashboardController` - Head office dashboard
- `Head/FileController` - File upload management
- `Head/CashflowController` - Cashflow management
- `Branch/DashboardController` - Branch dashboard
- `Branch/CashflowController` - Branch cashflow view

### Key Models
- `User` - User management with role-based methods
- `Branch` - Branch management
- `CashflowFile` - File upload tracking
- `Cashflow` - Cashflow data

### Routes
- `/admin/*` - Admin routes (admin middleware)
- `/head/*` - Head office routes (head middleware)
- `/branch/*` - Branch routes (branch middleware)

## 📝 License

This project is proprietary software for cooperative organizations.

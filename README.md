# Jobs & Internships - Complete Management System

A comprehensive job and internship management platform built with **Symfony 7**, **Doctrine ORM**, and **MySQL**.

## 🚀 Features

### User Roles & Permissions
- **ROLE_USER** (Candidate): Browse offers, apply, save favorites, manage profile
- **ROLE_COMPANY** (Employer): Post jobs, view applicants, manage offers, company profile
- **ROLE_ADMIN** (Administrator): Manage users, approve companies, moderate offers, view statistics and logs

### Admin Dashboard (Fully Implemented ✅)
- **Statistics Dashboard**: Real-time stats for users, companies, offers, and applications
- **User Management**: Create, edit, delete, and view all users with role filtering
- **Company Management**: Approve/reject companies, edit company details, view pending approvals
- **Job Offer Management**: List, edit, activate/deactivate, and delete job offers
- **Category Management**: Create, edit, delete categories with hierarchical support
- **Activity Logs**: Complete audit trail of all admin actions with filtering and CSV export

## 📋 Prerequisites

- PHP 8.2+
- MySQL 8.0+
- Composer
- Symfony CLI (optional)

## 🔧 Installation

### 1. Install Dependencies
```bash
composer install
```

### 2. Configure Database
Edit `.env` and set your `DATABASE_URL`:
```
DATABASE_URL="mysql://root:@127.0.0.1:3306/jobs_internships_db?serverVersion=8.0"
```

### 3. Setup Database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Start Server
```bash
# Using PHP built-in server
php -S localhost:8000 -t public

# Or using Symfony CLI
symfony serve
```

Access the application at `http://localhost:8000`

## 📁 Project Structure

```
src/
├── Entity/              # Doctrine entities
│   ├── User.php
│   ├── Company.php
│   ├── JobOffer.php
│   ├── Application.php
│   ├── Category.php
│   ├── Skill.php
│   ├── SavedOffer.php
│   └── AdminLog.php
├── Repository/          # Custom repositories
├── Service/             # Business logic services
│   ├── ApplicationService.php
│   ├── AdminLogService.php
│   └── CompanyApprovalService.php
├── Form/                # Form types
├── Controller/          # Controllers grouped by role
│   ├── Candidate/
│   ├── Company/
│   └── Admin/           # ✅ Fully implemented
└── Security/            # Authentication

templates/
├── admin/               # ✅ Admin templates (fully implemented)
│   ├── base.html.twig
│   ├── stats/
│   ├── users/
│   ├── companies/
│   ├── offers/
│   ├── categories/
│   └── logs/
├── candidate/           # Candidate templates
├── company/             # Company templates
└── security/            # Login/register templates
```

## 🔐 Security Configuration

Role hierarchy in `config/packages/security.yaml`:
```yaml
role_hierarchy:
    ROLE_ADMIN: ROLE_COMPANY
    ROLE_COMPANY: ROLE_USER
```

### Route Access
- `/admin/*` → ROLE_ADMIN only
- `/company/*` → ROLE_COMPANY required
- `/candidate/*` → ROLE_USER required
- `/login`, `/register` → Public

## 📚 Admin Endpoints (Fully Implemented)

### Statistics
- `GET /admin/stats` - Dashboard with comprehensive statistics
- `GET /admin/stats/users` - Detailed user statistics
- `GET /admin/stats/companies` - Company statistics
- `GET /admin/stats/applications` - Application statistics

### User Management
- `GET /admin/users` - List all users (with filters: role, search)
- `GET /admin/users/create` - Create new user
- `GET|POST /admin/users/{id}/edit` - Edit user
- `GET /admin/users/{id}` - View user details
- `POST /admin/users/{id}/delete` - Delete user

### Company Management
- `GET /admin/companies` - List all companies (with status filters)
- `GET /admin/companies/pending` - List pending approvals
- `GET /admin/companies/{id}` - View company details
- `GET|POST /admin/companies/{id}/edit` - Edit company
- `POST /admin/companies/{id}/approve` - Approve company
- `POST /admin/companies/{id}/reject` - Reject company (with reason)
- `POST /admin/companies/{id}/delete` - Delete company

### Job Offer Management
- `GET /admin/offers` - List all offers (with status filters)
- `GET /admin/offers/{id}` - View offer details
- `GET|POST /admin/offers/{id}/edit` - Edit offer
- `POST /admin/offers/{id}/toggle` - Activate/deactivate offer
- `POST /admin/offers/{id}/delete` - Delete offer

### Category Management
- `GET /admin/categories` - List all categories
- `GET|POST /admin/categories/create` - Create category (auto-generates slug)
- `GET|POST /admin/categories/{id}/edit` - Edit category
- `GET /admin/categories/{id}` - View category details
- `POST /admin/categories/{id}/delete` - Delete category (validates no linked offers)

### Activity Logs
- `GET /admin/logs` - List logs (with filters: admin, entity type, action, date range)
- `GET /admin/logs/{id}` - View log details
- `GET /admin/logs/export` - Export logs as CSV

## 🛠️ Development

### Create Migration
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Create Entity
```bash
php bin/console make:entity
```

### Create Form Type
```bash
php bin/console make:form
```

### Create Controller
```bash
php bin/console make:controller
```

### Clear Cache
```bash
php bin/console cache:clear
```

### List Routes
```bash
php bin/console debug:router
```

## ✅ Implementation Status

### ✅ Completed (Admin Module)
- [x] AdminStatsController - Dashboard with comprehensive statistics
- [x] AdminUserController - Full CRUD with filtering and search
- [x] AdminCompanyController - Company management with approval workflow
- [x] AdminOfferController - Job offer moderation
- [x] AdminCategoryController - Category management with validation
- [x] AdminLogController - Activity logging with filters and export
- [x] AdminLogService - Complete logging service integration
- [x] CompanyApprovalService - Approval/rejection workflow
- [x] All admin Twig templates created
- [x] Form handling and validation
- [x] Flash messages for user feedback
- [x] Security with role-based access control

### 🔄 In Progress / To Do
- [ ] Authentication controller (login/register)
- [ ] Candidate controllers implementation
- [ ] Company controllers implementation
- [ ] File upload handling (CV, logos)
- [ ] Email notifications
- [ ] Test fixtures
- [ ] Unit tests
- [ ] Pagination for large lists

## 🗄️ Database Schema

### Entities & Relations
- **User** (1) ←→ (1) **Company** : Company owner
- **User** (1) ←→ (n) **Application** : Candidate applications
- **User** (1) ←→ (n) **SavedOffer** : Saved job offers
- **User** (m) ←→ (n) **Skill** : User skills
- **User** (1) ←→ (n) **AdminLog** : Admin activity logs

- **Company** (1) ←→ (n) **JobOffer** : Published offers
- **JobOffer** (1) ←→ (n) **Application** : Applications per offer
- **JobOffer** (1) ←→ (n) **SavedOffer** : Saved offers
- **JobOffer** (n) ←→ (1) **Category** : Offer category

- **Category** (1) ←→ (n) **Category** : Hierarchical categories

## 📧 Email Configuration

To enable email notifications, edit `.env`:
```
MAILER_DSN=smtp://user:pass@smtp.example.com:587?encryption=tls
```

Then uncomment email sending in services (CompanyApprovalService, etc.)

## 🧪 Testing

### Run Tests
```bash
php bin/phpunit
```

### Create Test
```bash
php bin/console make:test
```

## 📝 Code Conventions

- Use **Symfony Attributes** for routes and validation
- Constructor injection for dependencies
- Immutable dates (DateTimeImmutable)
- Repository patterns for queries
- Services for business logic
- Form Types for validation
- All admin actions are logged via AdminLogService

## 🐛 Troubleshooting

### Database Connection Error
```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
```

### Cache Issues
```bash
php bin/console cache:clear
```

### Permission Issues (Linux/Mac)
```bash
chmod -R 755 public/uploads/
chmod -R 777 var/
```

## 📞 Support

- [Symfony Documentation](https://symfony.com/doc)
- [Doctrine Documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/index.html)

---

**Project Status:** Admin module fully implemented and tested ✅

**Last Updated:** December 2024

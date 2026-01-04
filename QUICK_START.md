# 🚀 Quick Start Guide - Jobs & Internships Project

## ✅ Project Status
**The project has been successfully merged and verified as FULLY FUNCTIONAL.**

---

## 📋 What Was Fixed

| Issue | Fix | Status |
|-------|-----|--------|
| Migration not tracked | Added to database | ✅ |
| Class name mismatch | Renamed RegistrationFormType | ✅ |
| Deprecated constraints | Updated to named arguments | ✅ |
| Missing form field | Added checkbox to template | ✅ |

---

## 🎯 Running the Project

### Start Development Server
```bash
php bin/console server:start
# or
php bin/console server:run
```
**Access at**: http://localhost:8000

### Create Test User
```bash
php bin/console app:create-user
```

### Load Dummy Data
```bash
php bin/console app:create-dummy-data
```

### Check Database Status
```bash
php bin/console doctrine:migrations:status
```

---

## 🧪 Testing

### Run All Tests
```bash
php bin/phpunit
```

### Run Specific Test
```bash
php bin/phpunit tests/Controller/RegistrationControllerTest.php
```

### Validate Configuration
```bash
php bin/console lint:yaml
php bin/console lint:twig templates/
php bin/console lint:container
```

---

## 📁 Project Structure

```
src/
├── Controller/        - Request handlers (Admin, Candidate, Company)
├── Entity/           - Database models (8 entities)
├── Form/             - Form types (8 forms)
├── Repository/       - Custom queries
└── Service/          - Business logic

templates/
├── base.html.twig                  - Main layout
├── registration/register.html.twig - Registration form (✅ FIXED)
├── admin/                          - Admin pages
├── candidate/                      - Candidate pages
└── company/                        - Company pages

config/
├── packages/         - Bundle configurations
├── routes.yaml       - URL routing
└── services.yaml     - Service definitions

migrations/
├── Version20251201193544.php  - Initial schema
└── Version20260101112104.php  - JSON type refinement
```

---

## 🔐 User Roles

| Role | Features |
|------|----------|
| **ROLE_USER** (Candidate) | Browse jobs, apply, save offers |
| **ROLE_COMPANY** | Create jobs, manage applications |
| **ROLE_ADMIN** | Manage users, approve companies, view logs |

---

## 📊 Database Schema

### Core Tables
- **users** - User accounts with roles
- **companies** - Company profiles
- **job_offers** - Job listings
- **applications** - Job applications
- **categories** - Job categories
- **skills** - Skill master data
- **user_skills** - User-skill relationships
- **saved_offers** - Bookmarked jobs
- **admin_logs** - Admin activity logging
- **messenger_messages** - Queue system

---

## 🛠️ Common Tasks

### Add New Job Category
```bash
php bin/console app:create-category "Category Name"
```

### Add Skills
```bash
php bin/console app:add-skill "PHP" "python" "javascript"
```

### Reset Database
```bash
# Drop and recreate
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### View Routes
```bash
php bin/console debug:router
```

### View Container
```bash
php bin/console debug:container
```

---

## 🐛 Troubleshooting

### Clear Cache
```bash
php bin/console cache:clear
```

### Clear Logs
```bash
rm -rf var/log/*
```

### Check Database Connection
```bash
php bin/console doctrine:database:create --if-not-exists
```

### Verify Migrations
```bash
php bin/console doctrine:migrations:status
```

---

## 📝 Key Files You Edited

1. **src/Form/RegistrationFormType.php**
   - Fixed class name
   - Updated constraint syntax

2. **templates/registration/register.html.twig**
   - Added agreeTerms checkbox field

---

## ✨ Features

### User Management
- Register as candidate or company
- Email verification (when configured)
- Role-based access control
- Profile management
- CV upload

### Job Management
- Create/edit job offers
- Categorize by type and industry
- Search and filter
- Save favorite jobs
- Apply with message

### Company Features
- Company profile
- Logo upload
- Website link
- Pending approval workflow
- Application management

### Admin Features
- User management
- Company approval
- Activity logging
- Audit trail
- System monitoring

---

## 🔗 Useful Links

- **Symfony Docs**: https://symfony.com/doc/current/index.html
- **Doctrine ORM**: https://www.doctrine-project.org/
- **Twig Documentation**: https://twig.symfony.com/

---

## 📞 Support

For issues:
1. Check `var/log/dev.log` for errors
2. Run `php bin/console debug:*` commands
3. Verify database with `doctrine:database:*` commands
4. Clear cache and try again

---

## ✅ Verification Results

- [x] Database: 10 tables created
- [x] Migrations: 2/2 executed
- [x] PHP: No syntax errors
- [x] YAML: All valid
- [x] Twig: 36 templates valid
- [x] Dependencies: All resolved
- [x] Forms: All functional
- [x] Routes: All accessible

---

**Last Updated**: January 4, 2026  
**Status**: ✅ PRODUCTION READY (with configuration)


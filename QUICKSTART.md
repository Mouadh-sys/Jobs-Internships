# 🚀 Quick Start Guide - Jobs & Internships Platform

## Installation & Setup (First Time)

### 1. Database & Migrations
```bash
cd "D:\projects\Jobs & Internships"
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. Create Upload Directory
```bash
mkdir -p public/uploads/cv
```

### 3. Clear Cache
```bash
php bin/console cache:clear
```

### 4. Create Admin User
```bash
php bin/console app:create-user admin@example.com MyPassword123 "Admin User" ROLE_ADMIN
```

### 5. Create Test Candidate User
```bash
php bin/console app:create-user candidate@example.com Password123 "John Candidate" ROLE_USER
```

### 6. Create Test Company User
```bash
php bin/console app:create-user company@example.com Password123 "Tech Corp" ROLE_COMPANY
```

## Running the Application

### Start Development Server
```bash
# Option 1: Using PHP built-in server
php -S 127.0.0.1:8000 -t public

# Option 2: Using Symfony CLI
symfony server:start
```

### Access the Application
- **Home:** http://127.0.0.1:8000/
- **Login:** http://127.0.0.1:8000/login
- **Register:** http://127.0.0.1:8000/register
- **Profile (after login):** http://127.0.0.1:8000/candidate/profile
- **Admin Skills (admin only):** http://127.0.0.1:8000/admin/skills

## Test Accounts

| Email | Password | Role | Purpose |
|-------|----------|------|---------|
| admin@example.com | MyPassword123 | ROLE_ADMIN | Manage system, create skills |
| candidate@example.com | Password123 | ROLE_USER | View/edit profile, upload CV, add skills |
| company@example.com | Password123 | ROLE_COMPANY | Company profile management |

## Running Tests

```bash
# All tests
php bin/phpunit

# Specific test
php bin/phpunit --filter SecurityControllerTest
php bin/phpunit --filter RegistrationControllerTest
php bin/phpunit --filter ProfileControllerTest
```

## Key Features Implemented

### 🔐 Security
- User login/logout
- Password hashing (bcrypt with 'auto' algorithm)
- CSRF protection on all forms
- Role-based access control

### 👤 User Registration
- Candidate registration at `/register`
- Email validation
- Password validation & matching
- Terms agreement requirement

### 📋 User Profile
- View personal profile at `/candidate/profile`
- Edit profile information (email, fullName, location)
- Add/remove skills from profile
- Upload/download CV files

### 💼 Skills Management (Admin)
- List all skills at `/admin/skills`
- Create new skills
- Edit existing skills
- Delete skills
- Auto-slug generation from skill name

### 👥 User Management
- Create users via console command: `app:create-user`
- Support for multiple roles (ROLE_USER, ROLE_COMPANY, ROLE_ADMIN)
- Email & password validation

## Important URLs

### Public Routes
- `GET /login` - Login form
- `POST /login` - Process login
- `GET /logout` - Logout user
- `GET /register` - Registration form
- `POST /register` - Process registration

### Protected Routes (Candidate)
- `GET /candidate/profile` - View profile
- `GET /candidate/profile/edit` - Edit profile form
- `POST /candidate/profile/edit` - Save profile changes
- `GET /candidate/profile/cv/download` - Download CV

### Admin Routes
- `GET /admin/skills` - List all skills
- `GET /admin/skills/new` - Create skill form
- `POST /admin/skills/new` - Save new skill
- `GET /admin/skills/{id}/edit` - Edit skill form
- `POST /admin/skills/{id}/edit` - Update skill
- `POST /admin/skills/{id}` - Delete skill

## Directory Structure (New Files)

```
src/
  ├── Command/
  │   └── CreateUserCommand.php                    [NEW]
  ├── Controller/
  │   ├── RegistrationController.php              [NEW]
  │   ├── SecurityController.php                  [UPDATED]
  │   ├── Candidate/
  │   │   └── ProfileController.php               [UPDATED]
  │   └── Admin/
  │       └── SkillController.php                 [NEW]
  └── Form/
      ├── RegistrationFormType.php                [NEW]
      ├── SkillType.php                           [NEW]
      └── UserProfileType.php                     [UPDATED]

templates/
  ├── registration/
  │   └── register.html.twig                      [NEW]
  ├── security/
  │   └── login.html.twig                         [CREATED]
  ├── candidate/profile/
  │   ├── show.html.twig                          [NEW]
  │   └── edit.html.twig                          [NEW]
  └── admin/skill/
      ├── index.html.twig                         [NEW]
      ├── new.html.twig                           [NEW]
      └── edit.html.twig                          [NEW]

tests/Controller/
  ├── SecurityControllerTest.php
  ├── RegistrationControllerTest.php              [NEW]
  └── ProfileControllerTest.php                   [NEW]

config/
  ├── packages/security.yaml                      [UPDATED]
  └── services.yaml                               [UPDATED]
```

## Troubleshooting

### "Command not found: app:create-user"
- Run: `php bin/console cache:clear`
- Verify file exists: `src/Command/CreateUserCommand.php`

### Registration form not submitting
- Check password fields match
- Ensure terms checkbox is checked
- Check email is valid format
- Check all fields are filled

### CV upload not working
- Ensure directory exists: `public/uploads/cv/`
- Check file is PDF or DOC format
- Check file size is under 5MB

### Access denied errors
- Ensure you're logged in for protected routes
- Check user has correct role
- Try logout and login again

### Template not found errors
- Run: `php bin/console cache:clear`
- Verify template path matches exactly

## What's Next?

After basic setup works, consider implementing:

1. ✅ Email verification on registration
2. ✅ Password reset functionality  
3. ✅ Company registration flow
4. ✅ Job offer management
5. ✅ Application tracking
6. ✅ Admin dashboard with analytics
7. ✅ Advanced search and filtering
8. ✅ API endpoints for mobile app

## Support & Documentation

- Symfony Documentation: https://symfony.com/doc/7.3/
- Doctrine ORM: https://www.doctrine-project.org/projects/orm.html
- Form Documentation: https://symfony.com/doc/7.3/forms.html
- Security: https://symfony.com/doc/7.3/security.html

---

**Last Updated:** December 31, 2025
**Symfony Version:** 7.3
**PHP Version:** 8.2+


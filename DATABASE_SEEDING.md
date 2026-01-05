# 🌱 Database Seeding Guide

This guide explains how to seed your database with test data using Doctrine Fixtures.

## 📋 Prerequisites

- Database must be created and migrations must be run
- Doctrine Fixtures Bundle is already installed

## 🚀 Quick Start

### Load All Fixtures

To populate your database with test data:

```bash
symfony php bin/console doctrine:fixtures:load
```

**⚠️ Warning:** This command will **purge** (delete) all existing data in your database before loading the fixtures.

### Load Fixtures Without Purging (Append Mode)

If you want to add fixtures without deleting existing data:

```bash
symfony php bin/console doctrine:fixtures:load --append
```

## 📊 What Gets Created

The fixtures will create:

### 👥 Users (12 total)
- **2 Admin Users**
  - `admin@example.com` / `password123`
  - `admin2@example.com` / `password123`
- **5 Candidate Users**
  - `candidate@example.com` / `password123`
  - `candidate2@example.com` / `password123`
  - `candidate3@example.com` / `password123`
  - `candidate4@example.com` / `password123`
  - `candidate5@example.com` / `password123`
- **5 Company Users**
  - `company@example.com` / `password123`
  - `company2@example.com` / `password123`
  - `company3@example.com` / `password123`
  - `company4@example.com` / `password123`
  - `company5@example.com` / `password123`

### 🏢 Companies (5 total)
- TechCorp Inc. (Approved, Active)
- Innovate Solutions (Approved, Active)
- Digital Ventures (Approved, Active)
- StartupHub (Pending Approval, Active)
- Global Tech (Approved, Active)

### 🏷️ Categories (15+ total)
- **Parent Categories:** Technology, Design, Marketing, Sales, Finance, Human Resources
- **Child Categories:** Web Development, Mobile Development, DevOps, Data Science, UI/UX Design, Graphic Design, Product Design, Digital Marketing, Content Marketing, SEO, Business Development, Account Management, Accounting, Financial Analysis, Recruitment, Talent Management

### 💼 Skills (30+ total)
- Programming Languages: PHP, JavaScript, Python, Java, C++, C#, Ruby, Go
- Frameworks: React, Vue.js, Angular, Node.js, Symfony, Laravel
- Databases: MySQL, PostgreSQL, MongoDB, Redis
- DevOps: Docker, Kubernetes, AWS, Azure, Git
- Frontend: HTML, CSS, SASS, TypeScript
- Soft Skills: Agile, Scrum, Project Management
- Design: UI/UX Design, Figma, Adobe XD
- Marketing: Marketing, SEO, Content Writing

### 💼 Job Offers (15-25 total)
- 3-5 job offers per company
- Various types: CDI, CDD, Stage, Freelance
- Different locations: Paris, London, Berlin, Barcelona, Remote
- Mix of active and inactive offers
- Random creation dates within the last 30 days

### 📝 Applications (10-20 total)
- 2-4 applications per candidate
- Various statuses: PENDING, ACCEPTED, REJECTED, WITHDRAWN
- Random creation dates within the last 20 days

### ⭐ Saved Offers (5-15 total)
- 1-3 saved offers per candidate
- Random creation dates within the last 15 days

### 📋 Admin Logs (20-30 total)
- Various actions: CREATE, UPDATE, DELETE, APPROVE, REJECT
- Different entity types: User, Company, JobOffer, Category, Application
- Random creation dates within the last 30 days

## 🔄 Reset Database

To completely reset your database and reload fixtures:

```bash
# Drop the database
symfony php bin/console doctrine:database:drop --force

# Create the database
symfony php bin/console doctrine:database:create

# Run migrations
symfony php bin/console doctrine:migrations:migrate --no-interaction

# Load fixtures
symfony php bin/console doctrine:fixtures:load
```

## 🎯 Test Accounts

After loading fixtures, you can use these accounts to test the application:

| Email | Password | Role | Purpose |
|-------|----------|------|---------|
| `admin@example.com` | `password123` | Admin | Full admin access |
| `candidate@example.com` | `password123` | Candidate | Browse jobs, apply, save offers |
| `company@example.com` | `password123` | Company | Create job offers, manage applications |

## 📝 Customizing Fixtures

To modify the fixtures, edit the file:
```
src/DataFixtures/AppFixtures.php
```

You can:
- Change the number of entities created
- Modify user credentials
- Add more categories, skills, or job offers
- Adjust the relationships between entities

## 🔍 Verify Data

After loading fixtures, you can verify the data:

```bash
# Check users
symfony php bin/console doctrine:query:sql "SELECT COUNT(*) FROM user"

# Check companies
symfony php bin/console doctrine:query:sql "SELECT COUNT(*) FROM company"

# Check job offers
symfony php bin/console doctrine:query:sql "SELECT COUNT(*) FROM job_offer"
```

## ⚠️ Important Notes

1. **All passwords are:** `password123` (for testing only)
2. **Fixtures will delete existing data** unless you use `--append`
3. **Some companies are pending approval** to test the approval workflow
4. **Dates are randomized** to simulate real-world scenarios
5. **Relationships are properly maintained** (e.g., applications link to candidates and job offers)

## 🐛 Troubleshooting

### Error: "Table doesn't exist"
Make sure migrations are run first:
```bash
symfony php bin/console doctrine:migrations:migrate
```

### Error: "Duplicate entry"
Use `--purge-with-truncate` to ensure clean deletion:
```bash
symfony php bin/console doctrine:fixtures:load --purge-with-truncate
```

### Error: "Foreign key constraint"
Make sure to load fixtures in the correct order. The current fixtures handle dependencies automatically.


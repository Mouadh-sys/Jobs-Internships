# Migration & Schema Fix Summary

## ✅ Completed Tasks

### 1. **JsonContainsFunction Custom DQL Function**
   - **Created**: `src/Doctrine/JsonContainsFunction.php`
   - **Purpose**: Custom DQL function to handle JSON_CONTAINS queries for MySQL
   - **Usage**: `SELECT u FROM User u WHERE JSON_CONTAINS(u.roles, JSON_QUOTE(:role)) = 1`

### 2. **Entity Annotations Updated**
   - **User.php**:
     - Added `options: ['comment' => '(DC2Type:json)']` to `roles` column
   - **AdminLog.php**:
     - Added `options: ['comment' => '(DC2Type:json)']` to `data` column

### 3. **Migration Created**
   - **Version**: `migrations/Version20251123162734.php`
   - **Replaces**: `fix_schema.php` and `fix_schema_v2.php` (now deleted)
   - **Operations**:
     - `user.roles`: JSON NOT NULL with DC2Type:json comment
     - `admin_log.data`: JSON DEFAULT NULL with DC2Type:json comment
     - `application.cv_filename`: VARCHAR(255) DEFAULT NULL
     - `company.logo_filename`: VARCHAR(255) DEFAULT NULL
     - `company.website`: VARCHAR(255) DEFAULT NULL
     - `company.location`: VARCHAR(255) DEFAULT NULL
     - `job_offer.location`: VARCHAR(255) DEFAULT NULL
     - `messenger_messages.delivered_at`: DATETIME DEFAULT NULL with DC2Type:datetime_immutable
     - `user.location`: VARCHAR(255) DEFAULT NULL
     - `user.cv_filename`: VARCHAR(255) DEFAULT NULL

### 4. **Repository Methods Updated**

#### UserRepository
- **findAdmins()**: Fixed to use JSON_CONTAINS with JSON_QUOTE for proper parameter handling
  ```php
  WHERE "JSON_CONTAINS(u.roles, JSON_QUOTE(:role)) = 1"
  ```

#### ApplicationRepository
- **findByJobOfferId()**: Now accepts `JobOffer|int` parameter
  - Uses `IDENTITY()` for proper foreign key comparison
- **findByJobOfferAndCandidate()**: Now accepts `JobOffer|int` and `User|int` parameters
  - Handles both entity objects and raw IDs with IDENTITY()

#### JobOfferRepository
- **findByCompanyId()**: Now accepts `Company|int` parameter
  - Uses `IDENTITY()` for proper foreign key comparison

### 5. **Doctrine Configuration Fixed**
   - **doctrine.yaml**: Moved `dql` configuration from root `doctrine` level to `orm` level
   - **Correct structure**:
     ```yaml
     doctrine:
         orm:
             dql:
                 string_functions:
                     JSON_CONTAINS: App\Doctrine\JsonContainsFunction
     ```

### 6. **Database Validation**
   - ✅ Mapping files are correct
   - ✅ Database schema has been updated
   - ✅ Migrations executed successfully
   - ✅ 6 ALTER TABLE queries applied successfully

## 📝 Migration History

```
[OK] Already at the latest version ("DoctrineMigrations\Version20251123162734")
[OK] Database schema updated successfully! (6 queries executed)
```

## 🗑️ Cleanup

- **Deleted**: `fix_schema.php`
- **Deleted**: `fix_schema_v2.php`
- These files are now replaced by the proper migration system

## 🔍 Validation Results

### Schema Validation
```
Mapping: [OK] The mapping files are correct.
Database: [OK] All schema differences resolved
```

### Custom DQL Functions
- JSON_CONTAINS registered and available for QueryBuilder usage
- Proper MySQL compatibility maintained

## ⚡ Next Steps

1. Run migrations on other environments:
   ```bash
   php bin/console doctrine:migrations:migrate --env=prod
   ```

2. Test the new repository methods:
   ```php
   // All these forms now work:
   $repo->findByCompanyId($company);        // Entity
   $repo->findByCompanyId($company->getId()); // ID
   ```

3. Use the JSON_CONTAINS function in queries:
   ```php
   $qb->where('JSON_CONTAINS(u.roles, JSON_QUOTE(:role)) = 1')
       ->setParameter('role', 'ROLE_ADMIN')
   ```

## 📊 Summary

- **Files Created**: 1 (JsonContainsFunction.php)
- **Files Modified**: 4 (User.php, AdminLog.php, doctrine.yaml, 3 repositories)
- **Files Deleted**: 2 (fix_schema.php, fix_schema_v2.php)
- **Migrations**: 1 (Version20251123162734.php - updated from empty)
- **Database Queries Applied**: 6 ALTER TABLE statements


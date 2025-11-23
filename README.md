# Jobs & Internships - Backend API

Un système complet de gestion d'offres d'emploi et de stages construit avec **Symfony 7**, **Doctrine ORM**, et **MySQL**.

## 🚀 Fonctionnalités

### Rôles et Permissions
- **ROLE_USER** (Candidat) : Parcourir les offres, postuler, sauvegarder
- **ROLE_COMPANY** (Entreprise) : Créer des offres, gérer les candidatures
- **ROLE_ADMIN** (Administrateur) : Gérer les utilisateurs, approuver les entreprises, logs

### Structure MVC
- **Entités Doctrine** : User, Company, JobOffer, Application, Category, Skill, SavedOffer, AdminLog
- **Repositories** : Requêtes personnalisées pour chaque entité
- **Services** : ApplicationService, AdminLogService, CompanyApprovalService
- **Formulaires** : Form Types pour CRUD
- **Contrôleurs** : Groupés par rôle (Candidate, Company, Admin)
- **Templates Twig** : Structure organisée par section

## 📋 Prérequis

- PHP 8.2+
- MySQL 8.0+
- Composer
- Symfony CLI (optionnel mais recommandé)

## 🔧 Installation

### 1. Cloner et installer
```bash
git clone <repository-url>
cd Jobs-Internships
composer install
```

### 2. Configurer la base de données
Éditer `.env` et vérifier la `DATABASE_URL` :
```
DATABASE_URL="mysql://root:@127.0.0.1:3306/jobs_internships_db?serverVersion=8.0"
```

### 3. Créer la base de données et les tables
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Charger les données de test (optionnel)
```bash
php bin/console doctrine:fixtures:load
```

### 5. Lancer le serveur
```bash
symfony serve
# ou
php bin/console server:run
```

L'application sera accessible à `http://localhost:8000`

## 📁 Structure du Projet

```
src/
├── Entity/              # Entités Doctrine
│   ├── User.php
│   ├── Company.php
│   ├── JobOffer.php
│   ├── Application.php
│   ├── Category.php
│   ├── Skill.php
│   ├── SavedOffer.php
│   └── AdminLog.php
├── Repository/          # Repositories personnalisés
├── Service/             # Services métier
│   ├── ApplicationService.php
│   ├── AdminLogService.php
│   └── CompanyApprovalService.php
├── Form/                # Form Types
├── Controller/          # Contrôleurs groupés par rôle
│   ├── Candidate/
│   ├── Company/
│   └── Admin/
└── Security/            # Authentification (à implémenter)

templates/
├── candidate/           # Templates candidat
├── company/             # Templates entreprise
├── admin/               # Templates admin
├── emails/              # Templates d'emails
└── security/            # Templates login/register

config/
├── packages/
│   ├── security.yaml    # Configuration sécurité + hiérarchie des rôles
│   └── doctrine.yaml
└── routes.yaml          # Routes principales

public/
└── uploads/
    ├── cv/              # Dossier upload des CV
    └── logos/           # Dossier upload des logos entreprise

migrations/              # Migrations Doctrine

```

## 🔐 Configuration de Sécurité

La hiérarchie des rôles dans `config/packages/security.yaml` :
```yaml
role_hierarchy:
    ROLE_ADMIN: ROLE_COMPANY
    ROLE_COMPANY: ROLE_USER
```

### Accès aux routes
- `/admin/*` → Réservé à ROLE_ADMIN
- `/company/*` → Réservé à ROLE_COMPANY
- `/candidate/*` → Réservé à ROLE_USER
- `/login`, `/register` → Public

## 📚 Endpoints Principaux

### Candidat
- `GET/POST /candidate/profile` - Profil candidat
- `GET /candidate/offers` - Parcourir les offres
- `GET /candidate/offers/{slug}` - Détail offre
- `POST /candidate/offers/{id}/apply` - Postuler
- `GET /candidate/applications` - Mes candidatures
- `POST /candidate/offers/{id}/save` - Sauvegarder une offre

### Entreprise
- `GET/POST /company/profile` - Profil entreprise
- `GET /company/offers` - Mes offres
- `POST /company/offers/create` - Créer une offre
- `GET /company/applications` - Candidatures reçues
- `POST /company/applications/{id}/accept` - Accepter
- `POST /company/applications/{id}/reject` - Rejeter

### Admin
- `GET /admin/users` - Gestion utilisateurs
- `GET /admin/companies` - Gestion entreprises
- `GET /admin/companies/pending` - Approuver les entreprises
- `GET /admin/offers` - Gestion des offres
- `GET /admin/categories` - Gestion des catégories
- `GET /admin/logs` - Logs d'activité
- `GET /admin/stats` - Tableau de bord statistiques

## 🛠️ Développement

### Créer une nouvelle migration
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Générer une nouvelle entité
```bash
php bin/console make:entity
```

### Créer un form type
```bash
php bin/console make:form
```

### Créer un contrôleur
```bash
php bin/console make:controller
```

## ✅ Checklist Implémentation

Chaque contrôleur et service contient des **TODO** indiquant ce qui doit être implémenté :

- [ ] Implémenter la logique des contrôleurs
- [ ] Créer les templates Twig
- [ ] Ajouter la validation des formulaires
- [ ] Implémenter les règles métier dans les services
- [ ] Configurer les uploads de fichiers
- [ ] Ajouter les email notifications
- [ ] Créer les fixtures de test
- [ ] Écrire les tests unitaires
- [ ] Implémenter l'authentification complète

## 📧 Configuration Email

Pour activer les notifications par email, éditer `.env` :
```
MAILER_DSN=smtp://user:pass@smtp.example.com:587?encryption=tls
```

Puis décommenter les appels à `$this->mailer->send()` dans les services.

## 🗄️ Base de Données

### Entités et Relations
- **User** (1) ←→ (1) **Company** : Propriétaire d'entreprise
- **User** (1) ←→ (n) **Application** : Candidature d'un candidat
- **User** (1) ←→ (n) **SavedOffer** : Offres sauvegardées
- **User** (m) ←→ (n) **Skill** : Compétences du candidat
- **User** (1) ←→ (n) **AdminLog** : Logs créés par un admin

- **Company** (1) ←→ (n) **JobOffer** : Offres publiées
- **JobOffer** (1) ←→ (n) **Application** : Candidatures pour une offre
- **JobOffer** (1) ←→ (n) **SavedOffer** : Offres sauvegardées
- **JobOffer** (n) ←→ (1) **Category** : Catégorie de l'offre

- **Category** (1) ←→ (n) **Category** : Catégories hiérarchiques
- **Category** (1) ←→ (n) **JobOffer** : Offres dans la catégorie

## 🧪 Tests

À implémenter avec PHPUnit :
```bash
php bin/console make:test
```

## 📝 Convention de Codage

- Utiliser les **Attributes Symfony** pour les routes et la validation
- Constructeur pour l'injection de dépendances
- Immutabilité des dates (DateTimeImmutable)
- Repository patterns pour les requêtes
- Services pour la logique métier
- Form Types pour la validation

## 🐛 Dépannage

### Erreur : "No such file or directory"
Vérifier que la base de données existe :
```bash
php bin/console doctrine:database:create
```

### Erreur d'import d'entités
S'assurer que le namespace est correct et que le fichier existe.

### Permissions de dossier
```bash
chmod -R 755 public/uploads/
chmod -R 777 var/
```

## 📞 Support

Pour les questions ou problèmes, consulter :
- [Documentation Symfony](https://symfony.com/doc)
- [Documentation Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/index.html)
- Issues du projet

---

**Maintenant prêt à être push sur GitHub !** 🚀


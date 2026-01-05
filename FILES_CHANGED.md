# 📋 Fichiers modifiés et créés - Migrations & Schema Fix

## 📂 Structure des changements

### ✅ FICHIERS CRÉÉS (3)

#### 1. `src/Doctrine/JsonContainsFunction.php` (NOUVEAU)
**Type**: Classe PHP - Fonction DQL personnalisée
**Taille**: ~45 lignes
**Description**: Implémente la fonction DQL `JSON_CONTAINS` pour MySQL
**Détails**:
- Classe qui étend `FunctionNode`
- Parseur pour la syntaxe DQL
- Générateur SQL pour MySQL
- Enregistrée dans `doctrine.yaml`

```php
namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class JsonContainsFunction extends FunctionNode { ... }
```

---

#### 2. `MIGRATION_SUMMARY.md` (NOUVEAU)
**Type**: Documentation Markdown
**Description**: Résumé complet de la migration et des changements
**Contient**:
- Liste des tâches complétées
- Description des modifications de schéma
- Résumé des corrections de repositories
- Résultats de validation
- Historique des migrations
- Prochaines étapes

---

#### 3. `DEVELOPER_GUIDE.md` (NOUVEAU)
**Type**: Guide d'utilisation Markdown
**Description**: Documentation pour les développeurs sur l'utilisation des nouvelles fonctionnalités
**Sections**:
- Types JSON et DATETIME
- Recherche avec JSON_CONTAINS
- Utilisation des repositories avec entités ou IDs
- Patterns recommandés
- Avantages du système
- Migration depuis l'ancien code
- Troubleshooting

---

#### 4. `CHECKLIST.md` (NOUVEAU)
**Type**: Checklist de validation Markdown
**Description**: Checklist complète pour vérifier que tout a été implémenté
**Contient**:
- ✅ Statut de chaque tâche
- 📊 Résumé des changements
- 🔍 Points de vérification additionnels
- 🚀 Statut prêt pour production

---

### 🔄 FICHIERS MODIFIÉS (7)

#### 1. `src/Entity/User.php`
**Modifications**: 1 ligne changée
**Description**: Ajout du commentaire DC2Type:json pour la colonne roles

**Avant**:
```php
#[ORM\Column(type: 'json')]
private array $roles = [];
```

**Après**:
```php
#[ORM\Column(type: 'json', options: ['comment' => '(DC2Type:json)'])]
private array $roles = [];
```

---

#### 2. `src/Entity/AdminLog.php`
**Modifications**: 1 ligne changée
**Description**: Ajout du commentaire DC2Type:json pour la colonne data

**Avant**:
```php
#[ORM\Column(type: 'json', nullable: true)]
private ?array $data = null;
```

**Après**:
```php
#[ORM\Column(type: 'json', nullable: true, options: ['comment' => '(DC2Type:json)'])]
private ?array $data = null;
```

---

#### 3. `config/packages/doctrine.yaml`
**Modifications**: 1 section déplacée/restructurée
**Description**: Déplacement de la configuration `dql` du niveau `doctrine` au niveau `orm`

**Avant**:
```yaml
doctrine:
    dbal:
        # ...
    orm:
        # ...
    dql:
        string_functions:
            JSON_CONTAINS: App\Doctrine\JsonContainsFunction
```

**Après**:
```yaml
doctrine:
    dbal:
        # ...
    orm:
        # ...
        dql:
            string_functions:
                JSON_CONTAINS: App\Doctrine\JsonContainsFunction
```

---

#### 4. `src/Repository/UserRepository.php`
**Modifications**: 1 méthode corrigée
**Description**: Correction de la méthode `findAdmins()` pour utiliser correctement JSON_CONTAINS

**Avant**:
```php
public function findAdmins(): array
{
    return $this->createQueryBuilder('u')
        ->where("JSON_CONTAINS(u.roles, '\"ROLE_ADMIN\"') = 1")
        ->orderBy('u.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}
```

**Après**:
```php
public function findAdmins(): array
{
    return $this->createQueryBuilder('u')
        ->where("JSON_CONTAINS(u.roles, JSON_QUOTE(:role)) = 1")
        ->setParameter('role', 'ROLE_ADMIN')
        ->orderBy('u.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}
```

---

#### 5. `src/Repository/ApplicationRepository.php`
**Modifications**: 2 méthodes modifiées + import ajouté
**Description**: Support des entités ET des IDs pour les recherches

**Méthode 1: findByJobOfferId()**
```php
// Avant
public function findByJobOfferId(int $jobOfferId): array { ... }

// Après
public function findByJobOfferId(JobOffer|int $jobOffer): array { ... }
```

**Méthode 2: findByJobOfferAndCandidate()**
```php
// Avant
public function findByJobOfferAndCandidate(int $jobOfferId, int $candidateId): ?Application { ... }

// Après
public function findByJobOfferAndCandidate(JobOffer|int $jobOffer, User|int $candidate): ?Application { ... }
```

**Import ajouté**:
```php
use App\Entity\JobOffer;
```

---

#### 6. `src/Repository/JobOfferRepository.php`
**Modifications**: 1 méthode modifiée + import ajouté
**Description**: Support des entités ET des IDs pour la recherche par entreprise

**Avant**:
```php
public function findByCompanyId(int $companyId): array { ... }
```

**Après**:
```php
public function findByCompanyId(Company|int $company): array { ... }
```

**Import ajouté**:
```php
use App\Entity\Company;
```

---

#### 7. `migrations/Version20251123162734.php`
**Modifications**: Complète migration créée
**Description**: Migration remplaçant les scripts fix_schema.php et fix_schema_v2.php

**Avant** (migration vide):
```php
public function up(Schema $schema): void
{
    // this up() migration is auto-generated, please modify it to your needs
}

public function down(Schema $schema): void
{
    // this down() migration is auto-generated, please modify it to your needs
}
```

**Après** (migration complète):
- 10 ALTER TABLE pour `user` (roles, location, cv_filename)
- 1 ALTER TABLE pour `admin_log` (data)
- 1 ALTER TABLE pour `application` (cv_filename)
- 1 ALTER TABLE pour `company` (logo_filename, website, location)
- 1 ALTER TABLE pour `job_offer` (location)
- 1 ALTER TABLE pour `messenger_messages` (delivered_at)

Tous les UP() ont leurs DOWN() correspondants.

---

### 🗑️ FICHIERS SUPPRIMÉS (2)

#### 1. `fix_schema.php` (SUPPRIMÉ)
**Raison**: Remplacé par la migration Doctrine
**Contenu**: Script PHP adhoc pour ALTER TABLE
**Remplacé par**: `migrations/Version20251123162734.php`

#### 2. `fix_schema_v2.php` (SUPPRIMÉ)
**Raison**: Remplacé par la migration Doctrine
**Contenu**: Script PHP adhoc pour ALTER TABLE avec commentaires
**Remplacé par**: `migrations/Version20251123162734.php`

---

## 📊 Statistiques des changements

```
┌─────────────────────────────────────┬──────────┐
│ Catégorie                           │ Nombre   │
├─────────────────────────────────────┼──────────┤
│ Fichiers créés                      │ 4        │
│ Fichiers modifiés                   │ 7        │
│ Fichiers supprimés                  │ 2        │
├─────────────────────────────────────┼──────────┤
│ TOTAL FICHIERS AFFECTÉS             │ 13       │
└─────────────────────────────────────┴──────────┘

Lignes:
├─ Créées: ~500 (documentation + code)
├─ Modifiées: ~30 (corrections ciblées)
└─ Supprimées: ~50 (scripts adhoc)
```

---

## 🔄 Dépendances entre fichiers

```
JsonContainsFunction.php
    ↓
    doctrine.yaml (configure la fonction)
    ↓
    UserRepository.php (utilise JSON_CONTAINS)

User.php, AdminLog.php (DC2Type comments)
    ↓
    Version20251123162734.php (migration)
    ↓
    Base de données (ALTER TABLE appliqués)

ApplicationRepository.php, JobOfferRepository.php
    ↓
    Utilise IDENTITY() + union types
    ↓
    Accepte entités OU IDs
```

---

## ✨ Impact global

### Avant la migration
- ❌ Scripts ad-hoc `fix_schema.php` et `fix_schema_v2.php`
- ❌ Migration vide qui ne fait rien
- ❌ Repositories acceptant seulement des IDs
- ❌ Fonction DQL JSON_CONTAINS non déclarée
- ❌ Colonnes JSON sans commentaires DC2Type

### Après la migration
- ✅ Migration Doctrine complète et exécutée
- ✅ Repositories flexibles (entités OU IDs)
- ✅ Fonction DQL JSON_CONTAINS disponible
- ✅ Commentaires DC2Type corrects
- ✅ Code versionné et maintenable

---

**Date**: 8 décembre 2025
**Statut**: ✅ COMPLET


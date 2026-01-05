# 📖 Guide d'utilisation - Migrations, JSON Types et QueryBuilder

Ce guide explique comment utiliser les nouveaux changements de schema et les méthodes de repository corrigées.

## 🔧 Types JSON et DATETIME

### User.roles (JSON)
La colonne `roles` stocke les rôles de l'utilisateur en JSON:

```php
$user = new User();
$user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
$entityManager->persist($user);
$entityManager->flush();
```

### AdminLog.data (JSON)
La colonne `data` stocke les données d'audit en JSON:

```php
$log = new AdminLog();
$log->setData([
    'before' => ['status' => 'pending'],
    'after' => ['status' => 'approved'],
]);
$entityManager->persist($log);
$entityManager->flush();
```

## 🔍 Recherche avec JSON_CONTAINS

### Trouver tous les admins
Utilisez la méthode `findAdmins()` du repository `UserRepository`:

```php
$userRepository = $entityManager->getRepository(User::class);
$admins = $userRepository->findAdmins();

foreach ($admins as $admin) {
    echo $admin->getEmail(); // Affiche les emails des admins
}
```

**Sous le capot:**
```php
// La requête DQL utilise la fonction personnalisée JSON_CONTAINS
$query = $this->createQueryBuilder('u')
    ->where("JSON_CONTAINS(u.roles, JSON_QUOTE(:role)) = 1")
    ->setParameter('role', 'ROLE_ADMIN')
    ->getQuery();
```

## 📋 Utilisation des Repositories avec entités ou IDs

### ApplicationRepository

#### Trouver les applications pour une offre
```php
$appRepository = $entityManager->getRepository(Application::class);

// Option 1: Avec une entité JobOffer
$jobOffer = $entityManager->getRepository(JobOffer::class)->find(1);
$applications = $appRepository->findByJobOfferId($jobOffer);

// Option 2: Avec un ID
$applications = $appRepository->findByJobOfferId(1);

// Les deux façons fonctionnent!
```

#### Trouver une application spécifique
```php
// Option 1: Avec des entités
$jobOffer = $entityManager->getRepository(JobOffer::class)->find(1);
$candidate = $entityManager->getRepository(User::class)->find(5);
$application = $appRepository->findByJobOfferAndCandidate($jobOffer, $candidate);

// Option 2: Avec des IDs
$application = $appRepository->findByJobOfferAndCandidate(1, 5);

// Option 3: Mélanger entités et IDs
$jobOffer = $entityManager->getRepository(JobOffer::class)->find(1);
$application = $appRepository->findByJobOfferAndCandidate($jobOffer, 5);

// Tous les trois fonctionnent!
```

### JobOfferRepository

#### Trouver les offres d'une entreprise
```php
$jobOfferRepository = $entityManager->getRepository(JobOffer::class);

// Option 1: Avec une entité Company
$company = $entityManager->getRepository(Company::class)->find(3);
$offers = $jobOfferRepository->findByCompanyId($company);

// Option 2: Avec un ID
$offers = $jobOfferRepository->findByCompanyId(3);

// Les deux fonctionnent!
```

## 🎯 Pattern d'utilisation recommandé

### Dans un Controller
```php
namespace App\Controller\Company;

use App\Repository\ApplicationRepository;
use App\Repository\JobOfferRepository;
use App\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CompanyApplicationsController extends AbstractController
{
    public function list(
        ApplicationRepository $appRepository,
        JobOfferRepository $offerRepository,
    ): Response {
        $user = $this->getUser();
        $company = $user->getCompany();
        
        // Les entités sont disponibles, on les utilise directement
        $applications = $appRepository->findForCompany($company);
        
        // Si on a juste un ID (par exemple en tant que paramètre):
        $offerId = $this->getRequest()->query->get('offer_id');
        if ($offerId) {
            $applications = $appRepository->findByJobOfferId((int)$offerId);
        }
        
        return $this->render('company/applications.html.twig', [
            'applications' => $applications,
        ]);
    }
}
```

### Dans un Service
```php
namespace App\Service;

use App\Repository\ApplicationRepository;
use App\Entity\JobOffer;
use App\Entity\User;

class ApplicationService
{
    public function __construct(
        private ApplicationRepository $appRepository,
    ) {}
    
    public function checkIfApplied(JobOffer $jobOffer, User $candidate): bool
    {
        // Utiliser les entités directement
        $application = $this->appRepository->findByJobOfferAndCandidate($jobOffer, $candidate);
        return $application !== null;
    }
    
    public function getApplicationsCount(int $jobOfferId): int
    {
        // Ou utiliser les IDs si c'est plus pratique
        $applications = $this->appRepository->findByJobOfferId($jobOfferId);
        return count($applications);
    }
}
```

## 🔐 Avantages du système

### ✅ Flexibilité
- Accepte entités **ET** IDs
- Pas besoin de conversion manuelle
- Code plus lisible et moins d'erreurs

### ✅ Performance
- Utilise `IDENTITY()` pour extraire les IDs
- Génère les mêmes requêtes SQL optimisées
- Pas de surcharge

### ✅ Type Safety
- Les IDEs peuvent mieux comprendre les types
- Meilleur autocomplete
- Moins d'erreurs à la compilation

## 🚀 Migration depuis l'ancien code

Si vous avez du code ancien qui utilise les anciennes méthodes:

### Avant (ancienne syntaxe)
```php
// ANCIEN - Acceptait seulement les IDs
$applications = $appRepository->findByJobOfferId($jobOfferId); // int only
```

### Après (nouvelle syntaxe)
```php
// NOUVEAU - Accepte entités ET IDs
$applications = $appRepository->findByJobOfferId($jobOffer);     // Entity
$applications = $appRepository->findByJobOfferId($jobOfferId);   // int

// Les deux fonctionnent identiquement!
```

## 📝 Notes importantes

### Sur les commentaires DC2Type
Les commentaires `(DC2Type:json)` et `(DC2Type:datetime_immutable)` sont des métadonnées Doctrine:

- **`(DC2Type:json)`**: Indique à Doctrine de convertir automatiquement PHP arrays ↔ JSON
- **`(DC2Type:datetime_immutable)`**: Indique à Doctrine d'utiliser `DateTimeImmutable` (recommandé)

Ces commentaires sont automatiquement gérés par Symfony et ne nécessitent pas d'action manuelle.

### Sur la validation du schéma
La commande `doctrine:schema:validate` peut signaler des différences mineurs de commentaires. C'est normal et ne pose pas de problème si:

1. Les mappings Doctrine sont corrects ✅
2. Le schéma a été mis à jour ✅
3. Les migrations s'exécutent sans erreurs ✅

## ❓ Troubleshooting

### Erreur: "JSON_CONTAINS not found"
**Solution**: Assurez-vous que la configuration `doctrine.yaml` inclut:
```yaml
doctrine:
    orm:
        dql:
            string_functions:
                JSON_CONTAINS: App\Doctrine\JsonContainsFunction
```

### Erreur: "Invalid parameter binding"
**Solution**: Utilisez `JSON_QUOTE()` pour les paramètres JSON:
```php
->where('JSON_CONTAINS(u.roles, JSON_QUOTE(:role)) = 1')
->setParameter('role', 'ROLE_ADMIN')
```

### Migration ne s'exécute pas
**Solution**: Exécutez:
```bash
php bin/console doctrine:migrations:migrate
```

## 📞 Questions?

Consultez:
- [Documentation Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html)
- [Symfony QueryBuilder](https://symfony.com/doc/current/doctrine/orm.html)
- [MySQL JSON Functions](https://dev.mysql.com/doc/refman/8.0/en/json-functions.html)


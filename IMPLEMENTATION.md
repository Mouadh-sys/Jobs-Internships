# Implémentation Guide

## 🎯 Priorité d'implémentation

### Phase 1 : Authentification et Base
1. **AuthenticationController** (à créer)
   - Login avec form_login
   - Register (créer utilisateur)
   - Logout

2. **User Management** (src/Controller/Admin/AdminUserController.php)
   - Implémenter create() avec UserPasswordHasherInterface
   - Implémenter edit() pour l'édition de rôles
   - Implémenter delete()

3. **User Fixtures** (à créer)
   - Créer un admin de test
   - Créer 2-3 utilisateurs candidats
   - Créer 1-2 entreprises

### Phase 2 : Gestion des offres
1. **CategoryController** (src/Controller/Admin/AdminCategoryController.php)
   - Implémenter create() avec génération de slug
   - Implémenter edit()
   - Implémenter delete() avec validation

2. **JobOfferController** (src/Controller/Company/CompanyOfferController.php)
   - Implémenter create() avec vérification d'entreprise approuvée
   - Implémenter edit() avec vérification d'ownership
   - Implémenter toggle() pour activer/désactiver
   - Implémenter list() avec pagination

3. **OfferBrowseController** (src/Controller/Candidate/OfferBrowseController.php)
   - Implémenter list() avec filtres (category, location, type, keyword)
   - Implémenter detail() pour afficher une offre

### Phase 3 : Candidatures
1. **ApplicationService** (src/Service/ApplicationService.php)
   - Compléter applyToOffer() avec gestion des uploads
   - Ajouter validation pour éviter les doublons
   - Implémenter sendApplicationNotificationEmail()

2. **ApplicationController** (src/Controller/Candidate/ApplicationController.php)
   - Implémenter applyToOffer() avec formulaire
   - Implémenter list() avec filtres par statut
   - Implémenter withdraw()

3. **CompanyApplicationsController** (src/Controller/Company/CompanyApplicationsController.php)
   - Implémenter list() avec pagination
   - Implémenter accept() et reject() avec emails
   - Implémenter downloadCv()

### Phase 4 : Admin et Approvals
1. **CompanyApprovalService** (src/Service/CompanyApprovalService.php)
   - Implémenter approve() avec log
   - Implémenter reject() avec raison

2. **AdminCompanyController** (src/Controller/Admin/AdminCompanyController.php)
   - Implémenter pending() pour les approbations
   - Implémenter approve() et reject()

3. **AdminLogService et AdminLogController** (à utiliser partout)
   - S'assurer que tous les actions importantes sont loggées
   - Implémenter l'affichage des logs avec pagination

### Phase 5 : Features supplémentaires
1. **SavedOfferController** (src/Controller/Candidate/SavedOfferController.php)
   - Implémenter saveOffer()
   - Implémenter unsaveOffer()
   - Implémenter list()

2. **Profile Controllers**
   - ProfileController candidat avec upload CV
   - CompanyProfileController avec upload logo

3. **Admin Stats** (src/Controller/Admin/AdminStatsController.php)
   - Implémenter dashboard() avec statistiques globales
   - Implémenter userStats(), companyStats(), applicationStats()

## 📝 Points clés pour les développeurs

### Upload de fichiers
- CV : `public/uploads/cv/` - Extension PDF, DOC
- Logos : `public/uploads/logos/` - Extension PNG, JPG
- Noms générés : `{userId}_{timestamp}_{originalName}.{ext}`

### Génération de slug
Utiliser Symfony\Component\String\Slugger\SluggerInterface :
```php
use Symfony\Component\String\Slugger\SluggerInterface;

public function create(SluggerInterface $slugger): Response {
    $slug = $slugger->slug($title)->lower();
    $jobOffer->setSlug($slug);
}
```

### Injection de dépendances
Tous les services doivent être injectés dans le constructeur :
```php
public function __construct(
    private UserRepository $userRepository,
    private EntityManagerInterface $entityManager,
) {
}
```

### Logs d'administration
Chaque action importante doit être loggée :
```php
$this->adminLogService->logCreate($admin, 'Company', $company->getId());
$this->adminLogService->logApprove($admin, 'Company', $company->getId());
```

### Validation
Toutes les contraintes doivent être dans les entités avec Attributes :
```php
#[Assert\NotBlank]
#[Assert\Email]
private ?string $email = null;
```

### Pagination
Utiliser Doctrine Paginator pour les listes :
```php
use Doctrine\ORM\Tools\Pagination\Paginator;

$query = $queryBuilder->getQuery();
$paginator = new Paginator($query);
```

### Envoi d'emails
Toujours utiliser MailerInterface :
```php
$email = (new Email())
    ->from('no-reply@jobsinternships.com')
    ->to($recipient->getEmail())
    ->subject('Subject')
    ->html($this->renderView('emails/template.html.twig', ['data' => $data]));

$this->mailer->send($email);
```

## 🧩 Template Twig Structure

Chaque section doit avoir :
- `list.html.twig` - Affichage en liste
- `show.html.twig` - Affichage détail
- `form.html.twig` - Création/Édition
- `_item.html.twig` - Composant réutilisable (optionnel)

Exemple :
```
templates/
├── admin/
│   ├── users/
│   │   ├── list.html.twig
│   │   ├── show.html.twig
│   │   └── form.html.twig
│   ├── companies/
│   │   └── ...
│   └── shared/
│       └── _sidebar.html.twig
```

## 🔒 Sécurité

### Vérification d'ownership
Avant de modifier une ressource, toujours vérifier que l'utilisateur en est propriétaire :
```php
if ($jobOffer->getCompany()->getUser() !== $this->getUser()) {
    throw $this->createAccessDeniedException();
}
```

### Vérification des rôles
Toujours inclure `#[IsGranted('ROLE_REQUIRED')]` sur les contrôleurs.

### Protection CSRF
Les formulaires POST doivent inclure un token CSRF (activé par défaut dans Symfony).

## 📊 Architecture des données

### Statuts de candidature
```php
Application::STATUS_PENDING   = 'PENDING'
Application::STATUS_ACCEPTED  = 'ACCEPTED'
Application::STATUS_REJECTED  = 'REJECTED'
```

### Types d'offres
- CDI (Permanent)
- CDD (Fixed-term)
- Stage (Internship)
- Freelance

### Actions admin (AdminLog)
- CREATE
- UPDATE
- DELETE
- APPROVE
- REJECT

## 🧪 Workflow de test

1. Créer admin dans fixtures
2. Login en tant qu'admin
3. Créer une catégorie
4. Créer une entreprise et l'approuver
5. Login en tant qu'entreprise
6. Créer une offre d'emploi
7. Login en tant que candidat
8. Parcourir et postuler à l'offre
9. Login en tant qu'entreprise
10. Accepter/Rejeter la candidature

---

**Bonne implémentation ! 🚀**


<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserInitializerSubscriber implements EventSubscriberInterface
{
    private static bool $adminChecked = false;
    private const ADMIN_EMAIL = 'admin@admin.com';
    private const ADMIN_PASSWORD = 'test123';
    private const ADMIN_FULL_NAME = 'Admin User';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Only check once per application lifecycle
        if (self::$adminChecked) {
            return;
        }

        // Skip for console commands
        if (!$event->isMainRequest()) {
            return;
        }

        self::$adminChecked = true;

        // Check if admin user exists
        $adminUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => self::ADMIN_EMAIL]);
        
        if ($adminUser) {
            // Admin exists, but ensure it has ROLE_ADMIN
            if (!$adminUser->isAdmin()) {
                $adminUser->setRoles([User::ROLE_ADMIN]);
                $this->entityManager->flush();
            }
            return;
        }

        // Create admin user
        $user = new User();
        $user->setEmail(self::ADMIN_EMAIL);
        $user->setFullName(self::ADMIN_FULL_NAME);
        $user->setRoles([User::ROLE_ADMIN]);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, self::ADMIN_PASSWORD);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}


<?php

namespace App\Command;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-company',
    description: 'Creates a test user and company for verification',
)]
class CreateTestCompanyCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = 'company@test.com';
        $password = 'password123';

        // Check if user exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $output->writeln("User {$email} already exists.");
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_COMPANY']);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, $password)
        );

        $company = new Company();
        $company->setName('Test Company Ltd.');
        $company->setUser($user);
        $company->setApproved(true);
        $company->setDescription('This is a test company created for verification purposes.');
        $company->setWebsite('https://example.com');
        $company->setLocation('Paris, France');

        $this->entityManager->persist($user);
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        $output->writeln("Created user: {$email} / {$password}");
        $output->writeln("Go to /login to sign in.");

        return Command::SUCCESS;
    }
}

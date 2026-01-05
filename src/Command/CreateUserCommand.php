<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user (admin, company, or candidate)',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email address')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->addArgument('fullName', InputArgument::OPTIONAL, 'Full name')
            ->addArgument('role', InputArgument::OPTIONAL, 'Role (ROLE_USER, ROLE_COMPANY, ROLE_ADMIN)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Enter email: ');
            $email = $helper->ask($input, $output, $question);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $output->writeln('<error>Invalid email address!</error>');
            return Command::FAILURE;
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $output->writeln('<error>User with this email already exists!</error>');
            return Command::FAILURE;
        }

        $password = $input->getArgument('password');
        if (!$password) {
            $question = new Question('Enter password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);
        }

        $fullName = $input->getArgument('fullName') ?? 'User ' . time();
        $role = $input->getArgument('role') ?? User::ROLE_USER;

        if (!in_array($role, [User::ROLE_USER, User::ROLE_COMPANY, User::ROLE_ADMIN], true)) {
            $output->writeln('<error>Invalid role!</error>');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFullName($fullName);
        $user->setRoles([$role]);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln("<info>User created successfully!</info>");
        $output->writeln("  Email: $email");
        $output->writeln("  Full Name: $fullName");
        $output->writeln("  Role: $role");

        return Command::SUCCESS;
    }
}


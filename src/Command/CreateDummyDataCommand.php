<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Company;
use App\Entity\JobOffer;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:create-dummy-data',
    description: 'Creates dummy data for testing offers listing',
)]
class CreateDummyDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // 1. Ensure a company exists
            $company = $this->entityManager->getRepository(Company::class)->findOneBy([]);
            if (!$company) {
                $output->writeln('No company found. Creating one...');

                $email = 'dummy_company@test.com';
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setFullName('Tech Corp');
                    $user->setRoles(['ROLE_COMPANY']);
                    $user->setPassword('$2y$13$ws/g/s/s/s/s/s'); // Dummy hash
                    $this->entityManager->persist($user);
                }

                $company = new Company();
                $company->setName('TechCorp Inc.');
                $company->setUser($user);
                $company->setApproved(true);
                $company->setLocation('New York, USA');
                $this->entityManager->persist($company);
            }

            // 2. Create Categories if not exist
            $categories = ['Development', 'Design', 'Marketing', 'Sales'];
            $categoryEntities = [];
            foreach ($categories as $catName) {
                $cat = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $catName]);
                if (!$cat) {
                    $cat = new Category();
                    $cat->setName($catName);
                    $cat->setSlug($this->slugger->slug($catName)->lower()->toString());
                    $this->entityManager->persist($cat);
                }
                $categoryEntities[] = $cat;
            }

            // 3. Create 25 Job Offers
            $types = ['CDI', 'CDD', 'Stage', 'Freelance'];
            $locations = ['Paris', 'London', 'Berlin', 'Remote'];

            for ($i = 1; $i <= 25; $i++) {
                $offer = new JobOffer();
                $offer->setTitle("Job Offer #$i - Developer");
                $offer->setDescription("This is a description for job offer #$i. We are looking for talented people.");
                $offer->setCompany($company);
                $offer->setCategory($categoryEntities[array_rand($categoryEntities)]);
                $offer->setLocation($locations[array_rand($locations)]);
                $offer->setType($types[array_rand($types)]);
                $offer->setSlug($this->slugger->slug($offer->getTitle() . '-' . uniqid())->lower()->toString());

                $this->entityManager->persist($offer);
            }

            $this->entityManager->flush();

            $output->writeln('Dummy data created: 25 job offers added.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

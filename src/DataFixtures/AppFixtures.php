<?php

namespace App\DataFixtures;

use App\Entity\AdminLog;
use App\Entity\Application;
use App\Entity\Category;
use App\Entity\Company;
use App\Entity\JobOffer;
use App\Entity\SavedOffer;
use App\Entity\Skill;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private SluggerInterface $slugger,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Create Skills
        $skills = $this->createSkills($manager);
        
        // 2. Create Categories
        $categories = $this->createCategories($manager);
        
        // 3. Create Users (Admins, Candidates, Companies)
        $users = $this->createUsers($manager, $skills);
        
        // 4. Create Companies
        $companies = $this->createCompanies($manager, $users['companies']);
        
        // 5. Create Job Offers
        $jobOffers = $this->createJobOffers($manager, $companies, $categories);
        
        // 6. Create Applications
        $this->createApplications($manager, $users['candidates'], $jobOffers);
        
        // 7. Create Saved Offers
        $this->createSavedOffers($manager, $users['candidates'], $jobOffers);
        
        // 8. Create Admin Logs
        $this->createAdminLogs($manager, $users['admins'], $companies, $jobOffers);
        
        $manager->flush();
    }

    private function createSkills(ObjectManager $manager): array
    {
        $skillNames = [
            'PHP', 'JavaScript', 'Python', 'Java', 'C++', 'C#', 'Ruby', 'Go',
            'React', 'Vue.js', 'Angular', 'Node.js', 'Symfony', 'Laravel',
            'MySQL', 'PostgreSQL', 'MongoDB', 'Redis',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'Git',
            'HTML', 'CSS', 'SASS', 'TypeScript',
            'Agile', 'Scrum', 'Project Management',
            'UI/UX Design', 'Figma', 'Adobe XD',
            'Marketing', 'SEO', 'Content Writing',
        ];

        $skills = [];
        $skillRepository = $manager->getRepository(Skill::class);
        $usedSlugs = [];
        
        foreach ($skillNames as $skillName) {
            // Check if skill already exists
            $existingSkill = $skillRepository->findOneBy(['name' => $skillName]);
            if ($existingSkill) {
                $skills[] = $existingSkill;
                continue;
            }
            
            // Generate unique slug
            $baseSlug = $this->slugger->slug($skillName)->lower()->toString();
            $slug = $baseSlug;
            $counter = 1;
            
            while (in_array($slug, $usedSlugs, true)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $usedSlugs[] = $slug;
            
            $skill = new Skill();
            $skill->setName($skillName);
            $skill->setSlug($slug);
            $manager->persist($skill);
            $skills[] = $skill;
        }

        return $skills;
    }

    private function createCategories(ObjectManager $manager): array
    {
        $categoriesData = [
            'Technology' => ['Web Development', 'Mobile Development', 'DevOps', 'Data Science'],
            'Design' => ['UI/UX Design', 'Graphic Design', 'Product Design'],
            'Marketing' => ['Digital Marketing', 'Content Marketing', 'SEO'],
            'Sales' => ['Business Development', 'Account Management'],
            'Finance' => ['Accounting', 'Financial Analysis'],
            'Human Resources' => ['Recruitment', 'Talent Management'],
        ];

        $categories = [];
        
        // Create parent categories
        foreach (array_keys($categoriesData) as $parentName) {
            $parent = new Category();
            $parent->setName($parentName);
            $parent->setSlug($this->slugger->slug($parentName)->lower()->toString());
            $manager->persist($parent);
            $categories[$parentName] = $parent;
        }

        // Create child categories
        foreach ($categoriesData as $parentName => $children) {
            $parent = $categories[$parentName];
            foreach ($children as $childName) {
                $child = new Category();
                $child->setName($childName);
                $child->setSlug($this->slugger->slug($childName)->lower()->toString());
                $child->setParent($parent);
                $manager->persist($child);
                $categories[$childName] = $child;
            }
        }

        return $categories;
    }

    private function createUsers(ObjectManager $manager, array $skills): array
    {
        $users = [
            'admins' => [],
            'candidates' => [],
            'companies' => [],
        ];

        // Create Admin Users
        $adminData = [
            ['admin@example.com', 'Admin User', 'password123'],
            ['admin2@example.com', 'Admin Two', 'password123'],
        ];

        foreach ($adminData as [$email, $fullName, $password]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFullName($fullName);
            $user->setRoles([User::ROLE_ADMIN]);
            $user->setLocation('Paris, France');
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $manager->persist($user);
            $users['admins'][] = $user;
        }

        // Create Candidate Users
        $candidateData = [
            ['candidate@example.com', 'John Doe', 'password123', 'Paris, France'],
            ['candidate2@example.com', 'Jane Smith', 'password123', 'London, UK'],
            ['candidate3@example.com', 'Bob Johnson', 'password123', 'Berlin, Germany'],
            ['candidate4@example.com', 'Alice Brown', 'password123', 'Barcelona, Spain'],
            ['candidate5@example.com', 'Charlie Wilson', 'password123', 'Amsterdam, Netherlands'],
        ];

        foreach ($candidateData as $index => [$email, $fullName, $password, $location]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFullName($fullName);
            $user->setRoles([User::ROLE_USER]);
            $user->setLocation($location);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            
            // Assign random skills to candidates
            $userSkills = (array) array_rand($skills, min(5, count($skills)));
            foreach ($userSkills as $skillIndex) {
                $user->addSkill($skills[$skillIndex]);
            }
            
            $manager->persist($user);
            $users['candidates'][] = $user;
        }

        // Create Company Users
        $companyData = [
            ['company@example.com', 'TechCorp Inc.', 'password123'],
            ['company2@example.com', 'Innovate Solutions', 'password123'],
            ['company3@example.com', 'Digital Ventures', 'password123'],
            ['company4@example.com', 'StartupHub', 'password123'],
            ['company5@example.com', 'Global Tech', 'password123'],
        ];

        foreach ($companyData as [$email, $fullName, $password]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFullName($fullName);
            $user->setRoles([User::ROLE_COMPANY]);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
            $manager->persist($user);
            $users['companies'][] = $user;
        }

        return $users;
    }

    private function createCompanies(ObjectManager $manager, array $companyUsers): array
    {
        $companiesData = [
            [
                'TechCorp Inc.',
                'Leading technology company specializing in software development and innovation.',
                'https://techcorp.com',
                'Paris, France',
                true,
                true,
            ],
            [
                'Innovate Solutions',
                'We provide cutting-edge solutions for businesses worldwide.',
                'https://innovate-solutions.com',
                'London, UK',
                true,
                true,
            ],
            [
                'Digital Ventures',
                'Transforming businesses through digital innovation.',
                'https://digital-ventures.com',
                'Berlin, Germany',
                true,
                true,
            ],
            [
                'StartupHub',
                'Supporting startups and entrepreneurs in their journey.',
                'https://startuphub.com',
                'Barcelona, Spain',
                false,
                true,
            ],
            [
                'Global Tech',
                'International technology consulting firm.',
                'https://global-tech.com',
                'Amsterdam, Netherlands',
                true,
                true,
            ],
        ];

        $companies = [];
        foreach ($companyUsers as $index => $user) {
            [$name, $description, $website, $location, $isApproved, $isActive] = $companiesData[$index];
            
            $company = new Company();
            $company->setUser($user);
            $company->setName($name);
            $company->setDescription($description);
            $company->setWebsite($website);
            $company->setLocation($location);
            $company->setApproved($isApproved);
            $company->setActive($isActive);
            $manager->persist($company);
            $companies[] = $company;
        }

        return $companies;
    }

    private function createJobOffers(ObjectManager $manager, array $companies, array $categories): array
    {
        $jobTitles = [
            'Senior PHP Developer',
            'Full Stack JavaScript Developer',
            'React Frontend Developer',
            'Python Backend Engineer',
            'DevOps Engineer',
            'UI/UX Designer',
            'Digital Marketing Specialist',
            'Product Manager',
            'Data Scientist',
            'Mobile App Developer',
            'Cloud Architect',
            'Security Engineer',
            'Content Writer',
            'Sales Manager',
            'HR Recruiter',
        ];

        $descriptions = [
            'We are looking for an experienced developer to join our team. You will work on exciting projects and collaborate with talented professionals.',
            'Join our dynamic team and help build innovative solutions. We offer competitive salary and great work-life balance.',
            'Looking for a passionate professional to drive our product development. You will have the opportunity to make a real impact.',
            'We need a creative individual who can help us grow our business. This is a great opportunity for career advancement.',
            'Join a fast-growing company and work on cutting-edge technology. We provide excellent benefits and professional development opportunities.',
        ];

        $types = ['CDI', 'CDD', 'Stage', 'Freelance'];
        $locations = ['Paris, France', 'London, UK', 'Berlin, Germany', 'Barcelona, Spain', 'Remote'];

        $jobOffers = [];
        $categoryValues = array_values($categories);
        
        foreach ($companies as $company) {
            // Create 3-5 job offers per company
            $numOffers = rand(3, 5);
            for ($i = 0; $i < $numOffers; $i++) {
                $title = $jobTitles[array_rand($jobTitles)] . ' - ' . $company->getName();
                $slug = $this->slugger->slug($title . ' ' . uniqid())->lower()->toString();
                
                $offer = new JobOffer();
                $offer->setCompany($company);
                $offer->setCategory($categoryValues[array_rand($categoryValues)]);
                $offer->setTitle($title);
                $offer->setSlug($slug);
                $offer->setDescription($descriptions[array_rand($descriptions)]);
                $offer->setLocation($locations[array_rand($locations)]);
                $offer->setType($types[array_rand($types)]);
                $offer->setActive(rand(0, 10) > 1); // 90% active
                
                // Random creation date within last 30 days
                $createdAt = new \DateTimeImmutable('-' . rand(0, 30) . ' days');
                $offer->setCreatedAt($createdAt);
                $offer->setUpdatedAt($createdAt);
                
                $manager->persist($offer);
                $jobOffers[] = $offer;
            }
        }

        return $jobOffers;
    }

    private function createApplications(ObjectManager $manager, array $candidates, array $jobOffers): void
    {
        $statuses = [
            Application::STATUS_PENDING,
            Application::STATUS_ACCEPTED,
            Application::STATUS_REJECTED,
            Application::STATUS_WITHDRAWN,
        ];

        $messages = [
            'I am very interested in this position and believe my skills align perfectly with your requirements.',
            'I would love to contribute to your team and help drive success.',
            'This opportunity excites me, and I am eager to bring my expertise to your organization.',
            'I am confident that my background and experience make me an ideal candidate for this role.',
            null, // Some applications without messages
        ];

        // Create 2-4 applications per candidate
        foreach ($candidates as $candidate) {
            $numApplications = rand(2, 4);
            $usedOffers = [];
            
            for ($i = 0; $i < $numApplications; $i++) {
                // Find an offer that hasn't been used by this candidate
                $availableOffers = array_filter($jobOffers, function($offer) use ($candidate, $usedOffers) {
                    return !in_array($offer->getId(), $usedOffers);
                });
                
                if (empty($availableOffers)) {
                    break;
                }
                
                $offer = $availableOffers[array_rand($availableOffers)];
                $usedOffers[] = $offer->getId();
                
                $application = new Application();
                $application->setJobOffer($offer);
                $application->setCandidate($candidate);
                $application->setStatus($statuses[array_rand($statuses)]);
                $application->setMessage($messages[array_rand($messages)]);
                
                // Random creation date within last 20 days
                $createdAt = new \DateTimeImmutable('-' . rand(0, 20) . ' days');
                $application->setCreatedAt($createdAt);
                $application->setUpdatedAt($createdAt);
                
                $manager->persist($application);
            }
        }
    }

    private function createSavedOffers(ObjectManager $manager, array $candidates, array $jobOffers): void
    {
        // Each candidate saves 1-3 offers
        foreach ($candidates as $candidate) {
            $numSaved = rand(1, 3);
            $usedOffers = [];
            
            for ($i = 0; $i < $numSaved; $i++) {
                $availableOffers = array_filter($jobOffers, function($offer) use ($usedOffers) {
                    return !in_array($offer->getId(), $usedOffers);
                });
                
                if (empty($availableOffers)) {
                    break;
                }
                
                $offer = $availableOffers[array_rand($availableOffers)];
                $usedOffers[] = $offer->getId();
                
                $savedOffer = new SavedOffer();
                $savedOffer->setUser($candidate);
                $savedOffer->setJobOffer($offer);
                
                // Random creation date within last 15 days
                $createdAt = new \DateTimeImmutable('-' . rand(0, 15) . ' days');
                $savedOffer->setCreatedAt($createdAt);
                
                $manager->persist($savedOffer);
            }
        }
    }

    private function createAdminLogs(ObjectManager $manager, array $admins, array $companies, array $jobOffers): void
    {
        $actions = ['CREATE', 'UPDATE', 'DELETE', 'APPROVE', 'REJECT'];
        $entityTypes = ['User', 'Company', 'JobOffer', 'Category', 'Application'];

        // Create 20-30 admin logs
        $numLogs = rand(20, 30);
        
        for ($i = 0; $i < $numLogs; $i++) {
            $log = new AdminLog();
            $log->setAdmin($admins[array_rand($admins)]);
            $log->setAction($actions[array_rand($actions)]);
            $log->setEntityType($entityTypes[array_rand($entityTypes)]);
            
            // Set entity ID based on entity type
            switch ($log->getEntityType()) {
                case 'Company':
                    $log->setEntityId((string) $companies[array_rand($companies)]->getId());
                    break;
                case 'JobOffer':
                    $log->setEntityId((string) $jobOffers[array_rand($jobOffers)]->getId());
                    break;
                default:
                    $log->setEntityId((string) rand(1, 100));
            }
            
            // Random creation date within last 30 days
            $createdAt = new \DateTimeImmutable('-' . rand(0, 30) . ' days');
            $log->setCreatedAt($createdAt);
            
            $manager->persist($log);
        }
    }
}

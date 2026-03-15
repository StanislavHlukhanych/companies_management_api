<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');

        for ($i = 0; $i < 5; $i++) {
            $company = new Company();
            $company->setName($faker->company);
            $company->setDescription($faker->text(200));

            $manager->persist($company);

            for ($j = 0; $j < 10; $j++) {
                $employee = new Employee();
                $employee->setFirstName($faker->firstName);
                $employee->setLastName($faker->lastName);
                $employee->setEmail($faker->unique()->safeEmail);
                $employee->setCompany($company);

                $manager->persist($employee);
                $employees[] = $employee;
            }

            for ($k = 0; $k < 3; $k++) {
                $project = new Project();
                $project->setTitle("Project: " . $faker->bs);
                $project->setCompany($company);

                $randomEmployees = $faker->randomElements($employees, $faker->numberBetween(2, 4));

                foreach ($randomEmployees as $emp) {
                    $project->addParticipant($emp);
                }

                $manager->persist($project);
            }
        }

        $manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Faculty;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends BaseFixture implements DependentFixtureInterface
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function loadData(ObjectManager $manager)
    {
        //create a coordinator
        $faculties = $manager->getRepository(Faculty::class)->findAll();
        foreach ($faculties as $faculty) {//each faculty has only 1 coordinator
            $i = $faculty->getId();
            $user = $this->setUserDetail(User::ROLE_COORDINATOR, $i, $faculty);
            $groupName = 'coordinator_users';
            $this->addReference(sprintf('%s_%d', $groupName, $i), $user);
            $manager->persist($user);
            $manager->flush();
        }
        //create admins
        $this->createMany(1, 'admin_users', function ($count) {
            $user = $this->setUserDetail(User::ROLE_ADMIN);

            return $user;
        });

        //create managers
        $this->createMany(1, 'manager_users', function ($count) {
            $user = $this->setUserDetail(User::ROLE_MANAGER);

            return $user;
        });

        //create students
        $this->createMany(100, 'student_users', function ($count) {
            /** @var Faculty $faculty */
            $faculty = $this->getRandomReference('main_faculties');
            $user = $this->setUserDetail(User::ROLE_STUDENT, $count + 1, $faculty);

            return $user;
        });
        //create guests
        $this->createMany(30, 'guest_users', function ($count) {
            $faculty = $this->getRandomReference('main_faculties');
            /* @var Faculty $faculty */
            $user = $this->setUserDetail(User::ROLE_GUEST, $count + 1, $faculty);

            return $user;
        });
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            FacultyFixture::class,
        ];
    }

    private function setUserDetail(string $role, int $index = 0, Faculty $faculty = null)
    {
        $user = new User();
        if ($faculty) {
            $user->setFaculty($faculty);
        }
        $user->setRoles([$role]);
        if (0 === $index) {
            $email = sprintf('%s@example.com', $user->getShortRole());
        } else {
            $email = sprintf('%s%d@example.com', $user->getShortRole(), $index);
        }
        $user->setEmail($email);
        $user->setFirstName($this->faker->firstName);
        $user->setLastName($this->faker->lastName);
        $user->setEnabled(true);
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'abc123'));

        return $user;
    }
}

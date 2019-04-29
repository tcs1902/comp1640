<?php

namespace App\DataFixtures;

use App\Entity\Faculty;
use Doctrine\Common\Persistence\ObjectManager;

class FacultyFixture extends BaseFixture
{
    const FACULTIES = [
        'Faculty of Art',
        'Faculty of Law',
        'Faculty of Engineering',
        'Faculty of Philosophy',
        'Faculty of Economics',
    ];

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(4, 'main_faculties', function ($count) {
            $faculty = new Faculty();
            $faculty->setName(self::FACULTIES[$count]);

            return $faculty;
        });

        $manager->flush();
    }
}

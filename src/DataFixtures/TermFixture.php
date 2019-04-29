<?php

namespace App\DataFixtures;

use App\Entity\Term;
use Doctrine\Common\Persistence\ObjectManager;

class TermFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(4, 'main_terms', function ($count) {
            $term = new Term();
            $term->setName(sprintf('Term %d', $count + 1));
            $term->setActive(true);

            /** @var \DateTime $entryClosesAtDate */
            $entryClosesAtDate = $this->faker->dateTimeBetween('-90 days', '90 days');
            $finalClosesAtDate = clone $entryClosesAtDate;
            $finalClosesAtDate->modify('+14 day');
            $term->setEntryClosesAt($entryClosesAtDate);
            $term->setFinalClosesAt($finalClosesAtDate);

            return $term;
        });
        $manager->flush();
    }
}

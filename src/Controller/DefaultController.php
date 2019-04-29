<?php

namespace App\Controller;

use App\Entity\Contribution;
use App\Entity\Faculty;
use App\Entity\Term;
use App\Entity\User;
use App\Repository\ContributionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $userRepository = $entityManager->getRepository(User::class);

        $termCollection = [];

        $terms = $entityManager->getRepository(Term::class)->findAll();

        foreach ($terms as $term) {
            $facultyCollection = [];

            $faculties = $entityManager->getRepository(Faculty::class)->findAll();

            $data = [];
            $dataColors = [
              '#003f5c', '#58508d', '#bc5090', '#ff6361', '#ffa600', '#003f5c', '#58508d', '#bc5090', '#ff6361',
            ];

            foreach ($faculties as $index => $faculty) {
                /** @var ContributionRepository $contributionRepo */
                $contributionRepo = $entityManager->getRepository(Contribution::class);
                $contributions = $contributionRepo->findByFacultyAndByTerm($faculty, $term);

                $withoutCommentContributions = $contributionRepo->findWithoutComment($faculty, $term);
                $withoutCommentAfter14daysContributions = $contributionRepo->findWithoutCommentAfter14days($faculty, $term);

                $facultyItem = [
                    'faculty' => $faculty,
                    'coordinator' => $entityManager->getRepository(User::class)
                        ->findOneCoordinatorByFaculty($faculty),
                    'students' => $userRepository->findAllStudentsByFaculty($faculty),
                    'guests' => $userRepository->findAllGuestsByFaculty($faculty),
                    'contributions' => $contributions,
                    'withoutCommentContributions' => $withoutCommentContributions,
                    'withoutCommentAfter14daysContributions' => $withoutCommentAfter14daysContributions,
                ];

                $facultyData = [
                    'label' => $faculty->getName(),
                    'data' => \count($contributions),
                    'color' => $dataColors[$index],
                ];
                $data[] = $facultyData;

                $facultyCollection[] = $facultyItem;
            }

            $termItem = [
                'term' => $term,
                'facultyCollection' => $facultyCollection,
                'data' => $data,
            ];
            $termCollection[] = $termItem;
        }

        return $this->render('default/index.html.twig', [
            'termCollection' => $termCollection,
            'controller_name' => 'DefaultController',
        ]);
    }
}

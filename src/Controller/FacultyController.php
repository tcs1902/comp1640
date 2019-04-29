<?php

namespace App\Controller;

use App\Entity\Faculty;
use App\Entity\User;
use App\Form\FacultyType;
use App\Repository\FacultyRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/faculty")
 */
class FacultyController extends AbstractController
{
    /**
     * @Route("/", name="faculty_index", methods={"GET"})
     */
    public function index(Request $request, FacultyRepository $facultyRepository): Response
    {
        $criteria = [];
        $page = $request->get('page', 1);
        $size = $request->get('size', 10);
        $sort = $request->get('sort', []);

        return $this->render('faculty/index.html.twig', [
            'pager' => $facultyRepository->search($criteria, $sort)->setMaxPerPage($size)->setCurrentPage($page),
            'faculties' => $facultyRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="faculty_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $faculty = new Faculty();
        $form = $this->createForm(FacultyType::class, $faculty);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($faculty);

            /** @var User $newCoordinator */
            $newCoordinator = $form['coordinator']->getData();
            $newCoordinator->setFaculty($faculty);
            $newCoordinator->setRoles([User::ROLE_COORDINATOR]);

            $this->getDoctrine()->getManager()->persist($newCoordinator);

            $entityManager->flush();

            return $this->redirectToRoute('faculty_index');
        }

        return $this->render('faculty/new.html.twig', [
            'faculty' => $faculty,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="faculty_show", methods={"GET"})
     */
    public function show(Faculty $faculty): Response
    {
        return $this->render('faculty/show.html.twig', [
            'faculty' => $faculty,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="faculty_edit", methods={"GET","POST"})
     *
     * @param Request $request
     * @param Faculty $faculty
     *
     * @throws NonUniqueResultException
     *
     * @return Response
     */
    public function edit(Request $request, Faculty $faculty): Response
    {
        /** @var User $coordinator */
        $coordinator = $this->getDoctrine()->getManager()
            ->getRepository(User::class)
            ->findByRoleAndFaculty(User::ROLE_COORDINATOR, $faculty);
        $form = $this->createForm(FacultyType::class, $faculty);
        $form->get('coordinator')->setData($coordinator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $newCoordinator */
            $newCoordinator = $form['coordinator']->getData();
            $newCoordinator->setFaculty($faculty);

            if ($coordinator) {
                $coordinator->setRoles([User::ROLE_GUEST]);
                $this->getDoctrine()->getManager()->persist($coordinator);
                $faculty->removeUser($coordinator);
            }
            $this->getDoctrine()->getManager()->persist($newCoordinator);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('faculty_index', [
                'id' => $faculty->getId(),
            ]);
        }

        return $this->render('faculty/edit.html.twig', [
            'faculty' => $faculty,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="faculty_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Faculty $faculty): Response
    {
        if ($this->isCsrfTokenValid('delete'.$faculty->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($faculty);
            $entityManager->flush();
        }

        return $this->redirectToRoute('faculty_index');
    }
}

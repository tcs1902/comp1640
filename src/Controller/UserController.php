<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserCreateType;
use App\Form\Type\UserEditType;
use App\Form\Type\UserSearchType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/users", name="user_index", methods={"GET"})
     *
     * @param Request        $request
     * @param UserRepository $userRepository
     *
     * @return Response
     */
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserSearchType::class);
        $form->handleRequest($request);
        $criteria = $form->getData() ?: [];
        $page = $request->get('page', 1);
        $size = $request->get('size', 10);
        $sort = $request->get('sort', ['id' => 'desc']);

        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
            'pager' => $userRepository->search($criteria, $sort)->setMaxPerPage($size)->setCurrentPage($page),
        ]);
    }

    /**
     * @Route("/users/create", name="user_create", methods={"GET", "POST"})
     *
     * @param Request                      $request
     * @param EntityManagerInterface       $entityManager
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(UserCreateType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));
            $user->setEnabled(true);

            if (!$user->isBelongToAFaculty()) {
                $user->setFaculty(null);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            $user->eraseCredentials();

            $this->addFlash('success', 'Create user successfully.');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit", methods={"GET", "POST"})
     *
     * @param User                   $user
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     */
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user->isBelongToAFaculty()) {
                $user->setFaculty(null);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Edit user successfully.');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

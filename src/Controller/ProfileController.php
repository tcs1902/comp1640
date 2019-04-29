<?php

namespace App\Controller;

use App\Form\ProfileEditType;
use App\Form\Type\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile", methods={"GET", "PUT"}))
     *
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse|Response
     */
    public function index(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(ProfileEditType::class, $this->getUser());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Update Profile Successfully!');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/change-password", name="profile_change_password")
     *
     * @param Request                      $request
     * @param EntityManagerInterface       $em
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return RedirectResponse|Response
     */
    public function changePassword(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $this->getUser();
            $user->setPassword($encoder->encodePassword($user, $data['password']));
            $em->flush();
            $user->eraseCredentials();

            $this->addFlash('success', 'Change password successfully.');

            return $this->redirectToRoute('default');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

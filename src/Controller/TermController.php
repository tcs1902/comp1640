<?php

namespace App\Controller;

use App\Entity\Term;
use App\Form\TermType;
use App\Repository\TermRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/term")
 */
class TermController extends AbstractController
{
    /**
     * @Route("/", name="term_index", methods={"GET"})
     */
    public function index(Request $request, TermRepository $termRepository): Response
    {
        $criteria = [];
        $page = $request->get('page', 1);
        $size = $request->get('size', 10);
        $sort = $request->get('sort', []);

        return $this->render('term/index.html.twig', [
            'pager' => $termRepository->search($criteria, $sort)->setMaxPerPage($size)->setCurrentPage($page),
        ]);
    }

    /**
     * @Route("/new", name="term_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $term = new Term();
        $form = $this->createForm(TermType::class, $term);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($term);
            $entityManager->flush();

            return $this->redirectToRoute('term_index');
        }

        return $this->render('term/new.html.twig', [
            'term' => $term,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="term_show", methods={"GET"})
     */
    public function show(Term $term): Response
    {
        return $this->render('term/show.html.twig', [
            'term' => $term,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="term_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Term $term): Response
    {
        $form = $this->createForm(TermType::class, $term);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('term_index', [
                'id' => $term->getId(),
            ]);
        }

        return $this->render('term/edit.html.twig', [
            'term' => $term,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="term_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Term $term): Response
    {
        if ($this->isCsrfTokenValid('delete'.$term->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($term);
            $entityManager->flush();
        }

        return $this->redirectToRoute('term_index');
    }
}

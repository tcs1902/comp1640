<?php

namespace App\Controller;

use App\Entity\Contribution;
use App\Entity\User;
use App\Form\ContributionCommentFormType;
use App\Form\ContributionEditFormType;
use App\Form\ContributionPublishFormType;
use App\Form\ContributionSubmissionFormType;
use App\Form\Model\ContributionEditFormModel;
use App\Form\Model\ContributionReviewFormModel;
use App\Form\Model\ContributionSubmissionFormModel;
use App\Repository\ContributionRepository;
use App\Repository\FacultyRepository;
use App\Repository\TermRepository;
use App\Utils\UploaderHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contribution")
 */
class ContributionController extends AbstractController
{
    /**
     * @Route("/", name="contribution_index", methods={"GET"})
     *
     * @param Request                $request
     * @param ContributionRepository $contributionRepository
     * @param FacultyRepository      $facultyRepository
     * @param TermRepository         $termRepository
     *
     * @return Response
     */
    public function index(Request $request, ContributionRepository $contributionRepository, FacultyRepository $facultyRepository, TermRepository $termRepository): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $criteria = [];

        $title = $request->query->get('title');
        if ($title) {
            $criteria['title'] = $title;
        }

        if ($currentUser->isBelongToAFaculty()) {
            $faculty = $currentUser->getFaculty();
            $criteria['faculty'] = $faculty;
        } else {
            $facultyId = $request->query->get('faculty');
            if ($facultyId) {
                $faculty = $facultyRepository->find($facultyId);
                $criteria['faculty'] = $faculty;
            }
        }

        if (User::ROLE_STUDENT === $currentUser->getLongRole()) {
            $criteria['author'] = $currentUser;
        }

        if (User::ROLE_GUEST === $currentUser->getLongRole()) {
            $criteria['published'] = true;
        }

        $termId = $request->query->get('term');
        if ($termId) {
            $term = $termRepository->find($termId);
            $criteria['term'] = $term;
        }

        $published = $request->query->get('published');
        if ($published) {
            $criteria['published'] = 'yes' === $published;
        }

        $approved = $request->query->get('approved');
        if ($approved) {
            $criteria['approved'] = 'yes' === $approved;
        }

        $page = $request->get('page', 1);
        $size = $request->get('size', 10);
        $sort = $request->get('sort', []);

        return $this->render('contribution/index.html.twig', [
            'pager' => $contributionRepository->search($criteria, $sort)->setMaxPerPage($size)->setCurrentPage($page),
            'faculties' => $facultyRepository->findAll(),
            'terms' => $termRepository->findAll(),
            ]);
    }

    /**
     * @Route("/new", name="contribution_new", methods={"GET","POST"})
     *
     * @param Request        $request
     * @param UploaderHelper $uploaderHelper
     * @param TermRepository $termRepository
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function new(Request $request, UploaderHelper $uploaderHelper, TermRepository $termRepository): Response
    {
        $form = $this->createForm(ContributionSubmissionFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ContributionSubmissionFormModel $contributionSubmissionModel */
            $contributionSubmissionModel = $form->getData();

            //check entry closure
            $term = $contributionSubmissionModel->term;
            $entryClosure = $term->getEntryClosesAt();
            if ($entryClosure < new \DateTimeImmutable()) {
                $this->addFlash('error', 'Cannot submit new contribution after Entry Closure Date ('.$entryClosure->format('Y-m-d h:i:s').')');

                return $this->render('contribution/new.html.twig', [
                    'form' => $form->createView(),
                    'terms' => $termRepository->findAll(),
                ]);
            }

            $contribution = new Contribution();

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['documentFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadContributionDocument($uploadedFile);
                $contribution->setDocumentFilename($newFilename);
            }

            $contribution->setTitle($contributionSubmissionModel->title);
            $contribution->setTerm($contributionSubmissionModel->term);
            if (true === $contributionSubmissionModel->agreeTerms) {
                $contribution->agreeToTerms();
            }
            $contribution->setAuthor($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contribution);
            $entityManager->flush();

            $this->addFlash('success', 'Contribution submission success!');

            return $this->redirectToRoute('contribution_index');
        }

        return $this->render('contribution/new.html.twig', [
            'form' => $form->createView(),
            'terms' => $termRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="contribution_show", methods={"GET"})
     *
     * @param Contribution $contribution
     *
     * @return Response
     */
    public function show(Contribution $contribution): Response
    {
        return $this->render('contribution/show.html.twig', [
            'contribution' => $contribution,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="contribution_edit", methods={"GET","POST"})
     *
     * @param Request        $request
     * @param Contribution   $contribution
     * @param UploaderHelper $uploaderHelper
     * @param TermRepository $termRepository
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function edit(Request $request, Contribution $contribution, UploaderHelper $uploaderHelper, TermRepository $termRepository): Response
    {
        $contributionEditModel = new ContributionEditFormModel();
        $contributionEditModel->title = $contribution->getTitle();
        $contributionEditModel->term = $contribution->getTerm();
        $contributionEditModel->publishedAt = $contribution->getPublishedAt();
        $contributionEditModel->comment = $contribution->getComment();
        $contributionEditModel->feedback = $contribution->getFeedback();
        $contributionEditModel->documentFileName = $contribution->getDocumentFilename();
        $form = $this->createForm(ContributionEditFormType::class, $contributionEditModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //check final closure
            $term = $contributionEditModel->term;
            $finalClosure = $term->getFinalClosesAt();
            if ($finalClosure < new \DateTimeImmutable()) {
                $this->addFlash('error', 'Cannot edit after Final Closure Date ('.$finalClosure->format('Y-m-d h:i:s').')');

                return $this->render('contribution/edit.html.twig', [
                    'contribution' => $contribution,
                    'form' => $form->createView(),
                    'terms' => $termRepository->findAll(),
                ]);
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['documentFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadContributionDocument($uploadedFile);
                $contribution->setDocumentFilename($newFilename);
            }

            $contribution->setTitle($contributionEditModel->title);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Contribution update success!');

            return $this->redirectToRoute('contribution_index', [
                'id' => $contribution->getId(),
            ]);
        }

        return $this->render('contribution/edit.html.twig', [
            'contribution' => $contribution,
            'form' => $form->createView(),
            'terms' => $termRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}/review", name="contribution_review", methods={"GET","POST"})
     *
     * @param Request        $request
     * @param Contribution   $contribution
     * @param TermRepository $termRepository
     *
     * @return Response
     */
    public function review(Request $request, Contribution $contribution, TermRepository $termRepository): Response
    {
        $contributionReviewModel = new ContributionReviewFormModel();
        $contributionReviewModel->comment = $contribution->getComment();
        $contributionReviewModel->feedback = $contribution->getFeedback();
        $contributionReviewModel->publish = (bool) $contribution->getPublishedAt();
        $contributionReviewModel->approve = (bool) $contribution->getApprovedAt();

        $form = $this->createForm(ContributionCommentFormType::class, $contributionReviewModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$contribution->getFeedback()) {
                $contribution->registerFirstFeedbackTime();
            }
            $contribution->setFeedback($contributionReviewModel->feedback);

            if (true === $contributionReviewModel->approve) {
                $contribution->approve();
            }
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Contribution review success!');

            return $this->redirectToRoute('contribution_index', [
                'id' => $contribution->getId(),
            ]);
        }

        return $this->render('contribution/review.html.twig', [
            'contribution' => $contribution,
            'form' => $form->createView(),
            'terms' => $termRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}/publish", name="contribution_publish", methods={"GET","POST"})
     *
     * @param Request        $request
     * @param Contribution   $contribution
     * @param TermRepository $termRepository
     *
     * @return Response
     */
    public function publish(Request $request, Contribution $contribution, TermRepository $termRepository): Response
    {
        $contributionReviewModel = new ContributionReviewFormModel();
        $contributionReviewModel->comment = $contribution->getComment();
        $contributionReviewModel->feedback = $contribution->getFeedback();
        $contributionReviewModel->publish = (bool) $contribution->getPublishedAt();
        $contributionReviewModel->approve = (bool) $contribution->getApprovedAt();

        $form = $this->createForm(ContributionPublishFormType::class, $contributionReviewModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (true === $contributionReviewModel->publish) {
                $contribution->publish();
            }
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Contribution publish success!');

            return $this->redirectToRoute('contribution_index', [
                'id' => $contribution->getId(),
            ]);
        }

        return $this->render('contribution/publish.html.twig', [
            'contribution' => $contribution,
            'form' => $form->createView(),
            'terms' => $termRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="contribution_delete", methods={"DELETE"})
     *
     * @param Request      $request
     * @param Contribution $contribution
     *
     * @return Response
     */
    public function delete(Request $request, Contribution $contribution): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contribution->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($contribution);
            $entityManager->flush();
        }

        $this->addFlash('success', 'Contribution delete success!');

        return $this->redirectToRoute('contribution_index');
    }
}

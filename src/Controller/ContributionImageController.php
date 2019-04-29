<?php

namespace App\Controller;

use App\Entity\Contribution;
use App\Entity\ContributionImage;
use App\Utils\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContributionImageController extends AbstractController
{
    /**
     * @Route("/contribution/{id}/image", name="contribution_add_image", methods={"POST"})
     *
     * @param Contribution           $contribution
     * @param Request                $request
     * @param UploaderHelper         $uploaderHelper
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface     $validator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function uploadContributionImage(Contribution $contribution, Request $request, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('image');
        $violations = $validator->validate(
            $uploadedFile,
            [
                new NotBlank([
                    'message' => 'Please select a file to upload',
                ]),
                new File([
                    'maxSize' => '20M',
                    'mimeTypes' => [
                        'image/*',
                    ],
                    'mimeTypesMessage' => 'Please upload image only!',
                ]),
            ]
        );

        if ($violations->count() > 0) {
            /** @var ConstraintViolation $violation */
            $violation = $violations[0];

            return $this->json([
                'detail' => $violation->getMessage(),
            ], 400);
        }

        $filename = $uploaderHelper->uploadContributionImage($uploadedFile);
        $contributionImage = new ContributionImage($contribution);
        $contributionImage->setFilename($filename);
        $contributionImage->setOriginalFilename($uploadedFile->getClientOriginalName() ?? $filename);
        $contributionImage->setMimeType($uploadedFile->getClientMimeType() ?? 'application/none');
        $entityManager->persist($contributionImage);
        $entityManager->flush();

        return $this->json($contributionImage,
            201,
            [],
            [
                'group' => ['main'],
            ]);
    }
}

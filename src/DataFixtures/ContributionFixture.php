<?php

namespace App\DataFixtures;

use App\Entity\Contribution;
use App\Entity\ContributionImage;
use App\Entity\Term;
use App\Entity\User;
use App\Utils\UploaderHelper;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ContributionFixture extends BaseFixture implements DependentFixtureInterface
{
    private static $contributionDocuments = [
        'sample.docx',
        'sample1.doc',
        'sample_docx_1page.docx',
        'sample_docx_10pages.docx',
        'sample_docx_50pages.docx',
        'sample_docx_1000pages.docx',
    ];

    private static $contributionImages = [
        'fjords.jpg',
        'lights.jpg',
        'nature.jpg',
        'newyork.jpg',
        'paris.jpg',
        'sanfran.jpg',
    ];

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * ContributionFixture constructor.
     *
     * @param UploaderHelper $uploaderHelper
     */
    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(200, 'main_contributions', function ($count) use ($manager) {
            $contribution = new Contribution();
            $contribution->setTitle($this->faker->realText($this->faker->numberBetween(10, 100)));
            /** @var User $student */
            $student = $this->getRandomReference('student_users');
            $contribution->setAuthor($student);
            /** @var Term $term */
            $term = $this->getRandomReference('main_terms');
            $contribution->setTerm($term);
            $contribution->setAgreedTermsAt($this->faker->dateTimeBetween('-1 year', 'now'));
            $isReviewed = $this->faker->boolean(70);
            $faculty = $student->getFaculty();
            if ($isReviewed) {
                $coordinator = $manager->getRepository(User::class)
                    ->findOneCoordinatorByFaculty($faculty);
                if ($coordinator) {
                    $contribution->setFeedback($this->faker->realText());
                    $feedbackAt = $this->faker->dateTimeBetween('-1 years', '-6 months');
                    $contribution->setFeedbackedAt($feedbackAt);
                    $contribution->setApprovedBy($coordinator);
                    $contribution->setApprovedAt($feedbackAt);

                    $studentCommented = $this->faker->boolean(50);
                    if ($studentCommented) {
                        $contribution->setComment($this->faker->realText());
                    }

                    $isPublished = $this->faker->boolean(70);
                    if ($isPublished) {
                        $contribution->setPublishedAt($this->faker->dateTimeBetween('-6 months', 'now'));
                    }
                }
            }

            $documentFilename = $this->fakeUploadDocument();

            $contribution->setDocumentFilename($documentFilename);

            for ($i = 0; $i <= $this->faker->numberBetween(0, \count(self::$contributionImages) - 1); ++$i) {
                $imageFilename = $this->fakeUploadImage();
                $contributionImage = new ContributionImage($contribution);
                $contributionImage->setFilename($imageFilename);
                $contributionImage->setOriginalFilename($imageFilename);
                $contributionImage->setMimeType('image/png');
                $contribution->addContributionImage($contributionImage);
            }

            return $contribution;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixture::class,
            FacultyFixture::class,
            TermFixture::class,
        ];
    }

    private function fakeUploadDocument(): string
    {
        $randomDocument = $this->faker->randomElement(self::$contributionDocuments);
        $fs = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$randomDocument;
        $fs->copy(__DIR__.'/documents/'.$randomDocument, $targetPath, true);

        return $this->uploaderHelper->uploadContributionDocument(new File($targetPath));
    }

    private function fakeUploadImage(): string
    {
        $randomImage = $this->faker->randomElement(self::$contributionImages);
        $fs = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$randomImage;
        $fs->copy(__DIR__.'/images/'.$randomImage, $targetPath, true);

        return $this->uploaderHelper->uploadContributionImage(new File($targetPath));
    }
}

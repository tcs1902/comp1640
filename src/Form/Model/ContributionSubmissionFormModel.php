<?php

namespace App\Form\Model;

use App\Entity\Term;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class ContributionSubmissionFormModel
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $title;

    /**
     * @var Term
     * @Assert\NotNull()
     */
    public $term;

    /**
     * @var bool
     * @Assert\IsTrue(message="You must agree the Terms and Conditions before submitting!!!")
     */
    public $agreeTerms;

    /**
     * @var File
     */
    public $documentFile;
}

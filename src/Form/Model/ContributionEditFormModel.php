<?php

namespace App\Form\Model;

use App\Entity\Term;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class ContributionEditFormModel
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $title;

    /**
     * @var \DateTimeInterface
     */
    public $publishedAt;

    /**
     * @var Term
     */
    public $term;

    /**
     * @var File
     */
    public $documentFile;

    /**
     * @var string
     */
    public $documentFileName;
    /**
     * @var string
     */
    public $comment;

    /**
     * @var string
     */
    public $feedback;
}

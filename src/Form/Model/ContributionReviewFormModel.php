<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ContributionReviewFormModel
{
    /**
     * @var string
     */
    public $comment;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $feedback;

    /**
     * @var bool
     */
    public $publish;

    /**
     * @var bool
     */
    public $approve;
}

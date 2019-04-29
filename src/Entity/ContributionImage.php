<?php

namespace App\Entity;

use App\Utils\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContributionImageRepository")
 */
class ContributionImage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $originalFilename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mimeType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Contribution", inversedBy="contributionImages", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $contribution;

    /**
     * ContributionImage constructor.
     *
     * @param Contribution $contribution
     */
    public function __construct(Contribution $contribution)
    {
        $this->contribution = $contribution;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getContribution(): ?Contribution
    {
        return $this->contribution;
    }

    public function getImagePath()
    {
        return UploaderHelper::CONTRIBUTION_IMAGE.'/'.$this->getFilename();
    }
}

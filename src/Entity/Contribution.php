<?php

namespace App\Entity;

use App\Utils\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContributionRepository")
 */
class Contribution
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
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $feedbackedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="contributions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reviewableContributions")
     */
    private $approvedBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Term", inversedBy="contributions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $term;

    /**
     * @ORM\Column(type="datetime")
     */
    private $agreedTermsAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $documentFilename;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ContributionImage", mappedBy="contribution", orphanRemoval=true, cascade={"persist"})
     */
    private $contributionImages;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvedAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $feedback;

    public function __construct()
    {
        $this->contributionImages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getFeedbackedAt(): ?\DateTimeInterface
    {
        return $this->feedbackedAt;
    }

    public function setFeedbackedAt(?\DateTimeInterface $feedbackedAt): self //for fixture purpose
    {
        $this->feedbackedAt = $feedbackedAt;

        return $this;
    }

    public function registerFirstFeedbackTime(): self
    {
        $this->feedbackedAt = new \DateTime();

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self //for fixture purpose
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function publish(): self
    {
        $this->publishedAt = new \DateTime();

        return $this;
    }

    public function approve(): self
    {
        $this->approvedAt = new \DateTime();

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?User $approvedBy): self
    {
        $this->approvedBy = $approvedBy;

        return $this;
    }

    public function getTerm(): ?Term
    {
        return $this->term;
    }

    public function setTerm(?Term $term): self
    {
        $this->term = $term;

        return $this;
    }

    public function getAgreedTermsAt(): ?\DateTimeInterface
    {
        return $this->agreedTermsAt;
    }

    public function setAgreedTermsAt(?\DateTimeInterface $agreeTermsAt): self //for fixture purpose
    {
        $this->agreedTermsAt = $agreeTermsAt;

        return $this;
    }

    public function agreeToTerms()
    {
        $this->agreedTermsAt = new \DateTime();
    }

    public function getDocumentFilename(): ?string
    {
        return $this->documentFilename;
    }

    public function setDocumentFilename(string $documentFilename): self
    {
        $this->documentFilename = $documentFilename;

        return $this;
    }

    public function getDocumentPath()
    {
        return UploaderHelper::CONTRIBUTION_DOCUMENT.'/'.$this->getDocumentFilename();
    }

    /**
     * @return Collection|ContributionImage[]
     */
    public function getContributionImages(): Collection
    {
        return $this->contributionImages;
    }

    public function addContributionImage(ContributionImage $contributionImage): self
    {
        if (!$this->contributionImages->contains($contributionImage)) {
            $this->contributionImages[] = $contributionImage;
        }

        return $this;
    }

    public function removeContributionImage(ContributionImage $contributionImage): self
    {
        if ($this->contributionImages->contains($contributionImage)) {
            $this->contributionImages->removeElement($contributionImage);
            // set the owning side to null (unless already changed)
            if ($contributionImage->getContribution() === $this) {
            }
        }

        return $this;
    }

    public function getApprovedAt(): ?\DateTimeInterface
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeInterface $approvedAt): self
    {
        $this->approvedAt = $approvedAt;

        return $this;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setFeedback(?string $feedback): self
    {
        $this->feedback = $feedback;

        return $this;
    }

    public function isAfter14days()
    {
        return $this->agreedTermsAt < new \DateTimeImmutable('-14 days');
    }
}

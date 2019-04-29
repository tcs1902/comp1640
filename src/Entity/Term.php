<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TermRepository")
 */
class Term
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
    private $name;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contribution", mappedBy="term", orphanRemoval=true)
     */
    private $contributions;

    /**
     * @ORM\Column(type="datetime")
     */
    private $entryClosesAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $finalClosesAt;

    public function __construct()
    {
        $this->contributions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return Collection|Contribution[]
     */
    public function getContributions(): Collection
    {
        return $this->contributions;
    }

    public function addContribution(Contribution $contribution): self
    {
        if (!$this->contributions->contains($contribution)) {
            $this->contributions[] = $contribution;
            $contribution->setTerm($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution): self
    {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getTerm() === $this) {
                $contribution->setTerm(null);
            }
        }

        return $this;
    }

    public function getEntryClosesAt(): ?\DateTimeInterface
    {
        return $this->entryClosesAt;
    }

    public function setEntryClosesAt(\DateTimeInterface $entryClosesAt): self
    {
        $this->entryClosesAt = $entryClosesAt;

        return $this;
    }

    public function getFinalClosesAt(): ?\DateTimeInterface
    {
        return $this->finalClosesAt;
    }

    public function setFinalClosesAt(\DateTimeInterface $finalClosesAt): self
    {
        $this->finalClosesAt = $finalClosesAt;

        return $this;
    }

    /**
     * @Assert\Callback
     *
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $term = $this;
        /** @var \DateTime $entryClosure */
        $entryClosure = $term->getEntryClosesAt();
        $finalClosure = $term->getFinalClosesAt();
        if ($finalClosure < $entryClosure || $finalClosure > ($entryClosure->modify('+14 day'))) {
            $context->buildViolation('Final Closure not valid, it must after Entry closure but no more than 14 days')->addViolation();
        }
    }

    public function __toString()
    {
        return $this->getName();
    }
}

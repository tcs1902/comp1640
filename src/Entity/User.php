<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_MANAGER = 'ROLE_MANAGER';
    const ROLE_COORDINATOR = 'ROLE_COORDINATOR';
    const ROLE_STUDENT = 'ROLE_STUDENT';
    const ROLE_GUEST = 'ROLE_GUEST';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string
     */
    private $plainPassword;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Faculty", inversedBy="users")
     */
    private $faculty;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contribution", mappedBy="author", orphanRemoval=true)
     */
    private $contributions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contribution", mappedBy="coordinator")
     */
    private $reviewableContributions;

    public function __construct()
    {
        $this->contributions = new ArrayCollection();
        $this->reviewableContributions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getDisplayName(): string
    {
        return (!$this->firstName && !$this->lastName) ? $this->email : "$this->firstName $this->lastName";
    }

    public function getFaculty(): ?Faculty
    {
        return $this->faculty;
    }

    public function setFaculty(?Faculty $faculty): self
    {
        $this->faculty = $faculty;

        return $this;
    }

    public function getShortRole(): string
    {
        return mb_strtolower(str_replace('ROLE_', '', $this->roles[0]));
    }

    public function getLongRole(): string
    {
        return $this->roles[0];
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
            $contribution->setAuthor($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution): self
    {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getAuthor() === $this) {
                $contribution->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Contribution[]
     */
    public function getReviewableContributions(): Collection
    {
        return $this->reviewableContributions;
    }

    public function addReviewableContribution(Contribution $reviewableContribution): self
    {
        if (!$this->reviewableContributions->contains($reviewableContribution)) {
            $this->reviewableContributions[] = $reviewableContribution;
            $reviewableContribution->setApprovedBy($this);
        }

        return $this;
    }

    public function removeReviewableContribution(Contribution $reviewableContribution): self
    {
        if ($this->reviewableContributions->contains($reviewableContribution)) {
            $this->reviewableContributions->removeElement($reviewableContribution);
            // set the owning side to null (unless already changed)
            if ($reviewableContribution->getApprovedBy() === $this) {
                $reviewableContribution->setApprovedBy(null);
            }
        }

        return $this;
    }

    public function isBelongToAFaculty()
    {
        return \in_array($this->getLongRole(), [
            self::ROLE_COORDINATOR,
            self::ROLE_STUDENT,
            self::ROLE_GUEST,
        ], true);
    }

    public function __toString()
    {
        return $this->getFirstName();
    }
}

<?php

namespace App\Entity;

use App\Repository\WineGameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

#[ORM\Entity(repositoryClass: WineGameRepository::class)]
class WineGame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Length(min: 1)]
    private ?string $wineGameName = null;

    #[ORM\Column]
    private ?bool $padlockIsOpen = null;

    #[ORM\Column]
    #[Range(min: 0, max: 3)]
    private ?int $music = null;

    #[ORM\Column]
    #[Range(min: 10.0, max: 25.0)]
    private ?float $temperature = null;

    #[ORM\Column(length: 4)]
    #[Length(min: 4)]
    #[Length(max: 4)]
    private ?string $bottleCode = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Length(min: 1)]
    private ?string $userCodeName = null;

    #[ORM\Column(length: 255)]
    #[Length(min: 1)]
    private ?string $userCode = null;

    #[ORM\Column(length: 255)]
    #[Length(min: 1)]
    private ?string $adminCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $hint = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'wineGames')]
    private Collection $user;

    #[ORM\Column]
    private ?bool $bottleRing = null;

    #[ORM\Column(length: 255)]
    private ?string $cookiePass = null;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWineGameName(): ?string
    {
        return $this->wineGameName;
    }

    public function setWineGameName(string $wineGameName): self
    {
        $this->wineGameName = $wineGameName;

        return $this;
    }

    public function isPadlockIsOpen(): ?bool
    {
        return $this->padlockIsOpen;
    }

    public function setPadlockIsOpen(bool $padlockIsOpen): self
    {
        $this->padlockIsOpen = $padlockIsOpen;

        return $this;
    }

    public function getMusic(): ?int
    {
        return $this->music;
    }

    public function setMusic(int $music): self
    {
        $this->music = $music;

        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getBottleCode(): ?string
    {
        return $this->bottleCode;
    }

    public function setBottleCode(string $bottleCode): self
    {
        $this->bottleCode = $bottleCode;

        return $this;
    }

    public function getUserCodeName(): ?string
    {
        return $this->userCodeName;
    }

    public function setUserCodeName(string $userCodeName): self
    {
        $this->userCodeName = $userCodeName;

        return $this;
    }

    public function getUserCode(): ?string
    {
        return $this->userCode;
    }

    public function setUserCode(string $userCode): self
    {
        $this->userCode = $userCode;

        return $this;
    }

    public function getAdminCode(): ?string
    {
        return $this->adminCode;
    }

    public function setAdminCode(string $adminCode): self
    {
        $this->adminCode = $adminCode;

        return $this;
    }

    public function getHint(): ?string
    {
        return $this->hint;
    }

    public function setHint(?string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->user->removeElement($user);

        return $this;
    }

    public function isBottleRing(): ?bool
    {
        return $this->bottleRing;
    }

    public function setBottleRing(bool $bottleRing): self
    {
        $this->bottleRing = $bottleRing;

        return $this;
    }

    public function getCookiePass(): ?string
    {
        return $this->cookiePass;
    }

    public function setCookiePass(string $cookiePass): self
    {
        $this->cookiePass = $cookiePass;

        return $this;
    }
}

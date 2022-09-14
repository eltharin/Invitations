<?php

namespace Eltharin\InvitationsBundle\Entity;

use Eltharin\InvitationsBundle\Repository\InvitationRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(fetch:'EAGER')]
    private ?User $user = null;

	#[ORM\Column(length: 255)]
	private ?string $email = null;

	#[ORM\Column()]
	private ?int $itemId = null;

	#[ORM\Column]
    private array $data = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
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

	public function getData(): array
	{
		return $this->data;
	}

	public function setData(array $data): self
	{
		$this->data = $data;

		return $this;
	}

	public function getItemId(): int
	{
		return $this->itemId;
	}

	public function setItemId(int $itemId): self
	{
		$this->itemId = $itemId;

		return $this;
	}
}

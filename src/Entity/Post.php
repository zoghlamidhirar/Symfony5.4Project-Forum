<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(name: "post_id", type: "integer")]
    private $postId;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $contentPost = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\DateTime(format: "Y-m-d")]
    private ?string $creationDatePost = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'thread_id', referencedColumnName: 'thread_id', nullable: false)]
    private ?Thread $thread = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $user = null;

    public function getPostID(): ?int
    {
        return $this->postId;
    }

    public function getContentPost(): ?string
    {
        return $this->contentPost;
    }

    public function setContentPost(string $contentPost): static
    {
        $this->contentPost = $contentPost;

        return $this;
    }

    public function getCreationDatePost(): ?string
    {
        return $this->creationDatePost;
    }

    public function setCreationDatePost(?string $creationDatePost): static
    {
        $this->creationDatePost = $creationDatePost;

        return $this;
    }

    public function getThread(): ?Thread
    {
        return $this->thread;
    }

    public function setThread(?Thread $thread): static
    {
        $this->thread = $thread;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}

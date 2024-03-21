<?php

namespace App\Entity;

use App\Repository\ThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ThreadRepository::class)]
class Thread
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(name: "thread_id", type: "integer")]
    private ?int $threadID = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Regex('/^[a-zA-Z\s]*$/')]
    private ?string $titleThread = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\DateTime(format: "Y-m-d")]
    private ?string $creationDateThread = null;

    #[ORM\ManyToOne(inversedBy: 'threads')]
    #[ORM\JoinColumn(name: 'forum_id', referencedColumnName: 'forum_id', nullable: false)]
    private ?Forum $forum = null;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'thread')]
    private Collection $posts;

    #[ORM\ManyToOne(inversedBy: 'threads')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\DateTime(format: "Y-m-d")]
    private ?string $scheduled_publish_time = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $isSpecial = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Published = null;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }


    public function getThreadID(): ?int
    {
        return $this->threadID;
    }

    public function getTitleThread(): ?string
    {
        return $this->titleThread;
    }

    public function setTitleThread(?string $titleThread): static
    {
        $this->titleThread = $titleThread;

        return $this;
    }

    public function getCreationDateThread(): ?string
    {
        return $this->creationDateThread;
    }

    public function setCreationDateThread(?string $creationDateThread): static
    {
        $this->creationDateThread = $creationDateThread;

        return $this;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): static
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setThread($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getThread() === $this) {
                $post->setThread(null);
            }
        }

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

    public function getScheduledPublishTime(): ?string
    {
        return $this->scheduled_publish_time;
    }

    public function setScheduledPublishTime(?string $scheduled_publish_time): static
    {
        $this->scheduled_publish_time = $scheduled_publish_time;

        return $this;
    }

    public function getIsSpecial(): ?string
    {
        return $this->isSpecial;
    }

    public function setIsSpecial(?string $isSpecial): static
    {
        $this->isSpecial = $isSpecial;

        return $this;
    }

    public function getPublished(): ?string
    {
        return $this->Published;
    }

    public function setPublished(?string $Published): static
    {
        $this->Published = $Published;

        return $this;
    }
}

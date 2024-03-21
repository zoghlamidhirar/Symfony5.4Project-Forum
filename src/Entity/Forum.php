<?php

namespace App\Entity;

use App\Repository\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForumRepository::class)]
class Forum
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "forum_id")]
    private ?int $forumID = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nameForum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descriptionForum = null;

    #[ORM\OneToMany(targetEntity: Thread::class, mappedBy: 'forum')]
    private Collection $threads;

    public function __construct()
    {
        $this->threads = new ArrayCollection();
    }

    public function getForumID(): ?int
    {
        return $this->forumID;
    }

    public function getNameForum(): ?string
    {
        return $this->nameForum;
    }

    public function setNameForum(?string $nameForum): static
    {
        $this->nameForum = $nameForum;

        return $this;
    }

    public function getDescriptionForum(): ?string
    {
        return $this->descriptionForum;
    }

    public function setDescriptionForum(?string $descriptionForum): static
    {
        $this->descriptionForum = $descriptionForum;

        return $this;
    }

    /**
     * @return Collection<int, Thread>
     */
    public function getThreads(): Collection
    {
        return $this->threads;
    }

    public function addThread(Thread $thread): static
    {
        if (!$this->threads->contains($thread)) {
            $this->threads->add($thread);
            $thread->setForum($this);
        }

        return $this;
    }

    public function removeThread(Thread $thread): static
    {
        if ($this->threads->removeElement($thread)) {
            // set the owning side to null (unless already changed)
            if ($thread->getForum() === $this) {
                $thread->setForum(null);
            }
        }

        return $this;
    }
}

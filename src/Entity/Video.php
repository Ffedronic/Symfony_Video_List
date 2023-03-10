<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\Table(name: "videos")]
#[ORM\Index(columns:["title"], name:"title_idx")]

class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(nullable:true)]
    private ?int $duration = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName:"id", onDelete:"CASCADE")]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'video', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'likedVideos')]
    #[ORM\JoinTable(name: 'likes')]
    private Collection $usersThatLike;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'dislikedVideos')]
    #[ORM\JoinTable(name: 'dislikes')]
    private Collection $usersThatDislikeVideos;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->usersThatLike = new ArrayCollection();
        $this->usersThatDislikeVideos = new ArrayCollection();
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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setVideo($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getVideo() === $this) {
                $comment->setVideo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersThatLike(): Collection
    {
        return $this->usersThatLike;
    }

    public function addUsersThatLike(User $usersThatLike): self
    {
        if (!$this->usersThatLike->contains($usersThatLike)) {
            $this->usersThatLike->add($usersThatLike);
        }

        return $this;
    }

    public function removeUsersThatLike(User $usersThatLike): self
    {
        $this->usersThatLike->removeElement($usersThatLike);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsersThatDislikeVideos(): Collection
    {
        return $this->usersThatDislikeVideos;
    }

    public function addUsersThatDislikeVideo(User $usersThatDislikeVideo): self
    {
        if (!$this->usersThatDislikeVideos->contains($usersThatDislikeVideo)) {
            $this->usersThatDislikeVideos->add($usersThatDislikeVideo);
        }

        return $this;
    }

    public function removeUsersThatDislikeVideo(User $usersThatDislikeVideo): self
    {
        $this->usersThatDislikeVideos->removeElement($usersThatDislikeVideo);

        return $this;
    }
}

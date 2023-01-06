<?php

namespace App\Entity;

use App\Repository\SurveyRepository;
use App\Entity\AnswerGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SurveyRepository::class)
 */
class Survey
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
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
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="surveys")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $public;

    /**
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="survey")
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="survey")
     */
    private $answerGroups;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->answerGroups = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setSurvey($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getSurvey() === $this) {
                $question->setSurvey(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AnswerGroup>
     */
    public function getAnswerGroups(): Collection
    {
        return $this->answerGroups;
    }

    public function addAnswerGroup(AnswerGroup $answerGroup): self
    {
        if (!$this->answerGroups->contains($answerGroup)) {
            $this->answerGroups[] = $answerGroup;
            $answerGroup->setSurvey($this);
        }

        return $this;
    }

    public function removeAnswerGroup(AnswerGroup $answerGroup): self
    {
        if ($this->answerGroups->removeElement($answerGroup)) {
            // set the owning side to null (unless already changed)
            if ($answerGroup->getSurvey() === $this) {
                $answerGroup->setSurvey(null);
            }
        }

        return $this;
    }
}

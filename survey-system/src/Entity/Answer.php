<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnswerRepository::class)
 */
class Answer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Option::class, inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pickedOption;

    /**
     * @ORM\ManyToOne(targetEntity=AnswerGroup::class, inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $answerGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPickedOption(): ?Option
    {
        return $this->pickedOption;
    }

    public function setPickedOption(?Option $pickedOption): self
    {
        $this->pickedOption = $pickedOption;

        return $this;
    }

    public function getAnswerGroup(): ?AnswerGroup
    {
        return $this->answerGroup;
    }

    public function setAnswerGroup(?AnswerGroup $answerGroup): self
    {
        $this->answerGroup = $answerGroup;

        return $this;
    }
}

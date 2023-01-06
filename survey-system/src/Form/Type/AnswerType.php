<?php


namespace App\Form\Type;

use App\Entity\Survey;
use App\Entity\Question;
use App\Entity\Option;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('survey', EntityType::class, [
                'class' => Survey::class,
                'required' => true,
            ])
            ->add('pickedOption', EntityType::class, [
                'class' => Option::class,
                'required' => true,
            ])
            ->add('question', EntityType::class, [
                'class' => Question::class,
                'required' => true,
            ]);
    }
}
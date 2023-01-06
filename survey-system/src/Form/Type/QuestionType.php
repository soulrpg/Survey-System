<?php


namespace App\Form\Type;

use App\Entity\Survey;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;


class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextareaType::class, [
                'required' => true
            ])
            ->add('description', TextareaType::class)
            ->add('survey', EntityType::class, [
                'class' => Survey::class,
                'required' => true,
            ]);
    }
}
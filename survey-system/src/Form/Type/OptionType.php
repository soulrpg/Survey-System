<?php


namespace App\Form\Type;

use App\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class OptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextAreaType::class, [
                'required' => true
            ])
            ->add('question', EntityType::class, [
                'class' => Question::class,
                'required' => true,
            ]);
    }
}
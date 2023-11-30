<?php

namespace App\Form;

use App\Entity\Rank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class RankType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label'=>"Nom"])
            ->add('logo', null, ['label'=>"Logo"])
            ->add('required_point', NumberType::class, [
                'label' => 'Point requis',
                'attr' => [
                    'min' => 0,  // Définissez la valeur minimale à 0
                ],
            ])
            ->add('submit', SubmitType::class   , ['label'=>"Enregistrer"])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rank::class,
        ]);
    }
}

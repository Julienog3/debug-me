<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\TagRepository;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label'=>"Titre"
                ])
            ->add('content', null, [
                'label'=>"Content"

                ])
            ->add('tags', EntityType::class, [
                'label'=>"Tag",
                'class'     => Tag::class,
                'expanded'  => true,
                'multiple'  => true,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'query_builder' => function (TagRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                }
                ])
            ->add('submit', SubmitType::class   , ['label'=>"Enregistrer"])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}

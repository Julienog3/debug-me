<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Model\SearchData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Tag;
use App\Repository\TagRepository;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [
                'attr'=>[
                    "placeholder"=> "Recherche via un mot clé..."
                ],
                'required' => false,
            ])
            ->add('tags', EntityType::class, [
                'label' => "Tag",
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'data_class' => SearchData::class,
        ]);
    }
}
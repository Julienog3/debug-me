<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, ['label' => "Adresse email"])
            ->add('firstname', null, ['label' => "Prénom"])
            ->add('lastname', null, ['label' => "Nom"])
            ->add('icon', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false, // Le champ n'est pas mappé sur une propriété de l'entité
                'required' => false, // Le champ n'est pas obligatoire

            ])
            ->add('reset', ResetType::class, ['label' => 'Annuler'])
            ->add('submit', SubmitType::class, ['label' => "Sauvegarder"]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

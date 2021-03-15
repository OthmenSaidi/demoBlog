<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, [
                'required' => false //on annul e la sécurité de base HTML, c'est à dire l'attribut 'required' des balises html imposé par symfony
            ])
            ->add('userName', TextType::class, [
                'required' => false
            ])
            ->add('password', PasswordType::class, [
                'required' => false
            ])
            ->add('confirm_password', PasswordType::class, [
                'required' => false
            ]) // sera pas enregistrer en bdd
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

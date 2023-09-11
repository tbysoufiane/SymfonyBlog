<?php

namespace App\Form;

use App\Entity\Bulletin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class BulletinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                //Champ pour petites entrées de texte
                'label' => 'Titre',
            ])
            ->add('category', ChoiceType::class, [
                //Champ pour imposer un choix
                'label' => 'Catégorie',
                'choices' => [
                    //Valeur affichée => Valeur réelle
                    'Général' => 'général',
                    'Divers' => 'divers',
                    'Urgent' => 'urgent',
                ],
                'expanded' => false, //Menu déroulant
                'multiple' => false, //Un seul choix possible
            ])
            ->add('content', TextareaType::class, [
                //Champ adapté à de plus grandes quantités de texte
                'label' => 'Contenu',
            ])
            ->add('pinned', CheckboxType::class, [
                'label' => 'Epingler?',
                'required' => false, //Pour autoriser à ne pas cocher
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    //Attributs HTML
                    'class' => 'btn btn-success',
                    'style' => 'margin-top:10px',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bulletin::class,
        ]);
    }
}

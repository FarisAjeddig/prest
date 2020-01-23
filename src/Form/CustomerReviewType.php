<?php

namespace App\Form;

use App\Entity\CustomerReview;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile', FileType::class, ['label' => 'Photo du client', 'mapped' => false, 'required' => false])
            ->add('name', TextType::class, ['label' => 'Nom & prénom du client'])
            ->add('title', TextType::class, ['label' => 'Titre et entreprise du client'])
            ->add('date', DateType::class, ['label' => 'Date du témoignage'])
            ->add('content', TextareaType::class, ['label' => 'Témoignage'])
            ->add('Enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerReview::class,
        ]);
    }
}

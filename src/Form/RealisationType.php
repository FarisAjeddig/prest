<?php

namespace App\Form;

use App\Entity\Realisation;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RealisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictureFile', FileType::class, ['label' => 'Photo du client', 'mapped' => false, 'required' => false])
            ->add('customerName', TextType::class, ['label' => 'Nom du client'])
            ->add('typeRealisation', TextType::class, ['label' => 'Type de réalisation'])
            ->add('url', TextType::class, ['label' => "URL vers lequel le travail est montré."])
            ->add('description', CKEditorType::class)
            ->add('Enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Realisation::class,
        ]);
    }
}

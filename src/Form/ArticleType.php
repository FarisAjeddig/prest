<?php

namespace App\Form;

use App\Entity\Article;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Nom de l\'article'])
            ->add('pictureFile', FileType::class, [
                'mapped' =>  false,
                'required' => false,
                'label' => 'Image principale associée à l\'article (JPEG ou PNG)',
                'constraints' => [
                    new File([
                        'maxSize' => '4096k',
//                        'mimeTypes' => [
//                            'image/png',
//                            'video/JPEG',
//                            'video/jpeg2000',
//                            'image/vnd.sealedmedia.softseal.jpg'
//                        ],
//                        'mimeTypesMessage' => 'Une image valide stp ! JPEG ou PNG.',
                    ])
                ]
            ])
            ->add('content', CKEditorType::class, ['label' => 'Contenu de l\'article'])
            ->add('isPublished', CheckboxType::class, ['label' => 'Publier l\'article ? (Ce sera un brouillon sinon)', 'required' => false])
            ->add('Créer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}

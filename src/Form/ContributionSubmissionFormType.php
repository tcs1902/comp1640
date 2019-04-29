<?php

namespace App\Form;

use App\Entity\Term;
use App\Form\Model\ContributionSubmissionFormModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ContributionSubmissionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //TODO disable add new after entry closure day (in controller ? )

        $builder
            ->add('title');
        $builder->add('term', EntityType::class, [
            'class' => Term::class,
        ]);
        //upload document file
        $builder->add('documentFile', FileType::class, [
            'label' => 'Upload Word document',
            'constraints' => [
                new File([
                    'maxSize' => '20M',
                    'mimeTypes' => [
                        'application/msword',
                        'application/vnd.ms-word',
                        'application/msword',
                        'application/msword; charset=binary',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid Word Document',
                ]),
            ],
        ]);

        $builder->add('agreeTerms');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContributionSubmissionFormModel::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Term;
use App\Form\Model\ContributionEditFormModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ContributionEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ContributionEditFormModel $contributionEditModel */
        $contributionEditModel = $options['data'];
        $isEmptyDocumentFile = !$contributionEditModel->documentFileName;
        $isPublished = $contributionEditModel && $contributionEditModel->publishedAt;
        $term = $contributionEditModel->term;
        $expired = $term->getFinalClosesAt() < new \DateTimeImmutable();
        if ($isPublished || $expired) {
            $builder->add('title', null, [
                'disabled' => true,
            ]);
        } else {
            $builder->add('title');
        }
        //upload document file
        $builder->add('documentFile', FileType::class, [
            'required' => $isEmptyDocumentFile,
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

        $builder->add('term', EntityType::class, [
            'disabled' => true,
            'class' => Term::class,
        ]);
        $builder->add('comment', TextareaType::class, [
        ]);
        $builder->add('feedback', TextareaType::class, [
            'disabled' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContributionEditFormModel::class,
        ]);
    }
}

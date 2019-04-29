<?php

namespace App\Form;

use App\Form\Model\ContributionReviewFormModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContributionPublishFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('comment', TextareaType::class, [
            'disabled' => true,
            'label' => 'Student Comment',
        ]);
        $builder->add('feedback', TextareaType::class, [
            'label' => 'Coordinator Feedback',
            'disabled' => true,
        ]);

        $builder->add('publish', CheckboxType::class, [
            'required' => true,
            'attr' => ['readonly' => true],
            'data' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContributionReviewFormModel::class,
        ]);
    }
}

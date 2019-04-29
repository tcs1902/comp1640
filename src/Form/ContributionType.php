<?php

namespace App\Form;

use App\Entity\Contribution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Contribution $contribution */
        $contribution = $options['data'] ?? null;
        $isEdit = $contribution && $contribution->getId();

        $builder->add('title');

        if (!$isEdit) {
        } else {
            $builder->add('comment');

//                ->add('commentedAt', null, [
//                    'widget' => 'single_text',
//                ])
        }

//            ->add('publishedAt', null, [
//                'widget' => 'single_text',
//            ])
//            ->add('author')
//            ->add('coordinator')
//            ->add('term')
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contribution::class,
        ]);
    }
}

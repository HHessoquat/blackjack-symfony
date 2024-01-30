<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BetType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('bet', ChoiceType::class, [
            'multiple' => false,
            'expanded' => true,
            'required' => false,
            'mapped' => false,
            'choices' =>[ 
            '2' => 2,
            '10' => 10,
            '50' => 50,
            'Tapis' => -1,
        ],
    ])
    ->add('otherBet', TextType::class, [
        'required' => false,
        'mapped' => false,
    ])
    ->add('pariez', SubmitType::class);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
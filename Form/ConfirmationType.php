<?php

namespace Maleo\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ConfirmationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $label_yes = (isset($options['attr']['label_yes'])) ? $options['attr']['label_yes'] : '<i class="fa fa-trash"></i> Oui, supprimer';
        $label_no = (isset($options['attr']['label_no'])) ? $options['attr']['label_no'] : '<i class="fa fa-edit"></i> Non, éditer';

        $builder
            ->add('confirmationOui', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-oui'],
                'label' => $label_yes,
            ))
            ->add('confirmationNon', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-non'],
                'label' => $label_no,
            ))
        ;
    }
}

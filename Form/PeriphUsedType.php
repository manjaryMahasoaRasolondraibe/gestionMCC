<?php

namespace Mcri\GestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PeriphUsedType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder 
            ->add('NumeroSerie','text',array('required'=>false))
            ->add('personnel','entity',array(
                                                   'empty_value' => 'Liste du Personnel',
                                                   'class' =>'McriGestBundle:Personnel',
                                                   'query_builder' => function (EntityRepository $er) {
                                                              return $er->createQueryBuilder('p')
                                                                        ->orderBy('p.nomPers', 'ASC'); 
                                                    }, 

                                                   //'property' => 'nomPers',

                        ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mcri\GestBundle\Entity\PeriphUsed'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mcri_gestbundle_periphused';
    }
}

<?php

namespace Mcri\GestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PcUsedType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('NumeroSerie','text')
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
            'data_class' => 'Mcri\GestBundle\Entity\PcUsed'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mcri_gestbundle_pcused';
    }
}

<?php

namespace Mcri\GestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ServiceType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('dir','entity',array(
                                                   'empty_value' => 'Veuillez choisir une direction à laquelle sera rattachée ce service',
                                                   'class' =>'McriGestBundle:Direction',
                                                   'query_builder' => function (EntityRepository $er) {
                                                              return $er->createQueryBuilder('d') 
                                                                        ->orderBy('d.nomDir', 'ASC');
                                                    },

                                                   'property' => 'nomDir',

                        ))
            ->add('nomServ','text')
            ->add('abrevServ','text',array('required'=>false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mcri\GestBundle\Entity\Service'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mcri_gestbundle_service';
    }
}

<?php

namespace Mcri\GestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PeriphStockType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('DateEmmagasinage','date')
            ->add('Marque','entity',array('empty_value' => 'Choisissez une Marque de Périphérique',
                                           'class' =>'McriGestBundle:Marque',
                                           'query_builder' => function (EntityRepository $er) {
                                                      return $er->createQueryBuilder('m')
                                                      ->where('m.type = :type')
                                                      ->setParameter('type', 'peripherique') //seulement charger les marques de Periph
                                                      ->orderBy('m.libelle', 'ASC');
                                            },

                                           'property' => 'libelle',

                ))

            ->add('Nombre','integer')
           
            ->add('type','entity',array(   'empty_value' => 'Choisissez un type  Périphérique',
                                           'class' =>'McriGestBundle:TypePeriphComp',
                                           'query_builder' => function (EntityRepository $er2) {
                                                      return $er2->createQueryBuilder('t')
                                                      ->where('t.categ = :categ')
                                                      ->setParameter('categ', 'peripherique') //seulement charger les marques de Periph
                                                      ->orderBy('t.libelletyp', 'ASC');
                                            },

                                           'property' => 'libelletyp',

                ))
            ->add('description','textarea',array('required' =>false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mcri\GestBundle\Entity\PeriphStock'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mcri_gestbundle_periphstock';
    }
}

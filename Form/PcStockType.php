<?php

namespace Mcri\GestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PcStockType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('DateEmmagasinage','date')
            ->add('Marque','entity',array('empty_value' => 'Choisissez une Marque de PC',
                                           'class' =>'McriGestBundle:Marque',
                                           'query_builder' => function (EntityRepository $er) {
                                                      return $er->createQueryBuilder('m')
                                                      ->where('m.type = :type')
                                                      ->setParameter('type', 'pc') //seulement charger les marques de PC
                                                      ->orderBy('m.libelle', 'ASC');
                                            },

                                           'property' => 'libelle',

                ))

            ->add('Nombre','integer')

            ->add('HDD','choice',array('choices'=>array(
                                                           40=>'40 Go',
                                                           50=>'50 Go',
                                                           80=>'80 Go',
                                                           160=>'160 Go',
                                                           320=>'320 Go',
                                                           500=>'500 Go',
                                                           1000=>'1 To',
                                                           ),
                ))
            ->add('RAM','choice',array('choices'=>array(
                                                           128=>'128 Mo',
                                                           256=>'256 Mo',
                                                           512=>'512 Mo',
                                                           1024=>'1 Go',
                                                           2048=>'2 Go',
                                                           4096=>'4 Go',
                                                           8192=>'8 Go',
                                                           16384=>'16 Go',
                                                           ),
                ))
            ->add('CPU','text')
            ->add('Gpu','text')
            ->add('GpuCapacity','choice',array('choices'=>array(
                                                           128=>'128 Mo',
                                                           256=>'256 Mo',
                                                           512=>'512 Mo',
                                                           1024=>'1 Go',
                                                           2048=>'2 Go',
                                                           ),
                ))
            ->add('CarteMere','text')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mcri\GestBundle\Entity\PcStock'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mcri_gestbundle_pcstock';
    }
}

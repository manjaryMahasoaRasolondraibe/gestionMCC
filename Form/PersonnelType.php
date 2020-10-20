<?php

namespace Mcri\GestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PersonnelType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
	 private $id;
	 public function __construct($d)
	 {
		 $this->id=$d;
	 }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('matricule','integer',array('required'=>false))
            ->add('nomPers','text')
            ->add('prenomPers','text')
            ->add('adressePers','text',array('required'=>false))
			
			->add('serv','entity',array(
                                                   'empty_value' => 'Veuillez choisir un service sur laquelle sera affectée cette personne',
                                                   'class' =>'McriGestBundle:Service',
                                                   'query_builder' => function (EntityRepository $er) { 
                                                              return $er->createQueryBuilder('s')
                                                                        ->where('s.dir = :id')
                                                                        ->setParameter('id', $this->id)
                                                                        ->orderBy('s.nomServ', 'ASC');
                                                    },

                                                   'property' => 'nomServ',

                        ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mcri\GestBundle\Entity\Personnel'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mcri_gestbundle_personnel';
    }
}

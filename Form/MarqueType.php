<?php

namespace Mcri\GestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MarqueType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle','text')
            ->add('type','choice',array('choices'=>array(
                                                           'pc'=>'PC complet',
                                                           'peripherique'=>'Périphérique',
                                                           'composant'=>'Composant',
                                                           ),
                ))
			->add('save', 'submit', array('label' => 'Enregistrer','attr' => array('class' => 'btn btn-primary col-sm-offset-3 col-sm-6'),))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mcri\GestBundle\Entity\Marque'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mcri_gestbundle_marque';
    }
}

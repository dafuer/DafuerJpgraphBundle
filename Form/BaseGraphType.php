<?php

namespace Dafuer\JpgraphBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class BaseGraphType extends AbstractType
{
    
    private $name;
    
    public function __construct($name="0_graphviewer"){
        $this->name=$name;
    }

    public function addLegendHide(FormBuilder $builder, array $options){
        $builder                
                ->add('graph_title', 'text',array('attr' => array('placeholder' => "Default")))
        ;     
        
        return $this;
    }  
    
    public function addGraphTitle(FormBuilder $builder, array $options){
        $builder                
                ->add('graph_title', 'text',array('attr' => array('placeholder' => "Default")))
        ;     
        
        return $this;
    }  
    
    public function addScale(FormBuilder $builder, array $options){
        $builder                
                ->add('graph_yscale_min', 'text',array('attr' => array('placeholder' => "Default")))
                ->add('graph_yscale_max','text',array('attr' => array('placeholder' => "Default")))
        ;     
        
        return $this;
    }    

   /*public function getDefaultOptions(array $options)
    {
        return array(
            'csrf_protection' => false,
         );
    }*/
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }     
    
    public function getName()
    {
        return $this->name."_properties_0";
    }

    
}

<?php

namespace Dafuer\JpgraphBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

abstract class GraphBaseType extends AbstractType
{
    
    private $name;
    
    public function __construct($name="0_graphviewer"){
        $this->name=$name;
    }

    
    public function addDimension(FormBuilder $builder, array $options){
        $builder                
                ->add('graph_yscale_min', 'text',array())
                ->add('graph_yscale_max','text',array())              
        ;        
    }    

    public function getDefaultOptions(array $options)
    {
        return array(
            'csrf_protection' => false,
            );
    }
    
    public function getName()
    {
        return $this->name."_properties_0";
    }

    
}

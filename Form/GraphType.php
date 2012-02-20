<?php

namespace Dafuer\JpgraphBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

abstract class GraphBaseType extends AbstractType
{
    

    
    public function addScale(FormBuilder $builder, array $options){
        $builder                
                ->add('graph_yscale_min', 'integer',array())
                ->add('graph_yscale_max','integer',array())              
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
        return 'graph_properties';
    }
    

    
}

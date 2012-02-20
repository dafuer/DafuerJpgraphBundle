<?php

namespace Dafuer\JpgraphBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ViewerType extends AbstractType
{
    

    public function buildForm(FormBuilder $builder, array $options)
    {                 
        $builder
                ->add('graph_width', 'integer',array())
                ->add('graph_height','integer',array())           
                ->add('graph_margincolor', 'choice',array('choices'=>array('white'=>'White','black'=>'Black')))
          
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

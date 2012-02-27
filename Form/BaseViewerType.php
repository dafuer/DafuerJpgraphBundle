<?php

namespace Dafuer\JpgraphBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

abstract class BaseViewerType extends AbstractType
{
    
  public function addMargin(FormBuilder $builder, array $options){
        $builder
            ->add('graph_img_margin_left', 'integer',array())
            ->add('graph_img_margin_right','integer',array())  
            ->add('graph_img_margin_top', 'integer',array())
            ->add('graph_img_margin_bottom','integer',array())                  
        ;        
        
        return $this;
    }
    
    public function addImageDimension(FormBuilder $builder, array $options){
        $builder
            ->add('graph_width', 'integer',array())
            ->add('graph_height','integer',array())   
        ;        
        
        return $this;
    }
    
    public function addMarginColor(FormBuilder $builder, array $options){
        $builder        
            ->add('graph_margincolor', 'choice',array('choices'=>array('white'=>'White','black'=>'Black')))
        ;       
        
        return $this;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'csrf_protection' => false,
            );
    }
    
    public function getName()
    {
        return 'graphviewer_graph_properties_0';
    }
    

    
}

<?php

namespace Dafuer\JpgraphBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class BaseViewerType extends AbstractType
{
    
  public function addMargin(FormBuilder $builder, array $options){
        $builder
            ->add('graph_img_margin_left', 'integer',array('attr' => array('placeholder' => "Default")))
            ->add('graph_img_margin_right','integer',array('attr' => array('placeholder' => "Default")))
            ->add('graph_img_margin_top', 'integer',array('attr' => array('placeholder' => "Default")))
            ->add('graph_img_margin_bottom','integer',array('attr' => array('placeholder' => "Default")))
        ;        
        
        return $this;
    }
    
    public function addImageDimension(FormBuilder $builder, array $options){
        $builder
            ->add('graph_width', 'integer',array('attr' => array('placeholder' => "Default")))
            ->add('graph_height','integer',array('attr' => array('placeholder' => "Default")))
        ;        
        
        return $this;
    }
    
    public function addMarginColor(FormBuilder $builder, array $options){
        $builder        
            ->add('graph_margincolor', 'choice',array('choices'=>array('white'=>'White','black'=>'Black'),'attr' => array('placeholder' => "Default")))
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
        return 'graphviewer_graph_properties_0';
    }
    

    
}

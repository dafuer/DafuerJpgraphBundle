<?php

namespace Dafuer\JpgraphBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

abstract class SingleElementBaseType extends AbstractType
{
    
    private $name;
    private $subnum;

    public function __construct($name="0_graphviewer",$subnum=0){
        $this->name=$name;
        $this->subnum=$subnum;
    }

    
    public function addColor(FormBuilder $builder, array $options){
        $builder
            ->add('lineplot_color', 'choice',array('choices' => array(''=>'','blue'=>'Blue','red'=>'Red')))
        ;
        
        return $this;
    }    

    
    public function addMultipleYAxis(FormBuilder $builder, array $options){
        $builder
            ->add('graph_yaxis_number', 'choice',array('choices' => array('-1'=>'Left','0'=>'Right','1'=>'Extra Rigth')))     
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
        return $this->name."_".$this->subnum;
    }
    
    
    

    
}
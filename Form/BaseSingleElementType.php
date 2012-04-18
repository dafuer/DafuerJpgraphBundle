<?php

namespace Dafuer\JpgraphBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

abstract class BaseSingleElementType extends AbstractType
{
    
    private $name;
    private $subnum;

    public function __construct($name="0_graphviewer",$subnum=0){
        $this->name=$name;
        $this->subnum=$subnum;
    }

    
    public function addColor(FormBuilder $builder, array $options){
        $builder
            ->add('lineplot_color', 'choice',array('choices' => array(''=>'','blue'=>'Blue','red'=>'Red'),'attr' => array('placeholder' => "Default")))
        ;
        return $this;
    }    

    public function addMarks(FormBuilder $builder, array $options){
        $builder
            ->add('lineplot_max_ptos_to_mark', 'choice',array('choices' => array(''=>'Auto','-1'=>'Always','0'=>'Never')))     
        ;      
        return $this;
    }
    
    public function addMultipleYAxis(FormBuilder $builder, array $options){
        $builder
            ->add('graph_yaxis_number', 'choice',array('choices' => array('0'=>'Left','1'=>'Right','2'=>'Extra Rigth'),'attr' => array('placeholder' => "Default")))     
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

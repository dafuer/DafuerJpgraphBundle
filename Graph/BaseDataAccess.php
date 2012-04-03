<?php


namespace Dafuer\JpgraphBundle\Graph;

use Symfony\Component\Yaml\Yaml;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GraphIndex
 *
 * @author david
 */
class BaseDataAccess{
    
    protected $graphindexpath="";
    
    protected $options;
    
    public function __construct(){
        if (!isset($this->graphindexpath) || $this->graphindexpath=="")
            throw new \Exception("JpgraphBundle says: Can't read graph index yml file: " . $this->graphindexpath );
  
        
        $this->options = Yaml::parse($this->graphindexpath);        
    }
    
    public function emptyResult(){
        return array('xdata' => array(array()), 'ydata' => array(array()));
    }
    
    
    public function getGraphList($roles=array('IS_AUTHENTICATED_ANONYMOUSLY')){
        $result=array();
       
        $rolenames=array();
        foreach($roles as $role){
            if(is_string($role)) array_push ($rolenames,$role);
            else                 array_push ($rolenames, $role->getRole());
        }
        
        array_push($roles, 'IS_AUTHENTICATED_ANONYMOUSLY');        
         //print_r($this->options);
        foreach($this->options as $key=>$graph){
            if(  count(array_intersect($rolenames, $graph['roles']))>0 ){  
                //$result=array_merge_recursive($result, $graph['classify']);
                $x=array($key=>$graph['description']);
                if(isset($graph['classify']) && $graph['classify']!=''){
                    if(isset($result[$graph['classify']])){
                        $result[$graph['classify']]=array_merge($result[$graph['classify']],$x);
                    }else{
                        $result[$graph['classify']]=$x;
                    }
                }else{
                     $result=array_merge($result,$x);
                }
                
            }
        }

        return $result;
    }
    

    public function getData($id,$params){
        if(isset($this->options[$id])){
            $function=$this->options[$id]['function'];
            return $this->$function($params);
        }else{
            return null;
        }
    }
    
    public function getStyle($id){
        return $this->options[$id]['style'];
    }
    
    public function getCustom($id){
        if(isset($this->options[$id]['custom_style'])){    
            return $this->options[$id]['custom_style'];
        }else{
            return array();
        }
    }
    
    public function readGraph($id,$params){
        $data=$this->getData($id, $params);
        
        // For a graph, style is unique. The same for all lines
        // BUG HERE: it's lie
        
        foreach($data['ydata'] as $i=>$values){
            if(!isset($data['style'][$i])){
                $data['style'][$i]=$this->getStyle($id);
            }
        }
        
        // Separate style in custom style or line style
        $styles=$this->getCustom($id);
        $custom=array();
        $custom_line=array();
        foreach($styles as $i=>$value){
            if($i=="graph_plots" ){ // && is_array($value)){    
                foreach($value as $line_name=>$line_style){
                    $custom_line[$line_name]=$line_style;
                }
            }else{
                $custom[$i]=$value;
            }
        }
        
        // For each line, set custom style
        foreach($data['ydata'] as $i=>$values){
            // Prepare line style merging style line with custom style
            if(isset($custom_line[$i])){ // If exist style line...
                $customstyle=array_merge($custom,$custom_line[$i]);
                
            }else{
                $customstyle=$custom;
            }
            
            // Set custom style but it can exist custom style defined in getData function
            if(isset($data['custom'][$i])){ 
                $data['custom'][$i]=array_merge($customstyle,$data['custom'][$i]);
            }else{
                $data['custom'][$i]=$customstyle;
            }
        } 
        
        // Last, set up user parameters
        $data['custom'][$i]=array_merge($data['custom'][$i],$params);

        return $data;    
    }
    

    
    
}

?>

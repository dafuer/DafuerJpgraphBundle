<?php


namespace Dafuer\JpgraphBundle\Graph;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Yaml\Yaml;




class BaseDataAccess{
    
    protected $graphindexpath="";
    
    protected $options;
    
    protected $securityContext;
    
       
    public function __construct(SecurityContextInterface $securityContext){
        if (!isset($this->graphindexpath) || $this->graphindexpath==""){
            throw new \Exception("JpgraphBundle says: Can't read graph index yml file: " . $this->graphindexpath );
        }
        $this->securityContext = $securityContext;
        $this->options = Yaml::parse($this->graphindexpath);        
    }
    
    
    /*
     * This function return if this graph is only legend
     */
    public function readOnlyLegend($params){
        if(isset($params['graph_legend_only']) && ($params['graph_legend_only']===true || strtolower($params['graph_legend_only'])==='true')){
            return true;
        }else{
            return false;
        }
    }
    
    /*
     * This function create a result in funtion of legend_only parameter
     */
    public function createResult($params, $plotNames=array()){
        if($this->readOnlyLegend($params)){
            return $this->createOneResult($plotNames);
        }else{
            return $this->createEmptyResult($plotNames);
        }
    }
    
    public function createEmptyResult($plotNames=array()){
        $result=array('xdata' => array(), 'ydata' => array(), 'custom'=>array() );
        if(count($plotNames)==0){
            $result['xdata'][0]=array();
            $result['ydata'][0]=array();
            $result['custom'][0]=array();
        }else{
            foreach($plotNames as $name){
                $result['xdata'][$name.'']=array();
                $result['ydata'][$name.'']=array();
                $result['custom'][$name.'']=array();                    
            }
        }
        
        return $result;
    }
    
    public function createOneResult($plotNames=array()){
        $result=array('xdata' => array(), 'ydata' => array(), 'custom'=>array() );
        if(count($plotNames)==0){
            $result['xdata'][0]=array(0);
            $result['ydata'][0]=array(0);
            $result['custom'][0]=array(0);
        }else{
            foreach($plotNames as $name){
                $result['xdata'][$name.'']=array(0);
                $result['ydata'][$name.'']=array(0);
                $result['custom'][$name.'']=array(0);                    
            }
        }
        
        return $result;
    }    
    
    public function errorResult(){
        return array('xdata' => array(array()), 'ydata' => array(array()), 'custom' => array(array('lineplot_error'=>true)));
    }    
    
    
    public function getGraphList(){
        $result=array();
       
        foreach($this->options as $key=>$graph){
            if( $this->securityContext->isGranted($graph['role']) ){ //count(array_intersect($rolenames, $graph['roles']))>0 ){  
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
            if( !$this->securityContext->isGranted($this->options[$id]['role']) ){
                throw new AccessDeniedException();  
            }
             
             $function=$this->options[$id]['function'];
             
            return $this->$function($params);
        }else{
            return null;
        }
    }
    
    
    public function getStyle($id){
        if( !$this->securityContext->isGranted($this->options[$id]['role']) ){
            throw new AccessDeniedException();  
        }        
        return $this->options[$id]['style'];
    }
    
    
    public function getCustom($id){
        if( !$this->securityContext->isGranted($this->options[$id]['role']) ){
            throw new AccessDeniedException();  
        }        
        if(isset($this->options[$id]['custom_style'])){    
            return $this->options[$id]['custom_style'];
        }else{
            return array();
        }
    }
    
    
    public function readGraph($id,$params){
        //print_r($this->options[$id]);
        if( !$this->securityContext->isGranted($this->options[$id]['role']) ){
            throw new AccessDeniedException();  
        }         
        $data=$this->getData($id, $params);

        if(is_null($data)) throw new \Exception("Can't read graph. Perhaps there is no parameter dataserie.");

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

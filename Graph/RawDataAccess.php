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
class RawDataAccess {
    
    protected $graphindexpath="";
    
    protected $options;
    
    public function __construct(){
        if (!isset($this->graphindexpath) || $this->graphindexpath=="")
            throw new \Exception("JpgraphBundle says: Can't read graph index yml file: " . $this->graphindexpath );
  
        
        $this->options = Yaml::parse($this->graphindexpath);        
    }
    
    
    public function getGraphList($roles=array('IS_AUTHENTICATED_ANONYMOUSLY')){
        $result=array();
       
        array_push($roles, 'IS_AUTHENTICATED_ANONYMOUSLY');        
         
        foreach($this->options as $graph){
            if(  count(array_intersect($roles, $graph['roles']))>0 ){  
                //$result=array_merge_recursive($result, $graph['classify']);
                $x=array($graph['dataname']=>$graph['description']);
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
    

    
    public function getIdByDataserie($dataname){
        //print_r( $this->options);
        foreach($this->options as $id=>$graph){
            if($graph['dataname']==$dataname){
                return $id;
            }
        }
        
        return null;
    }
    
    public function getGraph($id,$params){
        if(isset($this->options[$id])){
            return $this->$id($params);
        }else{
            return null;
        }
    }
    
    
}

?>

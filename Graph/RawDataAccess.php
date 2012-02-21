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
    
    public function getGraphList($role=array('IS_AUTHENTICATED_ANONYMOUSLY')){
        $result=array();
        
        foreach($this->options as $graph){
            if( in_array($graph['role'], $role) ){
                array_merge_recursive($result, $graph['classify']);
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

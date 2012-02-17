<?php

/*
 * @author David Fuertes
 * github: dafuer
 */

namespace Dafuer\JpgraphBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GOA\RimaBundle\Form\GraphviewerType;


class ViewerController extends Controller {

   
    /**
     * Esta accion permite visualizar cualqueir gráfica con varias opciones de comportamiento en Ajax
     * @param sfWebRequest $request
     */
    public function viewerAction($insertgraphroute) {

        
        $jpgrapher = $this->get('jpgraph');

        $numofgraphs = $this->get('request')->query->get('numofgraphs',1);

        $combined=$this->get('request')->query->get('combined',1);
        if($numofgraphs==1 and is_array($combined)) $combined=$combined[0];
        
        $js=$this->js_set_form_values($this->get('request')->query->all());
        
        return $this->render('DafuerJpgraphBundle:Viewer:viewer.html.twig', array('numofgraphs' => $numofgraphs, 'combined'=>$combined,  'insertgraphroute'=>$insertgraphroute,'js'=>$js), null);
    }

    /**
     * Esta accion permite visualizar comparar gráficas con varias opciones de comportamiento en Ajax
     * @param sfWebRequest $request
     */
    public function insertgraphAction($formname,$formviewpath,$combined) {
        
        $jpgrapher = $this->get('jpgraph');
        $num = $this->get('request')->query->get('num');

        
        $vars = $jpgrapher->parseQueryParameters($this->get('request')->query);
        
        return $this->render('DafuerJpgraphBundle:Viewer:insertgraph.html.twig', array('formname' => $formname, 'formviewpath'=>$formviewpath, 'combined' => $combined, 'insertgraphroute'=>'RimaBundle_graph_insertgraph')); //'urlopts' => $url,
    }
    
    private function js_set_form_values($array){
        //$result='<script type="text/javascript">';
        $result="";
//        print_r($array);
//        throw new \Exception("ea");
        foreach($array as $name=>$elements){
            if(is_array($elements)){
                foreach($elements as $linenum=>$element){
                    if(is_array($element)){
                        foreach($element as $formnum=>$value){
                            $result.="x=document.getElementById('".$formnum."_graphviewer_".$linenum."_".$name."');\n";
                            $result.="if(x!=null) x.value='".$value."';\n";    
                        }
                    }
                }
            }else{
                
            }
        }
        
        //$result.='</script>';       
        return $result;
    }
   
}

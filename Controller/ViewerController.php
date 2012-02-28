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
     */
    public function viewerAction($viewerpath,$insertgraphroute,$viewerformpath=null) {

	$router=$this->get('router');

	$viewerurl=$router->generate($viewerpath,array(),true);
     
   
        $jpgrapher = $this->get('jpgraph');

        $numofgraphs = $this->get('request')->query->get('numofgraphs',1);

        $combined=$this->get('request')->query->get('combined',1);
        if($numofgraphs==1 and is_array($combined)) $combined=$combined[0];
        
        $js=$this->js_set_form_values($this->get('request')->query->all());
        
        return $this->render('DafuerJpgraphBundle:Viewer:viewer.html.twig', array('viewerurl'=>$viewerurl, 'numofgraphs' => $numofgraphs, 'combined'=>$combined,  'insertgraphroute'=>$insertgraphroute,'js'=>$js,'viewerformpath'=>$viewerformpath), null);
    }

    /**
     * Esta accion permite visualizar comparar gráficas con varias opciones de comportamiento en Ajax
     * @param sfWebRequest $request
     */
    public function insertgraphAction($formname,$formviewpath,$combined,$formgraphpath=null) {        
        $jpgrapher = $this->get('jpgraph');
        $num = $this->get('request')->query->get('num');
        //$insertgraphroute=$this->get('request')->query->get('insertgraphroute');
        
     //   $formgraphpath=$this->get('request')->query->get('formgraphpath'); // General options form
//$formgraphpath='RimaBundle:Data:graphviewergraphform';
        $vars = $jpgrapher->parseQueryParameters($this->get('request')->query);        
        return $this->render('DafuerJpgraphBundle:Viewer:insertgraph.html.twig', array('formname' => $formname, 'formviewpath'=>$formviewpath, 'combined' => $combined, 'formgraphpath'=>$formgraphpath)); //'insertgraphroute'=>$insertgraphroute
    }
    
    private function js_set_form_values($array){
        $result="";

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
        
      
        return $result;
    }
   
}

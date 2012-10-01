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
     * Esta accion permite visualizar cualquier gráfica con varias opciones de comportamiento en Ajax
     */
    public function viewerAction($viewerpath,$insertgraphroute,$viewerformpath=null, $graphroute) {

	$router=$this->get('router');

	$viewerurl=$router->generate($viewerpath,array(),true);

        $jpgrapher = $this->get('jpgraph');

        $numofgraphs = $this->get('request')->query->get('numofgraphs',1);

        $combined=$this->get('request')->query->get('combined',1);
        if($numofgraphs==1 and is_array($combined)) $combined=$combined[0];
        
        $js=$this->js_set_form_values($this->get('request')->query->all());
        
        $graph_viewer_default=  json_encode($this->container->getParameter('dafuer_jpgraph.graph_viewer_default'));
        
        return $this->render('DafuerJpgraphBundle:Viewer:viewer.html.twig', array('viewerurl'=>$viewerurl, 'numofgraphs' => $numofgraphs, 'combined'=>$combined,  'insertgraphroute'=>$insertgraphroute,'js'=>$js,'viewerformpath'=>$viewerformpath, 'graphroute'=>$graphroute, 'graph_viewer_default'=>$graph_viewer_default), null);
    }

    /**
     * Esta accion permite visualizar comparar gráficas con varias opciones de comportamiento en Ajax
     * @param sfWebRequest $request
     */
    public function insertgraphAction($formname,$formviewpath,$combined,$formgraphpath=null,$graphroute) {        
        $jpgrapher = $this->get('jpgraph');
        $num = $this->get('request')->query->get('num');
        
        $vars = $jpgrapher->parseQueryParameters($this->get('request')->query);        
        return $this->render('DafuerJpgraphBundle:Viewer:insertgraph.html.twig', array('formname' => $formname, 'formviewpath'=>$formviewpath, 'combined' => $combined, 'formgraphpath'=>$formgraphpath, 'graphroute'=>$graphroute)); //'insertgraphroute'=>$insertgraphroute
    }
    
    private function js_set_form_values($array){
        $result="";

        foreach($array as $name=>$elements){
            if(is_array($elements)){
                foreach($elements as $linenum=>$element){
                    if(is_array($element)){
                        foreach($element as $formnum=>$value){
                            $result.="x=document.getElementById('g".$formnum."_graphviewer_".$linenum."_".$name."');\n";
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

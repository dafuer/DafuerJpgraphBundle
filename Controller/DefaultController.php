<?php

/*
 * @author David Fuertes
 * github: dafuer
 */

namespace Dafuer\JpgraphBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;



class DefaultController extends Controller {

    public function indexAction() {
        return $this->render('DafuerJpgraphBundle:Default:index.html.twig', array());
    }

    public function test1Action() {
        return $this->render('DafuerJpgraphBundle:Default:test1.html.twig', array());
    }

    public function graphtest1Action() {
        $datay1 = array(20, 15, 23, 15);
        $datay2 = array(12, 9, 42, 8);
        $datay3 = array(5, 17, 32, 24);

        // Obtain jpgraphBundle service
        $jpgrapher = $this->get('jpgraph');


        $graph = $jpgrapher->createGraph("graph_example2");

        // Create the first line
        $lineplot1 = $jpgrapher->createLinePlot('lineplot_example', $graph, $datay1);

        // Create the second line
        $lineplot2 = $jpgrapher->createLinePlot('lineplot_example', $graph, $datay2, null, array("lineplot.color" => "#B22222"));

        // Create the third line
        $lineplot3 = $jpgrapher->createLinePlot('lineplot_example', $graph, $datay3, null, array("lineplot.color" => "#FF1493"));

        $graph->Stroke();
    }

    public function drawAction() {
    // Falta alguna forma de customizar el color, obtener estilos por la url, etc..
     if (!is_null($this->get('request')->query->get('dataname'))) {
            $dataname = $this->get('request')->query->get('dataname');

            $vars['dataname'] = $dataname;
            $c = 0;
            foreach ($dataname as $name) {

                $xdata[$c] = $this->get('request')->query->get('x' . $name);
                $ydata[$c] = $this->get('request')->query->get('y' . $name);
                $c++;
            }
        } else {

            $xdata = $this->get('request')->query->get('xdata');
            $ydata = $this->get('request')->query->get('ydata');
        }
        

        $title = $this->get('request')->query->get('title');

        
        // Obtain jpgraphBundle service
        $jpgrapher = $this->get('jpgraph');


        return $jpgrapher->graphDaySeries('graph_timeserie','lineplot_timeserie', $ydata, $xdata, array('graph.title'=>$title))->Stroke();
    }
    
    public function styleshowAction($style_name) {
        // Obtain jpgraphBundle service
        $jpgrapher = $this->get('jpgraph');
        
        $options= $jpgrapher->readStyle($style_name);
        
        return $this->render('DafuerJpgraphBundle:Default:styleshow.html.twig', array('style_name'=>$style_name, 'options'=>$options));
    }    
    
    
    /**
     * Esta accion permite visualizar cualqueir gráfica con varias opciones de comportamiento en Ajax
     * @param sfWebRequest $request
     */    
    public function viewerAction(){
        //$this->graphviewerform=new graphviewerForm();
        //sfProjectConfiguration::getActive()->loadHelpers(array("myURL"));
        
        $vars;
        $numofgraphs=$this->get('request')->query->get('numofgraphs');

        if($numofgraphs==null){
            $vars=parseDaySerieParameters($this->get('request'));
            $url = get_url($vars,false,false,true);
            $combine=$this->get('request')->query->get('combine');
            if($combine!=null) $this->url.='&combine='.$combine;
        }else{
            $vars=array();
            $url=array();

            for($i=0;$i<$numofgraphs;$i++){
                $vars[$i]=parseDaySerieParameters($this->get('request'),$i);
                $url[$i]=get_url($vars[$i],false,false,true);
                $combine=$this->get('request')->query->get($i.'combine');
                if($combine!=null) $url[$i].='&'.'combine='.$combine;
            }
        }
        
        return $this->render('DafuerJpgraphBundle:Default:viewer.html.twig', array('numofgraphs'=>$numofgraphs,'url'=>$url));
    }

    
    
    
    /**
     * Esta accion permite visualizar comparar gráficas con varias opciones de comportamiento en Ajax
     * @param sfWebRequest $request
     */
    public function insertgraphAction(){
        $num=$this->get('request')->query->get('num');
        //sfProjectConfiguration::getActive()->loadHelpers(array("myURL"));
        $vars=parseDaySerieParameters($this->get('request'));
        $combine=$this->get('request')->query->get('combine');

        if($combine==null){
            $vars=parseDaySerieParameters($this->get('request'));
            $url = get_url($vars,false,false,true);
        }else{
            $vars=array();
            $url=array();

            for($i=0;$i<$combine;$i++){
                $vars[$i]=parseDaySerieParameters($this->get('request'),null,$i);
                $url[$i]=get_url($vars[$i],false,false,true);
            }
        }

        return $this->render('DafuerJpgraphBundle:Default:insertgraph.html.twig', array('num'=>$num));
        //$cred=$this->getUser()->maxPermission();
        //$this->graphviewercombineform=new graphviewercombineForm(null,array('name'=>'graphviewercombineform'.$this->num, 'user'=>$cred));
    }    
    
}



 function parseDaySerieParameters($request, $prefix=null, $suffix=null) {
        //$name = $this->get('request')->query->get('name');
        $result = array();

        

        if (is_null($request->query->get($prefix . 'combine')) || !is_null($suffix)) {

            $result['ph'] = $request->query->get($prefix . 'ph' . $suffix);

            $result['first'] = $request->query->get($prefix . 'first' . $suffix);
            $result['last'] = $request->query->get($prefix . 'last' . $suffix);
            $result['color'] = $request->query->get($prefix . 'color' . $suffix);
            $result['dataserie'] = $request->query->get($prefix . 'dataserie' . $suffix);
            $result['channel'] = $request->query->get($prefix . 'channel' . $suffix);
            $result['width'] = $request->query->get($prefix . 'width' . $suffix);
            $result['height'] = $request->query->get($prefix . 'height' . $suffix);
            $result['minyscale'] = $request->query->get($prefix . 'minyscale' . $suffix);
            $result['maxyscale'] = $request->query->get($prefix . 'maxyscale' . $suffix);
            $result['minxscale'] = $request->query->get($prefix . 'minxscale' . $suffix);
            $result['maxxscale'] = $request->query->get($prefix . 'maxxscale' . $suffix);
            $result['minypeaks'] = $request->query->get($prefix . 'minypeaks' . $suffix);
            $result['maxypeaks'] = $request->query->get($prefix . 'maxypeaks' . $suffix);
            $result['marks'] = $request->query->get($prefix . 'marks' . $suffix);


            if (!is_null($result['minxscale']))
                $result['minxscale'] = strtotime($result['minxscale']);
            if (!is_null($result['maxxscale']))
                $result['maxxscale'] = strtotime($result['maxxscale']);
        }else {
            $combined = $request->query->get($prefix . 'combine');

            for ($i = 0; $i < $combined; $i++) {
                //$result[$i]=array();

                $result[$i]['ph'] = $request->query->get($prefix . 'ph' . $i);
                $result[$i]['first'] = $request->query->get($prefix . 'first' . $i);
                $result[$i]['last'] = $request->query->get($prefix . 'last' . $i);
                $result[$i]['color'] = $request->query->get($prefix . 'color' . $i);
                $result[$i]['dataserie'] = $request->query->get($prefix . 'dataserie' . $i);
                $result[$i]['channel'] = $request->query->get($prefix . 'channel' . $i);
                $result[$i]['width'] = $request->query->get($prefix . 'width' . $i);
                $result[$i]['height'] = $request->query->get($prefix . 'height' . $i);
                $result[$i]['minyscale'] = $request->query->get($prefix . 'minyscale' . $i);
                $result[$i]['maxyscale'] = $request->query->get($prefix . 'maxyscale' . $i);
                $result[$i]['minxscale'] = $request->query->get($prefix . 'minxscale' . $i);
                $result[$i]['maxxscale'] = $request->query->get($prefix . 'maxxscale' . $i);
                $result[$i]['minypeaks'] = $request->query->get($prefix . 'minypeaks' . $i);
                $result[$i]['maxypeaks'] = $request->query->get($prefix . 'maxypeaks' . $i);
                $result[$i]['marks'] = $request->query->get($prefix . 'marks' . $i);


                if (!is_null($result[$i]['minxscale']))
                    $result[$i]['minxscale'] = strtotime($result[$i]['minxscale']);
                if (!is_null($result[$i]['maxxscale']))
                    $result[$i]['maxxscale'] = strtotime($result[$i]['maxxscale']);
            }
        }

        return $result;
    }
    
function get_url($options,$is_initial=false,$as_array=true,$reverse=false){
    $url = "";

    $c=0;
    foreach($options as $name=>$value){
        if($value!="" && !is_array($value)) $url.=$name."=".$value."&";
        if(is_array($value)) {
            foreach ($value as $indice=>$real_value){
                if($real_value!=null && $real_value!=""){
                    if($as_array){
                        if(!$reverse) $url.=$name."[".$indice."]=".$real_value."&";
                        else $url.=$indice."[".$name."]=".$real_value."&";
                    }else{
                        if(!$reverse) $url.=$name.$indice."=".$real_value."&";
                        else $url.=$indice.$name."=".$real_value."&";
                    }
                }
            }
        }
    }

    // Quito el ultimo "&"
    if(strlen($url)>0) $url=substr($url, 0, -1);

    if($is_initial) $url="?".$url;
    elseif ($url!="") $url="&".$url;

    return $url;
}
<?php

/*
 * @author David Fuertes
 * github: dafuer
 */

namespace Dafuer\JpgraphBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use GOA\RimaBundle\Form\GraphviewerType;

class GraphController extends Controller {

    /**
     * This action generate a graph from request parameters.
     * HERE MORE DOCUMENTATION ABOUT IT!
     */
    public function queryAction($request, $dataaccess) {
        // This allow print img tag if it's necessary
        if ($request->query->get('format') == 'html') {
            return $this->forward('DafuerJpgraphBundle:Graph:imggraph', array('request' => $request));
        }
        
        // If there are no parameters, draw error image
        if(strlen($request->getQueryString())==0 || (count($request->query->keys())==1 && $request->query->has('format'))){
            if ($request->query->get('format') == 'html' || strlen($request->getQueryString())==0) {
                $this->forward('DafuerJpgraphBundle:Graph:imgerror', array('style' => 'error_graph', 'custom' => array()));
            }else{
                $this->forward('DafuerJpgraphBundle:Graph:imgerrordraw', array('style' => 'error_graph', 'custom' => array()));
            }
            $response=new \Symfony\Component\HttpFoundation\Response('');
            $response->headers->set('Content-Type', 'image/png');        
            return $response;              
        }
        
        // First, extract manual data from url
        $dataname = $request->query->get('dataname',array(0=>'data'));
        $c=0;
        foreach ($dataname as $index=>$name) {
            if($c==0) $minindex=$index;
            $xdata[$index] = $request->query->get('x' . $name, array());
            $request->query->remove('x' . $name);
            $ydata[$index] = $request->query->get('y' . $name, array());
            $request->query->remove('y' . $name);
            $styledata[$index] = $request->query->get('style' . $name, 'lineplot_timeserie');
            $request->query->remove('style' . $name);
            $customdata[$index] = $request->query->get('custom' . $name, array());
            $request->query->remove('custom' . $name);      
            $c++;
        }
        $index=$minindex;
        $this->get('request')->query->remove('dataname');
       
                
        $jpgrapher = $this->get('jpgraph');

        $params = $jpgrapher->parseQueryParameters($request->query);

        
        // Combined parameter is mandatory except for single graph
        $combined = $request->query->get('combined', (int)(count($ydata[$index])==0));

        $datas = array();
        if($combined>0){ // Extract database data and create graph.
            
            for ($i = 0; $i < $combined; $i++) {
                if (!isset($params[$i]['dataserie']) ){
                    throw $this->createNotFoundException('The product does not exist');   
                }                 
                $datas[] = $dataaccess->readGraph($params[$i]['dataserie'], $params[$i]);
            }

            reset($datas[0]['ydata']);
            $keylines = array_keys($datas[0]['ydata']);
            $firstkey = $keylines[0];

            $firststyle = $datas[0]['style'][$firstkey];
            $firstcustom = $datas[0]['custom'][$firstkey];

            $base_style = array_merge($firstcustom, $params[0]);
            
            // Create graph
            //$graph = $jpgrapher->createGraph($firststyle, $base_style);


        }else{ // If there are not database data only create graph
            
            // Create graph with idividual style
            $firststyle = $styledata[$index];
            $firstcustom = $customdata[$index];
            $base_style = array_merge($firstcustom, $customdata[$index]);      
            //$graph = $jpgrapher->createGraph($styledata[$index],$customdata[$index]);
        }

        
        // Add plots stored in database
        foreach ($datas as $i => $data) {
            foreach ($data['ydata'] as $j => $line) {
                //if (count($data['ydata'][$j]) > 0) {
                    $style_line = array_merge($data['custom'][$j], $params[$i]);
                    if(isset($data['xdata'][$j])){
                        $xdata=$data['xdata'][$j];
                    }else{
                        $xdata=null;
                    }
                    $lineplot = $jpgrapher->createGraphPlot($data['style'][$j], $data['ydata'][$j], $xdata, $style_line);
                //}
            }
        }        
        
        // Add url plots  
        foreach ($dataname as $i => $linename) {
            if (count($ydata[$i]) > 0) {
                $lineplot = $jpgrapher->createGraphPlot($styledata[$i], $ydata[$i], $xdata[$i], $customdata[$i]);
            }
        }
        

        //Stroke the graph        
        $x = $jpgrapher->strokeGraph($firststyle, $base_style);
           
        if ($x == false) {
            $this->forward('DafuerJpgraphBundle:Graph:imgerrordraw', array('style' => 'error_graph', 'custom' => array()));
        }
        
        $response=new \Symfony\Component\HttpFoundation\Response('');
        $response->headers->set('Content-Type', 'image/png');        
        return $response;  
    }

    
    /**
     * This action shows real properties applied a graph
     */
    public function stylequeryshowAction($request, $dataaccess) {
        $jpgrapher = $this->get('jpgraph');

        $params = $jpgrapher->parseQueryParameters($request->query);

        $combined = $request->query->get('combined', 1);

        $datas = array();
        for ($i = 0; $i < $combined; $i++) {
            $datas[] = $dataaccess->readGraph($params[$i]['dataserie'], $params[$i]);
        }

        reset($datas[0]['ydata']);
        $keylines = array_keys($datas[0]['ydata']);
        $firstkey = $keylines[0];

        $firststyle = $datas[0]['style'][$firstkey];
        $firstcustom = $datas[0]['custom'][$firstkey];

        $base_style = array_merge($firstcustom, $params[0]);

        $styles=array();
        //Base style
        $styles[]= $jpgrapher->readStyle('lineplot_timeserie',$base_style);
        
        // Obtain final styles
        foreach ($datas as $i => $data) {
            foreach ($data['ydata'] as $j => $line) {
                    $style_line = array_merge($data['custom'][$j], $params[$i]);
            }
        }   

        foreach($styles as $name=>$style){
            $styles[$name]=$this->stylestostring($style);
        }
        
        return $this->render('DafuerJpgraphBundle:Graph:stylequeryshow.html.twig', array('styles'=>$styles));
    }
    
    
    /**
     * This function convert each attribute of style in a string
     * @param type $styles Array of style properties.
     */
    private function stylestostring($styles){
        $result=array();
        foreach($styles as $name=>$value){
            if (is_bool($value)){
                $result[$name]=$value?'true':'false';
            } else if (is_array($value)){
                 $result[$name]=print_r($value,true);
            } else if (is_callable($value)){
                 $result[$name]="closure";                 
            }else{
                $result[$name]=$value;
            }
        }
        return $result;
    }
   
    public function styleshowAction($style_name) {

        // Obtain jpgraphBundle service
        $jpgrapher = $this->get('jpgraph');

        $options = $jpgrapher->readStyle($style_name);

        return $this->render('DafuerJpgraphBundle:Graph:styleshow.html.twig', array('style_name' => $style_name, 'options' => $options));
    }    
    


    public function imggraphAction($request) {
        $query = $request->query;
        $query->remove('format');
        $options = $query->all();
        $options['format'] = 'nohtml';
        $route = $request->attributes->get('_route');
        //echo $route;
        //return null;
        return $this->render('DafuerJpgraphBundle:Graph:imggraph.html.twig', array('route' => $route, 'query' => $options));
    }

    public function imgerrordrawAction($style) {
        $jpgrapher = $this->get('jpgraph');

        $custom = $jpgrapher->parseQueryParameters($this->get('request')->query);


        $x = $jpgrapher->createErrorImg($style, $custom);
        
        $response=new \Symfony\Component\HttpFoundation\Response('');
        $response->headers->set('Content-Type', 'image/png');
        return $response;
    }

    public function imgerrorAction($style) {
        $jpgrapher = $this->get('jpgraph');

        $custom = $jpgrapher->parseQueryParameters($this->get('request')->query);

        return $this->render('DafuerJpgraphBundle:Graph:imgerror.html.twig', array('style' => $style, 'custom' => $custom));
    }



}

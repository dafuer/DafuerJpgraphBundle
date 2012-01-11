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
    
    

}

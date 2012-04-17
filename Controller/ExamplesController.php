<?php

/*
 * @author David Fuertes
 * github: dafuer
 */

namespace Dafuer\JpgraphBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GOA\RimaBundle\Form\GraphviewerType;

class ExamplesController extends Controller {

    /**
     * This example shows how to make a action that it render a 
     * template with a image
     */
    public function test1Action() {
        return $this->render('DafuerJpgraphBundle:Examples:test1.html.twig', array());
    }

    /**
     * This example shows how to make a graph in action and return the image 
     */
    public function drawtest1Action() {
        $datay1 = array(20, 15, 23, 15);
        $datay2 = array(12, 9, 42, 8);
        $datay3 = array(5, 17, 32, 24);

        // Obtain jpgraphBundle service
        $jpgrapher = $this->get('jpgraph');

        // Create graph from a determinate style
        $graph = $jpgrapher->createGraph("graph_example2");

        // Create the first line
        $lineplot1 = $jpgrapher->createGraphPlot('lineplot_example', $graph, $datay1);

        // Create the second line
        $lineplot2 = $jpgrapher->createGraphPlot('lineplot_example', $graph, $datay2, null, array("lineplot.color" => "#B22222"));

        // Create the third line
        $lineplot3 = $jpgrapher->createGraphPlot('lineplot_example', $graph, $datay3, null, array("lineplot.color" => "#FF1493"));

        $graph->Stroke();
    }

    /**
     * This is not a real example. This is normal function of library. 
     * You can compare the interfaces: which is more pretty?
     */
    public function drawtest2Action() {
        // Example of a stock chart
        include (__DIR__ . "/../../../../jpgraph/src/jpgraph.php");
        include (__DIR__ . "/../../../../jpgraph/src/jpgraph_pie.php");


$data = array();
 $data[]=2;
 $data[]=2;
 $data[]=2;
$graph = new \PieGraph(300,200);
$graph->SetShadow();
 
$graph->title->Set("A simple Pie plot");
 
$p1 = new \PiePlot($data);
$graph->Add($p1);
$graph->Stroke();

    }

}

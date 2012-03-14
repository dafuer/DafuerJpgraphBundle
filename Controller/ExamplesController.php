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
        include (__DIR__ . "/../../../../jpgraph/src/jpgraph_line.php");


 
// Some data
$datay=array(7,19,11,4,20);
 
// Create the graph and setup the basic parameters 
$graph = new \Graph(300,200,'auto');    
//$graph->img->SetMargin(40,30,40,50);
 $graph->SetScale("textint");
$graph->SetFrame(true,'blue',1); 
$graph->SetColor('lightblue');
$graph->SetMarginColor('lightblue');

// Setup X-axis labels
//$a = $gDateLocale->GetShortMonth();
//$graph->xaxis->SetTickLabels($a);
//$graph->xaxis->SetFont(FF_FONT1);
//$graph->xaxis->SetColor('darkblue','black');
 
// Setup "hidden" y-axis by given it the same color
// as the background (this could also be done by setting the weight
// to zero)
//$graph->yaxis->SetColor('lightblue','darkblue');
//$graph->ygrid->SetColor('white');
 
// Setup graph title ands fonts
//$graph->title->Set('Using grace = 50%');
//$graph->title->SetFont(FF_FONT2,FS_BOLD);
//$graph->xaxis->SetTitle('Year 2002','center');
//$graph->xaxis->SetTitleMargin(10);
//$graph->xaxis->title->SetFont(FF_FONT2,FS_BOLD);
 
// Add some grace to the top so that the scale doesn't
// end exactly at the max value. 
//$graph->yaxis->scale->SetGrace(50);
 
                              
// Create a bar pot
$bplot = new \LinePlot($datay);
//$bplot->SetFillColor('darkblue');
$bplot->SetColor('darkblue');
//$bplot->SetWidth(0.5);
//$bplot->SetShadow('darkgray');
 
// Setup the values that are displayed on top of each bar
// Must use TTF fonts if we want text at an arbitrary angle
//$bplot->value->Show();
$bplot->value->SetFont(FF_ARIAL,FS_NORMAL,8);
//$bplot->value->SetFormat('$%d');
//$bplot->value->SetColor('darkred');
//$bplot->value->SetAngle(45);
$graph->Add($bplot);
 
// Finally stroke the graph
$graph->Stroke();

    }

}

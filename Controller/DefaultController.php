<?php

/*
 * @author David Fuertes
 * github: dafuer
 */

namespace Dafuer\JpgraphBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GOA\RimaBundle\Form\GraphviewerType;

class DefaultController extends Controller {

    public function indexAction() {
        return $this->render('DafuerJpgraphBundle:Default:index.html.twig', array());
    }

    public function test1Action() {
        return $this->render('DafuerJpgraphBundle:Default:test1.html.twig', array());
    }

    public function graphtest2Action(){

require_once (__DIR__."/../../../../jpgraph/src/jpgraph.php");
require_once (__DIR__."/../../../../jpgraph/src/jpgraph_line.php");

$ydata = array(11,3,8,12,5,1,9,13,5,7);
$y2data = array(354,200,265,99,111,91,198,225,293,251);

// Create the graph and specify the scale for both Y-axis
$graph = new \Graph(300,240);    
$graph->SetScale("textlin");
$graph->SetY2Scale("lin");
$graph->SetShadow();

// Adjust the margin
$graph->img->SetMargin(40,40,20,50);

// Create the two linear plot
$lineplot=new \LinePlot($ydata);
$lineplot2=new \LinePlot($y2data);

// Add the plot to the graph
$graph->Add($lineplot);
$graph->AddY2($lineplot2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

// Adjust the axis color
$graph->y2axis->SetColor("orange");
$graph->yaxis->SetColor("blue");

$graph->title->Set("Example 6.1");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Set the colors for the plots 
$lineplot->SetColor("blue");
$lineplot->SetWeight(2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

// Set the legends for the plots
$lineplot->SetLegend("Plot 1");
$lineplot2->SetLegend("Plot 2");

// Adjust the legend position
$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.4,0.95,"center","bottom");


// Display the graph
$graph->Stroke();
        
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

    public function drawtimeseriesAction() {
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



        return $jpgrapher->graphDaySeries('graph_timeserie', 'lineplot_timeserie', $ydata, $xdata, array('graph.title' => $title))->Stroke();
    }

    public function viewdrawtimeseriesAction() {
        $jpgrapher = $this->get('jpgraph');

        $vars = $jpgrapher->parseDaySerieParameters($this->get('request'));
        $url = $jpgrapher->get_url($vars, true, false, true);
        $url = '?dataname[0]=data1020&dataname[1]=data675&dataname[2]=data440&dataname[3]=data870&xdata1020[0]=1013448134&xdata1020[1]=1088597159&xdata1020[2]=1096551023&xdata1020[3]=1096551023&xdata1020[4]=1160049522&xdata1020[5]=1160049522&xdata1020[6]=1172583493&xdata1020[7]=1217251947&xdata1020[8]=1231762692&xdata1020[9]=1278848780&xdata1020[10]=1280055749&xdata1020[11]=1321618286&ydata1020[0]=0.28492&ydata1020[1]=0.29298&ydata1020[2]=0.29339&ydata1020[3]=0.27386&ydata1020[4]=0.28694&ydata1020[5]=0.28732&ydata1020[6]=0.28712&ydata1020[7]=0.29508&ydata1020[8]=0.30319&ydata1020[9]=0.2906&ydata1020[10]=0.28763&ydata1020[11]=0.27192&xdata675[0]=1013448134&xdata675[1]=1088597159&xdata675[2]=1096551023&xdata675[3]=1096551023&xdata675[4]=1160049522&xdata675[5]=1160049522&xdata675[6]=1172583493&xdata675[7]=1217251947&xdata675[8]=1231762692&xdata675[9]=1278848780&xdata675[10]=1280055749&xdata675[11]=1321618286&ydata675[0]=0.28605&ydata675[1]=0.29058&ydata675[2]=0.29058&ydata675[3]=0.26795&ydata675[4]=0.27689&ydata675[5]=0.28453&ydata675[6]=0.28212&ydata675[7]=0.29396&ydata675[8]=0.29757&ydata675[9]=0.29261&ydata675[10]=0.29243&ydata675[11]=0.27591&xdata440[0]=1013448134&xdata440[1]=1088597159&xdata440[2]=1096551023&xdata440[3]=1096551023&xdata440[4]=1160049522&xdata440[5]=1160049522&xdata440[6]=1172583493&xdata440[7]=1217251947&xdata440[8]=1231762692&xdata440[9]=1278848780&xdata440[10]=1280055749&xdata440[11]=1321618286&ydata440[0]=0.28716&ydata440[1]=0.28515&ydata440[2]=0.28501&ydata440[3]=0.25318&ydata440[4]=0.27453&ydata440[5]=0.30748&ydata440[6]=0.28689&ydata440[7]=0.28861&ydata440[8]=0.29986&ydata440[9]=0.298&ydata440[10]=0.29751&ydata440[11]=0.28523&xdata870[0]=1013448134&xdata870[1]=1088597159&xdata870[2]=1096551023&xdata870[3]=1096551023&xdata870[4]=1160049522&xdata870[5]=1160049522&xdata870[6]=1172583493&xdata870[7]=1217251947&xdata870[8]=1231762692&xdata870[9]=1278848780&xdata870[10]=1280055749&xdata870[11]=1321618286&ydata870[0]=0.30516&ydata870[1]=0.30767&ydata870[2]=0.30734&ydata870[3]=0.2838&ydata870[4]=0.29094&ydata870[5]=0.29596&ydata870[6]=0.29776&ydata870[7]=0.30347&ydata870[8]=0.31583&ydata870[9]=0.30509&ydata870[10]=0.30539&ydata870[11]=0.29024&wlns[0]=1020&wlns[1]=675&wlns[2]=440&wlns[3]=870';
        return $this->render('DafuerJpgraphBundle:Default:viewdrawtimeseries.html.twig', array('url' => $url));
    }

    public function imggraphAction($request) {
        $query = $request->query;
        $query->remove('format');
        $options = $query->all();
        $options['format']='nohtml';
        $route = $request->attributes->get('_route');
        //echo $route;
        //return null;
        return $this->render('DafuerJpgraphBundle:Default:imggraph.html.twig', array('route' => $route, 'query' => $options));
    }

    public function imgerrordrawAction($style) {
        $jpgrapher=$this->get('jpgraph');
        
        $custom = $jpgrapher->parseQueryParameters($this->get('request')->query);

        
        $x= $jpgrapher->createErrorImg($style,$custom);
    }
    
    public function imgerrorAction($style) {
        $jpgrapher=$this->get('jpgraph');
        
        $custom = $jpgrapher->parseQueryParameters($this->get('request')->query);
        
        return $this->render('DafuerJpgraphBundle:Default:imgerror.html.twig', array('style' => $style, 'custom' => $custom));
    }    

    public function styleshowAction($style_name) {

        // Obtain jpgraphBundle service
        $jpgrapher = $this->get('jpgraph');

        $options = $jpgrapher->readStyle($style_name);

        return $this->render('DafuerJpgraphBundle:Default:styleshow.html.twig', array('style_name' => $style_name, 'options' => $options));
    }
    
    
    public function queryAction($request,$dataaccess){
        // This allow print img tag if it's necessary
        if ($request->query->get('format') == 'html') {
            return $this->forward('DafuerJpgraphBundle:Default:imggraph', array('request' => $request));
        }

        $jpgrapher = $this->get('jpgraph');

        $params = $jpgrapher->parseQueryParameters($request->query);
        
        if ($request->query->get('combined') == null) {
            $combined = 1;
            $custom = $params;
        } else {
            $combined = $request->query->get('combined');
            $custom = $params[0]; //[$combined];
        }
     
        $custom_fromdata=$dataaccess->getCustom($params[0]['dataserie']);

        // Find style
        if( isset($custom['style']) ){
            $style=$custom['style'];
        }else{
            $style=$dataaccess->getStyle($params[0]['dataserie']);
        }

        $custom=array_merge($custom_fromdata,$custom);
        

        $graph = $jpgrapher->createGraph($style, $custom);
        
        
        if ($request->query->get('combined') == null) {
            /* PARA MI QUE ESTO NO SE USA NUNCA Y SE PUEDE BORRAR FORZANDO A COMBINED SIEMPRE UN VALOR MINIMO DE 1 */
            
           /* $graphfunct = $dataaccess->dataFunction($params['dataserie']);
            $dataserie = $dataaccess->$graphfunct($params);
            if (count($dataserie['ydata']) > 0) {
                $lineplot1 = $jpgrapher->createLinePlot('lineplot_timeserie', $graph, $dataserie['ydata'], $dataserie['xdata'], $params);
            }*/
        } else {
            for ($i = 0; $i < $combined; $i++) {           
                $data=$dataaccess->getData($params[$i]['dataserie'],$params[$i]);

                
                if( isset($data['ydata'][0]) ){
                    if ( !is_array($data['ydata'][0]) ){
                        if( count($data['ydata']) > 0 ){ 
                            //print_r($params[$i]);
                            //throwException($e);
 /* 
  * ESTO AQUI ESTA MAL
  * PERO ES LA FORMA DE QUE CAMBIE EL ESTILO
  */                           
$custom_fromdata=$dataaccess->getCustom($params[$i]['dataserie']);
// Find style
if( isset($custom['style']) ){
    $style=$custom['style'];
}else{
    $style=$dataaccess->getStyle($params[$i]['dataserie']);
}
$custom=array_merge($custom,$custom_fromdata); 
        


                            $style_line=array_merge($custom,$params[$i]);
                            $lineplot = $jpgrapher->createLinePlot($style, $graph, $data['ydata'], $data['xdata'], $style_line);
                        }
                    }else{
                        if( count($data['ydata'][0]) > 0 ){
                            foreach( $data['ydata'] as $j=>$line ){
                                $style_line=array_merge($custom,$params[$i]);
                                $lineplot = $jpgrapher->createLinePlot($style, $graph, $data['ydata'][$j], $data['xdata'][$j], $params[$i]);
                            }                        
                        }
                    }
                }
            }
        
            
        }

       
            $x = $jpgrapher->strokeGraph($style,$custom,$graph);
            if ($x == false) {
                if($request->query->get('format')=='nohtml'){
                    $this->forward('DafuerJpgraphBundle:Default:imgerrordraw', array('style' => 'error_graph','custom'=>array()));

                    return $this->render('RimaBundle:Data:graph.html.twig', array());   
                }else{
                    return $this->forward('DafuerJpgraphBundle:Default:imgerror', array('style' => 'error_graph','custom'=>array()));
                }   
            }
                
    }

}

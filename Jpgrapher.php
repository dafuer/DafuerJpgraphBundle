<?php

namespace Dafuer\JpgraphBundle;

use Symfony\Component\Yaml\Yaml;

/**
 * Description of Jpgrapher
 *
 * @author David Fuertes
 * github: dafuer
 */
class Jpgrapher {

    private $config_file;
    //private $viewer_file;
    private $options;
    //private $viewer;

    public function getCallFunctions() {
        $callbacks = array();

        $callbacks['TimeCallbackDay'] = function ($aVal) {
                    return Date('Y-m-d', $aVal); //return Date ('Y-m-d',$aVal);
                };

        $callbacks['TimeCallbackTime'] = function ($aVal) {
                    return Date('H:i:s', $aVal); //return Date ('Y-m-d',$aVal);
                };

        return $callbacks;
    }

    public function __construct($config_file){ //, $viewer_file) {
        $this->config_file = $config_file;
        //$this->viewer_file = $viewer_file;
        $this->options = Yaml::parse($this->config_file);
        //$this->viewer = Yaml::parse($this->viewer_file);
    }

    /*public function getViewerOptions() {
        return $this->viewer;
    }*/

    public function readStyle($style_tag, $values=array()) {
        if (!isset($this->options[$style_tag]))
            throw new \Exception('DafuerJpgraphBundle says: ' . $style_tag . ' style does not exists.');
        $has_styles = 0;
        if (isset($this->options[$style_tag]['style'])) {
            $related_styles = $this->options[$style_tag]['style'];
            foreach ($related_styles as $style) {
                $values = $this->readStyle($style, $values);
                $has_styles = 1;
            }
        }


        $asociated_values = $this->options[$style_tag];
        if ($has_styles)
            unset($asociated_values['style']);

        foreach ($asociated_values as $asociated_name => $asociated_value) {
            $values[$asociated_name] = $asociated_value;
        }


        return $values;
    }

    private function getOptions($style_name, $custom) {
        //$values = $this->readStyle($style_default);
        $values = $this->readStyle($style_name); //, $values);
        $has_styles = 0;

        if (isset($custom['style'])) {
            $related_styles = $custom['style'];
            foreach ($related_styles as $style) {
                $values = $this->readStyle($style, $values);
                $has_styles = 1;
            }
        }


        if ($has_styles)
            unset($custom['style']);

        foreach ($custom as $asociated_name => $asociated_value) {
            $values[$asociated_name] = $asociated_value;
        }
        return $values;
    }

    public function createGraph($style_name, $custom=array()) {
        
        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {
            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            // Check mandatory vars
            if (!isset($values['graph_width']))
                throw new \Exception('DafuerJpgraphBundle says: Variable graph_width must be defined.');
            if (!isset($values['graph_height']))
                throw new \Exception('DafuerJpgraphBundle says: Variable graph_height must be defined.');
            if (!isset($values['graph']))
                throw new \Exception('DafuerJpgraphBundle says: Variable graph must be defined.');
            
            
            if($values['graph']=="graph"){
                require_once (__DIR__ . '/../../../jpgraph/src/jpgraph.php');
                $graph = new \Graph($values['graph_width'], $values['graph_height']);
            }           

            
            if (isset($values['graph_img_margin_left']) && isset($values['graph_img_margin_right']) && isset($values['graph_img_margin_top']) && isset($values['graph_img_margin_bottom']) ){
                
                $graph->SetMargin($values['graph_img_margin_left'], 
                        $values['graph_img_margin_right'], 
                        $values['graph_img_margin_top'], 
                        $values['graph_img_margin_bottom']);
                
            }
            
            if (isset($values['graph_scale']))
                $graph->SetScale($values['graph_scale']);
            if (isset($values['graph_title']))
                $graph->title->Set($values['graph_title']);
            if (isset($values['graph_box']))
                $graph->SetBox($values['graph_box']);
            if (isset($values['graph_xgrid_show']))
                $graph->xgrid->Show($values['graph_xgrid_show']);
            if (isset($values['graph_xgrid_color']))
                $graph->xgrid->SetColor($values['graph_xgrid_color']);
            if (isset($values['graph_xgrid_linestyle']))
                $graph->xgrid->SetLineStyle($values["graph_xgrid_linestyle"]);
            //if (isset($values['graph_img_antialiasing']))
            //    $graph->img->SetAntiAliasing($values['graph_img_antialiasing']);
            if (isset($values['graph_legend_frameweight']))
                $graph->legend->SetFrameWeight($values['graph_legend_frameweight']);
            if (isset($values['graph_frame'][0]) && isset($values['graph_frame'][1]))
                $graph->SetFrame($values['graph_frame'][0], $values['graph_frame'][1], $values['graph_frame'][2]);
            if (isset($values['graph_clipping']))
                $graph->SetClipping($values['graph_clipping']);



            
            
            return $graph;
        }
    }
   
    

    function graphDaySeries($graph_style, $line_style, $ydata, $xdata, $custom_graph=array(), $custom_lineplot=array(), $graph=null) { //, $title, $title_x=null, $title_y=null, $error=null, $width=null, $height=null, $max_ptos_to_mark=null, $color=null, $min_yscale=null, $max_yscale=null, $min_xscale=null, $max_xscale=null, $graph=null) {
        if (count($xdata) > 0) {

            if (is_null($graph)) {  // Si no me pasan una grafica a la que añadir la linea creo una nueva
                $graph = $this->createGraph($graph_style, $custom_graph);
            } else {// Esto sifnifica que la grafica viene para que añada la linea
                // Compruebo que la grafica tenga algo pintado (que no sea un cuadro blanco)
                if ($graph->img->width < 15)
                    $graph = $this->createGraph($graph_style, $custom_graph);
            }


            $indice = 0;
            // Obtengo un indice valido
            foreach ($xdata as $i => $value) {
                $indice = $i;
                break;
            }

            if (is_array($xdata[$indice])) {
                foreach ($xdata as $i => $value) {
                    // Si hay establecidos maximos y minimos en las escalas adapto los datos
                    $lineplot = $this->createLinePlot($line_style, $graph, $ydata[$i], $xdata[$i], $custom_lineplot);
                }
            } else {
                $lineplot = $this->createLinePlot($line_style, $graph, $ydata[$i], $xdata[$i], $custom_lineplot);
            }

            return $graph;
        } else {

            if (is_null($graph)) {  // Si no me pasan una grafica a la que añadir la linea creo una nueva para devolver el error
                $graph = $this->createErrorGraph($graph_style, $custom_graph);
            }

            return $graph;
        }
    }

    
    public function createLinePlot($style_name, $graph, $ydata, $xdata=null, $custom=array()) {
        
        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {

            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            //if($graph==null) $graph=$this->createGraph ($style_name, $custom);
            
            // Check mandatory vars
            if (!isset($values['lineplot']))
                throw new \Exception('DafuerDafuerJpgraphBundle says: Variable lineplot must be defined.');          
            
            
            if($values['lineplot']=="lineplot"){
                require_once (__DIR__ . '/../../../jpgraph/src/jpgraph_line.php');
                if (is_null($xdata)) {
                    $lineplot = new \LinePlot($ydata);
                } else {
                    $lineplot = new \LinePlot($ydata, $xdata);
                }
            }

            if($values['lineplot']=="errorlineplot"){
                require_once (__DIR__ . '/../../../jpgraph/src/jpgraph_line.php');
                require_once (__DIR__ . '/../../../jpgraph/src/jpgraph_error.php');
                if (is_null($xdata)) {
                    $lineplot = new \ErrorLinePlot($ydata);
                } else {
                    $lineplot = new \ErrorLinePlot($ydata, $xdata);
                }
            }


            // El eje
           if(isset($values['graph_yaxis_number'])){
                if($values['graph_yaxis_number']==0){
                    if (isset($values['graph_yaxis_title']))
                        $graph->yaxis->title->Set($values["graph_yaxis_title"]);
                    if (isset($values['graph_yaxis_titlemargin']))
                        $graph->yaxis->SetTitleMargin($values["graph_yaxis_titlemargin"]);
                    if (isset($values['graph_yaxis_hideline']))
                        $graph->yaxis->HideLine($values['graph_yaxis_hideline']);

                    if (isset($values['graph_ygrid_fill']))
                        $graph->ygrid->SetFill($values['graph_ygrid_fill'][0], $values['graph_ygrid_fill'][1], $values['graph_ygrid_fill'][2]);
                    
                    $graph->Add($lineplot);
                }else{
                    // First, I find maxium index allowed to prevent a exception
                    $index=0;
                    for($i=0;$i<$values['graph_yaxis_number']-1;$i++){
                        if(!isset($graph->ynaxis)) break;
                    }
                    if($i>0) $index=$i-1;
                    
                    $graph->SetYScale($index,'lin');
                    $graph->AddY($index,$lineplot);
                    //$graph->ynaxis[0]->SetColor('teal');                    
                }
            }         

            if($values['lineplot']=="lineplot"){
                $line=$lineplot;
            }else{
                $line=$lineplot->line;
            }
            
            if (isset($values['lineplot_color'])){
                    $line->SetColor($values['lineplot_color']);
            }

            if ($values['lineplot']=="errorlineplot" && isset($values['errorlineplot_color'])){
                $lineplot->SetColor($values['errorlineplot_color']);
            }
            
            
            if (isset($values['lineplot_legend'])){ 
                $lineplot->SetLegend($values['lineplot_legend']);
            }
            
            if (isset($values['lineplot_weight'])){
                $lineplot->SetWeight($values['lineplot_weight']);
            }
            
            if (isset($values['graph_xaxis_ticklabels'])){
                $graph->xaxis->SetTickLabels($values["graph_xaxis_ticklabels"]);
            }
            
            if (isset($values['lineplot_xaxis_title'])){
                $graph->xaxis->title->Set($values["graph_xaxis_title"]);
            }
            
            if (isset($values['lineplot_xaxis_pos'])){
                $graph->xaxis->SetPos($values["graph_xaxis_pos"]);
            }




            if (isset($values['lineplot_max_ptos_to_mark'])) {
                if ($values['lineplot_max_ptos_to_mark'] == -1 || count($xdata) < $values['lineplot_max_ptos_to_mark']) {
    
                    $line->mark->SetType(constant($values['lineplot_mark_type']));
                    $line->mark->SetWidth($values['lineplot_mark_width']);
                    if ($values['lineplot_mark_color'] != '%lineplot_color%') {
                        $line->mark->SetColor($values['lineplot_mark_color']);
                    } else {
                        $line->mark->SetColor($values['lineplot_color']);
                    }
                }
            }else{
                
            }



            /* if (isset($values['lineplot_reescale'])) {
              $graph->doAutoScaleYnAxis();
              } */

            if (isset($values['graph_yscale_autoticks']))
                $graph->yscale->SetAutoTicks($values['graph_yscale_autoticks']);


            
            return $lineplot;
        }
    }
    
    

    function strokeGraph($style_name,$custom, $graph) {
        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerDafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {

            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);
            
            $graph->doAutoScaleYAxis();
            
            $ymin=$graph->yscale->GetMinVal();
            $ymax=$graph->yscale->GetMaxVal();
            $xmin=$graph->xscale->GetMinVal();
            $xmax=$graph->xscale->GetMaxVal();

            if (isset($values['graph_yscale_min'])){
               $ymin=$values['graph_yscale_min'];
            }
            if (isset($values['graph_yscale_max'])){
                $ymax=$values['graph_yscale_max'];
            }
            if (isset($values['graph_xscale_min'])){
                $xmin=$values['graph_xscale_min'];
            }
            if (isset($values['graph_xscale_max'])){
                $xmax=$values['graph_xscale_max'];
            }
            

            
            $graph->SetScale($values['graph_scale'], $ymin, $ymax,$xmin,$xmax);
          if (count($graph->plots)) {
            
            if (isset($values['graph_xaxis_labelformatcallback'])) { // If it has labelformatcallback
                $callbacks = $this->getCallFunctions();
                if ($values['graph_xaxis_labelformatcallback'] == 'AutoTimeCallback') { // If it

                    $xminmax=$graph->GetXMinMax();
                    if ($xminmax[0]!=null) {
                        $tpo = $xminmax[1] - $xminmax[0];

                        $callbacks = $this->getCallFunctions();
                        if ($tpo > 172800) {
                            $graph->xaxis->SetLabelFormatCallback($callbacks['TimeCallbackDay']);
                        } else {
                            $graph->xaxis->SetLabelFormatCallback($callbacks['TimeCallbackTime']);
                        }
                    }
                } else {
                    $graph->xaxis->SetLabelFormatCallback($callbacks[$values['graph_xaxis_labelformatcallback']]);
                }
            }
            
            if (isset($values['graph_xaxis_labelangle'])){
                $graph->xaxis->SetLabelAngle($values["graph_xaxis_labelangle"]);
            }
            
            $graph->legend->SetAbsPos( 0.0,20.0,"right" ,"top"); 
            $graph->legend->SetLayout(LEGEND_VERT);
            $graph->legend->SetShadow('darkgray@0.5');
            $graph->legend->SetFillColor('white@0');           
          
            $graph->graph_theme=null;
            
                return $graph->Stroke();
            } else {
                return false;
            }
        }
    }

    function createErrorImg($style_name, $custom) {
        require_once (__DIR__ . '/../../../jpgraph/src/jpgraph.php');
        require_once (__DIR__ . '/../../../jpgraph/src/jpgraph_canvas.php');
        require_once (__DIR__ . '/../../../jpgraph/src/jpgraph_canvtools.php');

        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {
            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            // Now create graph and define style values before obtained.
            if (!isset($values['canvasgraph_width']))
                throw new \Exception('DafuerJpgraphBundle says: Variable canvasgraph_width must be defined.');
            if (!isset($values['canvasgraph_height']))
                throw new \Exception('DafuerJpgraphBundle says: Variable canvasgraph_height must be defined.');

            $graph = new \CanvasGraph($values['canvasgraph_width'], $values['canvasgraph_height'], 'auto');
            $graph->InitFrame();
            if (isset($values['canvasgraph_color'])){
                $graph->img->SetColor($values['canvasgraph_color']);
                $graph->img->FilledRectangle(0, 0, $values['canvasgraph_width'], $values['canvasgraph_height']);
            }

            $graph->Stroke();
        }
    }

    //function parseDaySerieParameters($request, $prefix=null, $suffix=null) {
    function parseQueryParameters($query) {

        $result = array();


        if (is_null($query->get('combined'))) {
            //$result = $query->all();
            $parameters=$query->all();
            foreach($parameters as $name=>$value){
                $result[0][$name]=$value;
            }
        } else {
            $parameters = $query->all();
            $single_parameters = array();
            foreach ($parameters as $i => $parameter) {
                if (!is_array($parameter)) {
                    $single_parameters[$i] = $parameter;
                } else {
                    foreach ($parameter as $j => $lineproperty) {
                        $result[$j][$i] = $lineproperty;
                    }
                }
            }
            array_push($result, $single_parameters);
        }

        return $result;
    }

    /*function get_url($options, $is_initial=false, $as_array=true, $reverse=false) {
        $url = "";

        $c = 0;
        foreach ($options as $name => $value) {
            if ($value != "" && !is_array($value))
                $url.=$name . "=" . $value . "&";
            if (is_array($value)) {
                foreach ($value as $indice => $real_value) {
                    if ($real_value != null && $real_value != "") {
                        if ($as_array) {
                            if (!$reverse)
                                $url.=$name . "[" . $indice . "]=" . $real_value . "&";
                            else
                                $url.=$indice . "[" . $name . "]=" . $real_value . "&";
                        }else {
                            if (!$reverse)
                                $url.=$name . $indice . "=" . $real_value . "&";
                            else
                                $url.=$indice . $name . "=" . $real_value . "&";
                        }
                    }
                }
            }
        }

        // Quito el ultimo "&"
        if (strlen($url) > 0)
            $url = substr($url, 0, -1);

        if ($is_initial)
            $url = "?" . $url;
        elseif ($url != "")
            $url = "&" . $url;

        return $url;
    }*/

}

/*
  function addDaySerie($graph, $xdata, $ydata, $error, $min_yscale=null, $max_yscale=null, $min_xscale=null, $max_xscale=null) {
  // Falta los titulos en los ejes.

  $mayorx = max($xdata);
  $minimox = min($xdata);


  // Create the linear plot
  if (is_null($error)) {
  $mayory = max($ydata);
  $minimoy = min($ydata);

  $lineplot = $this->createLinePlot('lineplot_default', $graph, $ydata, $xdata);
  } else {
  $v1 = max($ydata);
  $v2 = max($error);
  $mayory = ($v1 > $v2) ? $v1 : $v2;

  $v1 = min($ydata);
  $v2 = min($error);
  $minimoy = ($v1 < $v2) ? $v1 : $v2;

  $lineplot = $this->createErrorLinePlot('errorlineplot_default', $graph, $error, $xdata);

  }
  //echo $minimoy.",". $mayory.",". $minimox.",". $mayorx.",";
  //       if (!is_null($graph->yscale)) {
  //            if ($minimoy > $graph->yscale->GetMinVal())
  //                $minimoy = $graph->yscale->GetMinVal();
  //            if ($minimox > $graph->xscale->GetMinVal())
  //                $minimox = $graph->xscale->GetMinVal();
  //            if ($mayory < $graph->yscale->GetMaxVal())
  //                $mayory = $graph->yscale->GetMaxVal();
  //            if ($mayorx < $graph->xscale->GetMaxVal())
  //                $mayorx = $graph->xscale->GetMaxVal();
  //        }


  if (!is_null($min_yscale))
  $minimoy = $min_yscale;
  if (!is_null($max_yscale))
  $mayory = $max_yscale;
  if (!is_null($min_xscale))
  $minimox = $min_xscale;
  if (!is_null($max_xscale))
  $mayorx = $max_xscale;

  //throw new \Exception('DafuerJpgraphBundle says: ');
  $graph->SetScale('intlin', $minimoy, $mayory, $minimox, $mayorx);   //<<<<<<<------------
  $graph->yscale->SetAutoTicks();

  if (count($xdata) > 0) {
  $tpo = $mayorx - $minimox;

  $callbacks = $this->getCallFunctions();
  if ($tpo > 172800) {
  $graph->xaxis->SetLabelFormatCallback($callbacks['TimeCallbackDay']);
  } else {
  $graph->xaxis->SetLabelFormatCallback($callbacks['TimeCallbackTime']);
  }
  }

  return $graph;
  } */
?>

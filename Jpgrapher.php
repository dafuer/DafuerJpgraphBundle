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
    private $path;
    
    private $zebra_x_min=null;
    private $zebra_x_max=null;
    private $zebra_y_min=null;
    private $zebra_y_max=null;

    //private $viewer;

    public function getCallFunctions() {
        $callbacks = array();

        $callbacks['TimeCallbackDay'] = function ($aVal) {
                    return Date('Y-m-d', $aVal); //return Date ('Y-m-d',$aVal);
                };

        $callbacks['TimeCallbackTime'] = function ($aVal) {
                    return Date('H:i', $aVal); //return Date ('Y-m-d',$aVal);
                };

        $callbacks['CallbackMonthNumber'] = function ($aVal) {
                    $m = array("", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

                    if (isset($m[$aVal]))
                        return $m[$aVal];
                    return "";
                };

        return $callbacks;
    }

    public function __construct($config_file, $kernel_path) { //, $viewer_file) {
        $this->config_file = $config_file;
        //$this->viewer_file = $viewer_file;
        $this->options = Yaml::parse($this->config_file);
        $this->path=  $kernel_path."/../vendor/asial/jpgraph/src/";
        //$this->viewer = Yaml::parse($this->viewer_file);
    }

    public function readStyle($style_tag, $values = array()) {
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
      
        // I set the custom value
        foreach ($custom as $asociated_name => $asociated_value) {
            if ($asociated_value != "%%%") { // If value is equal %%% then remove this parameter
                $values[$asociated_name] = $asociated_value;
            } else {
                unset($values[$asociated_name]);
            }
        }
        
        // Last, I replace value of the linked attributes
        // performance penalty
        $finished=false;
        while($finished==false){
            $finished=true;
            foreach ($values as $asociated_name => $asociated_value) {
                if (is_string($asociated_value) && substr($asociated_value,0,1)=="%" ){// Perhaps is a especial attribute
                    if(substr($asociated_value,-1)=="%"){ // Linked attribute
                        $property=substr($asociated_value,1,strlen($asociated_value)-2);
                        if(isset($values[$property])){
                            $values[$asociated_name]=$values[$property];
                        }else{
                            unset($values[$asociated_name]);
                        }
                    }else{
                        if ($asociated_value == "%%%") { // If value is equal %%% then remove this parameter
                            unset($values[$asociated_name]);
                        }                        
                    }
                    $finished=false;
                }
            }
        }
        
        
        return $values;
    }

    public function createGraph($style_name, $custom = array()) {

        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {
            require_once ($this->path.'jpgraph.php');
            
            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            // Check mandatory vars
            if (!isset($values['graph_width']))
                throw new \Exception('DafuerJpgraphBundle says: Variable graph_width must be defined.');
            if (!isset($values['graph_height']))
                throw new \Exception('DafuerJpgraphBundle says: Variable graph_height must be defined.');
            if (!isset($values['graph']))
                throw new \Exception('DafuerJpgraphBundle says: Variable graph must be defined.');


            if ($values['graph'] == "graph") {
                $graph = new \Graph($values['graph_width'], $values['graph_height']);
            }

            if ($values['graph'] == "piegraph") {
                require_once ($this->path.'jpgraph_pie.php');
                $graph = new \PieGraph($values['graph_width'], $values['graph_height']);
            }      
            
            if ($values['graph'] == "ganttgraph") {
                require_once ($this->path.'jpgraph_gantt.php');
                $graph = new \GanttGraph($values['graph_width'], $values['graph_height']);
            }                  
            

            if (isset($values['graph_img_margin_left']) && isset($values['graph_img_margin_right']) && isset($values['graph_img_margin_top']) && isset($values['graph_img_margin_bottom'])) {
                $graph->SetMargin($values['graph_img_margin_left'], $values['graph_img_margin_right'], $values['graph_img_margin_top'], $values['graph_img_margin_bottom']);
            }


            if (isset($values['graph_scale'])) {
                $yt = substr($values['graph_scale'], -3, 3);
                $xt = substr($values['graph_scale'], 0, 3);
                if ($yt == 'dat' || $xt == 'dat') {
                    require_once ($this->path.'jpgraph_date.php');
                }
                if ($yt == 'log' || $xt == 'log') {
                    require_once ($this->path.'jpgraph_log.php');
                }                
                $graph->SetScale($values['graph_scale']);
            }


            if (isset($values['graph_title'])) {
                $graph->title->Set($values['graph_title']);
            }
            
            // Set up title font
            if(isset($values['graph_title_font_family'])){
                if(isset($values['graph_title_font_style'])){
                    if(isset($values['graph_title_font_size'])){
                        $graph->title->SetFont(constant($values['graph_title_font_family']),constant($values['graph_title_font_style']),$values['graph_title_font_size']);
                    }else{
                        $graph->title->SetFont(constant($values['graph_title_font_family']),constant($values['graph_title_font_style']));
                    }
                }else{
                    $graph->title->SetFont(constant($values['graph_title_font_family']));
                }
            }
            
            if (isset($values['graph_box'])) {
                $graph->SetBox($values['graph_box']);
            }
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
    
    
    

    public function createGraphPlot($style_name, $graph, $ydata, $xdata = null, $custom = array()) {

        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {

            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            //if($graph==null) $graph=$this->createGraph ($style_name, $custom);
            // Check mandatory vars
            if (!isset($values['lineplot']))
                throw new \Exception('DafuerDafuerJpgraphBundle says: Variable lineplot must be defined.');


            if ($values['lineplot'] == "lineplot") {
                 require_once ($this->path.'jpgraph_line.php');
                if (is_null($xdata)) {
                    $lineplot = new \LinePlot($ydata);
                } else {
                    $lineplot = new \LinePlot($ydata, $xdata);
                }
            }
            
            if ($values['lineplot'] == "zebraplot") {
                 require_once ($this->path.'jpgraph_line.php');
                 require_once ($this->path.'jpgraph_scatter.php');
                 require_once ($this->path.'jpgraph_plotline.php');
                 $lineplot=array();
                 foreach ($ydata as $zebrapoint){
                     $pline = new \PlotLine(constant($values['lineplot_direction']),$zebrapoint,$values['lineplot_color'],$values['lineplot_weight']);
                     $lineplot[]=$pline;
                 }
                 
                 $min=min($ydata);
                 $max=max($ydata);  
                 
                 if($values['lineplot_direction']=='VERTICAL'){
                     if($this->zebra_x_min==null || $min<$this->zebra_x_min){
                         $this->zebra_x_min=$min;
                     }
                     if($this->zebra_x_max==null || $max>$this->zebra_x_max){
                         $this->zebra_x_max=$max;
                     }                     
                 }else{
                     if($this->zebra_y_min==null || $min<$this->zebra_y_min){
                         $this->zebra_y_min=$min;
                     }
                     if($this->zebra_y_max==null || $max>$this->zebra_y_max){
                         $this->zebra_y_max=$max;
                     }                        
                 }
                 // Adding a extra line transparent to optimize scales
                 /*$min=min($ydata);
                 $max=max($ydata);
                 $transparent_x_data=array();
                 $transparent_x_data=array();
                 if($values['lineplot_direction']=='VERTICAL'){
                     $transparent_x_data=array($min,$max);
                     $transparent_y_data=array(5.6,5.6);
                 }else{
                     $transparent_x_data=array(5.6,5.6);
                     $transparent_y_data=array($min,$max);                
                 }
  
                 $scatter_for_scale = new \ScatterPlot($transparent_y_data, $transparent_x_data);
                 $scatter_for_scale->mark->SetWidth(0);
                 $scatter_for_scale->setColor('black');
                 $graph->add($scatter_for_scale);*/
            }            

            if ($values['lineplot'] == "errorlineplot") {
                require_once ($this->path.'jpgraph_line.php');
                require_once ($this->path.'jpgraph_error.php');
                if (is_null($xdata) || count($xdata)==0) {
                    $lineplot = new \ErrorLinePlot($ydata);
                } else {
                    $lineplot = new \ErrorLinePlot($ydata, $xdata);
                }
            }

            if ($values['lineplot'] == "boxplot") {
                require_once ($this->path.'jpgraph_stock.php');
                if (!isset($xdata)|| count($xdata)==0) {
                    $lineplot = new \BoxPlot($ydata);
                } else {
                    $lineplot = new \BoxPlot($ydata, $xdata);
                }
                $lineplot->SetMedianColor("red", "yellow");
            }     

            if ($values['lineplot'] == "barplot") {
                require_once ($this->path.'jpgraph_bar.php');

                $lineplot = new \BarPlot($ydata);
            }
           
            if ($values['lineplot'] == "scatterplot") {
                require_once ($this->path.'jpgraph_scatter.php');

                $lineplot = new \ScatterPlot($ydata, $xdata);
            }

            
            if ($values['lineplot'] == "pieplot") {
                require_once ($this->path.'jpgraph_pie.php');
                $lineplot = new \PiePlot($ydata);                
                $graph->Add($lineplot);
                if(isset($values['lineplot_slicecolors'])){
                    $lineplot->SetSliceColors($values['lineplot_slicecolors']);
                }
                
            }
            
            if ($values['lineplot'] == "pieplot3d") {
                require_once ($this->path.'jpgraph_pie.php');
                require_once ($this->path.'jpgraph_pie3d.php');
                $lineplot = new \PiePlot3D($ydata);                
                $graph->Add($lineplot);
                if(isset($values['lineplot_slicecolors'])){
                    $lineplot->SetSliceColors($values['lineplot_slicecolors']);
                }
                
            }            
            
            if ($values['lineplot'] == "ganttplot") {
                require_once ($this->path.'jpgraph_gantt.php');

                $label="";
                if(isset($values['lineplot_label'])){
                    $label=$values['lineplot_label'];
                }
                //GantBar (posicion,formato,inicio,fin,etiqueta,grosor)
                //$lineplot =  new \GanttBar($data[1][0],$data[1][1],$data[1][2],$data[1][3],"[50%]");
                $lineplot =  new \GanttBar($ydata,$values['lineplot_information'],$xdata[0],$xdata[1],$label);
                
                                  
                $graph->Add($lineplot);
            }            
            
            // El eje
            
            if (isset($values['graph_yaxis_number'])) {
                if ($values['graph_yaxis_number'] == 0) {
                    if (isset($values['graph_yaxis_title']))
                        $graph->yaxis->title->Set($values["graph_yaxis_title"]);
                    if (isset($values['graph_yaxis_titlemargin']))
                        $graph->yaxis->SetTitleMargin($values["graph_yaxis_titlemargin"]);
                    if (isset($values['graph_yaxis_hideline']))
                        $graph->yaxis->HideLine($values['graph_yaxis_hideline']);

                    if(!isset($lineplot)) throw new \Exception('DafuerDafuerJpgraphBundle says: Lineplot dont exist');
                    $graph->Add($lineplot);
                } else {
                    // First, I find maxium index allowed to prevent a exception
                    $index = 0;
                    for ($i = 0; $i < $values['graph_yaxis_number'] - 1; $i++) {
                        if (!isset($graph->ynaxis))
                            break;
                    }
                    if ($i > 0)
                        $index = $i - 1;

                    $graph->SetYScale($index, 'lin');
                    $graph->AddY($index, $lineplot);
                    //$graph->ynaxis[0]->SetColor('teal');                    
                }
            }

            if ($values['lineplot'] == "lineplot" || $values['lineplot'] == "scatterplot") {
                $line = $lineplot;
            } else if ($values['lineplot'] == "errorlineplot") {
                $line = $lineplot->line;
            } else {
                $line = null;
            }

            

            if ($line != null && isset($values['lineplot_color'])) {
                $line->SetColor($values['lineplot_color']);
            }

            if (isset($values['lineplot_value_color'])) {
                $lineplot->value->SetColor($values['lineplot_value_color']);
            }
            
            if ($values['lineplot'] == "errorlineplot" && isset($values['errorlineplot_color'])) {
                $lineplot->SetColor($values['errorlineplot_color']);
            }

            if (isset($values['lineplot_legend'])) {
                if(is_array($values['lineplot_legend'])){
                    $lineplot->SetLegends($values['lineplot_legend']);
                }else{
                    if($values['lineplot'] == "zebraplot"){
                        $lineplot[0]->SetLegend($values['lineplot_legend']);
                    }else{
                        $lineplot->SetLegend($values['lineplot_legend']);
                    }
                }
            }
            
            if($values['lineplot']!='zebraplot'){ // If zebraplot weight is asigned in construct
                if($values['lineplot']!='ganttplot'){
                    if (isset($values['lineplot_weight'])) {
                        $lineplot->SetWeight($values['lineplot_weight']);
                    }
                }else{
                    if (isset($values['lineplot_weight'])) {
                        //echo $values['lineplot_weight'];
                        $lineplot->SetHeight($values['lineplot_weight']);
                    }                
                }
            }

            if(isset($values['lineplot_fillcolor'])){
                    $line->SetFillColor($values['lineplot_fillcolor']);
            }
            
            if(isset($values['lineplot_area'])){
                foreach ($values['lineplot_area'] as $area){
                    $lineplot->AddArea($area['from'],$area['to'],LP_AREA_FILLED,$area['color']);
                }
            }


            if ($line != null && isset($values['lineplot_marks'])) {
                if ($values['lineplot_marks'] == -1 || count($xdata) < $values['lineplot_marks']) {

                    $line->mark->SetType(constant($values['lineplot_mark_type']));
                    if(isset($values['lineplot_mark_width'])){
                        $line->mark->SetWidth($values['lineplot_mark_width']);
                    }
                   
                    if(isset($values['lineplot_mark_color'])){
                        $line->mark->SetColor($values['lineplot_mark_color']);
                    }
                    
                    if(isset($values['lineplot_mark_fillcolor'])){
                        $line->mark->SetFillColor($values['lineplot_mark_fillcolor']);
                    }                    
                }
            } else {
                
            }

            if (isset($values['lineplot_mark_callback'])) {
                $line->mark->SetCallback($values["lineplot_mark_callback"]);
            }

            if (isset($values['lineplot_mark_callbackyx'])) {
                $line->mark->SetCallbackYX($values["lineplot_mark_callbackyx"]);
            }


            /* if (isset($values['lineplot_reescale'])) {
              $graph->doAutoScaleYnAxis();
              } */

            if (isset($values['graph_yscale_autoticks']))
                $graph->yscale->SetAutoTicks($values['graph_yscale_autoticks']);


            return $lineplot;
        }
    }

    function strokeGraph($style_name, $custom, $graph) {
        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerDafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {

            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            if($values['graph']!='piegraph' && $values['graph']!='ganttgraph'){

                // Setup scales
               
                $ymin = null;
                $ymax = null;
                $xmin = null;
                $xmax = null;
               
                if (count($graph->plots) > 0) {
                    try{
                        $graph->doAutoScaleYAxis();
                        $ymin = $graph->yscale->GetMinVal();
                        $ymax = $graph->yscale->GetMaxVal();                        
                    }  catch (\Exception $e) {
                        
                    }
                    
                    try{
                        $graph->doAutoScaleXAxis();
                        $xmin = $graph->xscale->GetMinVal();
                        $xmax = $graph->xscale->GetMaxVal();                         
                    }  catch (\Exception $e) {
                        
                    }
                }
                
                // Add a scatter points to adjust scale
                
                // If there are vertical zebras
                if($this->zebra_x_min != null){  
                     // if exist y autoscale 
                    if($ymin!=null){
                         $scatter_for_scale = new \ScatterPlot(array(($ymin+$ymax/2),($ymin+$ymax/2)), array($this->zebra_x_min,$this->zebra_x_max));
                         $scatter_for_scale->mark->SetWidth(0);
                         $scatter_for_scale->setColor('black');
                         $graph->add($scatter_for_scale);                         
                     }else{
                         $scatter_for_scale = new \ScatterPlot(array(0,0), array($this->zebra_x_min,$this->zebra_x_max));
                         $scatter_for_scale->mark->SetWidth(0);
                         $scatter_for_scale->setColor('black');
                         $graph->add($scatter_for_scale);     
                     }
                }                

                // If there are horizontal zebras
                if($this->zebra_y_min != null){  
                     // if exist y autoscale 
                    if($xmin!=null){
                         $scatter_for_scale = new \ScatterPlot(array($this->zebra_y_min,$this->zebra_y_max), array(($xmin+$xmax/2),($xmin+$xmax/2)));
                         $scatter_for_scale->mark->SetWidth(0);
                         $scatter_for_scale->setColor('black');
                         $graph->add($scatter_for_scale);                         
                     }else{
                         $scatter_for_scale = new \ScatterPlot(array($this->zebra_y_min,$this->zebra_y_max),array(0,0));
                         $scatter_for_scale->mark->SetWidth(0);
                         $scatter_for_scale->setColor('black');
                         $graph->add($scatter_for_scale);     
                     }
                }          
                
                
                // try to get autoscale again
              /*  if (count($graph->plots) > 0) {
                    $graph->doAutoScaleYAxis();
                    $ymin = $graph->yscale->GetMinVal();
                    $ymax = $graph->yscale->GetMaxVal();    
                 
                    $graph->doAutoScaleXAxis();
                    $xmin = $graph->xscale->GetMinVal();
                    $xmax = $graph->xscale->GetMaxVal();  
                } */
                /*
                $ymin = 0;
                $ymax = 1;
                $xmin = 0;
                $xmax = 1;
                 */
                
                $yt = substr($values['graph_scale'], -3, 3);
                $xt = substr($values['graph_scale'], 0, 3);

              
                if(count($graph->plots)>0){
                    $xmin=$graph->xscale->GetMinVal();
                    $xmax=$graph->xscale->GetMaxVal();
                }
            //throw new \Exception($xmax);
                
                if (isset($values['graph_yscale_min'])) {
                    $ymin = $values['graph_yscale_min'];
                }else{
                    if ($yt == 'log') {
                        $ymin=log($ymin,10);
                    }                    
                }
                if (isset($values['graph_yscale_max'])) {
                    $ymax = $values['graph_yscale_max'];
                }else{
                    if ($yt == 'log') {
                        $ymax=log($ymax,10);
                    }                    
                }
                if (isset($values['graph_xscale_min'])) {
                    $xmin = $values['graph_xscale_min'];
                }else{
                    if ($xt == 'log') {
                        $xmin=log($xmin,10);
                    }                    
                }
                if (isset($values['graph_xscale_max'])) {
                    $xmax = $values['graph_xscale_max'];
                }else{
                    if ($xt == 'log') {
                        $xmax=log($xmax,10);
                    }                    
                }       
                
                // If min or max are zebras, add grace space.
                $ygrace=($ymax-$ymin)*0.01;
                if($ymin===$this->zebra_y_min) $ymin=$ymin-$ygrace;
                if($ymax===$this->zebra_y_max) $ymax=$ymin+$ygrace; 
                
                $xgrace=($xmax-$xmin)*0.01;
                if($xmin===$this->zebra_x_min) $xmin=$xmin-$xgrace;
                if($xmax===$this->zebra_x_max) $xmax=$xmax+$xgrace;                 
//throw new \Exception($xmin);
                $graph->SetScale($values['graph_scale'], $ymin, $ymax, $xmin, $xmax);
                
                if (isset($values['graph_yscale_autoticks'])){
                    $graph->yscale->SetAutoTicks($values['graph_yscale_autoticks']);
                }    
            
            }
            
            // Mandatory: The color margin must be defined after set scale
            if (isset($values['graph_margincolor'])) {
                //frame with not implemented yet 
                $graph->SetFrame(true, $values['graph_margincolor'], 0);
                $graph->SetColor($values['graph_margincolor']);
                $graph->SetMarginColor($values['graph_margincolor']);
                
                // not implemented yet 
                //$graph->SetBackgroundGradient('darkred:0.7', 'black', 2, BGRAD_MARGIN);
            }
        

            if ( count($graph->plots) > 0 || $values['graph']=='piegraph' || $values['graph']=='ganttgraph') {

                if (isset($values['graph_xaxis_labelformatcallback'])) { // If it has labelformatcallback
                    if (is_callable($values['graph_xaxis_labelformatcallback'])) {
                        $graph->xaxis->SetLabelFormatCallback($values['graph_xaxis_labelformatcallback']);
                    } else {
                        $callbacks = $this->getCallFunctions();
                        if ($values['graph_xaxis_labelformatcallback'] == 'AutoTimeCallback') { // If it
                            $xminmax = $graph->GetXMinMax();
                            if ($xminmax[0] != null) {
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
                }

                
                // Setup axis
                
                // Y- Axis
                
                if (isset($values['graph_xaxis_labelangle'])) {
                    $graph->xaxis->SetLabelAngle($values["graph_xaxis_labelangle"]);
                }

                if (isset($values['graph_yaxis_title'])){
                    $graph->yaxis->title->Set($values["graph_yaxis_title"]);
                }
                if (isset($values['graph_yaxis_titlemargin'])){
                    $graph->yaxis->SetTitleMargin($values["graph_yaxis_titlemargin"]);
                }
                if (isset($values['graph_yaxis_hideline'])){
                    $graph->yaxis->HideLine($values['graph_yaxis_hideline']);                
                }

                if (isset($values['graph_yaxis_hidelabels'])){
                    $graph->yaxis->HideLabels($values['graph_yaxis_hidelabels']);      
                }
                
                if (isset($values['graph_xaxis_hidelabels'])){
                    $graph->xaxis->HideLabels($values['graph_xaxis_hidelabels']);                
                }                

                
                // X-Axis
                
                if (isset($values['graph_xaxis_ticklabels'])) {
                    $graph->xaxis->SetTickLabels($values["graph_xaxis_ticklabels"]);
                }
                
                if (isset($values['graph_yaxis_ticklabels'])) {
                    $graph->yaxis->SetTickLabels($values["graph_yaxis_ticklabels"]);
                }
                
 
                
                if (isset($values['graph_xaxis_title'])) {
                    if(isset($values['graph_xaxis_title_position'])){
                        $graph->xaxis->SetTitle($values["graph_xaxis_title"],$values['graph_xaxis_title_position']);
                    }else{
                        $graph->xaxis->SetTitle($values["graph_xaxis_title"]);
                    }
                } 
                
                if (isset($values['graph_xaxis_titlemargin'])){
                    $graph->xaxis->SetTitleMargin($values["graph_xaxis_titlemargin"]);
                }
                
                if (isset($values['graph_axis_tickposition'])) {
                    $graph->xaxis->SetTickPositions($values['graph_axis_tickposition']);
                }
                 
                
                //$graph->xaxis->SetTickPositions(array(0,  2.5),array(1,  2));
                              
                
                if (isset($values['graph_xaxis_tickposition'])) {
                    $graph->xaxis->SetTickPositions($values['graph_xaxis_tickposition'][0],$values['graph_xaxis_tickposition'][1],$values['graph_xaxis_tickposition'][2]);
                }          
                
                if (isset($values['graph_yaxis_tickposition'])) {
                    $graph->yaxis->SetTickPositions($values['graph_yaxis_tickposition'][0],$values['graph_yaxis_tickposition'][1],$values['graph_yaxis_tickposition'][2]);
                }          
                
                if (isset($values['graph_xaxis_tickside'])) {
                    $graph->xaxis->SetTickSide(constant($values['graph_xaxis_tickside']));
                } 
                
                if (isset($values['graph_yaxis_tickside'])) {
                    $graph->yaxis->SetTickSide(constant($values['graph_yaxis_tickside']));
                }                 
                
                if (isset($values['graph_xaxis_tick_hide_minor']) || isset($values['graph_xaxis_tick_hide_major'])) {
                    if(!isset($values['graph_xaxis_tick_hide_minor'])){
                        $values['graph_xaxis_tick_hide_minor']=true;
                        $values['graph_yaxis_tick_hide_major']=true;
                    } 
                    if(!isset($values['graph_xaxis_tick_hide_major'])){
                        $values['graph_xaxis_tick_hide_major']=true;
                    }
                    
                    $graph->xaxis->HideTicks($values['graph_xaxis_tick_hide_minor'], $values['graph_xaxis_tick_hide_major']);
                }                 
                
                if (isset($values['graph_yaxis_tick_hide_minor']) || isset($values['graph_yaxis_tick_hide_major'])) {
                    if(!isset($values['graph_yaxis_tick_hide_minor'])){
                        $values['graph_yaxis_tick_hide_minor']=true;
                        $values['graph_yaxis_tick_hide_major']=true;
                    } 
                    if(!isset($values['graph_yaxis_tick_hide_major'])){
                        $values['graph_yaxis_tick_hide_major']=true;
                    }
                    
                    $graph->yaxis->HideTicks($values['graph_yaxis_tick_hide_minor'], $values['graph_yaxis_tick_hide_major']);
                }                 

                if (isset($values['graph_xaxis_tick_size_minor']) || isset($values['graph_xaxis_tick_size_major'])) {
                    if(!isset($values['graph_xaxis_tick_size_minor'])){
                        $values['graph_xaxis_tick_size_minor']=3;
                    } 
                    if(!isset($values['graph_xaxis_tick_size_major'])){
                        $values['graph_xaxis_tick_size_major']=3;
                    }
                    
                    $graph->xaxis->scale->ticks->SetSize($values['graph_xaxis_tick_size_major'], $values['graph_xaxis_tick_size_minor']);
                }                   
                
                if (isset($values['graph_yaxis_tick_size_minor']) || isset($values['graph_yaxis_tick_size_major'])) {
                    if(!isset($values['graph_yaxis_tick_size_minor'])){
                        $values['graph_yaxis_tick_size_minor']=3;
                    } 
                    if(!isset($values['graph_yaxis_tick_size_major'])){
                        $values['graph_yaxis_tick_size_major']=3;
                    }
                    
                    $graph->yaxis->scale->ticks->SetSize($values['graph_yaxis_tick_size_major'], $values['graph_yaxis_tick_size_minor']);
                }               
                
                if (isset($values['graph_xaxis_tick_color'])){
                    $graph->xaxis->scale->ticks->SetColor($values['graph_xaxis_tick_color']);
                }
                
                if (isset($values['graph_yaxis_tick_color'])){
                    $graph->yaxis->scale->ticks->SetColor($values['graph_yaxis_tick_color']);
                }                

                if(isset($values['graph_xaxis_tick_labellogtype']) && get_class($graph->xaxis->scale->ticks)=='LogTicks'){
                    $graph->xaxis->scale->ticks->SetLabelLogType(constant($values['graph_xaxis_tick_labellogtype']));
                }
                
                if(isset($values['graph_yaxis_tick_labellogtype']) && get_class($graph->yaxis->scale->ticks)=='LogTicks'){
                    $graph->yaxis->scale->ticks->SetLabelLogType(constant($values['graph_yaxis_tick_labellogtype']));
                }
                

                if (isset($values['graph_ygrid_fill'])) {
                    $graph->ygrid->SetFill($values['graph_ygrid_fill'][0], $values['graph_ygrid_fill'][1], $values['graph_ygrid_fill'][2]);   
                    $graph->ygrid->Show();
                    $graph->SetGridDepth(DEPTH_BACK);  //DEPTH_BACK, Under plots //DEPTH_FRONT, On top of plots   
                }          
                
                if (isset($values['graph_color'])) {
                    $graph->SetColor($values['graph_color']); 
                }
                
                if (isset($values['graph_xgrid_show'])) {
                    $graph->xgrid->Show($values['graph_xgrid_show']); 
                }
                                
                if (isset($values['graph_ygrid_show'])) {
                    $graph->ygrid->Show($values['graph_ygrid_show']); 
                }
                
                // Set legend
                if (isset($values['graph_legend_abspos_x']) &&
                        isset($values['graph_legend_abspos_y']) &&
                        isset($values['graph_legend_abspos_halign']) &&
                        isset($values['graph_legend_abspos_valign'])) {
                    $graph->legend->SetAbsPos($values['graph_legend_abspos_x'], $values['graph_legend_abspos_x'], $values['graph_legend_abspos_halign'], $values['graph_legend_abspos_valign']);
                }

                if (isset($values['graph_legend_layout'])) {
                    $graph->legend->SetLayout($values['graph_legend_layout']);
                }

                if (isset($values['graph_legend_shadow'])) {
                    $graph->legend->SetShadow($values['graph_legend_shadow']);
                }

                if (isset($values['graph_legend_fillcolor'])) {
                    $graph->legend->SetFillColor($values['graph_legend_fillcolor']);
                }

                if (isset($values['graph_legend_hide'])) {
                    if(is_string($values['graph_legend_hide'])){
                        $val=strtolower($values['graph_legend_hide']);
                        if ($val=="false"){
                            $graph->legend->Hide(false);
                        }else{
                            $graph->legend->Hide(true);
                        }
                    }else{
                        $graph->legend->Hide($values['graph_legend_hide']);
                    }
                }                
                
                if (isset($values['graph_scale'])) {
                    $xt = substr($values['graph_scale'], 0, 3);
                    if($xt=='dat'){ // I can call xscale type date methods
                        // SetDateAlign not implemented yet
                        // $graph->xaxis->scale->SetDateAlign(YEARADJ_1,YEARADJ_1);

                        if(isset($values['graph_xaxis_scale_dateformat'])){
                            $graph->xaxis->scale->SetDateFormat($values['graph_xaxis_scale_dateformat']);
                        }                        
                              
                    }
                }                
           
                if (isset($values['graph_yaxis_scale_ticks_supressfirst'])) {
                    $graph->yaxis->scale->ticks->SupressFirst($values['graph_yaxis_scale_ticks_supressfirst']);
                }
                
           
                if (isset($values['graph_xaxis_scale_ticks_supressfirst'])) {
                    $graph->xaxis->scale->ticks->SupressFirst($values['graph_xaxis_scale_ticks_supressfirst']);
                }
                
                if(isset($values['graph_xaxis_scale_ticks'])){
                    $graph->xaxis->scale->ticks->Set($values['graph_xaxis_scale_ticks']);
                }       
                
                if(isset($values['graph_yaxis_scale_ticks'])){
                    $graph->yaxis->scale->ticks->Set($values['graph_yaxis_scale_ticks']);
                }                
              
                
                //$graph->yaxis->SetTextTickInterval(2);
                if(isset($values['graph_xaxis_tick_interval'])){
                    $graph->xaxis->scale->ticks->Set($values['graph_xaxis_tick_interval']);
                }
                
                if(isset($values['graph_yaxis_tick_interval'])){
                    $graph->xaxis->scale->ticks->Set($values['graph_xaxis_tick_interval']);
                }
                
                //$graph->xgrid->Show(true);

                if($values['graph']!='piegraph' && $values['graph']!='ganttgraph'){
                    $graph->SetClipping(true);
                    if (isset($values['graph_xaxis_pos'])) {
                        $graph->xaxis->SetPos($values["graph_xaxis_pos"]);
                    } 
                    if (isset($values['graph_yaxis_pos'])) {
                        $graph->yaxis->SetPos($values["graph_yaxis_pos"]);
                    }                     
                    $graph->graph_theme = null;
                }

                return $graph->Stroke();
            } else {
                return false;
            }
        }
    }

    function createErrorImg($style_name, $custom) {
        require_once ($this->path.'jpgraph.php');
        require_once ($this->path.'jpgraph_canvas.php');
        require_once ($this->path.'jpgraph_canvtools.php');

        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {
            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            // Now create graph and define style values before obtained.
            if (!isset($values['canvasgraph_width'])){
                throw new \Exception('DafuerJpgraphBundle says: Variable canvasgraph_width must be defined.');
            }
            
            if (!isset($values['canvasgraph_height'])){
                throw new \Exception('DafuerJpgraphBundle says: Variable canvasgraph_height must be defined.');
            }

            $graph = new \CanvasGraph($values['canvasgraph_width'], $values['canvasgraph_height'], 'auto');
            $graph->InitFrame();
            if (isset($values['canvasgraph_color'])) {
                $graph->img->SetColor($values['canvasgraph_color']);
                $graph->img->FilledRectangle(0, 0, $values['canvasgraph_width'], $values['canvasgraph_height']);
            }

            $graph->Stroke();
        }
    }

    function parseQueryParameters($query) {
        $result = array();

        if (is_null($query->get('combined'))) {
            //$result = $query->all();
            $parameters = $query->all();
            foreach ($parameters as $name => $value) {
                $result[0][$name] = $value;
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
/*
    function graphDaySeries($graph_style, $line_style, $ydata, $xdata, $custom_graph = array(), $custom_lineplot = array(), $graph = null) {
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
                    $lineplot = $this->createGraphPlot($line_style, $graph, $ydata[$i], $xdata[$i], $custom_lineplot);
                }
            } else {
                $lineplot = $this->createGraphPlot($line_style, $graph, $ydata[$i], $xdata[$i], $custom_lineplot);
            }

            return $graph;
        } else {

            if (is_null($graph)) {  // Si no me pasan una grafica a la que añadir la linea creo una nueva para devolver el error
                $graph = $this->createErrorImg($graph_style, $custom_graph);
            }

            return $graph;
        }
    }
 
 */

}

?>

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

    private $graph=null;
    private $numberOfPlots=0;
    private $numberOfEmptyPlots=0;
    private $numberOfErrorPlots=0;
    private $config_file;
    //private $viewer_file;
    private $options;
    private $path;
    
    private $zebra_x_min=null;
    private $zebra_x_max=null;
    private $zebra_y_min=null;
    private $zebra_y_max=null;


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
            if ($asociated_value !== "%%%") { // If value is equal %%% then remove this parameter
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

    private function createGraph($style_name, $custom = array()) {

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
                $this->graph = new \Graph($values['graph_width'], $values['graph_height']);
            }

            if ($values['graph'] == "piegraph") {
                require_once ($this->path.'jpgraph_pie.php');
                $this->graph = new \PieGraph($values['graph_width'], $values['graph_height']);
            }      
            
            if ($values['graph'] == "ganttgraph") {
                require_once ($this->path.'jpgraph_gantt.php');
                $this->graph = new \GanttGraph($values['graph_width'], $values['graph_height']);
            }                  
            

            if (isset($values['graph_img_margin_left']) && isset($values['graph_img_margin_right']) && isset($values['graph_img_margin_top']) && isset($values['graph_img_margin_bottom'])) {
                $this->graph->SetMargin($values['graph_img_margin_left'], $values['graph_img_margin_right'], $values['graph_img_margin_top'], $values['graph_img_margin_bottom']);
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
                $this->graph->SetScale($values['graph_scale']);
            }


            if (isset($values['graph_title'])) {
                $this->graph->title->Set($this->transformString($values['graph_title']));
            }
            
            // Set up title font
            if(isset($values['graph_title_font_family'])){
                if(isset($values['graph_title_font_style'])){
                    if(isset($values['graph_title_font_size'])){
                        $this->graph->title->SetFont(constant($values['graph_title_font_family']),constant($values['graph_title_font_style']),$values['graph_title_font_size']);
                    }else{
                        $this->graph->title->SetFont(constant($values['graph_title_font_family']),constant($values['graph_title_font_style']));
                    }
                }else{
                    $this->graph->title->SetFont(constant($values['graph_title_font_family']));
                }
            }
            

            if (isset($values['graph_xgrid_show']))
                $this->graph->xgrid->Show($values['graph_xgrid_show']);
            if (isset($values['graph_xgrid_color']))
                $this->graph->xgrid->SetColor($values['graph_xgrid_color']);
            if (isset($values['graph_xgrid_linestyle']))
                $this->graph->xgrid->SetLineStyle($values["graph_xgrid_linestyle"]);
            //if (isset($values['graph_img_antialiasing']))
            //    $this->graph->img->SetAntiAliasing($values['graph_img_antialiasing']);
            if (isset($values['graph_legend_frameweight']))
                $this->graph->legend->SetFrameWeight($values['graph_legend_frameweight']);
            if (isset($values['graph_clipping']))
                $this->graph->SetClipping($values['graph_clipping']);


            return $this->graph;
        }
    }
    
    
    

    public function createGraphPlot($style_name, $ydata, $xdata = null, $custom = array()) {
        $this->numberOfPlots++;

        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {
            // If the graph don't exist, create it.
            if($this->graph===null){
                $this->createGraph($style_name, $custom);
            }
           
            
            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            // If the plot has errors return null;
            if(isset($values['lineplot_error'])){
                if($values['lineplot_error']===true || strtolower($values['lineplot_error'])==='true'){
                    $this->numberOfErrorPlots++;
                    $this->numberOfEmptyPlots++;
                    return null;
                }
            }   
            // If the plot are empty return null;
            if(count($ydata)==0){
                $this->numberOfEmptyPlots++;
                return null;
            }                  

            //if($this->graph==null) $this->graph=$this->createGraph ($style_name, $custom);
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
                 
            }            

            if ($values['lineplot'] == "errorlineplot") {
                require_once ($this->path.'jpgraph_line.php');
                require_once ($this->path.'jpgraph_error.php');
                // Add control for graphs with one data
                if(count($ydata)==1 && $ydata[0]==0){
                    $ydata[]=0;
                }
                if (is_null($xdata) || count($xdata)==0) {
                    $lineplot = new \ErrorLinePlot($ydata);
                } else {
                    $lineplot = new \ErrorLinePlot($ydata, $xdata);
                }
            }

            if ($values['lineplot'] == "boxplot") {
                require_once ($this->path.'jpgraph_stock.php');
                // Add control for graphs with one data
                if(count($ydata)==1 && $ydata[0]==0){
                    $ydata[]=0;
                    $ydata[]=0;
                    $ydata[]=0;
                    $ydata[]=0;
                }                
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
                $this->graph->Add($lineplot);
                if(isset($values['lineplot_slicecolors'])){
                    $lineplot->SetSliceColors($values['lineplot_slicecolors']);
                }
                
            }
            
            if ($values['lineplot'] == "pieplot3d") {
                require_once ($this->path.'jpgraph_pie.php');
                require_once ($this->path.'jpgraph_pie3d.php');
                $lineplot = new \PiePlot3D($ydata);                
                $this->graph->Add($lineplot);
                if(isset($values['lineplot_slicecolors'])){
                    $lineplot->SetSliceColors($values['lineplot_slicecolors']);
                }
                
            }            
            
            if ($values['lineplot'] == "ganttplot") {
                require_once ($this->path.'jpgraph_gantt.php');

                $label="";
                if(isset($values['lineplot_label'])){
                    $label=$this->transformString($values['lineplot_label']);
                }
                //GantBar (posicion,formato,inicio,fin,etiqueta,grosor)
                //$lineplot =  new \GanttBar($data[1][0],$data[1][1],$data[1][2],$data[1][3],"[50%]");
                $lineplot =  new \GanttBar($ydata,$values['lineplot_information'],$xdata[0],$xdata[1],$label);
                
                                  
                $this->graph->Add($lineplot);
            }            
            
            // Add lineplot
            if (isset($values['graph_yaxis_number'])) {
                if ($values['graph_yaxis_number'] == 0) {
                    if(!isset($lineplot)) throw new \Exception('DafuerDafuerJpgraphBundle says: Lineplot dont exist');
                    $this->graph->Add($lineplot);
                } else {
                    // First, I find maxium index allowed to prevent a exception
                    $index = 0;
                    for ($i = 0; $i < $values['graph_yaxis_number'] - 1; $i++) {
                        if (!isset($this->graph->ynaxis))
                            break;
                    }
                    if ($i > 0)
                        $index = $i - 1;

                    $this->graph->SetYScale($index, 'lin');
                    $this->graph->AddY($index, $lineplot);                
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
                    $lineplot->SetLegends($this->transformString($values['lineplot_legend']));
                }else{
                    if($values['lineplot'] == "zebraplot"){
                        $lineplot[0]->SetLegend($this->transformString($values['lineplot_legend']));
                    }else{
                        $lineplot->SetLegend($this->transformString($values['lineplot_legend']));
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
            
            if(isset($values['lineplot_style'])){
                if(!is_array($lineplot)){
                    $lineplot->SetStyle($values['lineplot_style']);
                }else{
                    foreach($lineplot as $singleline){
                        $singleline->SetLineStyle($values['lineplot_style']);
                    }
                }
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


            if (isset($values['graph_yscale_autoticks']))
                $this->graph->yscale->SetAutoTicks($values['graph_yscale_autoticks']);


            return $lineplot;
        }
    }

    function strokeGraph($style_name, $custom) {
        if (!isset($this->options[$style_name])) {
            throw new \Exception('DafuerDafuerJpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {
            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);
            
            if($this->numberOfErrorPlots>0){ // If there are errors
                if($this->numberOfPlots==$this->numberOfErrorPlots){ // If all plots has errors
                    if(!isset($values['graph_error'])){
                        throw new \Exception('DafuerDafuerJpgraphBundle says: The property graph_error must be defined.');
                    }
                    
                    $this->createImg($values['graph_error'], $values);
                    return null;                    
                }else{
                    if(!isset($values['graph_error_multiple'])){
                        throw new \Exception('DafuerDafuerJpgraphBundle says: The property graph_error_multiple must be defined.');
                    }
                    
                    // Obtain modifcations
                    $newoptions=$this->getOptions($values['graph_error_multiple'], array());
    
                    // And it apply to value array
                    foreach($newoptions as $keyoption=>$valueoption){
                        $values[$keyoption]=$valueoption;
                    }
  
                }
            }
            
            if($this->numberOfPlots==$this->numberOfEmptyPlots){ // If all plots are empty
                if($values['graph']!='piegraph' && $values['graph']!='ganttgraph'){ // If graph is piegraph or ganttgraph there are no problem if it is empty
                    if(!isset($values['graph_empty'])){
                        throw new \Exception('DafuerDafuerJpgraphBundle says: The property graph_empty must be defined.');
                    }

                    if($values['graph_empty']=='AXIS'){
                        require_once ($this->path.'jpgraph_scatter.php');
                        $scatter_for_no_empty = new \ScatterPlot(array(0));
                        $scatter_for_no_empty->mark->SetWidth(0);
                        $scatter_for_no_empty->setColor('black');
                        $this->graph->add($scatter_for_no_empty);   
                        $values['graph_scale']='intint';
                        $values['graph_yaxis_pos']='min';
                        //$this->createGraphPlot($style_name, array(0), null, $custom);
                    }else{
                        $this->createImg($values['graph_empty'], $values);
                        return null;
                    }
                }
            }            

            // If I only want the legend, I make a bypass
            if(isset($values['graph_legend_only'])){
                if($values['graph_legend_only']===true || strtolower($values['graph_legend_only'])==='true'){
                  $this->prepareLegend($values);
                  $this->graph->legend->SetAbsPos(0, 0, 'left');
                  $this->graph->legend->Hide(false);
                  $this->graph->legend->SetShadow(false,0);
                  $this->graph->legend->SetFrameWeight(0);
                  $this->graph->doPrestrokeAdjustments();
                  $this->graph->legend->Stroke($this->graph->img);
                  return $this->graph->cache->PutAndStream($this->graph->img,$this->graph->cache_name,$this->graph->inline,null);
                }
            }
            
            // In other case, I continue preparing            
            $this->prepareScale($values);            
            $this->prepareLegend($values);
            
            // Mandatory: The color margin must be defined after set scale
            $this->prepareGraph($values);
            //if ( count($this->graph->plots) > 0 || $values['graph']=='piegraph' || $values['graph']=='ganttgraph') {
                $this->prepareAxis($values);
                $this->prepareGrid($values);
                
                return $this->graph->Stroke();
            /*} else {
                return false;
            }*/
        }
    }

    private function prepareGraph($values){
        // Mandatory: The color margin must be defined after set scale
        if (isset($values['graph_margincolor'])) {
            if (isset($values['graph_frame'][0]) && isset($values['graph_frame'][1])){
                $this->graph->SetFrame($values['graph_frame'][0], 'red', $values['graph_frame'][2]);
            }

            $this->graph->SetColor($values['graph_margincolor']);
            $this->graph->SetMarginColor($values['graph_margincolor']);
            
            if (isset($values['graph_box'])) {
                $this->graph->SetBox($values['graph_box'][0],$values['graph_box'][1],$values['graph_box'][2]);
            }
            
            // not implemented yet 
            //$this->graph->SetBackgroundGradient('darkred:0.7', 'black', 2, BGRAD_MARGIN);
        }

        if (isset($values['graph_color'])) {
            $this->graph->SetColor($values['graph_color']); 
        }          
    }    
    
    private function prepareGrid($values){    
        if (isset($values['graph_ygrid_fill'])) {
            $this->graph->ygrid->SetFill($values['graph_ygrid_fill'][0], $values['graph_ygrid_fill'][1], $values['graph_ygrid_fill'][2]);   
            $this->graph->ygrid->Show();
            $this->graph->SetGridDepth(DEPTH_BACK);  //DEPTH_BACK, Under plots //DEPTH_FRONT, On top of plots   
        }    

        if (isset($values['graph_xgrid_show'])) {
            $this->graph->xgrid->Show($values['graph_xgrid_show']); 
        }

        if (isset($values['graph_ygrid_show'])) {
            $this->graph->ygrid->Show($values['graph_ygrid_show']); 
        }                  
    }
    
    private function prepareAxis($values){
        if (isset($values['graph_xaxis_labelformatcallback'])) { // If it has labelformatcallback
            if (is_callable($values['graph_xaxis_labelformatcallback'])) {
                $this->graph->xaxis->SetLabelFormatCallback($values['graph_xaxis_labelformatcallback']);
            } else {
                $callbacks = $this->getCallFunctions();
                if ($values['graph_xaxis_labelformatcallback'] == 'AutoTimeCallback') { // If it
                    $xminmax = $this->graph->GetXMinMax();
                    if ($xminmax[0] != null) {
                        $tpo = $xminmax[1] - $xminmax[0];

                        $callbacks = $this->getCallFunctions();
                        if ($tpo > 172800) {
                            $this->graph->xaxis->SetLabelFormatCallback($callbacks['TimeCallbackDay']);
                        } else {
                            $this->graph->xaxis->SetLabelFormatCallback($callbacks['TimeCallbackTime']);
                        }
                    }
                } else {
                    $this->graph->xaxis->SetLabelFormatCallback($callbacks[$values['graph_xaxis_labelformatcallback']]);
                }
            }
        }


        // Setup axis

        // Y- Axis

        if (isset($values['graph_xaxis_labelangle'])) {
            $this->graph->xaxis->SetLabelAngle($values["graph_xaxis_labelangle"]);
        }

        if (isset($values['graph_yaxis_title'])){
            $this->graph->yaxis->title->Set($this->transformString($values["graph_yaxis_title"]));
        }


        if (isset($values['graph_yaxis_titlemargin'])){
            $this->graph->yaxis->SetTitleMargin($values["graph_yaxis_titlemargin"]);
        }
        if (isset($values['graph_yaxis_showline'])){
            $this->graph->yaxis->HideLine(!$values['graph_yaxis_showline']);                
        }

        if (isset($values['graph_yaxis_showlabels'])){
            $this->graph->yaxis->HideLabels(!$values['graph_yaxis_showlabels']);      
        }

        if (isset($values['graph_xaxis_showlabels'])){
            $this->graph->xaxis->HideLabels(!$values['graph_xaxis_showlabels']);                
        }                


        // X-Axis

        if (isset($values['graph_xaxis_ticklabels'])) {
            $this->graph->xaxis->SetTickLabels($values["graph_xaxis_ticklabels"]);
        }

        if (isset($values['graph_yaxis_ticklabels'])) {
            $this->graph->yaxis->SetTickLabels($values["graph_yaxis_ticklabels"]);
        }



        if (isset($values['graph_xaxis_title'])) {
            if(isset($values['graph_xaxis_title_position'])){
                $this->graph->xaxis->SetTitle($this->transformString($values["graph_xaxis_title"]),$values['graph_xaxis_title_position']);
            }else{
                $this->graph->xaxis->SetTitle($this->transformString($values["graph_xaxis_title"]));
            }
        } 

        if (isset($values['graph_xaxis_titlemargin'])){
            $this->graph->xaxis->SetTitleMargin($values["graph_xaxis_titlemargin"]);
        }

        if (isset($values['graph_axis_tickposition'])) {
            $this->graph->xaxis->SetTickPositions($values['graph_axis_tickposition']);
        }


        if (isset($values['graph_xaxis_tickposition'])) {
            $this->graph->xaxis->SetTickPositions($values['graph_xaxis_tickposition'][0],$values['graph_xaxis_tickposition'][1],$values['graph_xaxis_tickposition'][2]);
        }          

        if (isset($values['graph_yaxis_tickposition'])) {
            $this->graph->yaxis->SetTickPositions($values['graph_yaxis_tickposition'][0],$values['graph_yaxis_tickposition'][1],$values['graph_yaxis_tickposition'][2]);
        }          

        if (isset($values['graph_xaxis_tickside'])) {
            $this->graph->xaxis->SetTickSide(constant($values['graph_xaxis_tickside']));
        } 

        if (isset($values['graph_yaxis_tickside'])) {
            $this->graph->yaxis->SetTickSide(constant($values['graph_yaxis_tickside']));
        }                 

        if (isset($values['graph_xaxis_tick_show_minor']) || isset($values['graph_xaxis_tick_show_major'])) {
            if(!isset($values['graph_xaxis_tick_show_minor'])){
                $values['graph_xaxis_tick_show_minor']=false;
                $values['graph_yaxis_tick_show_major']=false;
            } 
            if(!isset($values['graph_xaxis_tick_show_major'])){
                $values['graph_xaxis_tick_show_major']=false;
            }

            $this->graph->xaxis->HideTicks(!$values['graph_xaxis_tick_show_minor'], !$values['graph_xaxis_tick_show_major']);
        }                 

        if (isset($values['graph_yaxis_tick_show_minor']) || isset($values['graph_yaxis_tick_show_major'])) {
            if(!isset($values['graph_yaxis_tick_show_minor'])){
                $values['graph_yaxis_tick_show_minor']=false;
                $values['graph_yaxis_tick_show_major']=false;
            } 
            if(!isset($values['graph_yaxis_tick_show_major'])){
                $values['graph_yaxis_tick_show_major']=false;
            }

            $this->graph->yaxis->HideTicks(!$values['graph_yaxis_tick_show_minor'], !$values['graph_yaxis_tick_show_major']);
        }                 

        if (isset($values['graph_xaxis_tick_size_minor']) || isset($values['graph_xaxis_tick_size_major'])) {
            if(!isset($values['graph_xaxis_tick_size_minor'])){
                $values['graph_xaxis_tick_size_minor']=3;
            } 
            if(!isset($values['graph_xaxis_tick_size_major'])){
                $values['graph_xaxis_tick_size_major']=3;
            }

            $this->graph->xaxis->scale->ticks->SetSize($values['graph_xaxis_tick_size_major'], $values['graph_xaxis_tick_size_minor']);
        }                   

        if (isset($values['graph_yaxis_tick_size_minor']) || isset($values['graph_yaxis_tick_size_major'])) {
            if(!isset($values['graph_yaxis_tick_size_minor'])){
                $values['graph_yaxis_tick_size_minor']=3;
            } 
            if(!isset($values['graph_yaxis_tick_size_major'])){
                $values['graph_yaxis_tick_size_major']=3;
            }

            $this->graph->yaxis->scale->ticks->SetSize($values['graph_yaxis_tick_size_major'], $values['graph_yaxis_tick_size_minor']);
        }               

        if (isset($values['graph_xaxis_tick_color'])){
            $this->graph->xaxis->scale->ticks->SetColor($values['graph_xaxis_tick_color']);
        }

        if (isset($values['graph_yaxis_tick_color'])){
            foreach($this->graph->ynaxis as $axis){
                $axis->scale->ticks->SetColor($values['graph_yaxis_tick_color']);
            }                      
            $this->graph->yaxis->scale->ticks->SetColor($values['graph_yaxis_tick_color']);
        }        


        if (isset($values['graph_yaxis_color'])){
            foreach($this->graph->ynaxis as $axis){
                if (isset($values['graph_yaxis_label_color'])){
                    $axis->SetColor($values['graph_yaxis_color'],$values['graph_yaxis_label_color']);
                }else{
                    $axis->SetColor($values['graph_yaxis_color']);
                }
            }    
            if (isset($values['graph_yaxis_label_color'])){
               $this->graph->yaxis->SetColor($values['graph_yaxis_color'],$values['graph_yaxis_label_color']);
            }else{
               $this->graph->yaxis->SetColor($values['graph_yaxis_color']);
            }            
        }   

        if (isset($values['graph_xaxis_color'])){
            if (isset($values['graph_xaxis_label_color'])){
                $this->graph->xaxis->SetColor($values['graph_xaxis_color'], $values['graph_xaxis_label_color']);
            }else{
                $this->graph->xaxis->SetColor($values['graph_xaxis_color']);
            }
        }                     
        //$this->graph->ynaxis[0]->SetColor('#E3E3E3','blue');

        if(isset($values['graph_xaxis_tick_labellogtype']) && get_class($this->graph->xaxis->scale->ticks)=='LogTicks'){
            $this->graph->xaxis->scale->ticks->SetLabelLogType(constant($values['graph_xaxis_tick_labellogtype']));
        }

        if(isset($values['graph_yaxis_tick_labellogtype']) && get_class($this->graph->yaxis->scale->ticks)=='LogTicks'){
            $this->graph->yaxis->scale->ticks->SetLabelLogType(constant($values['graph_yaxis_tick_labellogtype']));
        }


        if (isset($values['graph_yaxis_scale_ticks_supressfirst'])) {
            $this->graph->yaxis->scale->ticks->SupressFirst($values['graph_yaxis_scale_ticks_supressfirst']);
        }


        if (isset($values['graph_xaxis_scale_ticks_supressfirst'])) {
            $this->graph->xaxis->scale->ticks->SupressFirst($values['graph_xaxis_scale_ticks_supressfirst']);
        }

        if(isset($values['graph_xaxis_scale_ticks'])){
            $this->graph->xaxis->scale->ticks->Set($values['graph_xaxis_scale_ticks']);
        }       

        if(isset($values['graph_yaxis_scale_ticks'])){
            $this->graph->yaxis->scale->ticks->Set($values['graph_yaxis_scale_ticks']);
        }                


        //$this->graph->yaxis->SetTextTickInterval(2);
        if(isset($values['graph_xaxis_tick_interval'])){
            $this->graph->xaxis->scale->ticks->Set($values['graph_xaxis_tick_interval']);
        }

        if(isset($values['graph_yaxis_tick_interval'])){
            $this->graph->xaxis->scale->ticks->Set($values['graph_xaxis_tick_interval']);
        }
        
        if($values['graph']!='piegraph' && $values['graph']!='ganttgraph'){
            $this->graph->SetClipping(true);
            if (isset($values['graph_xaxis_pos'])) {
                $this->graph->xaxis->SetPos($values["graph_xaxis_pos"]);
            } 
            if (isset($values['graph_yaxis_pos'])) {
                $this->graph->yaxis->SetPos($values["graph_yaxis_pos"]);
            }                     
            $this->graph->graph_theme = null;
        }        

    }
    
    private function prepareScale($values){
        if($values['graph']!='piegraph' && $values['graph']!='ganttgraph'){

            // Setup scales

            $ymin = null;
            $ymax = null;
            $xmin = null;
            $xmax = null;

            if (count($this->graph->plots) > 0) {
                try{
                    $this->graph->doAutoScaleYAxis();
                    $ymin = $this->graph->yscale->GetMinVal();
                    $ymax = $this->graph->yscale->GetMaxVal();                        
                }  catch (\Exception $e) {

                }

                try{
                    $this->graph->doAutoScaleXAxis();
                    $xmin = $this->graph->xscale->GetMinVal();
                    $xmax = $this->graph->xscale->GetMaxVal();                         
                }  catch (\Exception $e) {

                }
            }

            // Add a scatter points to adjust scale

            // If there are vertical zebras
            if($this->zebra_x_min != null){  
                 // if exist y autoscale 
                require_once ($this->path.'jpgraph_scatter.php');
                if($ymin!=null){
                     $scatter_for_scale = new \ScatterPlot(array(($ymin+$ymax/2),($ymin+$ymax/2)), array($this->zebra_x_min,$this->zebra_x_max));
                     $scatter_for_scale->mark->SetWidth(0);
                     $scatter_for_scale->setColor('black');
                     $this->graph->add($scatter_for_scale);                         
                 }else{
                     $scatter_for_scale = new \ScatterPlot(array(0,0), array($this->zebra_x_min,$this->zebra_x_max));
                     $scatter_for_scale->mark->SetWidth(0);
                     $scatter_for_scale->setColor('black');
                     $this->graph->add($scatter_for_scale);     
                 }
            }                

            // If there are horizontal zebras
            if($this->zebra_y_min != null){  
                 // if exist y autoscale 
                require_once ($this->path.'jpgraph_scatter.php');
                if($xmin!=null){
                     $scatter_for_scale = new \ScatterPlot(array($this->zebra_y_min,$this->zebra_y_max), array(($xmin+$xmax/2),($xmin+$xmax/2)));
                     $scatter_for_scale->mark->SetWidth(0);
                     $scatter_for_scale->setColor('black');
                     $this->graph->add($scatter_for_scale);                         
                 }else{
                     $scatter_for_scale = new \ScatterPlot(array($this->zebra_y_min,$this->zebra_y_max),array(0,0));
                     $scatter_for_scale->mark->SetWidth(0);
                     $scatter_for_scale->setColor('black');
                     $this->graph->add($scatter_for_scale);     
                 }
            }          

            if($this->zebra_y_min != null && $this->zebra_y_min<$ymin){                  
                $ymin=$this->zebra_y_min;
            }
            if($this->zebra_y_max != null && $this->zebra_y_max>$ymax){
                $ymax=$this->zebra_y_max;
            } 

            if($this->zebra_x_min != null && $this->zebra_x_min<$xmin){
                $xmin=$this->zebra_x_min;
            }
            if($this->zebra_x_max != null && $this->zebra_x_max>$xmax){
                $xmax=$this->zebra_x_max;
            }                            


            $yt = substr($values['graph_scale'], -3, 3);
            $xt = substr($values['graph_scale'], 0, 3);

          if($xmin===null){
              $xmin=$this->zebra_x_min;
          }

          if($xmax===null){
              $xmax=$this->zebra_x_max;
          }     

          if($ymin===null){
              $ymin=$this->zebra_y_min;
          }

          if($ymax===null){
              $ymax=$this->zebra_y_max;
          }                   


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
            if($ymin==$this->zebra_y_min) $ymin=$ymin-$ygrace;
            if($ymax==$this->zebra_y_max)  $ymax=$ymax+$ygrace; 

            $xgrace=($xmax-$xmin)*0.01;
            if($xmin==$this->zebra_x_min) $xmin=$xmin-$xgrace;
            if($xmax==$this->zebra_x_max) $xmax=$xmax+$xgrace;                        

            $this->graph->SetScale($values['graph_scale'], $ymin, $ymax, $xmin, $xmax);

            if (isset($values['graph_yscale_autoticks'])){
                $this->graph->yscale->SetAutoTicks($values['graph_yscale_autoticks']);
            }    

        }        
    }
    
    private function prepareLegend($values){
        // Set legend
        if (isset($values['graph_legend_abspos_x']) &&
                isset($values['graph_legend_abspos_y']) &&
                isset($values['graph_legend_abspos_halign']) &&
                isset($values['graph_legend_abspos_valign'])) {
            $this->graph->legend->SetAbsPos($values['graph_legend_abspos_x'], $values['graph_legend_abspos_y'], $values['graph_legend_abspos_halign'], $values['graph_legend_abspos_valign']);
        }

        if (isset($values['graph_legend_layout'])) {
            $this->graph->legend->SetLayout($values['graph_legend_layout']);
        }

        if (isset($values['graph_legend_shadow'])) {
            $this->graph->legend->SetShadow($values['graph_legend_shadow']);
        }

        if (isset($values['graph_legend_fillcolor'])) {
            $this->graph->legend->SetFillColor($values['graph_legend_fillcolor']);
        }

        if (isset($values['graph_legend_show'])) {
            if(is_string($values['graph_legend_show'])){
                $val=strtolower($values['graph_legend_show']);
                if ($val=="false"){
                    $this->graph->legend->Hide(true);
                }else{
                    $this->graph->legend->Hide(false);
                }
            }else{
                $this->graph->legend->Hide(!$values['graph_legend_show']);
            }
        }                

        if (isset($values['graph_scale'])) {
            $xt = substr($values['graph_scale'], 0, 3);
            if($xt=='dat'){ // I can call xscale type date methods
                // SetDateAlign not implemented yet
                // $this->graph->xaxis->scale->SetDateAlign(YEARADJ_1,YEARADJ_1);

                if(isset($values['graph_xaxis_scale_dateformat'])){
                    $this->graph->xaxis->scale->SetDateFormat($values['graph_xaxis_scale_dateformat']);
                }                        

            }
        }          
    }

    function createImg($style_name, $custom) {
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

            $this->graph = new \CanvasGraph($values['canvasgraph_width'], $values['canvasgraph_height'], 'auto');
            $this->graph->InitFrame();
            if (isset($values['canvasgraph_color'])) {
                $this->graph->img->SetColor($values['canvasgraph_color']);
                $this->graph->img->FilledRectangle(0, 0, $values['canvasgraph_width'], $values['canvasgraph_height']);
            }

            $this->graph->Stroke();
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

     function transformString($string){
        if(strpos($string, "___")===false){
            return $string;
        }else{ // If there are special chars, find it
            $special_chars=array(
                \SymChar::Get('alpha',true),
                \SymChar::Get('alpha',false),
                \SymChar::Get('beta',true),
                \SymChar::Get('beta',false),
                \SymChar::Get('gamma',true),
                \SymChar::Get('gamma',false),
                \SymChar::Get('delta',true),
                \SymChar::Get('delta',false),
                \SymChar::Get('epsilon',true),
                \SymChar::Get('epsilon',false),
                \SymChar::Get('zeta',true),
                \SymChar::Get('zeta',false),
                \SymChar::Get('ny',true),
                \SymChar::Get('ny',false),
                \SymChar::Get('eta',true),
                \SymChar::Get('eta',false),
                \SymChar::Get('theta',true),
                \SymChar::Get('theta',false),
                \SymChar::Get('iota',true),
                \SymChar::Get('iota',false),
                \SymChar::Get('kappa',true),
                \SymChar::Get('kappa',false),
                \SymChar::Get('lambda',true),
                \SymChar::Get('lambda',false),
                \SymChar::Get('mu',true),
                \SymChar::Get('mu',false),
                \SymChar::Get('nu',true),
                \SymChar::Get('nu',false),
                \SymChar::Get('xi',true),
                \SymChar::Get('xi',false),
                \SymChar::Get('omicron',true),
                \SymChar::Get('omicron',false),
                \SymChar::Get('pi',true),
                \SymChar::Get('pi',false),
                \SymChar::Get('rho',true),
                \SymChar::Get('rho',false),
                \SymChar::Get('sigma',true),
                \SymChar::Get('sigma',false),
                \SymChar::Get('tau',true),
                \SymChar::Get('tau',false),
                \SymChar::Get('upsilon',true),
                \SymChar::Get('upsilon',false),
                \SymChar::Get('phi',true),
                \SymChar::Get('phi',false),
                \SymChar::Get('chi',true),
                \SymChar::Get('chi',false),
                \SymChar::Get('psi',true),
                \SymChar::Get('psi',false),
                \SymChar::Get('omega',true),
                \SymChar::Get('omega',false),
                \SymChar::Get('euro',true),
                \SymChar::Get('euro',false),
                \SymChar::Get('yen',true),
                \SymChar::Get('yen',false),
                \SymChar::Get('pound',true),
                \SymChar::Get('pound',false),
                \SymChar::Get('approx',true),
                \SymChar::Get('approx',false),
                \SymChar::Get('neq',true),
                \SymChar::Get('neq',false),
                \SymChar::Get('not',true),
                \SymChar::Get('not',false),
                \SymChar::Get('inf',true),
                \SymChar::Get('inf',false),
                \SymChar::Get('sqrt',true),
                \SymChar::Get('sqrt',false),
                \SymChar::Get('int',true),
                \SymChar::Get('int',false),
                \SymChar::Get('copy',true),
                \SymChar::Get('copy',false),
                \SymChar::Get('para',true),
                \SymChar::Get('para',false),
                );

            $special_tags=array(
                "___ALPHA",
                "___alpha",
                "___BETA",
                "___beta",
                "___GAMMA",
                "___gamma",
                "___DELTA",
                "___delta",
                "___EPSILON",
                "___epsilon",
                "___ZETA",
                "___zeta",
                "___NY",
                "___ny",
                "___ETA",
                "___eta",
                "___THETA",
                "___theta",
                "___IOTA",
                "___iota",
                "___KAPPA",
                "___kappa",
                "___LAMBDA",
                "___lambda",
                "___MU",
                "___mu",
                "___NU",
                "___nu",
                "___XI",
                "___xi",
                "___OMICRON",
                "___omicron",
                "___PI",
                "___pi",
                "___RHO",
                "___rho",
                "___SIGMA",
                "___sigma",
                "___TAU",
                "___tau",
                "___UPSILON",
                "___upsilon",
                "___PHI",
                "___phi",
                "___CHI",
                "___chi",
                "___PSI",
                "___psi",
                "___OMEGA",
                "___omega",
                "___EURO",
                "___euro",
                "___YEN",
                "___yen",
                "___POUND",
                "___pound",
                "___APPROX",
                "___approx",
                "___NEQ",
                "___neq",
                "___NOT",
                "___not",
                "___DEF",
                "___def",
                "___INF",
                "___inf",
                "___SQRT",
                "___sqrt",
                "___INT",
                "___int",
                "___COPY",
                "___copy",
                "___PARA",
                "___para",
                );

            return str_replace($special_tags, $special_chars, $string);
        }
    }
   

}

?>

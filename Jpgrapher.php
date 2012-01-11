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
    private $options;

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

    public function __construct($config_file) {
        $this->config_file = $config_file;
        $this->options = Yaml::parse($this->config_file);
    }

    public function readStyle($style_tag, $values=array()) {
        if (!isset($this->options[$style_tag]))
            throw new \Exception('JpgraphBundle says: ' . $style_tag . ' style does not exists.');
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
        require_once (__DIR__ . '/../../../jpgraph/src/jpgraph.php');
        if (!isset($this->options[$style_name])) {
            throw new \Exception('JpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {
            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            // Now create graph and define style values before obtained.
            if (!isset($values['graph.width']))
                throw new \Exception('JpgraphBundle says: Variable graph.width must be defined.');
            if (!isset($values['graph.height']))
                throw new \Exception('JpgraphBundle says: Variable graph.height must be defined.');
            $graph = new \Graph($values['graph.width'], $values['graph.height']);
            if (isset($values['graph.scale']))
                $graph->SetScale($values['graph.scale']);
            if (isset($values['graph.title']))
                $graph->title->Set($values['graph.title']);
            if (isset($values['graph.box']))
                $graph->SetBox($values['graph.box']);
            if (isset($values['graph.xgrid.show']))
                $graph->xgrid->Show($values['graph.xgrid.show']);
            if (isset($values['graph.xgrid.color']))
                $graph->xgrid->SetColor($values['graph.xgrid.color']);
            if (isset($values['graph.xgrid.linestyle']))
                $graph->xgrid->SetLineStyle($values["graph.xgrid.linestyle"]);
            if (isset($values['graph.img.antialiasing']))
                $graph->img->SetAntiAliasing($values['graph.img.antialiasing']);
            if (isset($values['graph.legend.frameweight']))
                $graph->legend->SetFrameWeight($values['graph.legend.frameweight']);
            if (isset($values['graph.frame'][0]) && isset($values['graph.frame'][1]))
                $graph->SetFrame($values['graph.frame'][0], $values['graph.frame'][1], $values['graph.frame'][2]);
            if (isset($values['graph.clipping']))
                $graph->SetClipping($values['graph.clipping']);
            if (isset($values['graph.xaxis.labelangle']))
                $graph->xaxis->SetLabelAngle($values["graph.xaxis.labelangle"]);
            if (isset($values['graph.img.margin']))
                $graph->img->SetMargin($values['graph.img.margin'][0],$values['graph.img.margin'][1],$values['graph.img.margin'][2],$values['graph.img.margin'][3]);

            
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
                $graph = new CanvasGraph(10, 10, 'auto');
                $graph->SetMargin(0, 0, 0, 0);
            }

            return $graph;
        }
    }

    public function createLinePlot($style_name, $graph, $ydata, $xdata=null, $custom=array()) {
        require_once (__DIR__ . '/../../../jpgraph/src/jpgraph_line.php');
        if (!isset($this->options[$style_name])) {
            throw new \Exception('JpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {

            // Setting up variable values
            $values = $this->getOptions($style_name, $custom);

            if (is_null($xdata)) {
                $lineplot = new \LinePlot($ydata);
            } else {
                $lineplot = new \LinePlot($ydata, $xdata);
            }

            $graph->Add($lineplot);

            if (isset($values['lineplot.color']))
                $lineplot->SetColor($values['lineplot.color']);
            if (isset($values['lineplot.legend']))
                $lineplot->SetLegend($values['lineplot.legend']);
            if (isset($values['lineplot.weight']))
                $lineplot->SetWeight($values['lineplot.weight']);

            if (isset($values['graph.xaxis.ticklabels']))
                $graph->xaxis->SetTickLabels($values["graph.xaxis.ticklabels"]);  
            
            if (isset($values['lineplot.xaxis.title']))
                $graph->xaxis->title->Set($values["graph.xaxis.title"]);
            
            if (isset($values['lineplot.xaxis.pos']))
                $graph->xaxis->SetPos($values["graph.xaxis.pos"]);            




            if (isset($values['graph.yaxis.title']))
                $graph->yaxis->title->Set($values["graph.yaxis.title"]);
            if (isset($values['graph.yaxis.titlemargin']))
                $graph->yaxis->SetTitleMargin($values["graph.yaxis.titlemargin"]);            
            if (isset($values['graph.yaxis.hideline']))
                $graph->yaxis->HideLine($values['graph.yaxis.hideline']);
            
            if (isset($values['graph.ygrid.fill']))
                $graph->ygrid->SetFill($values['graph.ygrid.fill'][0], $values['graph.ygrid.fill'][1], $values['graph.ygrid.fill'][2]);

            
            
            if (isset($values['lineplot.max_ptos_to_mark'])) {
                if ($values['lineplot.max_ptos_to_mark'] == -1 || count($xdata) < $values['lineplot.max_ptos_to_mark']) {
                    $lineplot->mark->SetType(constant($values['lineplot.mark.type']));
                    $lineplot->mark->SetWidth($values['lineplot.mark.width']);
                    if ($values['lineplot.mark.color'] != '%lineplot_color%') {
                        $lineplot->mark->SetColor($values['lineplot.mark.color']);
                    } else {
                        $lineplot->mark->SetColor($values['lineplot.color']);
                    }
                }
            }


   
            /*if (isset($values['lineplot.reescale'])) {
                $graph->doAutoScaleYnAxis();
            }*/

            if (isset($values['graph.yscale.autoticks']))
                $graph->yscale->SetAutoTicks($values['graph.yscale.autoticks']);

            if (isset($values['graph.xaxis.labelformatcallback'])) {
                $callbacks = $this->getCallFunctions();
                if ($values['graph.xaxis.labelformatcallback'] == 'AutoTimeCallback') {

                    if (count($xdata) > 0) {
                        $tpo = max($xdata) - min($xdata);

                        $callbacks = $this->getCallFunctions();
                        if ($tpo > 172800) {
                            $graph->xaxis->SetLabelFormatCallback($callbacks['TimeCallbackDay']);
                        } else {
                            $graph->xaxis->SetLabelFormatCallback($callbacks['TimeCallbackTime']);
                        }
                    }
                } else {
                    $graph->xaxis->SetLabelFormatCallback($callbacks[$values['graph.xaxis.labelformatcallback']]);
                }
            }
            if (isset($values['graph.xaxis.labelangle']))
                $graph->xaxis->SetLabelAngle($values["graph.xaxis.labelangle"]);
            return $lineplot;
        }
    }

    public function createErrorLinePlot($style_name, $graph, $ydata, $xdata=null, $custom=array()) {
        require_once (__DIR__ . '/../../../jpgraph/src/jpgraph_line.php');
        if (!isset($this->options[$style_name])) {
            throw new \Exception('JpgraphBundle says: ' . $style_name . ' style does not exists.');
        } else {
            // Setting up variable values
            $values = $this->readOptions($style_name, $custom, 'errorlineplot_default');

            if (is_null($xdata)) {
                $lineplot = new \LinePlot($ydata);
            } else {
                $lineplot = new \LinePlot($ydata, $xdata);
            }

            $graph->Add($lineplot);

            $lineplot->SetColor($values['lineplot.color']);
            $lineplot->SetLegend($values['lineplot.legend']);
            $lineplot->SetWeight($values['lineplot.weight']);
//              //$lineplot->SetColor("#293c82");
//              //$lineplot->SetColor("red");
//              $lineplot->SetColor("darkgray");
//              $lineplot->SetCenter();
//              $lineplot->line->setColor($color);
//              $lineplot->line->SetWeight(1);
//              $lineplot->SetWeight(1);
//              $graph->Add($lineplot);             

            return $lineplot;
        }
    }

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

  //throw new \Exception('JpgraphBundle says: ');
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

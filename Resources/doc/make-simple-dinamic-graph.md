Make simple dinamic graph from you BD
=====================================

Hay 2 opciones:

a) Crear una accion en un controlador que devuelva una grafica
b) Crear una archivo de acceso a datos y un indice de graficas

---

Opcion a:

   Es la mas sencilla y rapida si tan solo quieres hacer una grafica poco o nada
configurable. Este codigo muestra un ejemplo:

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

   Por tanto, se pueden utilizar los metodos ofrecidos por el servicio jpgraph 
para generar una grafica. Es importante antes haber definido el estilo que se 
utilizara en la grafica, en este caso graph_example2.


Opcion b:

   Debes crear una carpeta llamada Graph dentro del bundle que vaya a contener
las graficas. En esta carpeta debera existir el archivo de indice de acceso a las
datos (yml) y el archivo de acceso a datos (clase php).

Ejemplo:

clim:
    roles: [ IS_AUTHENTICATED_ANONYMOUSLY ]
    function: AeroClim
    description: Climatology data
    style: boxplot_timeserie
    custom_style:
        graph_title: Climatology
        lineplot_legend: ''
        graph_xaxis_labelformatcallback: CallbackMonthNumber
        graph_legend_hide: false
        graph_scale: intlin
        #graph_xaxis_ticklabels:  [ 'A', 'B', 'C', 'D','A', 'B', 'C', 'D','A', 'B', 'C', 'D' ]
        graph_axis_tickposition: [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ]
        graph_xscale_min: '0'
        graph_xscale_max: '13'
        graph_plots:
            "stdover":
                lineplot: lineplot
                lineplot_color: darkgray   
            "stdunder":
                lineplot: lineplot
                lineplot_color: darkgray   
            "avg":
                lineplot: lineplot
                lineplot_color: blue                   
    classify: Climatology




class DataAccess extends BaseDataAccess {

    public function __construct() {
        $this->graphindexpath = __DIR__ . "/graphs.yml";

        parent::__construct();
    }



    public function AeroClim($params) {
        
        if (!isset($params['station']))
            return array('xdata' => array(), 'ydata' => array());

        $level=isset($params['level'])?$params['level']:20;
        $year=isset($params['year'])?$params['year']:'all';
        
        if($year=='all'){
            $query = "SELECT month, `".$params['var']."_per25`, `".$params['var']."_per75`, `".$params['var']."_per5`, `".$params['var']."_per95`, `".$params['var']."_med`, `".$params['var']."_avg`, `".$params['var']."_std` from ".$level."_aod_monthly_clim where station='" . $params['station']."' order by `month`";
        }else{
            $query = "SELECT MONTH(`date`), `".$params['var']."_per25`, `".$params['var']."_per75`, `".$params['var']."_per5`, `".$params['var']."_per95`, `".$params['var']."_med`, `".$params['var']."_avg`, `".$params['var']."_std` from ".$level."_aod_monthly_ann where station='" . $params['station']."' and YEAR(`date`)=$year order by MONTH(`date`)";
        }
       


        $c = \Propel::getConnection("aeropa");

        $stmt = $c->prepare($query);
        $stmt->execute();

        $xdata = array();
        $stdover=array();
        $stdunder=array();
        $ydata =array();
        $avg=array();

        while ($row = $stmt->fetch()) {

            $xdata[] = $row[0];
            $ydata[] = $row[1];
            $ydata[] = $row[2];
            $ydata[] = $row[3];
            $ydata[] = $row[4];
            $ydata[] = $row[5];
            $avg[] = $row[6];
            $stdover[]=$row[6]+$row[7];
            $stdunder[]=$row[6]-$row[7];
            
            //$c++;
        }

   
        return array( 'xdata'=>array(  'stdover'=>$xdata,'stdunder'=>$xdata,'clim'=>$xdata,'avg'=>$xdata ), 'ydata'=>array( 'stdover'=>$stdover,'stdunder'=>$stdunder,'clim'=>$ydata, 'avg'=>$avg));
    }
}











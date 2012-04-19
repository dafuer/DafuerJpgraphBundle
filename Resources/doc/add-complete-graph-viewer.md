Add complete graph viewer to your Web Application
=================================================

- Requisito: necesitas haber creado previamente un indice de graficas y un archivo 
de generacion de las mismas.

Añade un controlador que maneje el graphviewer. Ejemplo:

    public function graphviewerAction() {
        $query=$this->get('request')->query->all();
        $viewer=$this->forward('DafuerJpgraphBundle:Viewer:viewer',array('viewerpath'=>'YourBundle_graph_viewer','insertgraphroute'=>'YourBundle_graph_insertgraph', 'viewerformpath'=>'YourBundle:Data:graphviewerform', 'graphroute'=>'YourBundle_data_graph') ,$query)->getContent();
        return $this->render('RimaBundle:Data:graphviewer.html.twig', array('viewer'=>$viewer));
    }
   
   Este codigo lo puedes copiar y pegar y sustituir YourBundle por el nombre de tu bundle. 
Como ves, el graphviewer conecta con DafuerJpgraphBundle. Es importante que le envies ciertos parametros:

viewerpath: El nombre de la ruta del viewer, es decir, el nombre de si mismo.
insertgraphroute: El nombre de la ruta que contiene la vista de insertar una nueva grafica.
viewerformpath: El nombre de la ruta que contiene el formulario para gestionar la grafica.
graphroute: Este es la ruta al requisito previo a comenzar este tutorial, el controlador de graficas dinamicas basadas en indice.

Por tanto, habra que crear estas acciones. Pero antes de eso, para terminar con la acciones graphviewer creado
creamos su vista. A continuacion se muestra un codigo de ejemplo de como debe ser esta vista:

{% extends "YourBundle::layout.html.twig" %}
{% block body %}
<h1>Graph Viewer</h1>
               
{{ viewer|raw }}

{% endblock %} 

  Ahora creamos el controlador para viewerformpath, que representa la acciones que genera la vista del formulario general del viewer.
Este es el que permite hacer acciones sobre todas las graficas a la vez (tamanyo, margenes...). Para ello creamos una accion como esta:

    public function graphviewerformAction() {        
        $form = $this->createForm(new ViewerType());
        
        return $this->render('YourBundle:Data:graphviewerform.html.twig',  array('form'=>$form->createView()));
    }     

    Esta acciones hace uso de una clase Type que contiene el formulario de personalizacion. Esta clase puede tener un codigo como el siguiente:

<?php

namespace ACME\YourBundle\Form;


use Dafuer\JpgraphBundle\Form\BaseViewerType;
use Symfony\Component\Form\FormBuilder;

class ViewerType extends BaseViewerType
{

    public function buildForm(FormBuilder $builder, array $options)
    {                   
       $this
            ->addImageDimension($builder, $options)
            ->addMargin($builder, $options)
       ;
    } 

    
}

   El detalle importante es que hereda de BaseViewerType y puede usar sus metodos para
anyadir de manera mas facil los controles necesarios. En esa clase residen los metodos 
addImageDimension y otros que permiten anyadir los controles facilmente. Por ultimo creamos
su vista que sera algo como esto:

        <form id="form_graphviewer_graph_properties_0" class="form-horizontal">
                {{ form_widget(form) }} 
        </form>

   Ahora creamos el insertgraphroute, que es el controlador que contiene la vista de 
como insertar una grafica. Ejemplo de controlador:

    public function insertgraphAction() {
        $formname=$this->get('request')->query->get('formname');
        $combined=$this->get('request')->query->get('combined');

        return $this->forward('DafuerJpgraphBundle:Viewer:insertgraph', array('formviewpath' => 'YourBundle_graph_graphviewerelementform','formname'=>$formname,'combined'=>$combined, 'insertgraphroute' => 'YourBundle_graph_insertgraph', 'formgraphpath'=>'YourBundle:Data:graphviewergraphform', 'graphroute'=>'YourBundle_data_graph'));
    }


  De este codigo solo debes modificar donde ponga your bundle para poner las rutas de tu bundle en concreto. 
Y ahora crear los elementos a los que hace referencia, que son:

formviewpath: Ruta al controlador que genera el formulario de un plot de una grafica (puede haber varios)
insertgraphroute: La ruta de si mismo.
formgraphpath: Ruta al controlador que contiene el formulario con las opciones generales de UNA grafica
graphroute: Este es la ruta al requisito previo a comenzar este tutorial, el controlador de graficas dinamicas basadas en indice.

  Comenzamos creando formviewpath. Este puede tener un codigo como el siguiente:

    public function graphviewerelementformAction() {
        $dataaccess=new Graph\DataSeries;
        
        $dataseries=$dataaccess->getGraphList($this->get('security.context'));

        
        $form = $this->createForm(new GraphElementType(
                                        $this->get('request')->query->get('formname'),
                                        $this->get('request')->query->get('combined',0)
                                        ),
                                  array('dataseries'=>$dataseries,'phlist'=>$phlist)
                                );
        
        return $this->render('RimaBundle:Data:graphviewerelementform.html.twig', array('form'=>$form->createView()));
    }    

   Importante reseñar la primera linea donde obtiene una clase de acceso a datos. Deberas
seleccionar la clase que previamente has creado para realizar graficas en funcion de parametros.
Despues deberas crear el formulario que da forma a cada linea y su vista correspondente. Este 
formulario por ejemplo puede ser asi:

<?php

namespace GOA\RimaBundle\Form;

//use Symfony\Component\Form\AbstractType;
use Dafuer\JpgraphBundle\Form\BaseSingleElementType;
use Symfony\Component\Form\FormBuilder;

class GraphElementType extends BaseSingleElementType
{
    


    public function buildForm(FormBuilder $builder, array $options)
    {       

        $dataseries=$options['data']['dataseries'];
        $builder
                ->add('station', 'choice',array('choices' => array('El_Arenosillos'=>'El_Arenosillos','Palencia'=>  'Palencia'))) // Ejemplo de campo
                ->add('dataserie', 'choice',array('choices' => $dataseries)) // Las dataseries que salen del archivo de indice
        ;
        
       
       $this
            ->addColor($builder, $options)
            ->addMultipleYAxis($builder, $options)
            ->addMarks($builder, $options);
    }
    
}

 Fijate bien en las herencias y trucos que utiliza. Por ultimo creamos su plantilla que sera como esta por lo menos:

{{ form_widget(form) }} 



 Seguimos creando la accion que contiene las opciones de una graficaa aplicables a todos los plots. Esta es de la siguiente manera:

    public function graphviewergraphformAction($formname='0_graphviewer') {
        $form = $this->createForm(new GraphType($formname));
        
        return $this->render('YourBundle:Data:graphviewergraphform.html.twig', array('form'=>$form->createView()));
    }    
 

 Donde GraphType sera...:

    <?php

    namespace GOA\RimaBundle\Form;


    use Dafuer\JpgraphBundle\Form\BaseGraphType;
    use Symfony\Component\Form\FormBuilder;

    class GraphType extends BaseGraphType
    {

        public function buildForm(FormBuilder $builder, array $options)
        {                   
        $this
                ->addScale($builder, $options)
                ->addGraphTitle($builder, $options)   
        ;
        }

    }

Y su vista sera:

{{ form_widget(form) }} 



En resumen:

Antes de empezar deberas tener el archivo de graficas y su indice, por ejemplo:

DataAccess.php
DataAccess.yml

Deberas crear 3 formularios:

GraphElementType.php
GraphType.php
ViewerType.php

En un controlador deberas tener:

1 acciones que realiza la generacion de graficas dinamica + su route
5 acciones para crear el viewer + sus routes









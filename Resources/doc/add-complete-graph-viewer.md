Add complete graph viewer to your Web Application
=================================================

- Requisito: necesitas haber creado previamente un indice de graficas y un archivo 
de generacion de las mismas.

Añade un controlador que maneje el graphviewer. Ejemplo:

    public function graphviewerAction() {
        $query=$this->get('request')->query->all();
        $viewer=$this->forward('DafuerJpgraphBundle:Viewer:viewer',array('viewerpath'=>'YourBundle_graph_viewer','insertgraphroute'=>'YourBundle_graph_insertgraph', 'viewerformpath'=>'YourBundle:Data:graphviewerform') ,$query)->getContent();
        return $this->render('RimaBundle:Data:graphviewer.html.twig', array('viewer'=>$viewer));
    }
   
   Este codigo lo puedes copiar y pegar y sustituir YourBundle por el nombre de tu bundle. 
Como ves, el graphviewer conecta con DafuerJpgraphBundle. Es importante que le envies ciertos parametros:

viewerpath: El nombre de la ruta del viewer, es decir, el nombre de si mismo.
insertgraphroute: El nombre de la ruta que contiene la vista de insertar una nueva grafica.
viewerformpath: El nombre de la ruta que contiene el formulario para gestionar la grafica.

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
addImageDimension y otros que permiten anyadir los controles facilmente.

   Ahora creamos el insertgraphroute, que es el controlador que contiene la vista de 
como insertar una grafica. Ejemplo de controlador:

    public function insertgraphAction() {
        $formname=$this->get('request')->query->get('formname');
        $combined=$this->get('request')->query->get('combined');

        return $this->forward('DafuerJpgraphBundle:Viewer:insertgraph', array('formviewpath' => 'YourBundle_graph_graphviewerelementform','formname'=>$formname,'combined'=>$combined, 'insertgraphroute' => 'YourBundle_graph_insertgraph', 'formgraphpath'=>'YourBundle:Data:graphviewergraphform'));
    }


  De este codigo solo debes modificar donde ponga your bundle para poner las rutas de tu bundle en concreto. 
Y ahora crear los elementos a los que hace referencia, que son:

formviewpath: Ruta al controlador que genera el formulario de un plot de una grafica (puede haber varios)
insertgraphroute: 
formgraphpath:

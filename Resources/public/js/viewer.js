

// Global variables

/**
 * Number of forms
 */
var num=0;


/**
 * Number of lines per form
 */
var singleformsnum=new Array();  

/**
 * Default width for graphs
 */
var graph_viewer_default_options=Array();

/**
 * Add a new graph
 */
function addGraph(ruta,combined){  
     combined = combined || 1;
    var url=ruta+'?formname=g'+num+'_graphviewer'+'&combined='+combined;

    var r = $.ajax({
        type: 'GET',
        url: url,
        async: false
    }).responseText;

    num++;
    
    $("#forms_container").append(r);   
}


/**
 * Remove a graph
 */
function removeGraph(){
    if(num>1){
        num--;
        var table=document.getElementById('table_for_g'+num+'_graphviewer' );
        var padre=table.parentNode;
        var removed=padre.removeChild(table);
    }
} 
   
   
/**
 * Update a graph
 */   
function update(formname,graphroute,force){
    var actualize=false;

    force = force || null;

    if(force==null){
        var check=document.getElementById('autoupdate_'+formname);
        actualize=check.checked;
    }else{
        actualize=force;
    }
        
    base=Routing.generate(graphroute); 
    
    //alert(base+getURLoptions(formname));
    
    if(actualize){
        jQuery.ajax(
        {
            type:'POST',
            dataType:'html',
            success: function(data, textStatus){
                jQuery('#div_'+formname).html(data);
            },
            beforeSend: function(xhr){
                jQuery('#div_'+formname).html("Downloading");//html("<img src='../../dafuerjpgraph/images/loading.gif'></img>");
            },
            complete: function(xhr, status){
                //jQuery('#div_'+formname).html("Downloading");
            },            
            url:base+getURLoptions(formname)
        }
        );       
    }
}   


/**
 * Update all graphs
 */
function update_all(graphroute){
    var i=0;
    
    for(i=0;i<num;i++){
        update("g"+i+"_graphviewer",graphroute,true);
    }
}


/**
 * Add line to graph
 */
function addPlotTo(formname,ruta,graphroute,updategraphs){
    if(updategraphs == undefined) {
        updategraphs = true;
    }

    urlopts=getURLoptions(formname);
    base=Routing.generate(ruta);

    var url=base;
    url+="?combined="+singleformsnum[formname]+"&formname="+formname;

    var r = $.ajax({
        type: 'GET',
        url: url,
        async: false
    }).responseText;

    singleformsnum[formname]++;
    // fin

    $("#more_"+formname).append(r);
    setUpdateListener(formname,graphroute); 
    
    updateFormValues(formname);  
    
    if(updategraphs){
        update(formname,graphroute);
    }
}     


/**
 * Returns a url to make a grah
 */
function getURLoptions(formname){
    var i;
       
    var urlopts="";
    
    if(singleformsnum[formname]==0) urlopts="?format=html";
    else urlopts="?combined="+singleformsnum[formname]+"&format=html";
   

    
    form=$("[id^='"+formname+"_']:input").not("[id$='_properties_0']").toArray();
    
    for (i=0;i<form.length;i++)
    {
        if(typeof form[i]  !== "undefined"){
            if( form[i].value!=""){
                // Find var name
                var from=form[i].name.lastIndexOf('[')+1;
                var to=form[i].name.lastIndexOf(']');
                var elementname=form[i].name.substring(from,to);
                // Find associated form name
                var concreteform=form[i].name.substring(0,from-1);   
                // Find number of associated form
                to=from;
                from=concreteform.lastIndexOf('_')+1;
                number=form[i].name.substring(from,to-1);
                // If is number make array
                if(isFinite(number)) number="["+number+"]";
                else number="";              
                // Add obteined value to result
                urlopts+="&"+elementname+number+"="+form[i].value;
                
            }
        }
    }
    
    
    form=document.getElementById('form_graphviewer_graph_properties_0');
    var options=new Array();
    for (i=0;i<form.elements.length;i++)
    {
        if(typeof form.elements[i]  !== "undefined"){
            if( form.elements[i].value!=""){    
                // Find var name
                var from=form.elements[i].name.lastIndexOf('[')+1;
                var to=form.elements[i].name.lastIndexOf(']');
                var elementname=form.elements[i].name.substring(from,to);
                // Find associated form name
                var concreteform=form.elements[i].name.substring(0,from-1);   
                // Find number of associated form
                to=from;
                from=concreteform.lastIndexOf('_')+1;
                number=form.elements[i].name.substring(from,to-1);
                // If is number make array
                if(isFinite(number)) number="["+number+"]";
                else number="";    
                // Set in obtained option name in array of options set up
                options.push(elementname);
                // Add obteined value to result
                urlopts+="&"+elementname+number+"="+form.elements[i].value;
            }
        }
        
        for(var option in graph_viewer_default_options){
            if(jQuery.inArray(option, options)==-1){
                if(graph_viewer_default_options[option]!=null){
                    urlopts+="&"+option+"[0]="+graph_viewer_default_options[option];
                }
            }
        }
       
    }

    return urlopts;
}


/**
 * Remove a line from a graph
 */
function removePlotTo(formname,graphroute){
    if(singleformsnum[formname]>1){
        singleformsnum[formname]--;
        
        $("[id^='"+formname+"_"+singleformsnum[formname]+"']").not("[id$='_properties_0']").remove();
        //var table=document.getElementById(formname+"_"+singleformsnum[formname]);
        //console.debug(formname+"_"+singleformsnum[formname]);
        //var padre=table.parentNode;
        //var removed=padre.removeChild(table);
        update(formname,graphroute);
    }
}


/**
 * Add linestener methods to form elements. 
 */
function setUpdateListener(formname,graphroute){
    var i;

    $('[data-linkedto]').each(function(element){
        var linked=$(this);
        var root=$("#"+linked.attr('data-linkedto'));
        //alert("root value="+root.val());
        root.change(function() {
             linked.val($(this).val());
        });
    });

    var form=$("[id^='"+formname+"_']:input").not("[id$='_properties_0']").toArray();
    
    for (i=0;i<form.length;i++)
    {
        if(typeof form[i]  !== "undefined"){
            $(form[i]).change(function() {
                update(formname,graphroute);
            });
        }
    }
    

}


/**
 * Returns the full path to the page by adding the images generated routes
 */
function refreshURL(viewerurl){
    // Para obtener la URL total la genero a partir de todas las graficas que haya
    textarea= document.getElementById('url');
    textarea.innerHTML=viewerurl+"?";
    var url=new String();
    
    for(var i=0;i<num;i++){
        tempurl=new String();
        //obtengo el div
        div=document.getElementById('div_g'+i+'_graphviewer'); 
        //El primer elemento del div es la imagen, la obtengo y me quedo con su url
        tempurl=new String(div.firstChild.src);
        //Me quedo con la parte de la url que corresponde a los parametros y obvio la par de www.caelis.uva.es
        tempurl=tempurl.slice(tempurl.indexOf("?"),tempurl.length);
        //Cambio ? por &
        tempurl=tempurl.replace("?", "&");
        //Anyado a cada grafica su numero de grafica

        tempurl=tempurl.replace(/%5B/g, "[");
        tempurl=tempurl.replace(/%5D/g, "]");
        tempurl=tempurl.replace(/=/g, "["+i+"]=");
        //tempurl=tempurl.replace(/%5B/g, "["+i+"][");
        //tempurl=tempurl.replace(/%5D/g, "]");
        url+=tempurl;

    }
    
    // Elimino el primer caracter (&) y concateno la opcion del numero de graficas
    url=url.slice(1,url.length)+"&numofgraphs="+i;
    textarea.innerHTML+=url;
}


/**
 * Make a copy between two forms. 
 */
function copyForms(formfrom,formto){
    var i;
    
    var elements=$("[name^='"+formfrom+"']").not("[id*='lineplot_color']");
    console.debug(elements);
    for (i=0;i<elements.length;i++)
    {
        if(typeof elements[i]  !== "undefined"){

            var original=elements[i].id;
            var reg = new RegExp(formfrom,"gi");
            elementname=original.replace(reg, formto);
            element=$("#"+elementname);

            if(element!=null){
                //alert(elements[i].id+"-"+elements[i].value+"-"+element.val);
                element.val(elements[i].value);
            }

        }
    }    
    
}


/**
 * Update last form from formname with penultimate values
 */
function updateFormValues(formname){
    var to=formname.indexOf('_');
    
    var number=formname.substring(1,to);
    
    var singleform=singleformsnum[formname] - 1;
    
    if(singleform>0){
        copyForms(formname+"_"+(singleform-1),formname+"_"+singleform);
    }else{
        if(number>0){
           var max=singleformsnum["g"+(number-1)+"_graphviewer"] - 1;
           copyForms("g"+(number-1)+"_graphviewer_"+max,formname+"_"+singleform); 
        }
    }
    
}


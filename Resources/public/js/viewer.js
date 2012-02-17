

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
 * Add a new graph
 */
function addGraph(ruta,combined){  
     combined = combined || 1;
    var url=ruta+'?formname='+num+'_graphviewer'+'&combined='+combined;

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
        var table=document.getElementById('table_for_'+num+'_graphviewer' );
        var padre=table.parentNode;
        var removed=padre.removeChild(table);
    }
}

    
     
/**
 * Update a graph
 */   
function update(formname,force){
    var actualize=false;

    force = force || null;

    if(force==null){
        var check=document.getElementById('autoupdate_'+formname);
        actualize=check.checked;
    }else{
        actualize=force;
    }
        
    base=Routing.generate('RimaBundle_data_graph');
    if(actualize){
        jQuery.ajax(
        {
            type:'POST',
            dataType:'html',
            success: function(data, textStatus){
                jQuery('#div_'+formname).html(data);
            },
            url:base+getURLoptions(formname)
        }
        );       
    }
}   


/**
 * Update all graphs
 */
function update_all(){
    var i=0;
    for(i=0;i<num;i++){
        update(i+"_graphviewer",true);
    }
}


/**
 * Add line to graph
 */
function addSingleFormTo(formname,ruta){//,urlopts){
    
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
    setUpdateListener(formname);       
}     


/**
 * Returns a url from make a grah
 */
function getURLoptions(formname){
    var i;
       
    var urlopts="";
    
    if(singleformsnum[formname]==0) urlopts="?format=html";
    else urlopts="?combined="+singleformsnum[formname]+"&format=html";
   

    var form=document.getElementById('form_'+formname);

    for (i=0;i<form.elements.length;i++)
    {
        if(typeof form.elements[i]  !== "undefined"){
            if( form.elements[i].value!=""){
                var from=form.elements[i].name.lastIndexOf('[')+1;
                var to=form.elements[i].name.lastIndexOf(']');
                var elementname=form.elements[i].name.substring(from,to);
                var concreteform=form.elements[i].name.substring(0,from-1);                
                to=from;
                from=concreteform.lastIndexOf('_')+1;
                number=form.elements[i].name.substring(from,to-1);
                if(isFinite(number)) number="["+number+"]";
                else number="";
                urlopts+="&"+elementname+number+"="+form.elements[i].value;
                
            }
        }
    }
    
    return urlopts;
}



/**
 * Remove a line from a graph
 */
function removeSingleFormTo(formname){
    if(singleformsnum[formname]>1){
        singleformsnum[formname]--;
        var table=document.getElementById(formname+"_"+singleformsnum[formname]);
        var padre=table.parentNode;
        var removed=padre.removeChild(table);
        update(formname);
    }
}


/**
 * Add linestener methods to form elements. 
 */
function setUpdateListener(formname){
    var i;
    
    var form=document.getElementById('form_'+formname);

    for (i=0;i<form.elements.length;i++)
    {
        if(typeof form.elements[i]  !== "undefined"){
            $(form.elements[i]).change(function() {
                update(formname);
            });
        }
    }
}


/**
 * Returns the full path to the page by adding the images generated routes
 */
function refreshURL(){
    // Para obtener la URL total la genero a partir de todas las graficas que haya
    textarea= document.getElementById('url');
    textarea.innerHTML="http://localhost/caelis/app_dev.php/rima/graph/viewer?";
    var url=new String();
    for(var i=0;i<num;i++){
        tempurl=new String();
        //obtengo el div
        div=document.getElementById('div_'+i+'_graphviewer'); 
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



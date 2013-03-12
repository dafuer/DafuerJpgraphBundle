How to install
=============

Add bundle to your kernel

```php
            new Dafuer\JpgraphBundle\DafuerJpgraphBundle(),
```

configurar el bundle 


Add dependency to composer.json

"require":
    "dafuer/dafuer-jpgraph-bundle":"*",
    
    "repositories": [
        {
            "type": "package",
            "package":{
                "name": "asial/jpgraph",
                "version": "3.5.0b1",
                "dist":{
                    "url": "http://jpgraph.net/download/download.php?p=5",
                    "type": "tar"
                }
            }
        }
    ]  

And optionally you can add this script to post install and update. In other case
you must remember to execute it when you want to make modifications.

"Dafuer\\JpgraphBundle\\Composer\\ScriptHandler::setupJpgraph"





Now, the bundle is installed. It's moment to configure it.

1. Copiar y pegar el archivo de styles


2. How make graphs

- add to routing.yml:

DafuerJpgraphBundle:
    resource: "@DafuerJpgraphBundle/Resources/config/routing.yml"
    prefix:   /  

He creado el archivos dataSerie.yml y dataSerie.php




Important notes:

    - Dinamic graphs need jquery library import before to call it.

    - If you want to disable imageantialias you must open the file /vendor/jpgraph/src/gd_image.inc.php, find the function SetAntiAliasing and comment this line out like this:

```php
// JpGraphError::RaiseL(25128);//('The function imageantialias() is not available in your PHP installation. Use the GD version that comes with PHP and not the standalone version.')
Update (20110215): Comment out just this line, not the whole function :)
```
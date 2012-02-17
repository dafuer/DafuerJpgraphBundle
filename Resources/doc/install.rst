How to install


- add deps

[DafuerJpgraphBundle]
    git=http://github.com/dafuer/DafuerJpgraphBundle.git
    target=bundles/Dafuer/JpgraphBundle

- vendor install


- add to config.yml:

imports:
    - { resource: @DafuerJpgraphBundle/Resources/config/services.yml }

- add to AppKernel.php
new Dafuer\JpgraphBundle\DafuerJpgraphBundle(),

- register namespaces in autoload:
'Dafuer' => __DIR__.'/../vendor/bundles',

- add to routing.yml:

DafuerJpgraphBundle:
    resource: "@DafuerJpgraphBundle/Resources/config/routing.yml"
    prefix:   /  

- Download and unzip Jpgraph library in vendor/jpgraph


- Dinamic graphs need jquery library import before to call it.


Note: If you want to disable imageantialias you must open the file /vendor/jpgraph/src/gd_image.inc.php, find the function SetAntiAliasing and comment this line out like this:
1
// JpGraphError::RaiseL(25128);//('The function imageantialias() is not available in your PHP installation. Use the GD version that comes with PHP and not the standalone version.')
Update (20110215): Comment out just this line, not the whole function :)

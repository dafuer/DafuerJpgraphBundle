How to install


- add deps

[DafuerJpgraphBundle]
    git=http://github.com/Dafuer/DafuerJpgraphBundle.git
    target=/bundles/Dafuer/JpgraphBundle

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


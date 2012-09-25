<?php


namespace Dafuer\JpgraphBundle\Composer;

/*use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;*/

class ScriptHandler
{
    public static function setupJpgraph($event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not clear the cache.'.PHP_EOL;

            return;
        }

        static::executeCommand($event, $appDir, 'dafuerjpgraph:fixantialiaserror');
        static::executeCommand($event, $appDir, 'dafuerjpgraph:installaconstants');
    }

   
}

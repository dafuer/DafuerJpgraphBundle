<?php

namespace Dafuer\JpgraphBundle\Composer;

use Composer\Script\Event;


/* use Symfony\Component\ClassLoader\ClassCollectionLoader;
  use Symfony\Component\Process\Process;
  use Symfony\Component\Process\PhpExecutableFinder; */
use Symfony\Component\Process\PhpExecutableFinder;
  use Symfony\Component\Process\Process;
  
class ScriptHandler {

    public static function postInstall($event) {
        $extra = $event->getComposer()->getPackage()->getExtra();
        $appDir = $extra['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir (' . $appDir . ') specified in composer.json was not found in ' . getcwd() . ', can not clear the cache.' . PHP_EOL;
            return;
        }

        static::executeCommand($appDir, 'dafuerjpgraph:fixantialiaserror');
        static::executeCommand($appDir, 'dafuerjpgraph:installaconstants');
    }
    
    public static function postUpdate($event) {
        $extra = $event->getComposer()->getPackage()->getExtra();
        $appDir = $extra['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir (' . $appDir . ') specified in composer.json was not found in ' . getcwd() . ', can not clear the cache.' . PHP_EOL;
            return;
        }

        static::executeCommand($appDir, 'dafuerjpgraph:installaconstants');
    }    

    protected static function executeCommand($appDir, $cmd) {

        $phpFinder = new PhpExecutableFinder;

        $php = escapeshellcmd($phpFinder->find());

        $console = escapeshellarg($appDir . '/console');



        $process = new Process($php . ' ' . $console . ' ' . $cmd);

        $process->run(function ($type, $buffer) {
                    echo $buffer;
                });
    }

}

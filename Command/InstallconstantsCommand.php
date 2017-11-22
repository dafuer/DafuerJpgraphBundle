<?php

namespace Dafuer\JpgraphBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallconstantsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dafuerjpgraph:installaconstants')
            ->setDescription('Modify jpgraph constant files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();
        $constants=$container->getParameter("dafuer_jpgraph.constants");
        $path=$container->getParameter("kernel.root_dir").'/../vendor/jpgraph/jpgraph/src/';
        
        
        foreach($constants as $file=>$constant){
            if($file=='jpg_config.inc.php') $file='jpg-config.inc.php'; // It's a exception.
            
            $text=file_get_contents($path.$file);
            foreach($constant as $name=>$value){
                // Make changes
                $text=  preg_replace("/\/\/.*define\('".$name."'/", "define('".$name."'" , $text);
                $text=  preg_replace("/define\('".$name."'/", "define('".$name."', '".$value."'); // Before => " , $text);
            }
            
            // Save changes in file
            $fp=fopen($path.$file, "w");
            fwrite($fp, $text);
            fclose($fp);            
        }
        
        $output->writeln("Configured jpgraph library with the constants in config.yml");
    }
}

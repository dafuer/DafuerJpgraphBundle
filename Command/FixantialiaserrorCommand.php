<?php

namespace Dafuer\JpgraphBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixantialiaserrorCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dafuerjpgraph:fixantialiaserror')
            ->setDescription('Resolve imageantialias error (typical error in jpgraph)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();
        $path=$container->getParameter("kernel.root_dir").'/../vendor/asial/jpgraph/src/';
        
        $text=file_get_contents($path.'gd_image.inc.php');
        
        $text=  preg_replace("/JpGraphError::RaiseL\(25128\);/", "//JpGraphError::RaiseL(25128);" , $text);
       
        $fp=fopen($path.'gd_image.inc.php', "w");
        fwrite($fp, $text);
        fclose($fp);     
        
        $output->writeln("Commented line 110 in jpgraph/src/gd_image_inc to resolve antialias error");
    }
}
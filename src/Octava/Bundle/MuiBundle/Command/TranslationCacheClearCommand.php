<?php
namespace Octava\Bundle\MuiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TranslationCacheClearCommand
 * @package Octava\Bundle\MuiBundle\Command
 */
class TranslationCacheClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('octava:mui:translation:cache-clear')
            ->setDescription('Clear translation cache');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getContainer()->get('octava_mui.translation_manager');
        $helper->clearCache();

        if ($output->isVerbose()) {
            $output->writeln("<info>Translation clear cache completed</info>");
        }
    }
}

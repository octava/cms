<?php
namespace Octava\Bundle\MuiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationDumpDbCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('octava:mui:translation:dump-db')
            ->setDescription('Dumps translation from DB to app/Resources/translations dir');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $globalPath = sprintf('%s/Resources/translations', $container->getParameter('kernel.root_dir'));

        if (!file_exists($globalPath)) {
            if ($output->isVerbose()) {
                $output->writeln(sprintf('Directory <info>%s</info> does not exist, creating ', $globalPath));
            }
            mkdir($globalPath);
        }

        $translationManager = $container->get('octava_mui.translation_manager');
        $writer = $this->getContainer()->get('translation.writer');
        $locales = $container->get('octava_mui.locale_manager')
            ->getActiveList();

        foreach ($locales as $locale) {
            $catalogue = new MessageCatalogue($locale->getAlias());
            $translationManager->fillCatalogue($catalogue);
            if ($output->isVerbose()) {
                $output->writeln(sprintf('Writing <info>%s</info> files to %s', $locale->getAlias(), $globalPath));
            }
            $writer->writeTranslations($catalogue, 'xlf', ['path' => $globalPath]);
        }
    }
}

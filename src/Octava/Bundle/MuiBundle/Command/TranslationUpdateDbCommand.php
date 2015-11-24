<?php
namespace Octava\Bundle\MuiBundle\Command;

use Octava\Bundle\MuiBundle\Entity\Locale;
use Octava\Bundle\MuiBundle\Translation\Translator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\MessageCatalogue;

class TranslationUpdateDbCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('octava:mui:translation:update-db')
            ->setDescription('Loads translation to a DB for all bundles and all locales');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface|Output $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $writer = $container->get('translation.writer');
        $loader = $container->get('translation.loader');
        $translationManager = $container->get('octava_mui.translation_manager');
        $translator = $container->get('translator.default');

        $locales = $container->get('octava_mui.locale_manager')->getActiveList();
        foreach ($locales as $locale) {
            if (!in_array($locale->getAlias(), ['ru', 'en'])) {
                continue;
            }

            $globalCatalogue = new MessageCatalogue($locale->getAlias());
            $resourceDirs = [];
            if ($translator instanceof Translator) {
                foreach ($translator->getResources() as $transFile) {
                    if (is_array($transFile)) {
                        foreach ($transFile as $file) {
                            $globalCatalogue = $this->merge($file, $resourceDirs, $locale, $loader, $globalCatalogue);
                        }
                    } else {
                        $globalCatalogue = $this->merge($transFile, $resourceDirs, $locale, $loader, $globalCatalogue);
                    }
                }
            }

            $writer->writeTranslations($globalCatalogue, 'zdb');

            $logs = $translationManager->getImportLog();
            foreach ($logs as $logItem) {
                if ($output->isVerbose()) {
                    $output->writeln(
                        sprintf(
                            "%s - %s to <info>%s</info> label <info>%s</info> = <comment>%s</comment>",
                            $logItem['locale'],
                            $logItem['action'],
                            $logItem['domain'],
                            $logItem['source'],
                            $logItem['target']
                        )
                    );
                }
            }
            $translationManager->clearImportLogs();
        }

        if ($output->isVerbose()) {
            $output->writeln('<info>Clearing translation cache</info>');
        }
        $translationManager->clearCache();
    }

    /**
     * @param $transFile
     * @param $resourceDirs
     * @param $locale
     * @param $loader
     * @param $globalCatalogue
     * @return MessageCatalogue
     */
    protected function merge($transFile, & $resourceDirs, Locale $locale, TranslationLoader $loader, $globalCatalogue)
    {
        $transFileDir = dirname($transFile);
        if (!in_array($transFileDir, $resourceDirs) && '.' != $transFileDir) {
            $resourceDirs[] = $transFileDir;

            $currentCatalogue = new MessageCatalogue($locale->getAlias());
            $loader->loadMessages($transFileDir, $currentCatalogue);
            $operation = new MergeOperation($globalCatalogue, $currentCatalogue);
            $globalCatalogue = $operation->getResult();
            return $globalCatalogue;
        }
        return $globalCatalogue;
    }
}

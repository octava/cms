<?php
namespace Octava\Bundle\MenuBundle\Command;

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Octava\Bundle\MuiBundle\LocaleManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResetProxyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('octava:menu:translation:reset-proxy')
            ->setDescription('Reset proxy settings for menu elements')
            ->addOption(
                'translation_fallback',
                'b',
                InputOption::VALUE_NONE,
                'Take title and link from default language'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var ContainerInterface */
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.entity_manager');
        /** @var TranslationRepository $translationRepository */
        $translationRepository = $em->getRepository('Gedmo\Translatable\Entity\Translation');
        /** @var LocaleManager $localeManager */
        $localeManager = $container->get('octava_mui.locale_manager');
        $defaultLocale = $container->getParameter('stof_doctrine_extensions.default_locale');

        $counter = 0;
        foreach ($em->getRepository('OctavaMenuBundle:Menu')->findAll() as $item) {
            if ($item->getStructure()->getId()) {
                $translations = $translationRepository->findTranslations($item);

                foreach ($localeManager->getAllAliases() as $locale) {
                    if (!isset($translations[$locale])
                        || !isset($translations[$locale]['proxyTitle'])
                        || !$translations[$locale]['proxyTitle']
                    ) {
                        $translationRepository->translate($item, 'proxyTitle', $locale, 1);
                        $output->writeln('#'.$counter.' Update proxyTitle '.$item->getId().' - '.$locale);
                        $counter++;
                    }

                    if (!isset($translations[$locale])
                        || !isset($translations[$locale]['proxyLink'])
                        || !$translations[$locale]['proxyLink']
                    ) {
                        $translationRepository->translate($item, 'proxyLink', $locale, 1);
                        $output->writeln('#'.$counter.' Update proxyLink '.$item->getId().' - '.$locale);
                        $counter++;
                    }

                    if ($input->getOption('translation_fallback')) {
                        if (empty($translations[$locale]['title'])
                            && !empty($translations[$defaultLocale]['title'])
                        ) {
                            $translationRepository->translate(
                                $item,
                                'title',
                                $locale,
                                $translations[$defaultLocale]['title']
                            );
                            $output->writeln('#'.$counter.' Update title '.$item->getId().' - '.$locale);
                            $counter++;
                        }

                        if (empty($translations[$locale]['link']) && !empty($translations[$defaultLocale]['link'])) {
                            $translationRepository->translate(
                                $item,
                                'link',
                                $locale,
                                $translations[$defaultLocale]['link']
                            );
                            $output->writeln('#'.$counter.' Update link '.$item->getId().' - '.$locale);
                            $counter++;
                        }
                    }
                }
            }
        }

        $em->flush();
    }
}

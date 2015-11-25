<?php
namespace Octava\Bundle\MuiBundle\Translation\Check;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\MuiBundle\LocaleManager;
use Octava\Bundle\MuiBundle\Translation\AbstractCheck;
use Octava\Bundle\MuiBundle\Translation\Translator as OctavaTranslator;
use Octava\Bundle\MuiBundle\TranslationManager;
use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Class DbContent
 * @package Octava\Bundle\MuiBundle\Translation\Check
 */
class DbContent extends AbstractCheck
{
    /**
     * @var LocaleManager
     */
    protected $localeManager;
    /**
     * @var TranslationLoader
     */
    protected $translationLoader;
    /**
     * @var TranslationManager
     */
    protected $translator;
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(
        LocaleManager $localeManager,
        TranslationLoader $translationLoader,
        Translator $translator,
        EntityManager $entityManager
    ) {
        $this->localeManager = $localeManager;
        $this->translationLoader = $translationLoader;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * @return LocaleManager
     */
    public function getLocaleManager()
    {
        return $this->localeManager;
    }

    /**
     * @return TranslationLoader
     */
    public function getTranslationLoader()
    {
        return $this->translationLoader;
    }

    /**
     * @return Translator|OctavaTranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function execute()
    {
        $locales = $this->getLocaleManager()->getActiveList();
        $errors = [];
        foreach ($locales as $locale) {
            if (!in_array($locale->getAlias(), ['ru', 'en'])) {
                continue;
            }

            $globalCatalogue = new MessageCatalogue($locale->getAlias());
            $resourceDirs = [];

            foreach ($this->getTranslator()->getResources() as $resources) {
                foreach ($resources as $transFile) {
                    if ($transFile[0] == DIRECTORY_SEPARATOR) {
                        $transFileDir = dirname($transFile);
                        if (!in_array($transFileDir, $resourceDirs)) {
                            $resourceDirs[] = $transFileDir;

                            $currentCatalogue = new MessageCatalogue($locale->getAlias());
                            $this->getTranslationLoader()->loadMessages($transFileDir, $currentCatalogue);
                            $operation = new MergeOperation($globalCatalogue, $currentCatalogue);
                            $globalCatalogue = $operation->getResult();
                        }
                    }
                }
            }

            $localeErrors = $this->compareDbWithFiles($globalCatalogue);
            $errors[$locale->getAlias()] = $localeErrors;
        }

        foreach ($errors as $localeAlias => $errorLocaleData) {
            if (count($errorLocaleData) > 0) {
                $this->getLogger()->info('<info>'.strtoupper($localeAlias).' update:</info>');
                $this->getLogger()->info('use OctavaTranslationDbMigrateTrait;');
                foreach ($errorLocaleData as $domain => $errorData) {
                    $this->getLogger()->error(
                        '$this->deleteTranslations(\''.$domain.'\', [\''.implode("', '", $errorData).'\']);'
                    );
                }
            }
        }

        $count = 0;
        foreach ($errors as $locale => $errorLocaleData) {
            foreach ($errorLocaleData as $domain => $errorData) {
                $count += count($errorData);
            }
            $this->getLogger()->info('<info>'.strtoupper($locale).'</info> - found errors: '.$count);
        }

        return $count ? 1 : 0;
    }

    protected function compareDbWithFiles(MessageCatalogue $globalCatalogue)
    {
        $domains = $globalCatalogue->getDomains();
        if (!count($domains)) {
            return [];
        }

        $dbData = $this->getEntityManager()
            ->getRepository('OctavaMuiBundle:Translation')
            ->findAll();

        $filesData = $globalCatalogue->all();
        $result = [];
        foreach ($dbData as $dbItem) {
            if (!isset($filesData[$dbItem->getDomain()][$dbItem->getSource()])) {
                $result[$dbItem->getDomain()][] = $dbItem->getSource();
            }
        }

        return $result;
    }
}

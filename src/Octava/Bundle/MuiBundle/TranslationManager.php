<?php
namespace Octava\Bundle\MuiBundle;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\MuiBundle\Entity\Translation;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TranslationManager
 * @package Octava\Bundle\MuiBundle
 */
class TranslationManager
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $importLog = [];

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param $cacheDir
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     */
    public function __construct(
        $cacheDir,
        EntityManager $entityManager,
        LoggerInterface $logger,
        TranslatorInterface $translator
    ) {
        $this->cacheDir = $cacheDir;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * @return TranslatorInterface
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

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Очищает кэш переводов
     */
    public function clearCache()
    {
        $translationCacheDir = sprintf("%s/translations", $this->getCacheDir());
        if (!file_exists($translationCacheDir) || !is_dir($translationCacheDir)) {
            return false;
        }
        $finder = Finder::create()
            ->in($translationCacheDir)
            ->files();

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            unlink($file->getPathname());
        }

        return true;
    }

    public function getDomains()
    {
        $data = $this->getEntityManager()
            ->getRepository('OctavaMuiBundle:Translation')
            ->getDomainsQueryBuilder()
            ->getQuery()
            ->getArrayResult();

        $ret = [];
        foreach ($data as $a) {
            $ret[] = $a['domain'];
        }

        return $ret;
    }

    /**
     * Сохраняет переводы в БД
     * @param MessageCatalogue $messages
     * @param bool $overwriteExisting - перезаписывать существующие переводы
     * @param bool $addNew - добавлять новые метки если они найдены в каталоге
     */
    public function saveTranslations(MessageCatalogue $messages, $overwriteExisting = false, $addNew = false)
    {
        $locale = $messages->getLocale();
        $domains = $messages->getDomains();

        if (!count($domains)) {
            return;
        }

        $logger = $this->getLogger();
        $logger->info("Start import");

        $entities = $this->getEntityManager()->getRepository('OctavaMuiBundle:Translation')
            ->getTranslationsByDomains($domains);

        $currentData = [];
        foreach ($entities as $item) {
            $currentData[$item->getDomain()][$item->getSource()] = $item;
        }

        foreach ($domains as $domain) {
            foreach ($messages->all($domain) as $source => $target) {
                $entity = null;
                if (isset($currentData[$domain]) && isset($currentData[$domain][$source])) {
                    $entity = $currentData[$domain][$source];
                }

                $needToPersist = false;
                if ($entity instanceof Translation) {
                    $translation = $entity->getTranslations();

                    if (!isset($translation[$locale]) ||
                        ($translation[$locale] == '' && $target != '') ||
                        ($overwriteExisting && $translation[$locale] != $target)
                    ) {
                        $oldValue = isset($translation[$locale]) ? $translation[$locale] : null;
                        $translation[$locale] = $target;
                        ksort($translation);
                        $entity->setTranslations($translation);
                        $needToPersist = true;

                        $this->importLog[] = [
                            'action' => 'update',
                            'locale' => $locale,
                            'domain' => $domain,
                            'source' => $source,
                            'old_value' => $oldValue,
                            'target' => $target,
                        ];
                    }
                } else {
                    if ($addNew) {
                        $entity = new Translation();
                        $entity->setDomain($domain)
                            ->setSource($source)
                            ->setTranslations([$locale => $target]);
                        $needToPersist = true;

                        $this->importLog[] = [
                            'action' => 'add',
                            'locale' => $locale,
                            'domain' => $domain,
                            'source' => $source,
                            'old_value' => '',
                            'target' => $target,
                        ];
                    }
                }

                if ($needToPersist) {
                    $this->getEntityManager()->persist($entity);
                }
            }
        }

        foreach ($this->importLog as $message) {
            $logger->info(json_encode($message));
        }
        $logger->info("Finish import");

        $this->getEntityManager()->flush();
        $this->clearCache();
    }

    /**
     * Получить лог последнего импорта
     * @return array
     */
    public function getImportLog()
    {
        return $this->importLog;
    }

    /**
     * Очистить лог импорта
     */
    public function clearImportLogs()
    {
        $this->importLog = [];
    }

    /**
     * Заполняет каталог данными из БД
     * @param MessageCatalogue $catalogue
     */
    public function fillCatalogue(MessageCatalogue $catalogue)
    {
        /** @var Translation[] $res */
        $res = $this->getEntityManager()->getRepository('OctavaMuiBundle:Translation')->findAll();
        $messages = [];
        foreach ($res as $object) {
            $translations = $object->getTranslations();
            if (empty($messages[$object->getDomain()])) {
                $messages[$object->getDomain()] = [];
            }
            if (!empty($translations[$catalogue->getLocale()])) {
                $messages[$object->getDomain()][$object->getSource()] = $translations[$catalogue->getLocale()];
            }
        }

        foreach ($messages as $domain => $data) {
            $catalogue->add($data, $domain);
        }
    }

    public function fillExtendedCatalogue(
        MessageCatalogue $catalogue,
        $sourceLocale,
        $targetLocale,
        $domains = [],
        $emptyTarget = false,
        $excludeAdmin = false
    ) {

        /** @var Translation[] $res */
        $res = $this->getEntityManager()->getRepository('OctavaMuiBundle:Translation')
            ->getTranslationsByDomains($domains, $excludeAdmin);

        $messages = [];
        foreach ($res as $object) {
            $translations = $object->getTranslations();
            $domain = $object->getDomain();

            if (empty($messages[$domain])) {
                $messages[$domain] = [];
            }

            if (!empty($translations[$sourceLocale])) {
                if (!$emptyTarget || empty($translations[$targetLocale])) {
                    $messages[$domain][] = [
                        'id' => $object->getSource(),
                        'source' => $translations[$sourceLocale],
                        'target' => empty($translations[$targetLocale]) ? '' : $translations[$targetLocale],
                    ];
                }
            }
        }

        foreach ($messages as $domain => $data) {
            if (!empty($data)) {
                $catalogue->add($data, $domain);
            }
        }
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        if (is_null($domain)) {
            $class = get_called_class();
            $classSegments = explode('\\', $class);
            $domain = $classSegments[0].$classSegments[1];
        }

        return $this->getTranslator()->trans($id, $parameters, $domain, $locale);
    }
}

<?php
namespace Octava\Bundle\MuiBundle\Translation\Check;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\MuiBundle\Entity\Translation;
use Octava\Bundle\MuiBundle\LocaleManager;
use Octava\Bundle\MuiBundle\Translation\AbstractCheck;

/**
 * Class Placeholders
 * @package Octava\Bundle\MuiBundle\Translation\Check
 */
class Placeholders extends AbstractCheck
{
    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(LocaleManager $localeManager, EntityManager $entityManager)
    {
        $this->localeManager = $localeManager;
        $this->entityManager = $entityManager;
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
        $counter = $this->checkExtTranslation();
        $counter += $this->checkTranslation();

        if ($counter) {
            $this->getLogger()->error(sprintf('<error>Found %d mistakes</error>', $counter));
        }
    }

    public function findPlaceholders($text)
    {
        $pattern = '/'.preg_quote('{{').'(.*?)'.preg_quote('}}').'/';
        $res = preg_match_all($pattern, $text, $matches);
        $result = [];
        if ($res) {
            $result = array_map('trim', $matches[1]);
        }

        return $result;
    }

    /**
     * @return LocaleManager
     */
    public function getLocaleManager()
    {
        return $this->localeManager;
    }

    protected function checkExtTranslation()
    {
        $result = 0;

        $defaultLocale = $this->getLocaleManager()->getDefaultLocaleAlias();
        $translationRepository = $this->getEntityManager()
            ->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        $queryBuilder = $translationRepository->createQueryBuilder('tr')
            ->orderBy('tr.objectClass')
            ->addOrderBy('tr.foreignKey')
            ->addOrderBy('tr.field')
            ->addOrderBy('tr.locale');
        $rows = $queryBuilder->getQuery()->getResult();

        $langRows = [];
        foreach ($rows as $row) {
            /** @var \Gedmo\Translatable\Entity\Translation $row */
            $key = md5($row->getForeignKey().$row->getObjectClass().$row->getField());
            if (!isset($langRows[$key])) {
                $langRows[$key] = [];
            }
            $langRows[$key][$row->getLocale()] = $row;
        }

        foreach ($langRows as $slice) {
            /** @var \Gedmo\Translatable\Entity\Translation[] $slice */
            if (!isset($slice[$defaultLocale])) {
                continue;
            }

            $defaultObject = $slice[$defaultLocale];
            unset($slice[$defaultLocale]);

            if (!$defaultObject->getContent()) {
                continue;
            }

            $originalPlaceholder = $this->findPlaceholders($defaultObject->getContent());
            foreach ($slice as $subObject) {
                if (!$subObject->getContent()) {
                    continue;
                }

                $placeholder = $this->findPlaceholders($subObject->getContent());

                $firstDiff = array_diff($originalPlaceholder, $placeholder);
                if ($firstDiff) {
                    $this->getLogger()->error(
                        'Ext Translation error:',
                        [
                            'id' => $subObject->getId(),
                            'key' => $subObject->getForeignKey(),
                            'entity' => $subObject->getObjectClass(),
                            'lang' => $subObject->getLocale(),
                            'placeholders' => implode(',', $firstDiff),
                        ]
                    );

                    $result++;
                }
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function checkTranslation()
    {
        $result = 0;

        $defaultLocale = $this->getLocaleManager()->getDefaultLocaleAlias();

        $repository = $this->getEntityManager()
            ->getRepository('OctavaMuiBundle:Translation');
        /** @var Translation[] $rows */
        $rows = $repository->createQueryBuilder('tr')
            ->select('tr')
            ->getQuery()->getResult();

        foreach ($rows as $row) {
            $translations = $row->getTranslations();
            if (!isset($translations[$defaultLocale])) {
                continue;
            }
            $originalPlaceholder = $this->findPlaceholders($translations[$defaultLocale]);
            unset($translations[$defaultLocale]);
            if ($originalPlaceholder) {
                foreach ($translations as $lang => $content) {
                    if (!$content) {
                        continue;
                    }

                    $placeholder = $this->findPlaceholders($content);

                    $firstDiff = array_diff($originalPlaceholder, $placeholder);
                    if ($firstDiff) {
                        $this->getLogger()->error(
                            'Translation table error',
                            [
                                'id' => $row->getId(),
                                'domain' => $row->getDomain(),
                                'source' => $row->getSource(),
                                'lang' => $lang,
                                'placeholders' => implode(',', $firstDiff),
                            ]
                        );
                        $result++;
                    }
                }
            }
        }

        return $result;
    }
}

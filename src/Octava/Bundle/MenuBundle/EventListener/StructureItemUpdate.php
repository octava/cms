<?php
namespace Octava\Bundle\MenuBundle\EventListener;

use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Octava\Bundle\MenuBundle\Entity\Menu;
use Octava\Bundle\StructureBundle\Event\ItemUpdateEvent;
use Octava\Bundle\StructureBundle\StructureEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class StructureItemUpdate
 * @package Octava\Bundle\MenuBundle\EventListener
 */
class StructureItemUpdate implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var FilesystemCache
     */
    protected $menuCache;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var array
     */
    protected $updatedItems = [];

    /**
     * @param EntityManager $entityManager
     * @param FilesystemCache $menuCache
     */
    public function __construct(EntityManager $entityManager, FilesystemCache $menuCache, $defaultLocale)
    {
        $this->entityManager = $entityManager;
        $this->menuCache = $menuCache;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            StructureEvents::ITEM_UPDATE => [
                'onStructureItemUpdate',
            ],
        ];
    }

    /**
     * @param ItemUpdateEvent $event
     */
    public function onStructureItemUpdate(ItemUpdateEvent $event)
    {
        $structureItem = $event->getStructureItem();
        $menuItems = $this->entityManager->getRepository('OctavaMenuBundle:Menu')
            ->getByStructureId($structureItem->getId());

        $this->update($menuItems);

        $this->menuCache->deleteAll();
    }

    /**
     * @param Menu[] $menuItems
     */
    protected function update($menuItems)
    {
        if (!count($menuItems)) {
            return;
        }

        foreach ($menuItems as $menuItem) {
            $this->updateItem($menuItem);
            /** @var Menu[] $children */
            $children = $menuItem->getChildren();
            if (count($children)) {
                $this->update($children);
            }
        }
    }

    /**
     * @param Menu $menuItem
     */
    protected function updateItem($menuItem)
    {
        if (in_array($menuItem->getId(), $this->updatedItems)) {
            return;
        }

        $this->updatedItems[] = $menuItem->getId();

        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        $structureItem = $menuItem->getStructure();
        if (is_null($structureItem)) {
            return;
        }

        $structureTranslations = $translationRepository->findTranslations($structureItem);
        $translations = $translationRepository->findTranslations($menuItem);

        foreach ($translations as $locale => $translation) {
            if (!empty($translation['proxyTitle'])) {
                $title = empty($structureTranslations[$this->defaultLocale]['title']) ?
                    $structureItem->getTitle() :
                    $structureTranslations[$this->defaultLocale]['title'];

                if (!empty($structureTranslations[$locale]['title'])) {
                    $title = $structureTranslations[$locale]['title'];
                }

                $translationRepository->translate($menuItem, 'title', $locale, $title);
            }

            if (!empty($translation['proxyLink'])) {
                $link = empty($structureTranslations[$this->defaultLocale]['path']) ?
                    $structureItem->getPath() :
                    $structureTranslations[$this->defaultLocale]['path'];

                if (!empty($structureTranslations[$locale]['path'])) {
                    $link = $structureTranslations[$locale]['path'];
                }

                $translationRepository->translate($menuItem, 'link', $locale, $link);
            }
        }
    }
}

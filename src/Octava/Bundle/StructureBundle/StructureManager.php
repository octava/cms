<?php
namespace Octava\Bundle\StructureBundle;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\StructureBundle\Entity\Structure;
use Octava\Bundle\StructureBundle\Event\ItemUpdateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class StructureManager
{
    /**
     * @var EntityManager
     */
    protected $entityManger;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Structure
     */
    protected $currentItem;

    /**
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     */
    public function __construct(
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack
    ) {
        $this->entityManger = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * Update all path values
     * @param int $parentId
     * @return $this
     */
    public function update($parentId = 0)
    {
        $repository = $this->entityManger->getRepository('OctavaStructureBundle:Structure');
        $repository->updateAllPathValues($parentId);
        foreach ($repository->getFlatTree() as $object) {
            $event = new ItemUpdateEvent($object);
            $this->eventDispatcher->dispatch(StructureEvents::ITEM_UPDATE, $event);
        }

        return $this;
    }

    /**
     * @return null|Structure
     */
    public function getCurrentItem()
    {
        $request = $this->requestStack->getMasterRequest();
        if (empty($this->currentItem) && $request && $request->attributes->has(Structure::ROUTING_ID_NAME)) {
            $structureId = $request->attributes->get(Structure::ROUTING_ID_NAME);
            $this->currentItem = $this->entityManger
                ->getRepository('OctavaStructureBundle:Structure')
                ->getById($structureId);
        }

        return $this->currentItem;
    }
}

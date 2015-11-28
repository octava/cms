<?php
namespace Octava\Bundle\StructureBundle;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\StructureBundle\Event\ItemUpdateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @param EntityManager $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManger = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
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
}

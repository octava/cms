<?php
namespace Octava\Bundle\StructureBundle\Event;

use Octava\Bundle\StructureBundle\Entity\Structure;
use Symfony\Component\EventDispatcher\Event;

class ItemUpdateEvent extends Event
{
    /**
     * @var Structure
     */
    protected $structureItem;

    public function __construct(Structure $structureItem)
    {
        $this->structureItem = $structureItem;
    }

    /**
     * @return Structure
     */
    public function getStructureItem()
    {
        return $this->structureItem;
    }
}

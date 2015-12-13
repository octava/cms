<?php
namespace Octava\Bundle\StructureBundle\Twig;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\StructureBundle\StructureManager;

/**
 * Class StructureExtension
 * @package Octava\Bundle\StructureBundle\Twig
 */
class StructureExtension extends \Twig_Extension
{
    /**
     * @var StructureManager
     */
    protected $structureManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(StructureManager $structureManager, EntityManager $entityManager)
    {
        $this->structureManager = $structureManager;
        $this->entityManager = $entityManager;
    }

    public function getName()
    {
        return 'octava_structure';
    }

    public function getFunctions()
    {
        return [
            'structure_title' => new \Twig_SimpleFunction('structure_title', [$this, 'getStructureTitle']),
            'structure_text' => new \Twig_SimpleFunction('structure_text', [$this, 'getStructureText']),
            'structure_id' => new \Twig_SimpleFunction('structure_id', [$this, 'getStructureId']),
        ];
    }

    public function getStructureTitle()
    {
        $currentItem = $this->structureManager->getCurrentItem();

        return !is_null($currentItem) ? $currentItem->getTitle() : '';
    }

    public function getStructureText()
    {
        $currentItem = $this->structureManager->getCurrentItem();
        if (is_null($currentItem)) {
            return '';
        }

        $text = $currentItem->getDescription();
        if (strlen(trim(strip_tags($text))) > 0) {
            return $text;
        } else {
            return '';
        }
    }

    public function getStructureId()
    {
        $currentItem = $this->structureManager->getCurrentItem();

        return !is_null($currentItem) ? $currentItem->getId() : 0;
    }
}

<?php
namespace Octava\Bundle\AdminMenuBundle;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\AdminMenuBundle\Entity\AdminMenu;
use Octava\Bundle\AdminMenuBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Translation\TranslatorInterface;

class AdminMenuManager
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var AdminMenu[]
     */
    protected $adminObjects = [];

    /**
     * @var AdminMenu[]
     */
    protected $tree = [];

    /**
     * @var FilterInterface[]
     */
    protected $filters = [];

    public function __construct(Pool $pool, TranslatorInterface $translator, EntityManager $entityManager)
    {
        $this->pool = $pool;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }
}

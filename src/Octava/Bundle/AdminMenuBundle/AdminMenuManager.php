<?php
namespace Octava\Bundle\AdminMenuBundle;

use Doctrine\ORM\EntityManager;
use Octava\Bundle\AdminMenuBundle\Entity\AdminMenu;
use Octava\Bundle\AdminMenuBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Admin\Admin;
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

    public function getAdminChoices()
    {
        $tree = $this->getTree();
        $existingModules = [];
        foreach ($tree as $row) {
            if ($row->getType() == AdminMenu::TYPE_MODULE) {
                $existingModules[] = $row->getServiceId();
            }
        }
        $ret = [];
        foreach ($this->pool->getDashboardGroups() as $group) {
            foreach ($group['items'] as $admin) {
                /** @var Admin $admin */
                $class = get_class($admin);
                $ret[$class] = [
                    'value' => $this->translator->trans(
                        $admin->getLabel(),
                        [],
                        $this->getBundleName($class)
                    ),
                    'en' => $this->translator->trans(
                        $admin->getLabel(),
                        [],
                        $this->getBundleName($class),
                        'en'
                    ),
                    'ru' => $this->translator->trans(
                        $admin->getLabel(),
                        [],
                        $this->getBundleName($class),
                        'ru'
                    ),
                    'used' => in_array($class, $existingModules),
                ];
            }
        }

        return $ret;
    }

    /**
     * @return AdminMenu[]
     */
    public function getTree()
    {
        if (empty($this->tree)) {
            $this->tree = $this->entityManager
                ->getRepository('OctavaAdminMenuBundle:AdminMenu')
                ->getMenuTree();
        }
        foreach ($this->filters as $filter) {
            $this->tree = $filter->filter($this->tree);
        }

        return $this->tree;
    }

    public function getFolderChoices()
    {
        $ret = [];
        $rows = $this->getTree();
        foreach ($rows as $row) {
            if ($row->getType() == AdminMenu::TYPE_FOLDER) {
                $ret[$row->getId()] = str_repeat('.', $row->getLevel() * 4)
                    .$row->getTitle();
            }
        }

        return $ret;
    }

    public function getBundleName($class)
    {
        $a = explode('\\', $class);

        return $a[0].$a[1];
    }

    /**
     * @param string $class
     * @return Admin
     */
    public function getAdminObject($class)
    {
        if (empty($this->adminObjects)) {
            foreach ($this->pool->getDashboardGroups() as $group) {
                foreach ($group['items'] as $admin) {
                    /** @var Admin $admin */
                    $key = get_class($admin);
                    $this->adminObjects[$key] = $admin;
                }
            }
        }

        return empty($this->adminObjects[$class]) ? null : $this->adminObjects[$class];
    }
}

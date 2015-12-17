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
        $poll = $this->pool;
        foreach ($poll->getAdminGroups() as $name => $group) {
            $domain = $group['label_catalogue'];
            foreach ($group['items'] as $item) {
                $serviceId = $item['admin'];
                $label = $item['label'];

                if (in_array($serviceId, $existingModules)) {
                    continue;
                }

                $ret[$serviceId] = $this->translator->trans(
                    $label,
                    [],
                    $domain
                );
            }
        }

        \Symfony\Component\VarDumper\VarDumper::dump($ret);


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
}

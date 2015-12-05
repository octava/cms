<?php
namespace Octava\Bundle\AdminMenuBundle;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Octava\Bundle\AdminMenuBundle\Entity\AdminMenu;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class Builder
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var MenuProviderInterface
     */
    protected $provider;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var AdminMenuManager
     */
    protected $adminMenuManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Pool $pool,
        FactoryInterface $factory,
        MenuProviderInterface $provider,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        AdminMenuManager $adminMenuManager,
        LoggerInterface $logger
    ) {
        $this->pool = $pool;
        $this->factory = $factory;
        $this->provider = $provider;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
        $this->adminMenuManager = $adminMenuManager;
        $this->logger = $logger;
    }

    /**
     * Builds sidebar menu.
     *
     * @return ItemInterface
     */
    public function createSidebarMenu()
    {
        $menu = $this->factory->createItem(
            'root',
            [
                'extras' => [
                    'request' => $this->requestStack->getMasterRequest(),
                ],
            ]
        );

        $tree = $this->adminMenuManager->getTree();
        $this->generateMenu($menu, $tree);

        $event = new ConfigureMenuEvent($this->factory, $menu);
        $this->eventDispatcher->dispatch(ConfigureMenuEvent::SIDEBAR, $event);

        return $event->getMenu();
    }

    /**
     * @param ItemInterface $menu
     * @param AdminMenu[] $tree
     * @param int $level
     */
    protected function generateMenu(&$menu, &$tree, $level = 0)
    {
        while (!empty($tree)) {
            $item = array_shift($tree);

            $type = $item->getType();
            $itemLabel = $item->getTitle();
            $itemLevel = $item->getLevel();

            if ($itemLevel == $level) {
                $options = [];
                if (AdminMenu::TYPE_FOLDER !== $type) {
                    $admin = $this->adminMenuManager->getAdminObject($item->getAdminClass());
                    if ($admin) {
                        $options = $admin->generateMenuUrl('list');
                        $options['extras'] = [
                            'admin' => $admin,
                        ];
                    } else {
                        $this->logger->alert('Admin not found for class', [$item->getAdminClass()]);
                    }
                }
                $child = $menu->addChild($itemLabel, $options);
            } elseif ($itemLevel > $level) {
                array_unshift($tree, $item);
                $this->generateMenu($child, $tree, $itemLevel);
            } else {
                array_unshift($tree, $item);
                break;
            }
        }
    }
}

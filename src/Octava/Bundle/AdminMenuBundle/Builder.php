<?php
namespace Octava\Bundle\AdminMenuBundle;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Octava\Bundle\AdminMenuBundle\Entity\AdminMenu;
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

    public function __construct(
        Pool $pool,
        FactoryInterface $factory,
        MenuProviderInterface $provider,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        AdminMenuManager $adminMenuManager
    ) {
        $this->pool = $pool;
        $this->factory = $factory;
        $this->provider = $provider;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
        $this->adminMenuManager = $adminMenuManager;
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

//        foreach ($this->pool->getAdminGroups() as $name => $group) {
//            $attributes = [];
//
//            $extras = [
//                'icon' => $group['icon'],
//                'label_catalogue' => $group['label_catalogue'],
//                'roles' => $group['roles'],
//            ];
//
//            // Check if the menu group is built by a menu provider
//            if (isset($group['provider'])) {
//                $subMenu = $this->provider->get($group['provider']);
//
//                $menu
//                    ->addChild($subMenu)
//                    ->setExtras(array_merge($subMenu->getExtras(), $extras))
//                    ->setAttributes(array_merge($subMenu->getAttributes(), $attributes));
//
//                continue;
//            }
//
//            // The menu group is built by config
//            $menu->addChild(
//                $name,
//                [
//                    'label' => $group['label'],
//                    'attributes' => $attributes,
//                    'extras' => $extras,
//                ]
//            );
//
//            foreach ($group['items'] as $item) {
//                if (isset($item['admin']) && !empty($item['admin'])) {
//                    $admin = $this->pool->getInstance($item['admin']);
//
//                    // skip menu item if no `list` url is available or user doesn't have the LIST access rights
//                    if (!$admin->hasRoute('list') || !$admin->isGranted('LIST')) {
//                        continue;
//                    }
//
//                    $label = $admin->getLabel();
//                    $options = $admin->generateMenuUrl('list');
//                    $options['extras'] = [
//                        'translation_domain' => $admin->getTranslationDomain(),
//                        'admin' => $admin,
//                    ];
//                } else {
//                    $label = $item['label'];
//                    $options = [
//                        'route' => $item['route'],
//                        'routeParameters' => $item['route_params'],
//                        'extras' => [
//                            'translation_domain' => $group['label_catalogue'],
//                        ],
//                    ];
//                }
//
//                $menu[$name]->addChild($label, $options);
//            }
//
//            if (0 === count($menu[$name]->getChildren())) {
//                $menu->removeChild($name);
//            }
//        }

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
                    $options = $admin->generateMenuUrl('list');
                    $options['extras'] = [
                        'admin' => $admin,
                    ];
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

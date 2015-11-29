<?php
namespace Octava\Bundle\AdminMenuBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Octava\Bundle\AdminMenuBundle\Entity\AdminMenu;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAdminMenu extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $container = $this->getContainer();
        $poll = $this->getContainer()->get('sonata.admin.pool');
        $translator = $this->getContainer()->get('translator');

        foreach ($poll->getAdminGroups() as $name => $group) {
            $parent = new AdminMenu();
            $parent->setTitle($translator->trans($group['label'], [], $group['label_catalogue']));
            $parent->setType(AdminMenu::TYPE_FOLDER);

            $manager->persist($parent);

            foreach ($group['items'] as $item) {
                $menu = new AdminMenu();
                $menu->setTitle($translator->trans($item['label'], [], $group['label_catalogue']));
                $menu->setType(AdminMenu::TYPE_MODULE);
                $menu->setAdminClass(get_class($container->get($item['admin'])));
                $menu->setParent($parent);

                $manager->persist($menu);
            }
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 40;
    }
}

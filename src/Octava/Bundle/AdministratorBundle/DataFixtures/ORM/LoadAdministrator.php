<?php
namespace Octava\Bundle\AdministratorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Octava\Bundle\AdministratorBundle\Entity\Administrator;
use Octava\Bundle\AdministratorBundle\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAdministrator extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
        $group = new Group();
        $group->setName('admin');
        $manager->persist($group);

        $administrator = new Administrator();
        $administrator->setUsername('admin');
        $administrator->setSalt('11oea43r7aqoggsc8k4o0g80wkkwkss');
        $encoder = $this->container
            ->get('security.encoder_factory')
            ->getEncoder($administrator);
        $administrator->setPassword($encoder->encodePassword('admin', $administrator->getSalt()));
        $administrator->setEnabled(true);
        $administrator->setEmail('admin@example.com');
        $administrator->addGroup($group);
        $administrator->setShowHidden(true);
        $manager->persist($administrator);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 10;
    }
}

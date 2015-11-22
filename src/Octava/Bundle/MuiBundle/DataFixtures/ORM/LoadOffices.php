<?php
namespace Octava\Bundle\MuiBundle\DataFixtures\ORM;

class LoadOffices extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
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
        $data = [
            ['English EN', 'en', true],
        ];

        foreach ($data as $position => $item) {
            list($name, $alias, $state) = $item;
            $locale = new Locale();
            $locale->setName($name);
            $locale->setAlias($alias);
            $locale->setState($state);

            $manager->persist($locale);
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
        return 20;
    }
}

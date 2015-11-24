<?php
namespace Octava\Bundle\MuiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Octava\Bundle\MuiBundle\Entity\Office;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $entity = new Office();
        $entity->setName('En Office');
        $entity->setAlias('en');
        $entity->setEmail('test@test.com');
        $entity->setProtocol('http');
        $entity->setHost($this->container->getParameter('hosts.root'));
        $entity->setRelatedUrl(null);
        $entity->setDefaultLanguage('en');
        $entity->setRecognizeLanguage('en');
        $entity->setAvailableLanguages(['en', 'ru']);
        $entity->setCurrencies(['EUR', 'USD']);
        $manager->persist($entity);

        $entity = new Office();
        $entity->setName('Ru Office');
        $entity->setAlias('ru');
        $entity->setEmail('test@test.com');
        $entity->setProtocol('http');
        $entity->setHost($this->container->getParameter('hosts.root'));
        $entity->setRelatedUrl(null);
        $entity->setDefaultLanguage('ru');
        $entity->setRecognizeLanguage('ru');
        $entity->setAvailableLanguages(['en', 'ru']);
        $entity->setCurrencies(['EUR', 'USD', 'RUB']);
        $manager->persist($entity);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 30;
    }
}

<?php
namespace Octava\Bundle\MuiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Octava\Bundle\MuiBundle\Entity\Locale;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadLocales extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
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
            ['English', 'en', true],
            ['Русский', 'ru', true],
            ['中文简体', 'zh', false],
            ['中文繁體', 'tw', false],
            ['Indonesia', 'id', false],
            ['Melayu', 'ms', false],
            ['Italian', 'it', false],
            ['Español', 'es', false],
            ['Português', 'pt', false],
            ['Polish', 'pl', false],
            ['Deutsch', 'de', false],
            ['Suomi', 'fi', false],
            ['Norsk', 'no', false],
            ['Svenska', 'sv', false],
            ['Dansk', 'da', false],
            ['Nederlands', 'nl', false],
            ['हिंदी', 'hi', false],
            ['اللغة العربية', 'ar', false],
            ['Українська', 'ua', false],
            ['ภาษาไทย', 'th', false],
            ['Français', 'fr', false],
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

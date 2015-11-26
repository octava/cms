<?php
namespace Octava\Bundle\MuiBundle\Translation\Handler;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManager;
use Octava\Bundle\MuiBundle\Entity\Translation;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Class Loader
 * @package Octava\Bundle\MuiBundle\Translation\Handler
 */
class Loader implements LoaderInterface
{
    /**
     * @var EntityManager
     */
    protected $manager;
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(EntityManager $manager, Logger $logger)
    {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    /**
     * Loads a locale.
     *
     * @param mixed $resource A resource
     * @param string $locale A locale
     * @param string $domain The domain
     *
     * @return MessageCatalogue A MessageCatalogue instance
     *
     * @api
     *
     * @throws NotFoundResourceException when the resource cannot be found
     * @throws InvalidResourceException  when the resource cannot be loaded
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $catalog = new MessageCatalogue($locale);
        try {
            /** @var Translation[] $entities */
            $entities = $this->manager->getRepository('OctavaMuiBundle:Translation')
                ->findBy(['domain' => $domain]);

            foreach ($entities as $entity) {
                $trans = $entity->getTranslations();
                if (!empty($trans[$locale])) {
                    $catalog->set($entity->getSource(), $trans[$locale], $domain);
                }
            }
        } catch (ConversionException $e) {
            // problem with conversion (suppose bad serialize)
            $this->getLogger()->error($e->getMessage());
        } catch (TableNotFoundException $e) {
            //problem with cache:warmup
            $this->getLogger()->warning($e->getMessage());
        }

        return $catalog;
    }

    /**
     * @return EntityManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}

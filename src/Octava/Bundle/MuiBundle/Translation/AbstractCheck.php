<?php
namespace Octava\Bundle\MuiBundle\Translation;

use Monolog\Handler\NullHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Monolog\Logger;

abstract class AbstractCheck
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = new Logger(__CLASS__, [new NullHandler()]);
        }

        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    abstract public function execute();
}

<?php
namespace Octava\Bundle\MuiBundle\Routing;

use Monolog\Handler\StreamHandler;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher as BaseMatcher;

class RedirectableUrlMatcher extends BaseMatcher
{
    public function redirect($path, $route, $scheme = null, $logPath = null)
    {
        if ($logPath) {
            $logger = new Logger('redirect');
            $logger->pushHandler(new StreamHandler($logPath, Logger::INFO));
            $logger->addInfo(
                'redirect',
                [
                    'url' => $this->context->getPathInfo().'?'.$this->context->getQueryString(),
                    'location' => $path,
                    'method' => $this->context->getMethod(),
                ]
            );
        }

        return parent::redirect($path, $route, $scheme);
    }
}

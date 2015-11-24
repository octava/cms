<?php
namespace Octava\Bundle\MuiBundle\EventListener;

use Octava\Bundle\MuiBundle\Entity\Office;
use Octava\Bundle\MuiBundle\Exception\OfficeNotFoundException;
use Octava\Bundle\MuiBundle\OfficeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Механизм определения текущего офиса
 * по локали определённой системой
 * Подписка на событие KernelEvents::REQUEST
 * проводится с наиболее низким приоритетом 0
 * для того, чтобы все другие подписчики
 * определяющие локаль успели отработать до
 * запуска данного механизма.
 * Выброс исключения проводится без
 * дополнительных проверок так как данный обработчик
 * гарантировано запускается после
 * механизма определения офиса по хосту.
 */
class OfficeByLocaleListener implements EventSubscriberInterface
{
    /**
     * @var OfficeManager
     */
    private $officeManager;

    /**
     * @var array
     */
    private $urlIgnorePrefixes;

    public function __construct(OfficeManager $officeManager, array $urlIgnorePrefixes = [])
    {
        $this->officeManager = $officeManager;
        $this->urlIgnorePrefixes = $urlIgnorePrefixes;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 10]],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->officeManager->getCurrentOffice()) {
            return;
        }

        $request = $event->getRequest();

        //Disable office check for control panel and web-profiler view
        foreach ($this->urlIgnorePrefixes as $prefix) {
            if (strpos($request->getPathInfo(), $prefix) === 0) {
                return;
            }
        }

        $officeNotFoundException = new OfficeNotFoundException(
            'Not found office for host `' . $request->getHost() . '`.' .
            'Maybe you need to create this office from Control Panel.'
        );

        $officeByHostLocale = [];
        foreach ($this->officeManager->getOffices() as $office) {
            if (empty($officeByHostLocale[$office->getHost()])) {
                $officeByHostLocale[$office->getHost()] = [];
            }
            $officeByHostLocale[$office->getHost()][$office->getDefaultLanguage()] = $office;
        }

        if (empty($officeByHostLocale[$request->getHost()])) {
            throw $officeNotFoundException;
        }

        /** @var Office[] $offices */
        $offices = $officeByHostLocale[$request->getHost()];

        $route = $request->attributes->get('_route');

        $currentOffice = null;
        // офис определили по домену
        if (count($offices) == 1) {
            list(, $currentOffice) = each($offices);
        }

        if (!$route) { // обрабатываем 404 ошибку
            $expectedLocale = null;
            // пытаемся по урлу определить офис
            if (preg_match('!^/([a-z]{2})/!', $request->getRequestUri(), $a)) {
                $expectedLocale = $a[1];
                if (!empty($offices[$expectedLocale])) {
                    $currentOffice = $offices[$expectedLocale];
                }
            }
            if (!$currentOffice instanceof Office) {
                foreach ($offices as $office) {
                    // берем офис по умолчанию
                    if (!$office->getIncludeLangInUrl()) {
                        $currentOffice = $office;
                        break;
                    }
                }
                // ничего не нашли, берем первый офис
                if (!$currentOffice instanceof Office) {
                    list(, $currentOffice) = each($offices);
                }
            }
        } elseif (substr($route, 0, 1) == '_') {
            foreach ($offices as $office) {
                // берем офис по умолчанию
                if (!$office->getIncludeLangInUrl()) {
                    $currentOffice = $office;
                    break;
                }
            }
        }

        if (!$currentOffice instanceof Office
            && !empty($offices[$request->getLocale()])
        ) {
            $currentOffice = $offices[$request->getLocale()];
        }

        if ($currentOffice instanceof Office) {
            $this->officeManager->setCurrentOffice($currentOffice);
            $request->setLocale($currentOffice->getDefaultLanguage());
        } else {
            throw $officeNotFoundException;
        }
    }
}

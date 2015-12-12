<?php
namespace Octava\Bundle\MuiBundle\Router;

use JMS\I18nRoutingBundle\EventListener\LocaleChoosingListener as ParentLocaleChoosingListener;
use JMS\I18nRoutingBundle\Router\LocaleResolverInterface;
use Octava\Bundle\MuiBundle\LocaleManager;

class LocaleChoosingListener extends ParentLocaleChoosingListener
{
    public function __construct($defaultLocale, LocaleManager $localeManager, LocaleResolverInterface $localeResolver)
    {
        parent::__construct($defaultLocale, $localeManager->getAllAliases(), $localeResolver);
    }
}

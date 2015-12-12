<?php
namespace Octava\Bundle\MuiBundle\Config;

class RouteConfig
{
    const KEY_SKIP_EXCEPTION_IF_ROUTE_NOT_FOUND = 'skip_exception_if_route_not_found';

    /**
     * @var array
     */
    protected $config;

    /**
     * FrameworkConfig constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function skipExceptionIfRouteNotFound()
    {
        return $this->config[self::KEY_SKIP_EXCEPTION_IF_ROUTE_NOT_FOUND];
    }
}

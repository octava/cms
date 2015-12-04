<?php
namespace Octava\Bundle\MenuBundle\Config;

/**
 * Class MenuConfig
 * @package Octava\Bundle\MenuBundle\Config
 */
class MenuConfig
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getLocations()
    {
        return $this->config['locations'];
    }
}

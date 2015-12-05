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
        if (empty($this->config['locations'])) {
            $this->config['locations'] = [
                ['alias' => 'top', 'name' => 'top_menu', 'trans_domain' => 'OctavaMenuBundle'],
                ['alias' => 'right', 'name' => 'right_menu', 'trans_domain' => 'OctavaMenuBundle'],
                ['alias' => 'bottom', 'name' => 'bottom_menu', 'trans_domain' => 'OctavaMenuBundle'],
            ];
        }

        return $this->config['locations'];
    }
}

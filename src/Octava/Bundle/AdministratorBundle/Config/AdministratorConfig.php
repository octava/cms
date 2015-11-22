<?php
namespace Octava\Bundle\AdministratorBundle\Config;

class AdministratorConfig
{
    /**
     * @var bool
     */
    protected $defaultShowHidden = false;

    /**
     * @var array
     */
    protected $whiteList = [];

    public function __construct($defaultShowHidden, array $whiteList = [])
    {
        $this->defaultShowHidden = $defaultShowHidden;
        $this->whiteList = array_filter($whiteList);
    }

    /**
     * @return boolean
     */
    public function getDefaultShowHidden()
    {
        return $this->defaultShowHidden;
    }

    /**
     * @return array
     */
    public function getWhiteList()
    {
        return $this->whiteList;
    }
}

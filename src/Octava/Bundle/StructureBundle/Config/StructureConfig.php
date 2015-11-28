<?php
namespace Octava\Bundle\StructureBundle\Config;

class StructureConfig
{
    const KEY_DEFAULT_TEMPLATE = 'default_template';

    const KEY_ADDITIONAL_TEMPLATES = 'additional_templates';

    protected $config;

    /**
     * StructureConfig constructor.
     * @param $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getDefaultTemplate()
    {
        return $this->config[static::KEY_DEFAULT_TEMPLATE];
    }

    /**
     * @return array
     */
    public function getAdditionalTemplates()
    {
        return $this->config[static::KEY_ADDITIONAL_TEMPLATES];
    }
}

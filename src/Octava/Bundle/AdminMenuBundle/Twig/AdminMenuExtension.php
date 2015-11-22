<?php
namespace Octava\Bundle\AdminMenuBundle\Twig;

use Octava\Bundle\AdminMenuBundle\AdminMenuManager;

/**
 * Class AdminMenuExtension
 * @package Octava\Bundle\AdminMenuBundle\Twig
 */
class AdminMenuExtension extends \Twig_Extension
{
    protected $adminMenuManager;

    /**
     * AdminMenuExtension constructor.
     * @param AdminMenuManager $adminMenuManager
     */
    public function __construct(AdminMenuManager $adminMenuManager)
    {
        $this->adminMenuManager = $adminMenuManager;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return [
            'admin_menu_manager' => $this->adminMenuManager
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'admin_menu_manager';
    }
}

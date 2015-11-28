<?php
namespace Octava\Bundle\AdministratorBundle\Twig;

use Octava\Bundle\AdministratorBundle\Entity\Administrator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdministratorExtension extends \Twig_Extension
{
    const NAME = 'octava_administration';
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    public function getFunctions()
    {
        return [
            'octava_administrator_locales' => new \Twig_SimpleFunction(
                'octava_administrator_locales',
                [$this, 'getLocales']
            ),
        ];
    }

    /**
     * @return TokenStorageInterface
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    public function getLocales()
    {
        $result = [];
        $administrator = $this->getTokenStorage()->getToken()->getUser();
        if ($administrator instanceof Administrator) {
            $result = $administrator->getLocalesAlias();
        }

        return $result;
    }
}

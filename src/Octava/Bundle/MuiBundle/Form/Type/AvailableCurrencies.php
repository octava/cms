<?php
namespace Octava\Bundle\MuiBundle\Form\Type;

use Octava\Bundle\MuiBundle\Dict\Currencies;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AvailableCurrencies
 * @package Octava\Bundle\MuiBundle\Form\Type
 */
class AvailableCurrencies extends AbstractType
{
    /**
     * @var Currencies
     */
    protected $currencyDict;

    /**
     * AvailableCurrencies constructor.
     * @param Currencies $currencyDict
     */
    public function __construct(Currencies $currencyDict)
    {
        $this->setCurrencyDict($currencyDict);
    }

    /**
     * @return Currencies
     */
    public function getCurrencyDict()
    {
        return $this->currencyDict;
    }

    /**
     * @param Currencies $currencyDict
     * @return self
     */
    public function setCurrencyDict(Currencies $currencyDict)
    {
        $this->currencyDict = $currencyDict;
        return $this;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'octava_available_currencies';
    }

    public function getParent()
    {
        return 'choice';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => $this->currencyDict->getAvailableCurrenciesChoices(),
                'multiple' => true,
                'expanded' => true,
            ]
        );
    }
}

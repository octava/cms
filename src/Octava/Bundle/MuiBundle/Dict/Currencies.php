<?php
namespace Octava\Bundle\MuiBundle\Dict;

class Currencies
{
    const USD = 'USD';
    const EUR = 'EUR';
    const RUB = 'RUB';

    protected $availableCurrencies = [];

    public function __construct($availableCurrencies)
    {
        $this->availableCurrencies = $availableCurrencies;
    }

    /**
     * @return array
     */
    public function getAvailableCurrencies()
    {
        return $this->availableCurrencies;
    }

    /**
     * @return array
     */
    public function getAvailableCurrenciesChoices()
    {
        return array_combine($this->availableCurrencies, $this->availableCurrencies);
    }
}

<?php
namespace Octava\Bundle\MenuBundle\Filter;

/**
 * Interface FilterInterface
 * @package Octava\Bundle\MenuBundle\Filter
 */
interface FilterInterface
{
    /**
     * @param $item
     * @param $location
     * @param $locale
     * @return mixed
     */
    public function filter($item, $location, $locale);
}

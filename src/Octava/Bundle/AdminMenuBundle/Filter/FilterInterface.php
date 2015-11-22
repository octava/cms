<?php
namespace Octava\Bundle\AdminMenuBundle\Filter;

interface FilterInterface
{
    /**
     * @param array $tree
     * @return array
     */
    public function filter(array $tree);
}

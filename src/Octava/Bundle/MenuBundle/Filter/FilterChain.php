<?php
namespace Octava\Bundle\MenuBundle\Filter;

/**
 * Class FilterChain
 * @package Octava\Bundle\MenuBundle\Filter
 */
class FilterChain
{
    /**
     * @var FilterInterface[]
     */
    private $filters = [];

    /**
     * @param FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @param array $tree
     * @param $location
     * @param $locale
     * @param array $ret
     * @return array
     */
    public function filter(array $tree, $location, $locale, $ret = [])
    {
        foreach ($tree as $id => $item) {
            foreach ($this->filters as $filter) {
                if ($filter->filter($item, $location, $locale) === false) {
                    continue 2;
                }
            }
            if (!empty($item['children'])) {
                $item['children'] = $this->filter($item['children'], $location, $locale, []);
            }
            $ret[$id] = $item;
        }

        return $ret;
    }
}

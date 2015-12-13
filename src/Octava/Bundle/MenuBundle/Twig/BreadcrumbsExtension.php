<?php
namespace Octava\Bundle\MenuBundle\Twig;

use Octava\Bundle\MuiBundle\OfficeManager;
use Octava\Bundle\StructureBundle\Entity\Structure;
use Octava\Bundle\StructureBundle\StructureManager;

class BreadcrumbsExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'octava_breadcrumbs';

    /**
     * @var StructureManager
     */
    protected $structureManager;

    /**
     * @var OfficeManager
     */
    protected $officeManager;

    /**
     * @var array
     */
    protected $prependItems = [];

    /**
     * @var array
     */
    protected $appendItems = [];

    public function __construct(StructureManager $structureManager, OfficeManager $officeManager)
    {
        $this->structureManager = $structureManager;
        $this->officeManager = $officeManager;
    }

    public function get(array $menuStructure, $structureId = null)
    {
        if (empty($structureId)) {
            /** @var Structure $structure */
            $structure = $this->structureManager->getCurrentItem();
            if ($structure instanceof Structure) {
                $structureId = $structure->getId();
            }
        }
        $ret = array_merge($this->prependItems, $this->getPath($menuStructure, $structureId), $this->appendItems);
        $office = $this->officeManager->getCurrentOffice();
        if ($office && $office->getIncludeLangInUrl()) {
            foreach ($ret as &$item) {
                if (!empty($item['link'])
                    && !preg_match('!^https?://!', $item['link'])
                    && !preg_match(
                        '!^/'.$office->getDefaultLanguage().'/!',
                        $item['link']
                    )
                ) {
                    $item['link'] = sprintf('/%s%s', $office->getDefaultLanguage(), $item['link']);
                }
            }
        }

        return $ret;
    }

    public function append($title, $link = '')
    {
        $this->appendItems[] = $this->getItem($title, $link);
    }

    public function prepend($title, $link = '')
    {
        $this->prependItems[] = $this->getItem($title, $link);
    }

    public function getGlobals()
    {
        return [self::EXTENSION_NAME => $this];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }

    protected function getItem($title, $link)
    {
        return ['title' => $title, 'link' => $link];
    }

    protected function getPath($structure, $structureId, $path = [])
    {
        $ret = [];
        foreach ($structure as $row) {
            $title = $row['title'];
            $link = $row['link'];
            $type = $row['structure_type'];
            $item = $this->getItem($title, $link);
            if (!empty($row['structure_id']) && $row['structure_id'] == $structureId) {
                $ret = array_merge($path, [$item]);
                break;
            }
            if (!empty($row['children'])) {
                if (empty($link) || $type == Structure::TYPE_STRUCTURE_EMPTY) {
                    foreach ($row['children'] as $child) {
                        $valid = $child['structure_type'] != Structure::TYPE_STRUCTURE_EMPTY && !empty($child['link']);
                        if ($valid) {
                            $item['link'] = $child['link'];
                            break;
                        }
                    }
                }
                $ret = $this->getPath($row['children'], $structureId, array_merge($path, [$item]));
                if (!empty($ret)) {
                    break;
                }
            }
        }

        return $ret;
    }
}

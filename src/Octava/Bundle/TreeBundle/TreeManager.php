<?php
namespace Octava\Bundle\TreeBundle;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\TwigBundle\TwigEngine;

class TreeManager
{
    /**
     * Query builder for target table
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * Идентификатор текущего дерева
     * @var string
     */
    private $token;

    /**
     * @var TwigEngine
     */
    private $twigEngine;

    /**
     * @var array
     */
    private $viewData = [];

    /**
     * @var string
     */
    private $primaryField = 'id';

    /**
     * @var string
     */
    private $parentField;

    /**
     * @var string
     */
    private $nameField;

    /**
     * @var string
     */
    private $selectedId;

    /**
     * @var string
     */
    private $linkPath;

    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $rootName = 'Root';

    /**
     * @var string
     */
    private $orderString;

    /**
     * @var string
     */
    private $blockClass;

    /**
     * @var string
     */
    private $urlParam = 'parent_id';

    /**
     * @var string
     */
    private $levelTemplate = 'OctavaTreeBundle:Default:level.html.twig';

    /**
     * @var string
     */
    private $treeTemplate = 'OctavaTreeBundle:Default:tree.html.twig';

    /**
     * Массив параметров которые должны быть
     * перезаписаны в адресной строке.
     * Используется для сбрасывания
     * значений фильтров и грида
     * @var array
     */
    private $additionalUrlParams = [];

    /**
     * @var boolean
     */
    private $actZeroAsNull = false;


    /**
     * @param TwigEngine $twigEngine
     */
    public function __construct(TwigEngine $twigEngine)
    {
        $this->twigEngine = $twigEngine;
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    public function getPrimaryField()
    {
        return $this->primaryField;
    }

    public function setPrimaryField($primaryField)
    {
        $this->primaryField = $primaryField;

        return $this;
    }

    public function getParentField()
    {
        return $this->parentField;
    }

    public function setParentField($parentField)
    {
        $this->parentField = $parentField;

        return $this;
    }

    public function getNameField()
    {
        return $this->nameField;
    }

    public function setNameField($nameField)
    {
        $this->nameField = $nameField;

        return $this;
    }

    public function getLinkPath()
    {
        return $this->linkPath;
    }

    public function setLinkPath($linkPath)
    {
        $this->linkPath = $linkPath;

        return $this;
    }

    public function getRootName()
    {
        return $this->rootName;
    }

    public function setRootName($rootName)
    {
        $this->rootName = $rootName;

        return $this;
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }

    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;

        return $this;
    }

    public function getOrderString()
    {
        return $this->orderString;
    }

    public function setOrderString($order)
    {
        $this->orderString = $order;

        return $this;
    }

    public function getBlockClass()
    {
        return $this->blockClass;
    }

    public function setBlockClass($blockClass)
    {
        $this->blockClass = $blockClass;

        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    public function getUrlParam()
    {
        return $this->urlParam;
    }

    public function setUrlParam($paramName)
    {
        $this->urlParam = $paramName;

        return $this;
    }

    public function getAdditionalUrlParams()
    {
        return $this->additionalUrlParams;
    }

    public function setAdditionalUrlParams($data)
    {
        $this->additionalUrlParams = $data;

        return $this;
    }

    /**
     * Метод установки выбранного элемента
     * после инициализации дерева
     * @param integer $id
     * @return $this
     */
    public function setSelected($id)
    {
        $this->selectedId = $id;

        return $this;
    }

    public function getActZeroAsNull()
    {
        return $this->actZeroAsNull;
    }

    public function setActZeroAsNull($actZeroAsNull)
    {
        $this->actZeroAsNull = $actZeroAsNull;

        return $this;
    }

    /**
     * Метод передаёт в приложение
     * для отображения шаблон дерева
     */
    public function show()
    {
        if ($this->sendData2Viewer()) {
            return $this->twigEngine->render($this->treeTemplate, $this->viewData);
        }
        return null;
    }

    /**
     * Метод получает из вьювера HTML код дерева
     *
     * @return string HTML код
     */
    public function get()
    {
        if (!$this->sendData2Viewer()) {
            return null;
        }

        return $this->twigEngine->render($this->treeTemplate, $this->viewData);
    }

    /**
     * @param string $levelTemplate
     * @return self
     */
    public function setLevelTemplate($levelTemplate)
    {
        $this->levelTemplate = $levelTemplate;
        return $this;
    }

    /**
     * @param string $treeTemplate
     * @return self
     */
    public function setTreeTemplate($treeTemplate)
    {
        $this->treeTemplate = $treeTemplate;
        return $this;
    }

    /**
     * Метод подключает скрипты вывода древовидной структуры,
     * получает данные для отрисовки
     * и передаёт в текущее приложение для отображения
     *
     * @return boolean Возвращает false в случае когда отрисовывать нечего
     */
    protected function sendData2Viewer()
    {
        $data = $this->getData();
        if (!sizeof($data)) {
            return false;
        }

        if (!$this->token) {
            $this->token = substr(md5(time() . mt_rand(1000, 10000)), 0, 8);
        }

        $this->viewData['tree_recursive_path'] = $this->levelTemplate;
        $this->viewData['tree_data'] = $data;
        $this->viewData['tree_link_path'] = $this->linkPath;
        $this->viewData['tree_root_name'] = $this->rootName;
        $this->viewData['tree_root_path'] = $this->rootPath;
        $this->viewData['tree_name'] = $this->nameField;
        $this->viewData['tree_primary'] = $this->primaryField;
        $this->viewData['tree_token'] = $this->token;
        $this->viewData['tree_selected'] = $this->getSelected($data);
        $this->viewData['tree_add_url'] = $this->getAdditionalUrlParamsAsString();
        $this->viewData['tree_block_class'] = $this->blockClass;
        $this->viewData['tree_url_param'] = $this->urlParam;

        return true;
    }

    /**
     * Метод построения строки дополнительных параметров в URL
     * @return string
     */
    protected function getAdditionalUrlParamsAsString()
    {
        $get = $_GET;
        unset($get['filter'], $get[$this->getUrlParam()]);

        $params = array_merge($get, $this->additionalUrlParams);
        $str = http_build_query($params);

        return $str ? '&' . $str : '';
    }

    protected function getData()
    {
        return $this->getNodeRecursive();
    }

    /**
     * Метод получения данных
     * для отображения в древовидной структуре
     *
     * @param integer $pid ID родительского элемента
     * @param null $rows
     * @return array Массив данных в формате для отоюражения в дереве
     */
    protected function getNodeRecursive($pid = 0, $rows = null)
    {
        $queryBuilder = clone $this->queryBuilder;
        if (!is_null($this->orderString)) {
            $queryBuilder->orderBy($this->orderString);
        }

        if (!$this->parentField) {
            return $queryBuilder->getQuery()->getArrayResult();
        }

        if (is_null($rows)) {
            $rows = $queryBuilder->getQuery()->getResult();
        }

        $result = [];

        $getIdMethod = 'get' . ucfirst($this->primaryField);
        $getNameMethod = 'get' . ucfirst($this->nameField);
        $getParentMethod = 'get' . ucfirst($this->parentField);

        foreach ($rows as $value) {
            $currentId = $value->$getIdMethod();
            $parent = $value->$getParentMethod();
            $parentId = is_object($parent) ? $parent->$getIdMethod() : $parent;
            if ($parentId == $pid) {
                $result[$currentId] = [
                    $this->primaryField => $currentId,
                    $this->nameField => $value->$getNameMethod(),
                ];

                $children = $this->getNodeRecursive($currentId, $rows);
                if (sizeof($children)) {
                    $result[$currentId]['children'] = $children;
                }
            }
        }

        return $result;
    }

    /**
     * Метод получения выбранного
     * элемента и установки выбранным первого,
     * если не задан явно
     * @param array $data
     * @return int Идентификатор выбранного элемента
     */
    protected function getSelected(array $data)
    {
        if (is_null($this->selectedId)) {
            $firstValue = array_pop($data);
            $this->selectedId = $firstValue[$this->primaryField];
        }

        return $this->selectedId;
    }
}

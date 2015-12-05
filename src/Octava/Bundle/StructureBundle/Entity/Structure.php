<?php

namespace Octava\Bundle\StructureBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Structure
 * @UniqueEntity("routeName")
 */
class Structure implements Translatable
{
    /**
     * Ссылка отсутствует
     */
    const TYPE_STRUCTURE_EMPTY = 'octava_structure_empty';

    /**
     * Название поля ID в рутинге
     */
    const ROUTING_ID_NAME = 'structureId';

    /**
     * Страница
     */
    const TYPE_PAGE = 'page';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $path;

    /**
     * @var boolean
     */
    private $state;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var Structure
     */
    private $parent;

    /**
     * @var integer
     */
    private $level = 1;

    /**
     * @var string
     */
    private $locale;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return self
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getTitle();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Structure
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Structure
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Structure
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Structure
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Structure
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set alias
     *
     * @param string $alias
     *
     * @return Structure
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return Structure
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get state
     *
     * @return boolean
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state
     *
     * @param boolean $state
     *
     * @return Structure
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set template
     *
     * @param string $template
     *
     * @return Structure
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get routeName
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Set routeName
     *
     * @param string $routeName
     *
     * @return Structure
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * Add child
     *
     * @param Structure $child
     *
     * @return Structure
     */
    public function addChild(Structure $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param Structure $child
     */
    public function removeChild(Structure $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get parent
     *
     * @return Structure
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent
     *
     * @param Structure $parent
     *
     * @return Structure
     */
    public function setParent(Structure $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());

        $this->setPathByParent();
        $this->updateRouteName();

        if ($this->getState()) {
            $this->setState(true);
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime());

        $this->setPathByParent();
        $this->updateRouteName();

        if ($this->getState()) {
            $this->setState(true);
        }
    }

    /**
     * Установить путь элемента по родительскому элемента
     */
    public function setPathByParent()
    {
        if ($this->getAlias()) {
            $path = '/'.$this->getAlias().'/';
            if ($this->getParent()) {
                $path = rtrim($this->getParent()->getPath(), '/').'/'.$this->getAlias().'/';
            }
            $this->setPath($path);
        }
    }

    /**
     * @return void
     */
    public function updateRouteName()
    {
        if (!$this->getRouteName() && $this->getPath()) {
            $routeName = $this->type;
            if (in_array($this->type, [self::TYPE_PAGE, self::TYPE_STRUCTURE_EMPTY])) {
                $path = $this->getPath();
                $routeName = sprintf('structure_page_%s', str_replace('/', '_', trim($path, '/')));
            }
            $this->setRouteName($routeName);
        }
    }

    /**
     * Получить подготовленный путь
     * @return string
     */
    public function getPreparedPath()
    {
        if ($this->getType() == self::TYPE_STRUCTURE_EMPTY) {
            return '';
        }

        return $this->path;
    }
}

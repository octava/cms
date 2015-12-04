<?php

namespace Octava\Bundle\MenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Octava\Bundle\StructureBundle\Entity\Structure;

/**
 * Menu
 */
class Menu implements Translatable
{
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
     * @var boolean
     */
    private $proxyTitle;
    /**
     * @var string
     */
    private $link;
    /**
     * @var boolean
     */
    private $proxyLink;
    /**
     * @var string
     */
    private $location;
    /**
     * @var integer
     */
    private $position;
    /**
     * @var boolean
     */
    private $state;
    /**
     * @var boolean
     */
    private $isTest;
    /**
     * @var Collection
     */
    private $children;
    /**
     * @var Menu
     */
    private $parent;
    /**
     * @var Structure
     */
    private $structure;
    /**
     * @var boolean
     */
    private $selected;
    /**
     * @var string
     */
    private $locale;
    /**
     * @var int
     */
    private $level;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString()
    {
        return (string)$this->getId();
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
     * @return Menu
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
     * @return Menu
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * @param boolean $selected
     * @return self
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

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
     * @return Menu
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get proxyTitle
     *
     * @return boolean
     */
    public function getProxyTitle()
    {
        return $this->proxyTitle;
    }

    /**
     * Set proxyTitle
     *
     * @param boolean $proxyTitle
     *
     * @return Menu
     */
    public function setProxyTitle($proxyTitle)
    {
        $this->proxyTitle = $proxyTitle;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return Menu
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get proxyLink
     *
     * @return boolean
     */
    public function getProxyLink()
    {
        return $this->proxyLink;
    }

    /**
     * Set proxyLink
     *
     * @param boolean $proxyLink
     *
     * @return Menu
     */
    public function setProxyLink($proxyLink)
    {
        $this->proxyLink = $proxyLink;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return Menu
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Menu
     */
    public function setPosition($position)
    {
        $this->position = $position;

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
     * @return Menu
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get isTest
     *
     * @return boolean
     */
    public function getIsTest()
    {
        return $this->isTest;
    }

    /**
     * Set isTest
     *
     * @param boolean $isTest
     *
     * @return Menu
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * Add child
     *
     * @param Menu $child
     *
     * @return Menu
     */
    public function addChild(Menu $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param Menu $child
     */
    public function removeChild(Menu $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get parent
     *
     * @return Menu
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent
     *
     * @param Menu $parent
     *
     * @return Menu
     */
    public function setParent(Menu $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get structure
     *
     * @return Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Set structure
     *
     * @param Structure $structure
     *
     * @return Menu
     */
    public function setStructure(Structure $structure = null)
    {
        $this->structure = $structure;

        return $this;
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
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }
}

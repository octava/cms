<?php

namespace Octava\Bundle\AdministratorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Resource
 */
class Resource
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
    private $resource;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $label;

    /**
     * @var integer
     */
    private $sort;

    /**
     * @var string
     */
    private $groupLabel;

    /**
     * @var string
     */
    private $groupLabelDomain;

    /**
     * @var boolean
     */
    private $hidden;


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
     * Get resource
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set resource
     *
     * @param string $resource
     *
     * @return Resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set action
     *
     * @param string $action
     *
     * @return Resource
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Resource
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get sort
     *
     * @return integer
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set sort
     *
     * @param integer $sort
     *
     * @return Resource
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get groupLabel
     *
     * @return string
     */
    public function getGroupLabel()
    {
        return $this->groupLabel;
    }

    /**
     * Set groupLabel
     *
     * @param string $groupLabel
     *
     * @return Resource
     */
    public function setGroupLabel($groupLabel)
    {
        $this->groupLabel = $groupLabel;

        return $this;
    }

    /**
     * Get groupLabelDomain
     *
     * @return string
     */
    public function getGroupLabelDomain()
    {
        return $this->groupLabelDomain;
    }

    /**
     * Set groupLabelDomain
     *
     * @param string $groupLabelDomain
     *
     * @return Resource
     */
    public function setGroupLabelDomain($groupLabelDomain)
    {
        $this->groupLabelDomain = $groupLabelDomain;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     *
     * @return Resource
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}

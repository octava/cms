<?php

namespace Octava\Bundle\MuiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Office
 * @UniqueEntity("alias")
 */
class Office
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
    private $name;
    /**
     * @var string
     */
    private $alias;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $protocol = 'http';
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $relatedUrl;
    /**
     * @var string
     */
    private $defaultLanguage;
    /**
     * @var string
     */
    private $recognizeLanguage;
    /**
     * @var array
     */
    private $availableLanguages;
    /**
     * @var array
     */
    private $currencies;
    /**
     * @var boolean
     */
    private $includeLangInUrl = true;
    /**
     * @var integer
     */
    private $position;

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
     * @return Office
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
     * @return Office
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Office
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return Office
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Office
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get protocol
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Set protocol
     *
     * @param string $protocol
     *
     * @return Office
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set host
     *
     * @param string $host
     *
     * @return Office
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get relatedUrl
     *
     * @return string
     */
    public function getRelatedUrl()
    {
        return $this->relatedUrl;
    }

    /**
     * Set relatedUrl
     *
     * @param string $relatedUrl
     *
     * @return Office
     */
    public function setRelatedUrl($relatedUrl)
    {
        $this->relatedUrl = $relatedUrl;

        return $this;
    }

    /**
     * Get defaultLanguage
     *
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * Set defaultLanguage
     *
     * @param string $defaultLanguage
     *
     * @return Office
     */
    public function setDefaultLanguage($defaultLanguage)
    {
        $this->defaultLanguage = $defaultLanguage;

        return $this;
    }

    /**
     * Get recognizeLanguage
     *
     * @return string
     */
    public function getRecognizeLanguage()
    {
        return $this->recognizeLanguage;
    }

    /**
     * Set recognizeLanguage
     *
     * @param string $recognizeLanguage
     *
     * @return Office
     */
    public function setRecognizeLanguage($recognizeLanguage)
    {
        $this->recognizeLanguage = $recognizeLanguage;

        return $this;
    }

    /**
     * Get availableLanguages
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        return $this->availableLanguages;
    }

    /**
     * Set availableLanguages
     *
     * @param array $availableLanguages
     *
     * @return Office
     */
    public function setAvailableLanguages($availableLanguages)
    {
        $this->availableLanguages = $availableLanguages;

        return $this;
    }

    /**
     * Get currencies
     *
     * @return array
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * Set currencies
     *
     * @param array $currencies
     *
     * @return Office
     */
    public function setCurrencies($currencies)
    {
        $this->currencies = $currencies;

        return $this;
    }

    /**
     * Get includeLangInUrl
     *
     * @return boolean
     */
    public function getIncludeLangInUrl()
    {
        return $this->includeLangInUrl;
    }

    /**
     * Set includeLangInUrl
     *
     * @param boolean $includeLangInUrl
     *
     * @return Office
     */
    public function setIncludeLangInUrl($includeLangInUrl)
    {
        $this->includeLangInUrl = $includeLangInUrl;

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
     * @return Office
     */
    public function setPosition($position)
    {
        $this->position = $position;

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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAlias() ?: '';
    }
}

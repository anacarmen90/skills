<?php

namespace SkillsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Skill
 */
class Skill
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $tips;

    /**
     * @var ArrayCollection
     */
    private $categories;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Skill
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Skill
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getTips()
    {
        return $this->tips;
    }

    /**
     * @param string $tips
     *
     * @return Skill
     */
    public function setTips(string $tips)
    {
        $this->tips = $tips;

        return $this;
    }

    /**
     * Add category
     *
     * @param Category $category
     *
     * @return Skill
     */
    public function addCategory(Category $category)
    {
        $this->categories[] = $category;
        $category->addSkill($this);

        return $this;
    }

    /**
     * @param Category $category
     */
    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * @return ArrayCollection|Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array
     */
    public function getNameSuggest()
    {

        return [
          'input' => [$this->getName(), $this->getDescription()],
          'output' => $this->getName(),
          'payload' => [
            'id' => $this->getId(),
          ],
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->getName())) {
            return '';
        }

        return $this->getName();
    }
}

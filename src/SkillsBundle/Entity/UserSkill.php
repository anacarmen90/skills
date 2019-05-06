<?php

namespace SkillsBundle\Entity;

use AppBundle\Entity\ZUser;

/**
 * UserSkill
 */
class UserSkill
{

    const MIN_LEVEL = 0;
    const MAX_LEVEL = 4;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var ZUser
     */
    private $user;

    /**
     * @var \SkillsBundle\Entity\Skill
     */
    private $skill;

    /**
     * @var int
     */
    private $level;

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
     * Set user
     *
     * @param ZUser $user
     *
     * @return UserSkill
     */
    public function setUser(ZUser $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return ZUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set skill
     *
     * @param Skill $skill
     *
     * @return UserSkill
     */
    public function setSkill(Skill $skill = null)
    {
        $this->skill = $skill;

        return $this;
    }

    /**
     * Get skill
     *
     * @return Skill
     */
    public function getSkill()
    {
        return $this->skill;
    }

    /**
     * Set skill level
     *
     * @param int $level
     *
     * @return UserSkill
     */
    public function setLevel(int $level = null)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get skill level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    public function __toString()
    {
        if (is_null($this->user && is_null($this->skill))){
            return '';
        }
        return $this->user . ' - ' . $this->skill;
    }


}

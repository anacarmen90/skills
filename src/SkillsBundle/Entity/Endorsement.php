<?php

namespace SkillsBundle\Entity;

use AppBundle\Entity\ZUser;

/**
 * Endorsement
 */
class Endorsement
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var ZUser
     */
    private $endorser;

    /**
     * @var ZUser
     */
    private $endorsee;

    /**
     * @var Skill
     */
    private $skill;


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
     * Set endorser
     *
     * @param ZUser $endorser
     *
     * @return Endorsement
     */
    public function setEndorser(ZUser $endorser = null)
    {
        $this->endorser = $endorser;

        return $this;
    }

    /**
     * Get endorser
     *
     * @return ZUser
     */
    public function getEndorser()
    {
        return $this->endorser;
    }

    /**
     * Set endorsee
     *
     * @param ZUser $endorsee
     *
     * @return Endorsement
     */
    public function setEndorsee(ZUser $endorsee = null)
    {
        $this->endorsee = $endorsee;

        return $this;
    }

    /**
     * Get endorsee
     *
     * @return ZUser
     */
    public function getEndorsee()
    {
        return $this->endorsee;
    }

    /**
     * Set skill
     *
     * @param Skill $skill
     *
     * @return Endorsement
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
}


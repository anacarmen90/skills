<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use SkillsBundle\Entity\Endorsement;
use SkillsBundle\Entity\UserSkill;

/**
 * @ORM\Entity
 * @ORM\Table(name="zuser")
 */
class ZUser extends User implements LdapUserInterface
{

    /**
     * @var string
     */
    protected $dn;

    /**
     * @var string
     */
    private $fullName;

    /**
     * @var Collection
     */
    private $skills;

    /**
     * @var Collection
     */
    private $endorsements;

    /**
     * @var string
     */
    private $employeeId;

    /**
     * @var bool
     */
    private $isAvailable = false;

    /**
     * {@inheritDoc}
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * {@inheritDoc}
     */
    public function setDn($dn)
    {
        $this->dn = $dn;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     *
     * @return ZUser
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Add skill
     *
     * @param UserSkill $skill
     *
     * @return ZUser
     */
    public function addSkill(UserSkill $skill)
    {
        $this->skills[] = $skill;

        return $this;
    }

    /**
     * Remove skill
     *
     * @param UserSkill $skill
     */
    public function removeSkill(UserSkill $skill)
    {
        $this->skills->removeElement($skill);
    }

    /**
     * Get skills
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * Add endorsement
     *
     * @param Endorsement $endorsement
     *
     * @return ZUser
     */
    public function addEndorsement(Endorsement $endorsement)
    {
        $this->endorsements[] = $endorsement;

        return $this;
    }

    /**
     * Remove endorsement
     *
     * @param Endorsement $endorsement
     */
    public function removeEndorsement(Endorsement $endorsement)
    {
        $this->endorsements->removeElement($endorsement);
    }

    /**
     * Get endorsements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEndorsements()
    {
        return $this->endorsements;
    }

    /**
     * @return string
     */
    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    /**
     * @param string $employeeId
     * @return ZUser
     */
    public function setEmployeeId(string $employeeId): ZUser
    {
        $this->employeeId = $employeeId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     * @return ZUser
     */
    public function setIsAvailable(bool $isAvailable): ZUser
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

}

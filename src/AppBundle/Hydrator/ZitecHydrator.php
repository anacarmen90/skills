<?php

namespace AppBundle\Hydrator;

use AppBundle\Entity\ZUser;
use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FR3D\LdapBundle\Hydrator\AbstractHydrator;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Class ZitecHydrator
 *
 * @package AppBundle\Hydrator
 */
class ZitecHydrator extends AbstractHydrator
{

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var MakeitCommunicator
     */
    protected $makeit;

    /**
     * @var array
     */
    protected $ldapEntry;

    /**
     * @var ZUser
     */
    protected $user;

    /**
     * @var string
     */
    protected $webRoot;


    /**
     * ZitecHydrator constructor.
     *
     * @param \FOS\UserBundle\Model\UserManagerInterface $userManager
     * @param array $attributeMap
     * @param MakeitCommunicator $makeit
     * @param string $webRoot
     */
    public function __construct(
        UserManagerInterface $userManager,
        array $attributeMap,
        MakeitCommunicator $makeit,
        $webRoot
    ) {
        parent::__construct($attributeMap);

        $this->userManager = $userManager;
        $this->makeit = $makeit;
        $this->webRoot = realpath($webRoot[0].'/../web');
    }

    /**
     * @return UserInterface
     */
    protected function createUser()
    {
        $user = $this->userManager->createUser();
        $user->setPassword('');

        if ($user instanceof AdvancedUserInterface) {
            $user->setEnabled(true);
        }

        return $user;
    }

    /**
     * @param array $ldapEntry
     *
     * @return ZUser
     */
    public function hydrate(array $ldapEntry)
    {
        $this->ldapEntry = $ldapEntry;
        $this->user = parent::hydrate($ldapEntry);
        $this->setPicture();

        return $this->user;
    }

    protected function setPicture()
    {
        $employeeId = $this->user->getEmployeeId();
        $profilePic = $this->makeit->getUserImagePath($employeeId);
        $systemFilePath = $this->webRoot."/employees/".$employeeId.'.jpg';

        file_put_contents($systemFilePath, base64_decode($profilePic));
    }
}

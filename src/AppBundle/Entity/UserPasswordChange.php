<?php
/**
 * Created by PhpStorm.
 * User: ceci
 * Date: 4/27/2018
 * Time: 7:26 PM
 */

namespace AppBundle\Entity;


class UserPasswordChange
{
    /**
     * @var string
     */
    private $oldPassword;

    /**
     * @var string
     */
    private $newPassword;

    /**
     * @var string
     */
    private $confPassword;

    /**
     * @return string
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * @param string $oldPassword
     */
    public function setOldPassword(string $oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }

    /**
     * @param string $newPassword
     */
    public function setNewPassword(string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }

    /**
     * @return string
     */
    public function getConfPassword()
    {
        return $this->confPassword;
    }

    /**
     * @param string $confPassword
     */
    public function setConfPassword(string $confPassword): void
    {
        $this->confPassword = $confPassword;
    }

    /**
     * @return bool
     */
    public function isPasswordsSame() : bool {
        return $this->newPassword == $this->confPassword;
    }
}
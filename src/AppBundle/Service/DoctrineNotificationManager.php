<?php
/**
 * Created by PhpStorm.
 * User: ceci
 * Date: 4/30/2018
 * Time: 5:03 PM
 */

namespace AppBundle\Service;


use AppBundle\Constant\Config;
use AppBundle\Constant\ConstantValues;
use AppBundle\Entity\Notification;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineNotificationManager 
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    function sendToUser(User $user, Notification $notification): void
    {
        // TODO: Implement sendToUser() method.
    }

    function sendToGroup(array $users, Notification $notification): void
    {
        // TODO: Implement sendToGroup() method.
    }

    function sendToAdmins(Notification $notification){
        $users  = $this->entityManager->getRepository(User::class)->findBy(array('authorityLevel'=>array(Config::$ADMIN_USER_LEVEL, Config::$ADMINISTRATOR_LEVEL)));

        if($users == null)
            throw new \Exception("No Administrators found??");
        foreach ($users as $user){
            $noti = new Notification();
            $noti->copy($notification);
            $noti->setTargetId($user->getId());
            $this->entityManager->persist($noti);
        }
        $this->entityManager->flush();
    }
}
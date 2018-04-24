<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/18/2018
 * Time: 1:17 AM
 */

namespace AppBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\Service\CartManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{

    /**
     * @var \Doctrine\ORM\EntityManager|object
     */
    private $entityManager;

    /**
     * @var User
     */
    private $user;

    public function __construct(ContainerInterface $container)
    {
       $this->entityManager = $container->get('doctrine.orm.entity_manager');
       $this->user = $container->get('security.token_storage')->getToken()->getUser();
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
       $cartManager = new CartManager($this->entityManager);
       $cartManager->mergeCartWithDb($this->user, $this->entityManager);
    }
}
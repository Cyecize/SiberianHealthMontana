<?php
/**
 * Created by PhpStorm.
 * User: ceci
 * Date: 5/31/2018
 * Time: 11:23 PM
 */

namespace AppBundle\EventListener;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Product;
use AppBundle\Service\DoctrineNotificationManager;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;


 class OnProductChangeListener
{
     public function postUpdate(LifecycleEventArgs $args)
    {
        $em = $args->getObjectManager();
        if(!$args->getObject() instanceof Product)
            return;
        $product = $this->castProduct($args->getObject());
        if($product->getQuantity() <= 0){
            $notification = new Notification();
            $notification->setNotificationType("Известие");
            $notification->setContent("Продукт с id: " . $product->getId() . " е изчерпан.");
            try {
                $notificationManager = new DoctrineNotificationManager($em);
                $notificationManager->sendToAdmins($notification);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param $obj
     * @return Product
     */
    private function castProduct($obj) : Product{
        return $obj;
    }
}
<?php

namespace AppBundle\Controller;

use AppBundle\Constant\ConstantValues;
use AppBundle\Entity\HomeFlexBanner;
use AppBundle\Entity\Notification;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\SocialLink;
use AppBundle\Form\ProductCategoryType;
use AppBundle\Repository\SocialLinkRepository;
use AppBundle\Service\DoctrineNotificationManager;
use AppBundle\Service\TwigInformer;
use AppBundle\Service\ProductManager;
use AppBundle\Util\CharacterTranslator;
use AppBundle\Util\DirectoryCreator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    //TODO WHEN UPDATING schema make sure that you remove the ProductCategoryForCreationClass
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @param ProductManager $productManager
     * @param TwigInformer $informator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, ProductManager $productManager, TwigInformer $informator)
    {

        $informator->setError($request->get('error'));
        // replace this example code with whatever you need
        $banners = $this->getDoctrine()->getRepository(HomeFlexBanner::class)->findBy(array('hidden' => false));

        $trendingProds = $productManager->getTrendingProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE, rand(0,10));
        $trendingProdsP2 = $productManager->getTrendingProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE, rand(10,20));
        $newProds = $productManager->getNewestProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE, rand(0,50));
        $newProdsP2 = $productManager->getNewestProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE, rand(50,110));

        return $this->render('default/index.html.twig', [
            'flexBanners' => $banners,
            'trendingProds' => $trendingProds,
            'trendingProdsP2' => $trendingProdsP2,
            'newProds' => $newProds,
            'newProdsP2' => $newProdsP2,
        ]);
    }


    /**
     * @Route("/contacts", name="contacts")
     * @param Request $request
     * @param DoctrineNotificationManager $notificationManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function contactsAction(Request $request, DoctrineNotificationManager $notificationManager)
    {
        $error = null;
        $info = null;

        $name = $request->get('name');
        $email = $request->get('email');
        $message = $request->get('body');

        if($name != null){
            if($email == null || $message == null){
                $error = "Поля попълнете всички полета!";
                goto escape;
            }

            $msgSkeleton = ConstantValues::$NEW_CONTACT_US_MESSAGE;
            $msg = preg_replace('/{{question}}/', $message, $msgSkeleton);
            $msg = preg_replace('/{{email}}/', $email, $msg);
            $msg = preg_replace('/{{name}}/', $name, $msg);

            $notification = new Notification();
            $notification->setNotificationType("Запитване");
            $notification->setContent($msg);

            $notificationManager->sendToAdmins($notification);

            $info = "Вашето запитване беше изпратено!";
        }

        escape:

        return $this->render("default/contact-us.html.twig",
            [
                'error'=>$error,
                'success'=>$info,
            ]);
    }

    /**
     * @Route("/about-us", name="about_us")
     */
    public function aboutUsPage(){

        return $this->render('default/about-us.html.twig');
    }


}

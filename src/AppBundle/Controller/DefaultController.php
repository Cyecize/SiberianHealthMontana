<?php

namespace AppBundle\Controller;

use AppBundle\Constant\ConstantValues;
use AppBundle\Entity\HomeFlexBanner;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\SocialLink;
use AppBundle\Form\ProductCategoryType;
use AppBundle\Repository\SocialLinkRepository;
use AppBundle\Service\BasicInformator;
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
     * @param BasicInformator $informator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, ProductManager $productManager, BasicInformator $informator)
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
     */
    public function contactsAction(Request $request)
    {

        return $this->render("default/contact-us.html.twig",
            [

            ]);
    }


}

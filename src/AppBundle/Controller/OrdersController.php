<?php
/**
 * Created by PhpStorm.
 * User: ceci
 * Date: 5/1/2018
 * Time: 11:17 PM
 */

namespace AppBundle\Controller;

use AppBundle\Constant\Config;
use AppBundle\Constant\ConstantValues;
use AppBundle\Entity\HomeFlexBanner;
use AppBundle\Entity\Notification;
use AppBundle\Entity\ProductOrder;
use AppBundle\Entity\User;
use AppBundle\Service\CartManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OrdersController extends Controller
{
    /**
     * @param User $user
     * @return bool
     */
    private function isUserPrivileged(User $user)
    {
        if ($user->getAuthorityLevel() <= Config::$ADMIN_USER_LEVEL)
            return true;
        return false;
    }
    //Admin side of orders

    /**
     * @Route("/admin/orders/{filter}", name="admin_orders", defaults={"filter"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $filter
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function showOrdersAction(Request $request, $filter)
    {
        if (!$this->isUserPrivileged($this->getUser())) {
            return $this->redirectToRoute('homepage');
        }
        $error = $request->get('error');
        $success = $request->get('success');

        $page = $request->get('page');
        if ($page == null || $page < 1)
            $page = 1;
        $maxOrdersPerPage = ConstantValues::$MAX_ORDERS_PER_PAGE;
        $offset = $page * $maxOrdersPerPage - $maxOrdersPerPage;

        $orderRepo = $this->getDoctrine()->getRepository(ProductOrder::class);
        $orders = array();
        $ordersCount = 0;
        switch ($filter) {
            case "all":
                $orders = $orderRepo->findBy(array(), array(), $maxOrdersPerPage, $offset);
                $ordersCount = $orderRepo->getOrdersCount();
                break;
            case "finished":
                $orders = $orderRepo->findBy(array('accepted' => true), array(), $maxOrdersPerPage, $offset);
                $ordersCount = $orderRepo->getOrdersCountByParam(true);
                break;
            case "inProgress":
                $orders = $orderRepo->findBy(array('accepted' => false), array(), $maxOrdersPerPage, $offset);
                $ordersCount = $orderRepo->getOrdersCountByParam(false);
                break;
        }

        $pages = ceil($ordersCount / $maxOrdersPerPage);
        if ($pages < 1)
            $pages = 1;

        return $this->render('administrative/display-orders.html.twig',
            [
                'error' => $error,
                'success' => $success,
                'ordersType' => $filter,
                'pages' => $pages,
                'orders' => $orders,
                'currentPage' => $page,
            ]);

    }



    //user side of orders

    /**
     * @Route("/user/orders/all", name="user_orders")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function showUserOrdersAction(Request $request)
    {
        $error = $request->get('error');
        $success = $request->get('success');

        $page = $request->get('page');
        if ($page == null || $page < 1)
            $page = 1;
        $maxOrdersPerPage = ConstantValues::$MAX_ORDERS_PER_PAGE;
        $offset = $page * $maxOrdersPerPage - $maxOrdersPerPage;


        $user = $this->getUser();
        $orders = $this->getDoctrine()->getRepository(ProductOrder::class)->findBy(array('userId' => $user->getId()), array(), $maxOrdersPerPage, $offset);
        $ordersCount = $this->getDoctrine()->getRepository(ProductOrder::class)->getOrdersCountForUser($user);


        $pages = ceil($ordersCount / $maxOrdersPerPage);
        if ($pages < 1)
            $pages = 1;

        $filter = "all";
        return $this->render('user-related/display-user-orders.html.twig',
            [
                'error' => $error,
                'success' => $success,
                'ordersType' => $filter,
                'pages' => $pages,
                'orders' => $orders,
                'currentPage' => $page,
            ]);
    }

    /**
     * @Route("/user/order/{orderId}", name="user_single_order")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $orderId
     * @param CartManager $cartManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function userOrderObserveAction(Request $request, $orderId, CartManager $cartManager)
    {
        $order = $this->getDoctrine()->getRepository(ProductOrder::class)->findOneBy(array('id'=>$orderId, 'userId'=>$this->getUser()->getId()));
        if($order == null)
            return $this->redirectToRoute('homepage');

        $cartProdArr = $cartManager->forgeProductsFromCookie(json_decode($order->getShoppingCart()), $this->getDoctrine()->getManager());
        $prods = [];

        foreach ($cartProdArr as $product){
            $prods[] = $product->getProduct();
        }

        return $this->render('user-related/order-observe.html.twig',
            [
                'order'=>$order,
                'products'=>$prods,
                'forgedProducts'=>$cartProdArr,
            ]);
    }


    //private functions
}
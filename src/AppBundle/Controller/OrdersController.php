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
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductOrder;
use AppBundle\Entity\User;
use AppBundle\Service\CartManager;
use AppBundle\Service\DoctrineNotificationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swift_TransportException;
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

    /**
     * @Route("/admin/order/{orderId}", name="admin_edit_order")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $orderId
     * @param CartManager $cartManager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminOrderObserveAction(Request $request, $orderId, CartManager $cartManager)
    {
        if (!$this->isUserPrivileged($this->getUser()))
            return $this->redirectToRoute('homepage');
        $order = $this->getDoctrine()->getRepository(ProductOrder::class)->findOneBy(array('id' => $orderId));

        $error = $request->get('error');
        $success = $request->get('success');

        if ($order == null)
            return $this->redirectToRoute('homepage');

        $cartProdArr = $cartManager->forgeProductsFromCookie(json_decode($order->getShoppingCart()), $this->getDoctrine()->getManager());
        $prods = [];

        $relevantPrice = 0.0;

        foreach ($cartProdArr as $product) {
            $prods[] = $product->getProduct();
            $relevantPrice += $product->getQuantity() * $product->getProduct()->getPrice();
        }

        return $this->render('administrative/order-edit.html.twig',
            [
                'error' => $error,
                'success' => $success,
                'order' => $order,
                'products' => $prods,
                'forgedProducts' => $cartProdArr,
                'relevantPrice' => $relevantPrice,
            ]);
    }

    /**
     * @Route("/admin/order/remove/{orderId}", name="admin_remove_order", defaults={"orderId"=null}, methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $orderId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeOrderAction(Request $request, $orderId)
    {
        $state = ['status' => 0, 'message' => null];
        if (!$this->isUserPrivileged($this->getUser())) {
            $state['message'] = "Not authorized";
            $state['status'] = 401;
            goto  escape;
        }


        $orderId = $request->get('orderId');
        $order = $this->getDoctrine()->getRepository(ProductOrder::class)->findOneBy(array('id' => $orderId));
        if ($order == null) {
            $state['message'] = "Order not found";
            $state['status'] = 404;
            goto  escape;
        }

        if ($order->getAccepted()) {
            $state['status'] = 405;
            $state['message'] = 'Cannot remove accepted order!';
            goto  escape;
        }

        $entityManage = $this->getDoctrine()->getManager();
        $entityManage->remove($order);
        $entityManage->flush();


        $state['status'] = 200;
        $state['message'] = "OK";

        escape:

        return $this->render('queries/generic-query-aftermath-message.twig',
            [
                'error' => json_encode($state),
            ]);

    }

    /**
     * @Route("/admin/order/accept/{orderId}", name="admin_accept_order", defaults={"orderId"=null})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $orderId
     * @param DoctrineNotificationManager $notificationManager
     * @param \Swift_Mailer $mailer
     * @param CartManager $cartManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function acceptOrderAction(Request $request, $orderId, DoctrineNotificationManager $notificationManager, \Swift_Mailer $mailer, CartManager $cartManager)
    {

        $state = ['status' => 0, 'message' => null];
        if (!$this->isUserPrivileged($this->getUser())) {
            $state['status'] = 401;
            $state['message'] = "Not authorized";
            goto escape;
        }

        $order = $this->getDoctrine()->getRepository(ProductOrder::class)->findOneBy(array('id' => $orderId));

        if ($order == null) {
            $state['status'] = 404;
            $state['message'] = "Not Found! (order)";
            goto escape;
        }

        if ($order->getAccepted()) {
            $state['status'] = 405;
            $state['message'] = 'Order already accepted';
            goto  escape;
        }

        $productsArr = json_decode($order->getShoppingCart());
        $email = $order->getEmail();

        $prodRepo = $this->getDoctrine()->getRepository(Product::class);
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($productsArr as $id => $quantity) {
            $prod = $prodRepo->findOneBy(array('id' => $id));
            $prod->setSoldCount($prod->getSoldCount() + $quantity);
            $prod->setQuantity($prod->getQuantity() - $quantity);
            $entityManager->merge($prod);
        }
        $order->setAccepted(true);

        $entityManager->flush();


        if ($order->getUserId() != null) {
            $notification = new Notification();
            $notification->setNotificationType("Приета поръчка");
            $notification->setContent("Вашата поръчка номер <a href = '/user/order/$orderId'> $orderId <a/> беше приета");
            $targetUser = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $order->getUserId()));
            if ($targetUser != null)
                $notificationManager->sendToUser($targetUser, $notification);
        }

        $state['status'] = 200;
        $state['message'] = "OK";

        //send mail
        $forgedProds = $cartManager->forgeProductsFromCookie(json_decode($order->getShoppingCart()), $entityManager);
        $message = (new \Swift_Message("Поръчка в Сибирско Здраве - Монтана"))
            ->setFrom([Config::$MAILER_EMAIL_ADDRESS => Config::$MAILER_DISPLAY_NAME])
            ->setTo($order->getEmail())
            ->setBody($this->renderView(
                'mailing/order-accepted-message.html.twig',
                array('order'=>$order, 'forgedProducts'=>$forgedProds)
            ),
                'text/html');

        try {
            $mailer->send($message);
        } catch (Swift_TransportException $e){

        }
        //end sendEmail

        escape:

        return $this->render('queries/generic-query-aftermath-message.twig',
            [
                'error' => json_encode($state)
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
        $order = $this->getDoctrine()->getRepository(ProductOrder::class)->findOneBy(array('id' => $orderId, 'userId' => $this->getUser()->getId()));
        if ($order == null)
            return $this->redirectToRoute('homepage');

        $cartProdArr = $cartManager->forgeProductsFromCookie(json_decode($order->getShoppingCart()), $this->getDoctrine()->getManager());
        $prods = [];

        foreach ($cartProdArr as $product) {
            $prods[] = $product->getProduct();
        }

        return $this->render('user-related/order-observe.html.twig',
            [
                'order' => $order,
                'products' => $prods,
                'forgedProducts' => $cartProdArr,
            ]);
    }


    //private functions
}
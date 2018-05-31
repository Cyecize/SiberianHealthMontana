<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/10/2018
 * Time: 11:00 PM
 */

namespace AppBundle\Controller;


use AppBundle\Constant\ConstantValues;
use AppBundle\Constant\PathConstants;
use AppBundle\Entity\CartProduct;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\ProductOrder;
use AppBundle\Entity\Township;
use AppBundle\Entity\UserAddress;
use AppBundle\Form\UserAddressType;
use AppBundle\Service\CartManager;
use AppBundle\Service\DoctrineNotificationManager;
use AppBundle\Service\ProductManager;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use function Sodium\add;
use Symfony\Bundle\SecurityBundle\Tests\Functional\Bundle\AclBundle\Entity\Car;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProductController extends Controller
{
    function isUserLogged()
    {
        return $this->get('security.authorization_checker')->isGranted('ROLE_USER', 'ROLES');  //when user is logged
    }

    /**
     * @Route("/{catPath}/product/{id}", name="product_details", defaults={"id"=null})
     * @param $id
     * @param ProductManager $productManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function specificProductAction($id, ProductManager $productManager)
    {
        if ($id == null)
            return $this->redirectToRoute("homepage");
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(array('id' => $id, 'hidden' => false));
        if ($product == null) {
            return $this->redirectToRoute("homepage", ['error' => "Невалиден Продукт!"]);
        }

        $relatedProdsP1 = $productManager->getSimilarProducts(3, 0, $product->getFatherCategory()->getId());
        $relatedProdsP2 = $productManager->getSimilarProducts(3, 3, $product->getFatherCategory()->getId());

        return $this->render("default/specific-product.html.twig",
            [
                'product' => $product,
                'relatedProdustsP1' => $relatedProdsP1,
                'relatedProdustsP2' => $relatedProdsP2,
            ]);
    }

    /**
     * @Route("/category/{id}", name="category_details", defaults={"id"=null})
     * @param Request $request
     * @param ProductManager $productManager
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function categoryAction(Request $request, ProductManager $productManager, $id)
    {
        if ($id == null) {
            return $this->redirectToRoute('homepage', ['error' => "404: Категорията не съществува"]);
        }
        $category = $this->getDoctrine()->getRepository(ProductCategory::class)->find($id);
        if ($category == null) {
            return $this->redirectToRoute('homepage', ['error' => "404: Категорията не съществува"]);
        }

        $allProds = $category->getAllProductsRecursive();

        $productPageLimit = ConstantValues::$MAX_PRODUCTS_PER_PAGE;
        $allPages = ceil(count($allProds) / $productPageLimit);
        $currentPage = $request->get('page');
        if ($currentPage == null || $currentPage < 1)
            $currentPage = 1;

        $offset = $currentPage * $productPageLimit - $productPageLimit;


        return $this->render("default/products-page.html.twig",
            [
                'category' => $category,
                'subcategories' => $category->getSubCategories(),
                'catProds' => $allProds,
                'allPages' => $allPages,
                'offset' => $offset,
                'currentPage' => $currentPage,
            ]);
    }

    /**
     * @Route("/shopping-cart", name="shopping_cart")
     * @param Request $request
     * @param CartManager $cartManager
     * @param ProductManager $productManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shoppingCartAction(Request $request, CartManager $cartManager, ProductManager $productManager)
    {

        $arr = $cartManager->getDefaultCartCookie();
        $products = $cartManager->forgeProductsFromCookie($arr, $this->getDoctrine()->getManager());

        $price = 0;
        foreach ($products as $catPr) {
            $price += $catPr->getQuantity() * $catPr->getProduct()->getPrice();
        }

        return $this->render('default/shopping-cart.html.twig',
            [
                'products' => $products,
                'isEmpty' => count($arr) < 1,
                'totalPrice' => round($price, 2),
            ]);
    }

    /**
     * @Route("/cart/checkout", name="shopping_cart_checkout")
     * @param Request $request
     * @param CartManager $cartManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkoutAction(Request $request, CartManager $cartManager)
    {
        $rawProds = $cartManager->getDefaultCartCookie();

        if (count($rawProds) < 1) {
            return $this->redirectToRoute('homepage', ['error' => "Количката ви е празна"]);
        }
        $productRepo = $this->getDoctrine()->getRepository(Product::class);
        $products = [];
        $totalPrice = 0.0;
        foreach ($rawProds as $id => $quantity) {
            $pp = $productRepo->findOneBy(array('id' => $id));
            if ($pp != null) {
                $totalPrice += $pp->getPrice() * $quantity;
                $products[] = $pp;
            }
        }

        $error = $request->get('error');
        $success = $request->get('success');
        $addresses = array();
        if ($this->isUserLogged()) {
            $addresses = $this->getDoctrine()->getRepository(UserAddress::class)->findBy(array('userId' => $this->getUser()->getId()));
        }

        return $this->render('default/checkout.html.twig',
            [
                'addresses' => $addresses,
                'error' => $error,
                'success' => $success,
                'totalPrice' => $totalPrice,
                'products' => $products,
            ]);
    }

    /**
     * @Route("/cart/checkout/commit", name="checkout_commit")
     * @param Request $request
     * @param CartManager $cartManager
     * @param DoctrineNotificationManager $notificationManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function commitCheckoutAction(Request $request, CartManager $cartManager, DoctrineNotificationManager $notificationManager)
    {

        $address = new UserAddress();
        $form = $this->createForm(UserAddressType::class, $address);
        $form->handleRequest($request);
        $email = $request->get('email');
        $state = array('status' => 0, 'message' => null, 'orderId' => null);

        if (!$address->isAddressValid() || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $state['status'] = 500;
            $state['message'] = "Невалиден адрес!";
            goto  escape;
        }
        $township = $this->getDoctrine()->getRepository(Township::class)->findOneBy(array('id' => $address->getTownshipId()));
        if ($township == null) {
            $state['status'] = 500;
            $state['message'] = "Невалидна област!";
            goto  escape;
        }
        $address->setTownship($township);

        $rawProds = $cartManager->getDefaultCartCookieRaw();
        $order = new ProductOrder();
        $order->setShoppingCart($rawProds);
        $order->setEmail($email);
        $order->addAddress($address);

        $totalPrice = $this->getTotalPriceForCart($cartManager);
        $order->setTotalPrice($totalPrice);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($order);
        $entityManager->flush();

        $this->sendOrderNotificationToAdmins($notificationManager, $order->getId());

        $cartManager->unsetDefaultCartCookie();

        $state['status'] = 200;
        $state['message'] = "OK";
        $state['orderId'] = $order->getId();

        escape:
        return $this->render('queries/generic-query-aftermath-message.twig',
            [
                'error' => json_encode($state),
            ]);
    }

    /**
     * @Route("/cart/checkout/commit/logged", name="checkout_commit_logged")
     * @param Request $request
     * @param CartManager $cartManager
     * @param DoctrineNotificationManager $notificationManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function commitCheckoutLoggedAction(Request $request, CartManager $cartManager, DoctrineNotificationManager $notificationManager)
    {
        $state = array('status' => 0, 'message' => null, 'orderId' => null);

        if (!$this->isUserLogged()) {
            $state['status'] = 401;
            $state['message'] = "Unauthorized";
            goto  escape;
        }

        $selectedAddressId = $request->get('address');
        $address = $this->getDoctrine()->getRepository(UserAddress::class)->findOneBy(array('id' => $selectedAddressId));
        if ($address == null) {
            $state['status'] = 404;
            $state['message'] = "Адресът не беше избран";
            goto  escape;
        }

        if ($address->getUserId() != $this->getUser()->getId()) {
            $state['status'] = 401;
            $state['message'] = "Адресът не е намерен!";
            goto  escape;
        }

        $totalPrice = $this->getTotalPriceForCart($cartManager);
        $order = new ProductOrder();
        $order->addAddress($address);
        $order->setEmail($this->getUser()->getEmail());
        $order->setTotalPrice($totalPrice);
        $order->setShoppingCart($cartManager->getDefaultCartCookieRaw());
        $order->setUserId($this->getUser()->getId());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($order);
        $entityManager->flush();

        $this->sendOrderNotificationToAdmins($notificationManager, $order->getId());

        $notification = new Notification();
        $notification->setNotificationType("Изпратена поръчка!");
        $orderId = $order->getId();

        $notification->setContent(
            "<b>Вашата поръчка беше регистрирана!</b><p>Очаквайте обаждане за потвърждаване на поръчка номер:"
            . $orderId .
            "</p><p>Можете да видите статуса на вашата поръчка <a href='/user/order/$orderId'>тук</a> </p>" .
        "<p>Също можете да видите вашите предишни поръчки <a href='/user/orders/all'>тук</a> </p>");
        $notificationManager->sendToUser($this->getUser(), $notification);

        $cartManager->unsetDefaultCartCookie();
        $cartManager->mergeFromCookieToDb($this->getUser(), []);
        $state['status'] = 200;
        $state['message'] = "OK";
        $state['orderId'] = $order->getId();
        escape:
        return $this->render('queries/generic-query-aftermath-message.twig',
            [
                'error' => json_encode($state),
            ]);
    }

    /**
     * @Route("/cart/checkout/success/{id}", name="checkout_commit_success", defaults={"id"=null})
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registeredOrderAction($id)
    {
        $order = $this->getDoctrine()->getRepository(ProductOrder::class)->findOneBy(array('id' => $id));
        if ($order == null) {
            return $this->redirectToRoute('homepage', ['error' => "Имаше проблем с вашата поръчка, моля свържете се с нас!"]);
        }

        return $this->render('default/order-registered.html.twig',
            [
                'order' => $order,
            ]);
    }

    /**
     * @Route("/addToCart/{prodId}", name="add_to_cart", defaults={"prodId"=null})
     * @param Request $request
     * @param $prodId
     * @param CartManager $cartManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addToCartAction(Request $request, $prodId, CartManager $cartManager)
    {
        $quantity = $request->get('quantity');
        if (intval($quantity) == 0 || $quantity == null || $quantity < 1 || $quantity > 10) {
            return $this->render('queries/generic-query-aftermath-message.twig', ['error' => "Невалиден брой! (1-10)"]);
        }

        if ($prodId == null || $prodId < 0)
            return $this->render('queries/generic-query-aftermath-message.twig', ["error" => "Несъществуващ продукт"]);

        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(
            array('id' => $prodId, 'hidden' => false)
        );

        if ($product == null)
            return $this->render('queries/generic-query-aftermath-message.twig', ["error" => "Несъществуващ продукт"]);

        $arr = $cartManager->addNewProdToCart($prodId, $quantity);
        if ($this->isUserLogged())
            $cartManager->mergeFromCookieToDb($this->getUser(), $arr);

        return $this->render('queries/generic-query-aftermath-message.twig', ['error' => "Успешно добавяне в кошницата!"]);
    }

    /**
     * @Route("/erase-shopping-cart", name="erase_cart")
     * @param CartManager $cartManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function clearCartAction(CartManager $cartManager)
    {
        $cartManager->unsetDefaultCartCookie();
        if ($this->isUserLogged()) {
            $cart = $this->getUser()->getCart();
            $cart->setRawProducts("");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->merge($cart);
            $entityManager->flush();
        }
        return $this->render('queries/generic-query-aftermath-message.twig', ['error' => "Успешно изчистихте вашата количка!"]);
    }

    /**
     * @Route("/remove-product-from-cart/{count}", name="remove_product_from_cart")
     * @param CartManager $cartManager
     * @param $count
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeProdFromCartAction(CartManager $cartManager, $count)
    {
        $arr = $cartManager->getDefaultCartCookie();
        $products = $cartManager->forgeProductsFromCookie($arr, $this->getDoctrine()->getManager());

        if ($count == null || $count < 0 || $count >= count($products)) {
            return $this->render('queries/generic-query-aftermath-message.twig', ['error' => "Операцията е невъзможна"]);
        }

        $arr = $cartManager->deleteSpecificProduct($products[$count]);
        if ($this->isUserLogged())
            $cartManager->mergeFromCookieToDb($this->getUser(), $arr);
        return $this->render('queries/generic-query-aftermath-message.twig', ['error' => "Успешно премахване на продукт!"]);
    }

    /**
     * @Route("/check-for-prods", name="check_for_cart_prods")
     * @param CartManager $cartManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkForProductsInCartAction(CartManager $cartManager)
    {
        $countOfProds = [];
        $products = $cartManager->forgeProductsFromCookie($cartManager->getDefaultCartCookie(), $this->getDoctrine()->getManager());
        $countOfProds['products'] = count($products);

        $json = json_encode($countOfProds);

        return $this->render('queries/generic-query-aftermath-message.twig',
            [
                'error' => $json,
            ]);
    }

    /**
     * @Route("/search/{text}", name="search_product", methods={"POST", "GET"}, defaults={"text"=null})
     * @param Request $request
     * @param $text
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request, $text)
    {
        $currentPage = 1;
        if ($request->get('page') != null) {
            if (intval($request->get('page') > 0))
                $currentPage = $request->get('page');
        }
        $searchParam = $request->get('searchText');
        if ($searchParam == null)
            $searchParam = $text;

        $prodRepo = $this->getDoctrine()->getRepository(Product::class);
        $products = $prodRepo->findBy(array('id' => $searchParam));
        if ($products == null) {
            $products = $prodRepo->findBy(array('sibirCode' => $searchParam));
        }

        if ($searchParam == null)
            $searchParam = "";
        if ($products == null) {
            $products = $prodRepo->findByNameRegx($searchParam);
        }

        $allPages = ceil(count($products) / ConstantValues::$MAX_PRODUCTS_PER_PAGE);
        $offset = $currentPage * ConstantValues::$MAX_PRODUCTS_PER_PAGE - ConstantValues::$MAX_PRODUCTS_PER_PAGE;

        return $this->render('default/search-result.html.twig',
            [
                'searchText' => $searchParam,
                'products' => $products,
                'offset' => $offset,
                'currentPage' => $currentPage,
                'allPages' => $allPages,
            ]);
    }


    /**
     * @Route("/cart/checkout/login", name="checkout_login_redirect")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginToCheckoutAction()
    {
        return $this->redirectToRoute('shopping_cart_checkout');
    }


    /**
     * @param CartManager $manager
     * @throws \Exception
     * @return float
     */
    private function getTotalPriceForCart(CartManager $manager): float
    {
        $prodRepo = $this->getDoctrine()->getRepository(Product::class);
        $prodJson = $manager->getDefaultCartCookie();
        $totalPrice = 0.0;
        foreach ($prodJson as $id => $quantity) {
            $p = $prodRepo->findOneBy(array('id' => $id));
            if ($p != null)
                $totalPrice += $p->getPrice() * $quantity;
        }
        return $totalPrice;
    }

    private function sendOrderNotificationToAdmins(DoctrineNotificationManager $notificationManager, int $orderId)
    {
        $message = ConstantValues::$NEW_ORDER_MESSAGE;
        $message = preg_replace('/{{id}}/', $orderId, $message);
        $notification = new Notification();
        $notification->setContent($message);
        $notification->setNotificationType("Поръчка");
        $notificationManager->sendToAdmins($notification);
    }
}
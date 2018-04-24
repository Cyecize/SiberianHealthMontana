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
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductCategory;
use AppBundle\Service\CartManager;
use AppBundle\Service\ProductManager;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     */
    public function specificProductAction(Request $request, $catPath, $id, ProductManager $productManager)
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
     * @Route("/addToCart/{prodId}", name="add_to_cart", defaults={"prodId"=null})
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
     * @Route("/shopping-cart", name="shopping_cart")
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
     * @Route("/erase-shopping-cart", name="erase_cart")
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
     * @Route("/search", name="search_product", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request){
        $searchName= $request->get('searchText');

        //$products =

        return $this->render('default/search-result.html.twig',
            [
               'searchText'=>$searchName,
            ]);
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/13/2018
 * Time: 9:22 PM
 */

namespace AppBundle\Service;


use AppBundle\Constant\ConstantValues;
use AppBundle\Constant\PathConstants;
use AppBundle\Entity\CartProduct;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;


class CartManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param $prodId
     * @param $quantity
     * @return array
     */
    public function addNewProdToCart($prodId, $quantity): array
    {
        $arr = array();
        if (isset($_COOKIE[ConstantValues::$CART_COOKIE_NAME])) {
            $arr = $this->getDefaultCartCookie();
        }
        if (!array_key_exists($prodId, $arr)) {
            $arr[$prodId] = 0;
        }
        $arr[$prodId] += intval($quantity);

        setcookie(ConstantValues::$CART_COOKIE_NAME, json_encode($arr), time() + ConstantValues::$COOKIE_LEASE_TIME, PathConstants::$COOKIE_DEFAULT_PATH);
        return $arr;
    }

    /**
     * @return array
     */
    public function getDefaultCartCookie(): array
    {
        if (isset($_COOKIE[ConstantValues::$CART_COOKIE_NAME])) {
            return json_decode($_COOKIE[ConstantValues::$CART_COOKIE_NAME], true);
        }
        return array();
    }

    /**
     * @return string
     */
    public function getDefaultCartCookieRaw() : string {
        if (isset($_COOKIE[ConstantValues::$CART_COOKIE_NAME])) {
            return $_COOKIE[ConstantValues::$CART_COOKIE_NAME];
        }
        return "";
    }

    /**
     * @return  void
     */
    public function unsetDefaultCartCookie() : void
    {
        setcookie(ConstantValues::$CART_COOKIE_NAME, null, time() - ConstantValues::$COOKIE_LEASE_TIME, PathConstants::$COOKIE_DEFAULT_PATH);
    }

    /**
     * @param $prods
     * @param ObjectManager $entityManager
     * @return CartProduct[]
     */
    public function forgeProductsFromCookie($prods, ObjectManager $entityManager)
    {
        //prodId   quantity
        $res = array();
        foreach ($prods as $id => $quantity) {
            $product = $entityManager->getRepository(Product::class)->find($id);
            if ($product == null)
                continue;
            $res[] = new CartProduct($product, intval($quantity));
        }

        return $res;
    }

    /**
     * @param CartProduct $product
     * @return array
     */
    public function deleteSpecificProduct(CartProduct $product)
    {
        $arr = $this->getDefaultCartCookie();
        unset($arr[$product->getProduct()->getId()]);
        $this->updateCookie($arr);
        return $arr;
    }

    /**
     * @param User $user
     * @param ObjectManager $entityManager
     */
    public function mergeCartWithDb(User $user, ObjectManager $entityManager)
    {
        $arr = $this->getDefaultCartCookie();
        $dbArr = $user->getCart()->getDecodedProducts();

        $arr = $this->mergeCarts($dbArr, $arr);
        $dbArr = $this->mergeCarts($arr, $dbArr);

        $cart = $user->getCart();
        $cart->setRawProducts(json_encode($dbArr));
        $entityManager->merge($cart);
        $entityManager->flush();

        $this->updateCookie($arr);
    }

    /**
     * @param User $user
     * @param $cartArr
     */
    public function mergeFromCookieToDb(User $user, $cartArr)
    {
        $cart = $user->getCart();
        $cart->setRawProducts(json_encode($cartArr, true));
        $this->entityManager->merge($cart);
        $this->entityManager->flush();
    }

    /**
     * @param $arr
     */
    public function updateCookie($arr)
    {
        setcookie(ConstantValues::$CART_COOKIE_NAME, json_encode($arr), time() + ConstantValues::$COOKIE_LEASE_TIME, PathConstants::$COOKIE_DEFAULT_PATH);
    }

    private function mergeCarts($donator, $recipient)
    {
        foreach ($donator as $id => $quantity) {
                if (!array_key_exists($id, $recipient)) {
                    $recipient[$id] = 0;
                }
                $recipient[$id] = intval($donator[$id]);
        }
        return $recipient;
    }

}
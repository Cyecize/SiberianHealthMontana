<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/13/2018
 * Time: 11:11 PM
 */

namespace AppBundle\Entity;


class CartProduct
{
    /**
     * @var Product
     */
    private $product;



    /**
     * @var int
     */
    private $quantity;

    /**
     * CartProduct constructor.
     * @param Product $product
     * @param int $quantity
     */
    public function __construct(Product $product, $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }




}
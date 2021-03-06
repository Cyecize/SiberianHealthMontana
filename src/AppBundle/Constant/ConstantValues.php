<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/10/2018
 * Time: 4:26 PM
 */

namespace AppBundle\Constant;


class ConstantValues
{
    /**
     * @var int
     */
    public static $MAX_PRODUCTS_PER_SLIDE = 4;

    /**
     * @var string
     */
    public static $WEBSITE_NAME = "Сибирско здраве Монтана";

    /**
     * @var int
     */
    public static $MAX_PRODUCTS_PER_PAGE = 9;

    /**
     * @var string
     */
    public static $CART_COOKIE_NAME = "cart";

    /**
     * @var int
     */
    public static $COOKIE_LEASE_TIME = 2592000;

    /**
     * @var int
     */
    public static $MAX_UPLOAD_FILESIZE = 6291456;

    /**
     * @var float
     */
    public static $DELIVERY_PRICE = 3.99;

    /**
     * @var int
     */
    public static $MAX_SEARCH_RESULTS = 60;

    /**
     * @var int
     */
    public static $MAX_ORDERS_PER_PAGE = 10;

    /**
     * @var string
     * TODO function will replace the {{id}} with order ID
     */
    public static $NEW_ORDER_MESSAGE = 'Нова поръчка! Номер на поръчката -> <a href="/admin/order/{{id}}">{{id}}</a>';

    /**
     * @var string
     * TODO replace email and question upon sending notification
     */
    public static $NEW_CONTACT_US_MESSAGE = 'Ново запитване! <br> Email: {{email}}, <br> Име: {{name}} <br> Въпрос: <br><p>{{question}}</p>';

}
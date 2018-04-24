<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/10/2018
 * Time: 2:15 AM
 */

namespace AppBundle\Constant;


class PathConstants
{
    public static $FLEX_BANNER_PATH = "content" . DIRECTORY_SEPARATOR . "banners" . DIRECTORY_SEPARATOR;

    public static $CATEGORIES_PATH = "content" . DIRECTORY_SEPARATOR . "categories" . DIRECTORY_SEPARATOR;

    public static $GALLERIES_PATH = "content" . DIRECTORY_SEPARATOR . "galleries" . DIRECTORY_SEPARATOR;

    public static $PAGE_BANNER_PATH = "styles" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "pageBanner.png";

    public static $COOKIE_DEFAULT_PATH = "/";

    public static $TEMPORARY_UPLOAD_DIRECTORY =  "temp-files" . DIRECTORY_SEPARATOR. "temp-uploads" . DIRECTORY_SEPARATOR; // fileName

    public static $TEMPORARY_OUTPUT_DIRECTORY =  "temp-files" . DIRECTORY_SEPARATOR. "temp-outputs" . DIRECTORY_SEPARATOR; // fileName


}
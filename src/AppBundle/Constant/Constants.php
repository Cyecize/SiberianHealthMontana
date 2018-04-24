<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 11/15/2017
 * Time: 12:54 AM
 */

namespace AppBundle\Constant;


class Constants
{
    public static $galleryPath = "product-gallery" . DIRECTORY_SEPARATOR. "galleries" . DIRECTORY_SEPARATOR; // productId/imgName.jpg
    public static $categoriesPath = "product-gallery/categories/";

    public static $TEMPORARY_UPLOAD_DIRECTORY =  "temp-files" . DIRECTORY_SEPARATOR. "temp-uploads" . DIRECTORY_SEPARATOR; // fileName
    public static $TEMPORARY_OUTPUT_DIRECTORY =  "temp-files" . DIRECTORY_SEPARATOR. "temp-outputs" . DIRECTORY_SEPARATOR; // fileName

    public static $projectDirectory = "C:".DIRECTORY_SEPARATOR."z_symfony".DIRECTORY_SEPARATOR."zatvarachki".DIRECTORY_SEPARATOR."zatvarachki".DIRECTORY_SEPARATOR."zatvarachki".DIRECTORY_SEPARATOR ."web" . DIRECTORY_SEPARATOR;

    public static $MAX_UPLOAD_FILESIZE = 6291456;

    public static function maxUploadSizeInBytes(){ //defined in php settings
        $maxSize = ini_get('upload_max_filesize');
        trim($maxSize);
        $val = substr($maxSize, 0, strlen($maxSize) -1);
        return $val  * 1024 * 1024;
    }

    public static $passowordLen = 6;

    public static $mailer = "ceci_nfs9@abv.bg";
    public static $mailerTarget = "ceci2205@abv.bg";
    public static $mailerAs = "Zatvarachki BG";

    //public const DOMAIN_NAME = "http://localhost:8000";
    public static $DOMAIN_NAME = "http://zatvarachki.com";
    //todo php7.0 does not support these constants
}
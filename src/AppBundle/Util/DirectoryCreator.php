<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/10/2018
 * Time: 8:00 PM
 */

namespace AppBundle\Util;


use AppBundle\Constant\ConstantValues;
use AppBundle\Constant\PathConstants;

class DirectoryCreator
{
    public static function createProductDirectory($productId, $categoryName){
        if(!is_dir(PathConstants::$CATEGORIES_PATH . $productId))
            mkdir(PathConstants::$CATEGORIES_PATH . $categoryName. DIRECTORY_SEPARATOR.  $productId, 0777, true);
    }

    public static function createCategoryDirectory($categoryName){
        if (!is_dir(PathConstants::$CATEGORIES_PATH . $categoryName))
            mkdir(PathConstants::$CATEGORIES_PATH . $categoryName, 0777, true);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 11/21/2017
 * Time: 10:00 PM
 */

namespace AppBundle\Util;


class ImageCompressorManager
{
    public static function createSettingsForImage($tempDirectory) : array {
        $setting = array(
            'directory' => $tempDirectory, // directory file compressed output
            'file_type' => array( // file format allowed
                'image/jpeg',
                'image/png',
                'image/gif'
            )
        );
        return $setting;
    }

    public static function compress(ImgCompressor $compressor, $imagePath) : string {
        $result = $compressor->run($imagePath, 'jpg', 2);
        $compressedImgName = $result['data']['compressed']['name'];
        return $compressedImgName;
    }

    public static function isValidImage($arrName) :bool {
        $imageName = $_FILES['image']['name'][$arrName];
        if($imageName == null)
            return false;
        $allowedExtensions = array("jpg", "png");
        $imgSlicedName = explode(".", $imageName);
        $isValidImageExtension = false;
        foreach ($imgSlicedName as $item) {
            if(in_array(strtolower($item), $allowedExtensions)){
                $isValidImageExtension = true;
                break;
            }
        }
        return $isValidImageExtension;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 11/30/2017
 * Time: 11:35 AM
 */

namespace AppBundle\Util;


class CharacterTranslator
{
    private  $latins = [
        'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
        'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
        'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
        'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
    ];

    private $cyrilics = [
        'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
        'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
        'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
        'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
    ];

    public  function convertFromCyrilicToLatin(string $cyrilicText) : string {
        return str_replace($this->cyrilics, $this->latins, $cyrilicText);
    }

    public function convertFromLationToCyrilic(string  $latinText) : string {
        return str_replace($this->latins, $this->cyrilics, $latinText);
    }


    public static function parsebb($body) {
        $find = array(
            "@\n@",
            "@[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]@is",
            "/\[url\=(.+?)\](.+?)\[\/url\]/is",
            "/\[b\](.+?)\[\/b\]/is",
            "/\[i\](.+?)\[\/i\]/is",
            "/\[u\](.+?)\[\/u\]/is",
            "/\[color\=(.+?)\](.+?)\[\/color\]/is",
            "/\[size\=(.+?)\](.+?)\[\/size\]/is",
            "/\[font\=(.+?)\](.+?)\[\/font\]/is",
            "/\[center\](.+?)\[\/center\]/is",
            "/\[right\](.+?)\[\/right\]/is",
            "/\[left\](.+?)\[\/left\]/is",
            "/\[img\](.+?)\[\/img\]/is",
            "/\[email\](.+?)\[\/email\]/is"
        );
        $replace = array(
            "<br />",
            "<a href=\"\\0\">\\0</a>",
            "<a href=\"$1\" target=\"_blank\">$2</a>",
            "<strong>$1</strong>",
            "<em>$1</em>",
            "<span style=\"text-decoration:underline;\">$1</span>",
            "<font color=\"$1\">$2</font>",
            "<font size=\"$1\">$2</font>",
            "<span style=\"font-family: $1\">$2</span>",
            "<div style=\"text-align:center;\">$1</div>",
            "<div style=\"text-align:right;\">$1</div>",
            "<div style=\"text-align:left;\">$1</div>",
            "<img src=\"$1\" alt=\"Image\" />",
            "<a href=\"mailto:$1\" target=\"_blank\">$1</a>"
        );
        $body = htmlspecialchars($body);
        $body = preg_replace($find, $replace, $body);
        return $body;
    }
}
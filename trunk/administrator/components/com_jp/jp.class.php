<?php
/**
 * @version     $Id$
 * @package JPromoter for Joostina
 * @copyright Авторские права (C) JPromoter team & (C) Joostina team &. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * JPromoter for Joostina - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */
//echo http_parse_cookie("test; ; /;");

defined('_VALID_MOS') or die('Restricted access');

define('GOOGLE_MAGIC', 0xE6359A60);

function jpGetKeyWords($text)
{

    $func = create_function('$c', 'return preg_replace("/[^\n\t]/", " ", $c[0]);');
    $b_regexp[] = "/<!--.*-->/isU";
    $b_regexp[] = "/<object\b.*>.*<\/object>/isU";
    $b_regexp[] = "/<script\b.*>.*<\/script>/isU";
    $b_regexp[] = "/<head\b.*>.*<\/head>/isU";
    $b_regexp[] = "/<style\b.*>.*<\/style>/isU";
    $b_regexp[] = "/<link\b.*rel\b.*>/isU";
    $b_regexp[] = "/<br\b\s?\/?\s?.*>/isU";
    $b_regexp[] = "/<hr\b\s?\/?\s?.*>/isU";
    $b_regexp[] = "/<nobr\b\s?\/?\s?.*>/isU";

    foreach ($b_regexp as $regexp)
        $text = preg_replace_callback($regexp, $func, $text);
    $text = strip_tags($text);
    $text = ereg_replace("(\&[^;]*;)", ' ', $text);
    $text = preg_replace("/[,\:\|\/\?\(\)\~\#\$\%\^\&\*\+\\\{\}]+/su", " ", $text);
    $text = str_replace("\n", ' ', $text);

    $words = $text;
    $words = explode(' ', $words);
    $skip_words = jpGetSkipWords();
    $finish = array();
    foreach ($words as $word) {
        $word = strtolower(trim($word));
        if (!$word)
            continue;
        if (ereg("[0-9]+", $word))
            continue;
        if (is_float($word))
            continue;
        if (strlen($word) <= 2)
            continue;
        if (in_array($word, $skip_words))
            continue;
        if (@$finish[$word])
            @$finish[$word]++;
        else
            $finish[$word] = 1;
    }

    arsort($finish);

    $amount = JEConfig::get('general.jp_kw_per_page', 'com_jp');
    array_splice($finish, $amount);
    unset($finish[0]);
    return $finish;
}

class jpParser
{

    function jpParser($url)
    {

        global $mosConfig_live_site, $database;

        $this->fullhtml = '';
        $this->mdescription = '';
        $this->mkeywords = '';
        $this->mtitle = '';
        $this->ptitle = '';
        $this->url = '';
        $this->size = 0;
        $this->errorcode = '0';
        $this->html = '';
        $this->url = $url;
        $this->urli = parse_url($url);
        $this->status = 'GOOD';
        $this->contentType = '';
        $this->location = '';
        $this->page_size = 0;
        $this->images_size = 0;
        $this->type = substr($url, 0, strlen($mosConfig_live_site)) == $mosConfig_live_site ?
            'internal' : 'external';
        $this->keywords = '';

        $pageHeader = $this->page_get_contents($url, ($this->urli['scheme'] == 'http' ? false : true), true);

        if ($pageHeader === false) {
            $this->status = 'NOT_FOUND';
            return true;
        }
        ;

        $this->headers = $this->parse_headers($pageHeader);

        foreach ($this->headers as $header) {
            if (preg_match("/Content-Type: ([^\r\n]+)/i", $header, $match)) {
                $contType = strtolower(substr(trim($match[1]), 0, 9));

                $this->contentType = strtolower(trim($match[1]));

                if ($contType != 'text/html') {
                    $this->status = 'NO_HTML_CONTENT';
                    $this->skip = true;
                    return;
                }
            } elseif (preg_match("/^set-cookie\s?:\s?(.*)/i", $header, $cookie)) {
                $cooks = explode(";", $cookie[1]);
                foreach ($cooks as $cook) {
                    $vals = explode("=", trim($cook));
                    if ($vals[0] == 'path')
                        $indx = $vals[1];
                }
                reset($cooks);
                foreach ($cooks as $cook) {
                    $vals = explode("=", trim($cook));
                    @$_SESSION['jpCookie'][$indx][$vals[0]] = @$vals[1];
                }
            } elseif (preg_match("/^location\s?:\s?(.*)/i", $header, $location)) {
                $this->location = $location[1];
                $this->status = 'MOVED';
                return true;
            } elseif (preg_match("|^HTTP/[\d\.x]+ (\d+) |", $header, $match)) {
                /*
                200 OK
                301 Moved Permanently
                302 Found
                304 Not Modified
                307 Temporary Redirect
                400 Bad Request
                401 Unauthorized
                403 Forbidden
                404 Not Found
                410 Gone
                500 Internal Server Error
                501 Not Implemented
                */
                $this->errorcode = trim($match[1]);

            }
        }

        if ($this->urli['scheme'] . '://' . $this->urli['host'] == $mosConfig_live_site and
            (!in_array($this->errorcode, array('400', '401', '403', '404', '410', '500',
            '501')))) {
            switch ($this->urli['scheme']) {
                case 'http':
                    $this->html = $this->page_get_contents($url);
                    break;
                case 'https':
                    $this->html = $this->page_get_contents($url, true);
                    break;
            }
        }
        ;

        if ($this->html) {

            $keywords = jpGetKeyWords($this->html);

            foreach ($keywords as $word => $wcount) {
                $this->keywords .= $word . ',' . $wcount . ';';
            }

            $this->keywords = str_replace(array('\'', '\"'), '', $this->keywords);

            $this->size = strlen($this->html);
            $this->page_size = $this->size;

            $this->html = str_replace("\r\n", "\n", $this->html);
            $this->fullhtml = $this->html;

            if (preg_match("/\<HEAD\>.*\<\/HEAD\>/isU", $this->html, $head)) {
                $head = trim($head[0]);
                if (!empty($head)) {
                    $this->ptitle = $this->getTitle($head);
                    $this->mtitle = @implode(",", $this->getMeta($head, 'title', 'name'));
                    $this->mkeywords = @implode(",", $this->getMeta($head, 'keywords', 'name'));
                    $this->mdescription = @implode(",", $this->getMeta($head, 'description', 'name'));
                }
            }
        }
    }

    function parse_headers($header)
    {
        $header = trim($header);
        return explode("\r\n", $header);
    }

    function getCookie($vals)
    {
        $str = '';
        foreach ($vals as $key => $val) {
            if ($key == 'path')
                continue;
            $str .= "$key=$val; ";
        }
        return $str;
        //print_r();
    }

    function page_get_contents($url, $https = false, $headers = false)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT,
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'jpromoterbot');

        if (isset($_SESSION['jpCookie'])) {
            foreach ($_SESSION['jpCookie'] as $path => $vals) {
                curl_setopt($ch, CURLOPT_COOKIE, $this->getCookie($vals) . "path=$path");
            }
        }

        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        if ($headers == true) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
        }

        $result = curl_exec($ch);

        $err = curl_errno($ch);

        curl_close($ch);

        return $err == 0 ? $result : false;

    }

    function getTitle($head)
    {
        preg_match("/<title>(.*)<\/title>/isU", $head, $title);
        if ($title[1])
            return $title[1];
    }

    function getMeta($head, $tagname = null, $type = 'all')
    {

        $Meta = array();
        $c = 0;

        preg_match_all("/<meta\b.*>/isU", $head, $metas);

        foreach ($metas[0] as $m) {
            preg_match("/<meta\b(.*)>/is", $m, $meta);
            $meta = trim($meta[1]);
            if (substr($meta, -1, 1) == '/')
                $meta = trim(substr($meta, 0, -1));

            if (preg_match("/http\-equiv[\t\ ]*=[\t\ ]*([\'\"]+)?([^\'\"]*)\\1?.*/is", $meta,
                $v))
                $Meta[$c]['http-equiv'] = trim($v[2]);
            elseif (preg_match("/name[\t\ ]*=[\t\ ]*([\'\"]+)?([^\'\"]*)\\1?.*/is", $meta, $v))
                $Meta[$c]['name'] = trim($v[2]);
            if (preg_match("/content[\t\ ]*=[\t\ ]*([\'\"]+)?([^\'\"]*)\\1?.*/is", $meta, $v))
                $Meta[$c]['content'] = trim($v[2]);
            $c++;
        }

        if ($tagname) {
            foreach ($Meta as $meta) {
                switch (strtolower($type)) {
                    case 'http-equiv':
                        if (preg_match("/" . preg_quote($tagname) . "/i", $meta['http-equiv']))
                            $o[] = $meta['content'];
                        break;

                    case 'name':
                        if (preg_match("/" . preg_quote($tagname) . "/i", $meta['name']))
                            $o[] = $meta['content'];
                        break;

                    case 'all':
                        if (preg_match("/" . preg_quote($tagname) . "/i", $meta['name']) || preg_match("/" .
                            preg_quote($tagname) . "/i", $meta['http-equiv']))
                            $o[] = $meta['content'];
                        break;
                }
            }


            return $o;

        }
        return $Meta;
    }

    function getLinks()
    {

        $Anchors = array();
        $c = 0;
        preg_match_all("/<a\b.*>.*<\/a>/isU", $this->html, $anchors, PREG_OFFSET_CAPTURE);

        $ta = array('alt', 'title', 'href');

        //print_r($anchors);

        foreach ($anchors[0] as $l) {
            $Anchors[$c] = $this->parseTags($l[0], $l[1]);

            foreach ($Anchors[$c] as $key => $val) {
                if (in_array($key, $ta)) {
                    $Anchors[$c][$key] = $val;
                }
            }

            if (isset($Anchors[$c]['href'])) {
                $Anchors[$c]['href'] = trim(str_replace("&amp;", "&", $Anchors[$c]['href']));
            }
            ;

            $c++;
        }

        return $Anchors;
    }

    function getImages()
    {

        $Images = array();
        $c = 0;

        preg_match_all("/<img[\s].*>/isU", $this->html, $anchors, PREG_OFFSET_CAPTURE);

        $ta = array('src', 'alt');

        foreach ($anchors[0] as $l) {

            $Images[$c] = $this->parseTags($l[0], $l[1]);
            //echo "dfgdfgdfh+++++";
            //print_r($Images[$c]);
            foreach ($Images[$c] as $key => $val) {
                if (in_array($key, $ta)) {
                    $Images[$c][$key] = $val;
                }
            }

            $Anchors[$c]['src'] = str_replace("&amp;", "&", $Images[$c]['src']);

            $c++;

        }
        return $Images;
    }

    function parseTags($fulltag, $position = null)
    {

        $fulltag = trim($fulltag);


        if (preg_match("/<(\w+?)\b\s?(.*)>(.*)<\/\\1>/isU", $fulltag, $a)) {

            if ($tmp = trim($a[1]))
                $Anchors['tagname'] = $tmp;
            if ($tmp = trim($a[0]))
                $Anchors['full'] = $tmp;
            if ($tmp = trim($a[3]))
                $Anchors['fulltext'] = $tmp; // to UTF-8
            if ($tmp = trim($a[3]))
                $Anchors['innerHTML'] = $tmp;
            if ($tmp = trim(strip_tags(($a[3]))))
                $Anchors['text'] = $tmp; // to UTF-8

            // Номер позиции в искомой строке
            if ($position)
                $Anchors['_pos'] = $position;


            // выдираем имена аттрибутов...
            preg_match_all("/[\s]([\w\-]+)[\s]*=[\s]*/im", $a[2], $attr);

            foreach ($attr[0] as $kk => $attr_name) {

                $attr_v = null;
                $p = strpos($a[2], $attr_name); // Позиция начала аттрибута
                $param = substr($a[2], $p + strlen($attr_name), 1); // 1й символ значения аттрибута

                // Обрабатываем 3 случая вида значения аттрибута

                if ($param == "'") {
                    $is = substr($a[2], ($p + strlen($attr_name) + 1));
                    $attr_v = substr($is, 0, strpos($is, "'"));
                } elseif ($param == '"') {
                    $is = substr($a[2], ($p + strlen($attr_name) + 1));
                    $attr_v = substr($is, 0, strpos($is, '"'));

                } else {
                    $a[2] .= ">";
                    $is = substr($a[2], ($p + strlen($attr_name)));
                    $stop = strpos($is, ' ') ? strpos($is, ' ') : strpos($is, '>');
                    $attr_v = substr($is, 0, $stop);
                }

                // Добавляем параметры в общий массив
                $Anchors[strtolower($attr[1][$kk])] = $attr_v;
            }

        }


        // Тэг простой
        elseif (preg_match("/<(\w+)(.*)>/isU", $fulltag, $a)) {
            if ($tmp = trim($a[1]))
                $Anchors['tagname'] = $tmp;
            if ($tmp = trim($a[0]))
                $Anchors['full'] = $tmp;

            // выдираем имена аттрибутов...
            preg_match_all("/[\s]([\w\-]+)[\s]*=[\s]*/im", $a[2], $attr);

            foreach ($attr[0] as $kk => $attr_name) {

                $attr_v = null;
                $p = strpos($a[2], $attr_name); // Позиция начала аттрибута
                $param = substr($a[2], $p + strlen($attr_name), 1); // 1й символ значения аттрибута

                // Обрабатываем 3 случая вида значения аттрибута

                if ($param == "'") {

                    $is = substr($a[2], ($p + strlen($attr_name) + 1));
                    $attr_v = substr($is, 0, strpos($is, "'"));

                } elseif ($param == '"') {

                    $is = substr($a[2], ($p + strlen($attr_name) + 1));
                    $attr_v = substr($is, 0, strpos($is, '"'));

                } else {

                    $a[2] .= ">";

                    $is = substr($a[2], ($p + strlen($attr_name)));
                    $stop = strpos($is, ' ') ? strpos($is, ' ') : strpos($is, '>');
                    $attr_v = substr($is, 0, $stop);

                }

                // Добавляем параметры в общий массив
                $Anchors[strtolower($attr[1][$kk])] = $attr_v;

            }

        }
        //print_r($Anchors);
        return $Anchors;
    }

}

class jpRank
{
    function zeroFill($a, $b)
    {
        $z = hexdec(80000000);
        if ($z & $a) {
            $a = ($a >> 1);
            $a &= ( ~ $z);
            $a |= 0x40000000;
            $a = ($a >> ($b - 1));
        } else {
            $a = ($a >> $b);
        }
        return $a;
    }

    function mix($a, $b, $c)
    {
        $a -= $b;
        $a -= $c;
        $a ^= (jpRank::zeroFill($c, 13));
        $b -= $c;
        $b -= $a;
        $b ^= ($a << 8);
        $c -= $a;
        $c -= $b;
        $c ^= (jpRank::zeroFill($b, 13));
        $a -= $b;
        $a -= $c;
        $a ^= (jpRank::zeroFill($c, 12));
        $b -= $c;
        $b -= $a;
        $b ^= ($a << 16);
        $c -= $a;
        $c -= $b;
        $c ^= (jpRank::zeroFill($b, 5));
        $a -= $b;
        $a -= $c;
        $a ^= (jpRank::zeroFill($c, 3));
        $b -= $c;
        $b -= $a;
        $b ^= ($a << 10);
        $c -= $a;
        $c -= $b;
        $c ^= (jpRank::zeroFill($b, 15));

        return array($a, $b, $c);
    }

    function GoogleCH($url, $length = null, $init = GOOGLE_MAGIC)
    {
        if (is_null($length)) {
            $length = sizeof($url);
        }
        $a = $b = 0x9E3779B9;
        $c = $init;
        $k = 0;
        $len = $length;
        while ($len >= 12) {
            $a += ($url[$k + 0] + ($url[$k + 1] << 8) + ($url[$k + 2] << 16) + ($url[$k + 3] <<
                24));
            $b += ($url[$k + 4] + ($url[$k + 5] << 8) + ($url[$k + 6] << 16) + ($url[$k + 7] <<
                24));
            $c += ($url[$k + 8] + ($url[$k + 9] << 8) + ($url[$k + 10] << 16) + ($url[$k +
                11] << 24));
            $mix = jpRank::mix($a, $b, $c);
            $a = $mix[0];
            $b = $mix[1];
            $c = $mix[2];
            $k += 12;
            $len -= 12;
        }

        $c += $length;
        switch ($len) /* all the case statements fall through */ {
            case 11:
                $c += ($url[$k + 10] << 24);
            case 10:
                $c += ($url[$k + 9] << 16);
            case 9:
                $c += ($url[$k + 8] << 8);
                /* the first byte of c is reserved for the length */
            case 8:
                $b += ($url[$k + 7] << 24);
            case 7:
                $b += ($url[$k + 6] << 16);
            case 6:
                $b += ($url[$k + 5] << 8);
            case 5:
                $b += ($url[$k + 4]);
            case 4:
                $a += ($url[$k + 3] << 24);
            case 3:
                $a += ($url[$k + 2] << 16);
            case 2:
                $a += ($url[$k + 1] << 8);
            case 1:
                $a += ($url[$k + 0]);
                /* case 0: nothing left to add */
        }
        $mix = jpRank::mix($a, $b, $c);
        /*-------------------------------------------- report the result */
        return $mix[2];
    }

    //converts a string into an array of integers containing the numeric value of the char
    function strord($string)
    {
        for ($i = 0; $i < strlen($string); $i++) {
            $result[$i] = ord($string{$i});
        }
        return $result;
    }

    function getGoogleRank($url)
    {
        $url = 'info:' . $url;

        //$dothis         = Application::getCnf('use_google_pr','Links');
        //if($dothis == 'N')
        //{
        //    return FALSE;
        //}

        $ch = jpRank::GoogleCH(jpRank::strord($url));
        $file = "http://www.google.com/search?client=navclient-auto&ch=6$ch&features=Rank&q=$url";
        $data = @file_get_contents($file);

        if ($data) {
            $rankarray = explode(':', $data);
            $rank = $rankarray[2];
            return $rank;
        } else {
            return false;
        }
    }

    function getAlexaRank($url)
    {
        if (empty($url)) {
            return '';
        }

        $link_url = str_replace('http://', '', $url);

        $path = "http://www.alexa.com/data/details/main?q=&url=http://" . $link_url;
        if (!file_exists($path)) {
            $data = strtolower(strip_tags(@implode("", @file($path))));
            $data = substr($data, strpos($data, "traffic rank for ") + 17, strlen($data));
            $data = str_replace(str_replace('www.', '', $link_url), '', $data);
            $data = str_replace(':&nbsp;', '', $data);
            $data = trim(substr($data, 0, strpos(trim($data), ' ') - 1)); //echo "$data<br>"; // TEST
            if (eregi("[[:alpha:]]", $data)) {
                $result = '';
            } else {
                $result = $data;
            }
        } else {
            $result = '';
        }

        return $result;

        /* OLD
        $AWSAccessKeyId = JEConfig::get("general.jp_alexa_key", 'com_jp');
        $secretkey      = JEConfig::get("general.jp_alexa_secret_key", 'com_jp');
        $dothis         = JEConfig::get("general.jp_alexa_pr", 'com_jp');
        
        if((!$AWSAccessKeyId || !$secretkey) && !$dothis)
        {
        return FALSE;
        }
        $service        = 'AlexaWebInfoService';
        $operation      = 'UrlInfo';
        
        $timestamp = date(
        "Y-m-d\TH:i:s.\\0\\0\\0\\Z", 
        mktime((date('h') + 6), date('i'), date('s'), date('m'), date('d'), date('Y'))
        );
        
        $data = $service.''.$operation.''.$timestamp;
        
        $signature = jpRank::calculate_RFC2104HMAC ($data, $secretkey);

        //        $URL = "http://aws.amazon.com/onca/xml?" .
        //        "Service=$service" .
        //        "&AWSAccessKeyId=$AWSAccessKeyId" .
        //        "&Operation=$operation&ResponseGroup=Rank" .
        //        "&Url=$url" .
        //        "&Timestamp=$timestamp" .
        //        "&Signature=$signature";

        $URL = 
        'Action=UrlInfo' .
        "&AWSAccessKeyId=$AWSAccessKeyId" .
        "&Signature=$signature" .
        "&Timestamp=$timestamp" .
        "&Url=$url" .
        "&ResponseGroup=Rank";

        $xml = null;
        
        $xml = @file_get_contents('http://awis.amazonaws.com/?'.$URL);
        if ($xml) 
        {
        preg_match('/\<Rank\>(\d+)\<\/Rank\>/', $xml, $res);
        return $res[1] == 0 ? false : $res[1];
        } else 
        {
        return false;
        }
        */
    }

    function calculate_RFC2104HMAC($data, $key)
    {
        return base64_encode(pack("H*", sha1((str_pad($key, 64, chr(0x00)) ^ (str_repeat
            (chr(0x5c), 64))) . pack("H*", sha1((str_pad($key, 64, chr(0x00)) ^ (str_repeat
            (chr(0x36), 64))) . $data)))));
    }
}

?>


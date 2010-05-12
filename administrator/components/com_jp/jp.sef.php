<?php
/**
 * @version     $Id$
 * @package JPromoter for Joostina
 * @copyright Авторские права (C) JPromoter team & (C) Joostina team &. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * JPromoter for Joostina - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */

defined('_VALID_MOS') or die();
$_SERVER['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']);//Здесь сделать проверку на использование mod_rewrite и наличие if (Jstring::substr($_SERVER['REQUEST_URI'], 0, 10) != '/index.php') {
$fst = preg_quote("&gclid=");
$_SERVER['REQUEST_URI'] = preg_replace("#$fst*(.*?).*#", '', $_SERVER['REQUEST_URI']);
$fst = preg_quote("?gclid=");
$_SERVER['REQUEST_URI'] = preg_replace("#$fst*(.*?).*#", '', $_SERVER['REQUEST_URI']);

$withoutModrewrite = JEConfig::get('SEF.jp_mod_rewrite', 'com_jp');//isset($temp['options']['encoding']) ? $temp['options']['encoding'] : 'UTF-8';
//$urlTranslit = JEConfig::get('SEF.jp_url_translit', 'com_jp');
//define('NO_MOD_REWRITE', '/index.php' );

function urlTranslit($string)
{
    $replacedLetters = array(); //Таблица транслитерации
    $string = Jstring::strtolower($string);
    //$string = Jstring::strtr($string, $replacedLetters);
    $string = preg_replace('/[^\p{L}\p{Nd}\/0-9]+/u', '-', $string); //http://habrahabr.ru/blogs/php/45910/
    $string = preg_replace('/-\//u', '/', $string);
    $string = preg_replace('/\/-/u', '/', $string);
    $string = Jstring::trim($string, '-');
    return $string;
}

// JPromoter END ---------------------------------------------------------------

if ($mosConfig_sef) {

    $_MAMBOTS->trigger('jpBeforeSEF', null);

    // JPromoter BEGIN -------------------------------------------------------------

    $foundURL = false;
    $Exclusion = array();
    $database->setQuery('SELECT original, sef FROM #__jp_pages');
    $originalAndSefUrls = $database->loadAssocList('original');

    if (Jstring::substr($_SERVER['REQUEST_URI'], 0, 10) != '/index.php') {

        $inURL = $_SERVER['REQUEST_URI'];

        $tempPU = parse_url($mosConfig_live_site);
        if (!empty($tempPU['path'])) {
            $sitePath = $tempPU['path'];
        } else {
            $sitePath = '';
        }

        $inURL = Jstring::substr($inURL, Jstring::strlen($sitePath));

        $fragment = '';

        $sharpPos = Jstring::strpos($inURL, '#');

        if ($sharpPos !== false) {
            $fragment = Jstring::substr($inURL, $sharpPos);
            $inURL = Jstring::substr($inURL, 0, $sharpPos);
        }

        foreach ($originalAndSefUrls as $orAndSef) {

            if (@$orAndSef['sef'] == $inURL and @$orAndSef['sef'] != '') {

                // �������� �� �������� 301 ?

                //            header('HTTP/1.1 301 Moved Permanently');
                //            header('Location: '. $mosConfig_live_site . $orAndSef['original'] . $fragment);
                //            die();

                // ��� ���������, ���� ���������� ������ � �����������

                $_SERVER['REQUEST_URI'] = $sitePath . $orAndSef['original'] . $fragment;

                $pUrl = parse_url($_SERVER['REQUEST_URI']);

                if (isset($pUrl['query'])) {

                    $_SERVER['QUERY_STRING'] = $pUrl['query'];

                    parse_str($pUrl['query'], $pQuery);

                    $_REQUEST = $_REQUEST + $pQuery;
                    $_GET = $_GET + $pQuery;

                }

                $foundURL = true;

                break;
            }
        }
    }

    $_MAMBOTS->trigger('jpAfterSEF', null);

}

// Original SEF ---------------------------------------------------------------

if ($mosConfig_sef and (!$foundURL)) {


    $url_array = explode('/', $_SERVER['REQUEST_URI']);

    if (in_array('component', $url_array)) {

        /*
        Components
        http://www.domain.com/component/$name,$value
        */
        $uri = explode('component/', $_SERVER['REQUEST_URI']);
        $uri_array = explode('/', $uri[1]);
        $QUERY_STRING = '';

        // needed for check if component exists
        $path = $mosConfig_absolute_path . '/components';
        $dirlist = array();
        if (is_dir($path)) {
            $base = opendir($path);
            while (false !== ($dir = readdir($base))) {
                if ($dir !== '.' && $dir !== '..' && is_dir($path . '/' . $dir) && strtolower($dir)
                    !== 'cvs' && strtolower($dir) !== '.svn') {
                    $dirlist[] = $dir;
                }
            }
            closedir($base);
        }

        foreach ($uri_array as $value) {
            $temp = explode(',', $value);
            if (isset($temp[0]) && $temp[0] != '' && isset($temp[1]) && $temp[1] != '') {
                $_GET[$temp[0]] = $temp[1];
                $_REQUEST[$temp[0]] = $temp[1];

                // check to ensure component actually exists
                if ($temp[0] == 'option') {
                    $check = '';
                    if (count($dirlist)) {
                        foreach ($dirlist as $dir) {
                            if ($temp[1] == $dir) {
                                $check = 1;
                                break;
                            }
                        }
                    }
                    // redirect to 404 page if no component found to match url
                    if (!$check) {
                        header('HTTP/1.0 404 Not Found');
                        require_once ($mosConfig_absolute_path . '/templates/404.php');
                        exit(404);
                    }
                }

                if ($QUERY_STRING == '') {
                    $QUERY_STRING .= "$temp[0]=$temp[1]";
                } else {
                    $QUERY_STRING .= "&$temp[0]=$temp[1]";
                }
            }
        }

        $_SERVER['QUERY_STRING'] = $QUERY_STRING;
        $REQUEST_URI = $uri[0] . 'index.php?' . $QUERY_STRING;
        $_SERVER['REQUEST_URI'] = $REQUEST_URI;

        if (defined('RG_EMULATION') && RG_EMULATION == 1) {
            // Extract to globals
            while (list($key, $value) = each($_GET)) {
                if ($key != "GLOBALS") {
                    $GLOBALS[$key] = $value;
                }
            }
            // Don't allow config vars to be passed as global
            include ('configuration.php');

            // SSL check - $http_host returns <live site url>:<port number if it is 443>
            $http_host = explode(':', $_SERVER['HTTP_HOST']);
            if ((!empty($_SERVER['HTTPS']) && Jstring::strtolower($_SERVER['HTTPS']) !=
                'off' || isset($http_host[1]) && $http_host[1] == 443) && Jstring::substr($mosConfig_live_site,
                0, 8) != 'https://') {
                $mosConfig_live_site = 'https://' . Jstring::substr($mosConfig_live_site, 7);
            }
        }

    } else {

        /*
        Unknown content
        http://www.domain.com/unknown
        */
        $jdir = Jstring::str_ireplace('index.php', '', $_SERVER['PHP_SELF']);
        $juri = Jstring::str_ireplace($jdir, '', $_SERVER['REQUEST_URI']);

        if ($juri != '' && $juri != '/' && !mb_eregi("index\.php", $_SERVER['REQUEST_URI']) &&
            !mb_eregi("index2\.php", $_SERVER['REQUEST_URI']) && !mb_eregi("/\?", $_SERVER['REQUEST_URI']) &&
            $_SERVER['QUERY_STRING'] == '') {
            header('HTTP/1.0 404 Not Found');
            require_once ($mosConfig_absolute_path . '/templates/system/404.php');
            exit(404);
        }
    }
}


/**
 * Converts an absolute URL to SEF format
 * @param string The URL
 * @return string
 */
function sefRelToAbs($string)
{
    global $mosConfig_live_site, $mosConfig_sef, $mosConfig_multilingual_support;
    global $iso_client_lang, $_MAMBOTS;

    if (Jstring::strpos($string, '.value') !== false)
        return $string;

    $_MAMBOTS->trigger('jpOnURLGenerate', $string);

    if (Jstring::substr($string, 0, Jstring::strlen($mosConfig_live_site)) == $mosConfig_live_site) {
        $string = Jstring::substr($string, Jstring::strlen($mosConfig_live_site));
    }

    $string = Jstring::ltrim($string, '/');


    //multilingual code url support
    if ($mosConfig_sef && $mosConfig_multilingual_support && $string != 'index.php' &&
        !mb_eregi("^(([^:/?#]+):)", $string) && !strcasecmp(Jstring::substr($string, 0,
        9), 'index.php') && !mb_eregi('lang=', $string)) {
        $string .= '&amp;lang=' . $iso_client_lang;
    }

    // SEF URL Handling
    if ($mosConfig_sef && !mb_eregi("^(([^:/?#]+):)", $string) && !strcasecmp(Jstring::
        substr($string, 0, 9), 'index.php')) {
        // Replace all &amp; with &
        $string = str_replace('&amp;', '&', $string);

        // Home index.php
        if ($string == 'index.php') {
            $string = '';
        }

        // break link into url component parts
        $url = parse_url($string);

        // check if link contained fragment identifiers (ex. #foo)
        $fragment = '';

        if (isset($url['fragment'])) {
            // ensure fragment identifiers are compatible with HTML4
            if (preg_match('@^[A-Za-z][A-Za-z0-9:_.-]*$@', $url['fragment'])) {
                $fragment = '#' . $url['fragment'];
            }
        }

        // JPromoter BEGIN -------------------------------------------------------------

        global $originalAndSefUrls;

        if ($mosConfig_sef) {

            $originalURL = '/' . $string;


            $sharpPos = Jstring::strpos($originalURL, '#');

            if ($sharpPos !== false) {
                $originalURL = Jstring::substr($originalURL, 0, $sharpPos);
            }

            $_to_insert = false;
            if (isset($originalAndSefUrls[$originalURL])) {
                if ($originalAndSefUrls[$originalURL]['sef'] != '') {
                    return $mosConfig_live_site .
                    //'/index.php'. 
                    $originalAndSefUrls[$originalURL]['sef'] . $fragment;
                }
            } else {
                $_to_insert = true;
            }

        }

        // JPromoter END ---------------------------------------------------------------

        // check if link contained a query component
        if (isset($url['query'])) {
            // special handling for javascript
            $url['query'] = stripslashes(Jstring::str_ireplace('+', '%2b', $url['query']));
            // clean possible xss attacks
            $url['query'] = preg_replace("'%3Cscript[^%3E]*%3E.*?%3C/script%3E'si", '', $url['query']);

            // break url into component parts
            parse_str($url['query'], $parts);

            // special handling for javascript
            foreach ($parts as $key => $value) {
                if (Jstring::strpos($value, '+') !== false) {
                    $parts[$key] = stripslashes(Jstring::str_ireplace('%2b', '+', $value));
                }
            }
            //var_dump($parts);
            $sefstring = '';

            // Component com_content urls
            if (((isset($parts['option']) && ($parts['option'] == 'com_content' || $parts['option'] ==
                'content'))) && ($parts['task'] != 'new') && ($parts['task'] != 'edit')) {
                // index.php?option=com_content [&task=$task] [&sectionid=$sectionid] [&id=$id] [&Itemid=$Itemid] [&limit=$limit] [&limitstart=$limitstart] [&year=$year] [&month=$month] [&module=$module]
                $sefstring .= 'content/';

                // task
                if (isset($parts['task'])) {
                    $sefstring .= $parts['task'] . '/';
                }
                // sectionid
                if (isset($parts['sectionid'])) {
                    $sefstring .= $parts['sectionid'] . '/';
                }
                // id
                if (isset($parts['id'])) {
                    $sefstring .= $parts['id'] . '/';
                }
                // Itemid
                if (isset($parts['Itemid'])) {
                    //only add Itemid value if it does not correspond with the 'unassigned' Itemid value
                    if ($parts['Itemid'] != 99999999 && $parts['Itemid'] != 0) {
                        $sefstring .= $parts['Itemid'] . '/';
                    }
                }
                // order
                if (isset($parts['order'])) {
                    $sefstring .= 'order,' . $parts['order'] . '/';
                }
                // filter
                if (isset($parts['filter'])) {
                    $sefstring .= 'filter,' . $parts['filter'] . '/';
                }
                // limit
                if (isset($parts['limit'])) {
                    $sefstring .= $parts['limit'] . '/';
                }
                // limitstart
                if (isset($parts['limitstart'])) {
                    $sefstring .= $parts['limitstart'] . '/';
                }
                // year
                if (isset($parts['year'])) {
                    $sefstring .= $parts['year'] . '/';
                }
                // month
                if (isset($parts['month'])) {
                    $sefstring .= $parts['month'] . '/';
                }
                // module
                if (isset($parts['module'])) {
                    $sefstring .= $parts['module'] . '/';
                }
                // lang
                if (isset($parts['lang'])) {
                    $sefstring .= 'lang,' . $parts['lang'] . '/';
                }

                $string = $sefstring;

                // all other components
                // index.php?option=com_xxxx &...
            } else
                if (isset($parts['option']) && (Jstring::strpos($parts['option'], 'com_') !== false)) {
                    // do not SEF where com_content - `edit` or `new` task link
                    if (!(($parts['option'] == 'com_content') && ((isset($parts['task']) == 'new') ||
                        (isset($parts['task']) == 'edit')))) {
                        $sefstring = 'component/';

                        foreach ($parts as $key => $value) {
                            // remove slashes automatically added by parse_str
                            $value = stripslashes($value);
                            $sefstring .= $key . ',' . $value . '/';
                        }

                        $string = Jstring::str_ireplace('=', ',', $sefstring);
                    }
                }
            // no query given. Empty $string to get only the fragment
            // index.php#anchor or index.php?#anchor
        } else {
            $string = '';
        }

        // allows SEF without mod_rewrite
        // comment line below if you dont have mod_rewrite

        // JPromoter return $mosConfig_live_site .'/'. $string . $fragment;

        // JPromoter BEGIN -------------------------------------------------------------

        global $database, $Exclusion;

        if (isset($parts['option']) && array_search($parts['option'], $Exclusion)) {

            return $mosConfig_live_site . 
            //'/index.php'.
            $originalURL . $fragment;
        }

        $resultUrl = '';

        $urlComponents = parse_url($originalURL);

        if (!empty($urlComponents['query'])) {

            global $sefConfigs;

            // ������ imit=' + this.options[selectedIndex].value + '
            parse_str($urlComponents['query'], $urlQuery);

            // ��������, ����� �������������� ���� ������ �� �����, ��� ���������� ��� �������� � �������
            //        $tempArray = explode('&', $urlComponents['query']);
            //        foreach ($tempArray as $temp) {
            //            $tempArray2 = explode('=', $temp);
            //            $urlQuery[$tempArray2[0]] = $tempArray2[1];
            //        };

            if (!empty($urlQuery['option'])) {

                $comp = $urlQuery['option']; // ������������ ����������

                if (!isset($sefConfigs[$comp])) {

                    $absPath = $GLOBALS['mosConfig_absolute_path'];

                    if (file_exists($sefConfigFile = $absPath . '/administrator/components/' . $comp .
                        '/jp_' . $comp . '.xml') or file_exists($sefConfigFile = $absPath .
                        '/components/' . $comp . '/jp_' . $comp . '.xml') or file_exists($sefConfigFile =
                        $absPath . '/administrator/components/com_jp/sef_configs/jp_' . $comp . '.xml')) {

                        require_once ($GLOBALS['mosConfig_absolute_path'] .
                            '/includes/domit/xml_domit_lite_include.php');

                        $xmlDoc = new DOMIT_Lite_Document();
                        $xmlDoc->resolveErrors(true);

                        $xmlDoc->parseXML(preg_replace('/<\!\-\-.*\-\-\>/sU', '', $xmlDoc->
                            getTextFromFile($sefConfigFile)));

                        $sefElement = &$xmlDoc->documentElement;

                        if ($sefElement->getAttribute('component') == $comp) {

                            $varElements = &$sefElement->getElementsByTagName('var');

                            for ($i = 0; $i < ($varElements->getLength()); $i++) {

                                $varElement = &$varElements->item($i);
                                $sefConfigs[$comp]['vars'][$varElement->attributes['name']]['type'] = $varElement->
                                    attributes['type'];

                                if (isset($varElement->attributes['ifpresent'])) {
                                    $sefConfigs[$comp]['vars'][$varElement->attributes['name']]['ifpresent'] = $varElement->
                                        attributes['ifpresent'];
                                }

                                if (isset($varElement->attributes['ifabsent'])) {
                                    $sefConfigs[$comp]['vars'][$varElement->attributes['name']]['ifabsent'] = $varElement->
                                        attributes['ifabsent'];
                                }

                                switch ($varElement->attributes['type']) {
                                    case 'query':
                                        $queryElements = &$varElement->getElementsByTagName('query');
                                        $sefConfigs[$comp]['vars'][$varElement->attributes['name']]['query'] = $queryElements->
                                            arNodeList[0]->getText();

                                        $emptyElements = &$varElement->getElementsByTagName('empty');
                                        $sefConfigs[$comp]['vars'][$varElement->attributes['name']]['empty'] = $emptyElements->
                                            arNodeList[0]->getText();
                                        break;
                                    case 'string':
                                        $valueElements = &$varElement->getElementsByTagName('value');
                                        $sefConfigs[$comp]['vars'][$varElement->attributes['name']]['value'] = $valueElements->
                                            arNodeList[0]->getText();
                                        break;
                                }

                            }

                            $condElements = &$sefElement->getElementsByTagName('cond');

                            for ($i = 0; $i < ($condElements->getLength()); $i++) {

                                $condElement = &$condElements->item($i);
                                $sefConfigs[$comp]['conds'][$i]['arguments'] = $condElement->attributes;

                                $tplElements = &$condElement->getElementsByTagName('tpl');
                                $sefConfigs[$comp]['conds'][$i]['tpl'] = $tplElements->arNodeList[0]->getText();

                            }
                        }
                    }

                    if (empty($sefConfigs[$comp])) {
                        $sefConfigs[$comp]['mode'] = 'bad';
                    } else {
                        $sefConfigs[$comp]['mode'] = 'good';
                    }
                    ;

                }
                if ($sefConfigs[$comp]['mode'] != 'bad') {

                    $urlArgumentList = $urlQuery;

                    unset($urlArgumentList['option']);

                    $sefConfigs[$comp]['values']['keys'] = array();
                    $sefConfigs[$comp]['values']['values'] = array();

                    $searchArray = array_keys($urlArgumentList);

                    array_walk($searchArray, create_function('&$v,$k', '$v = \'{\' . $v . \'}\';'));

                    //_xdump($sefConfigs[$comp]['vars']);

                    foreach ($sefConfigs[$comp]['vars'] as $varName => $var) {

                        $val = '';

                        $sefConfigs[$comp]['values']['keys'][] = '{' . $varName . '}';

                        if (isset($var['ifpresent']) and empty($urlArgumentList[$var['ifpresent']])) {
                            $sefConfigs[$comp]['values']['values'][] = '';
                            continue;
                        }
                        if (isset($var['ifabsent']) and (!empty($urlArgumentList[$var['ifabsent']]))) {
                            $sefConfigs[$comp]['values']['values'][] = '';
                            continue;
                        }

                        switch ($var['type']) {
                            case 'query':

                                $var['query'] = str_ireplace($searchArray, array_values($urlArgumentList), $var['query']);

                                static $_pach;

                                if (!isset($_pach[$var['query']]) && $var['query'] != '') {
                                    $database->setQuery($var['query']);
                                    $_pach[$var['query']] = $database->loadResult();
                                }
                                ;
                                $val = $_pach[$var['query']];

                                if (empty($val)) {
                                    $rnd = Jstring::substr(md5(mt_rand(1, 1000)), 0, 16);
                                    $val = Jstring::str_ireplace('?', $rnd, $var['empty']);
                                }

                                $sefConfigs[$comp]['values']['values'][] = urlTranslit($val);

                                break;
                            case 'string':

                                $sefConfigs[$comp]['values']['values'][] = urlTranslit(str_ireplace($searchArray,
                                    array_values($urlArgumentList), $var['value']));

                                break;
                        }

                    }

                    foreach ($sefConfigs[$comp]['conds'] as $cond) {

                        foreach ($cond['arguments'] as $argumentName => $argumentValue) {

                            if (isset($urlArgumentList[$argumentName])) {
                                if ($argumentValue == '*')
                                    $cond['arguments'][$argumentName] = $urlArgumentList[$argumentName];
                                $valueVariants = explode('|', $argumentValue);
                                if (in_array($urlArgumentList[$argumentName], $valueVariants))
                                    $cond['arguments'][$argumentName] = $urlArgumentList[$argumentName];
                            }
                        }


                        if (array_intersect_assoc($cond['arguments'], $urlArgumentList) == $cond['arguments']) {

                            $resultUrl = str_ireplace($sefConfigs[$comp]['values']['keys'], $sefConfigs[$comp]['values']['values'],
                                $cond['tpl']);

                            break;

                        }
                    }
                }
            }
        }

        if ($resultUrl == '')
            $resultUrl = '/' . $string;

        $originalAndSefUrls[] = array('original' => $originalURL, 'sef' => $resultUrl);

        $fst = preg_quote("option=");
        $scd = preg_quote("&");
        $_result = preg_match("#$fst(.*?)$scd#", $database->getEscaped($originalURL), $matches);

        if ($_result) {
            $_comp = $matches[1];
        } else {
            $_comp = mosGetParam($_REQUEST, 'option', null);
        }
        $comp = isset($comp) ? $comp : 'com_frontpage';//Проверка если нет оптион то эту ссылку писать в базу не надо

        static $_insert;
        if (!isset($_insert[$resultUrl])) {
            $sql = 'INSERT INTO #__jp_pages (original, sef, component)' . ' VALUES ("' . $database->
            getEscaped($originalURL) . '", "' . $database->getEscaped($resultUrl) . '","' .
            $_comp . '")' . ' ON DUPLICATE KEY UPDATE `sef`="' . $database->getEscaped($resultUrl) .
            '";';
            $database->setQuery($sql);
            $_insert[$resultUrl] = $database->query();
        }

        return $mosConfig_live_site . 
        //'/index.php'.
        $resultUrl . $fragment;

        // JPromoter END -------------------------------------------------------------

        // allows SEF without mod_rewrite
        // uncomment Line 512 and comment out Line 514

        // uncomment line below if you dont have mod_rewrite
        // return $mosConfig_live_site .'/index.php/'. $string . $fragment;
        // If the above doesnt work - try uncommenting this line instead
        // return $mosConfig_live_site .'/index.php?/'. $string . $fragment;
    } else {
        // Handling for when SEF is not activated
        // Relative link handling
        if ((Jstring::strpos($string, $mosConfig_live_site) !== 0)) {
            // if URI starts with a "/", means URL is at the root of the host...
            if (strncmp($string, '/', 1) == 0) {
                // splits http(s)://xx.xx/yy/zz..." into [1]="http(s)://xx.xx" and [2]="/yy/zz...":
                $live_site_parts = array();
                mb_eregi("^(https?:[\/]+[^\/]+)(.*$)", $mosConfig_live_site, $live_site_parts);

                $string = $live_site_parts[1] . $string;
            } else {
                $check = 1;

                // array list of non http/https	URL schemes
                $url_schemes = explode(', ', _URL_SCHEMES);
                $url_schemes[] = 'http:';
                $url_schemes[] = 'https:';

                foreach ($url_schemes as $url) {
                    if (Jstring::strpos($string, $url) === 0) {
                        $check = 0;
                    }
                }

                if ($check) {
                    $string = $mosConfig_live_site . '/' . $string;
                }
            }
        }

        return $string;
    }
}

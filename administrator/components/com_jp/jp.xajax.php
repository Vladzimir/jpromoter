<?php
/**
 * @version     $Id$
 * @package JPromoter for Joostina
 * @copyright Авторские права (C) JPromoter team & (C) Joostina team &. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * JPromoter for Joostina - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */
defined('_VALID_MOS') or die('Restricted access');

require_once ('components/com_jp/xajax/xajax_core/xajax.inc.php');

function jpShowPageCache($id)
{
    global $mosConfig_live_site;
    //echo $mosConfig_live_site.'/administrator/index3.php?option=com_jp&task=showpage&no_html=1&id='.$id;
    $objResponse = new xajaxResponse();
    $objResponse->assign("pagecache", "src", $mosConfig_live_site .
        '/administrator/index3.php?option=com_jp&task=showpage&no_html=1&id=' . $id);
    $objResponse->assign("pagecache", "style.visibility", 'visible');
    $objResponse->assign("pagecache", "style.display", 'block');
    //$objResponse->assign("pagecache","innerHTML",$text);
    return $objResponse;
}

function jpShowPageReal($url)
{

    $objResponse = new xajaxResponse();
    $objResponse->assign("pagecache", "src", $url);
    $objResponse->assign("pagecache", "style.visibility", 'visible');
    $objResponse->assign("pagecache", "style.display", 'block');
    //$objResponse->assign("pagecache","innerHTML",$text);
    return $objResponse;
}

$xajax = new xajax($mosConfig_live_site .
    '/administrator/index3.php?option=com_jp&task=xajax&no_html=1');
//$xajax->debugOn(); // Uncomment this line to turn debugging on
$xajax->registerFunction("jpShowPageCache");
$xajax->registerFunction("jpShowPageReal");
$xajax->processRequest();
$xajax->printJavascript('components/com_jp/xajax/');


?>
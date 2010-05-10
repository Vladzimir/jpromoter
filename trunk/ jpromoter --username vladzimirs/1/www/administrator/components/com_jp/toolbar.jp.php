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

require_once ($mainframe->getPath('toolbar_html'));
$task = mosGetParam($_REQUEST, 'task', false);

switch ($task) {
    case 'config':
    case 'applycnf':
        require_once ('components/com_jp/jp.config.php');
        JEConfig::Toolbar();
        break;

    case 'reports':
        jpToolBar::onlycontrol();
        break;
    case 'editpage':
    case 'applypage':
    case 'addskip':
        jpToolBar::editpage();
        break;

    case 'hidepagesitemap':
    case 'hidepage':
    case 'clear':
    case 'getrank':
    case 'remove':
    case 'pagerank':
    case 'index':
    case 'savepage':
    case 'cancelpage':
    case 'noindex':
    case 'follow':
    case 'nofollow':
    case 'accept':
    case 'publish':
    case 'unpublish':
    case 'optimize':
        jpToolBar::optimize();
        break;

    case 'scan':
    case 'removesimulation':
        jpToolBar::index();
        break;

    case 'gsm':
    case 'updateXML':
    case 'google':
        jpToolBar::google();
        break;
    case 'newsimulate':
        jpToolBar::newsimulate();
        break;
    case 'doindex':
    default:
        jpToolBar::_default();
}

?>
<?php
/**
 * @version     $Id$
 * @package JPromoter for Joostina
 * @copyright Авторские права (C) JPromoter team & (C) Joostina team &. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * JPromoter for Joostina - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */

defined('_VALID_MOS') or die('Доступ запрещен');

// Перенаправление на JPromoter обработчик SEF

if (!class_exists('JEconfig'))
    require_once ($GLOBALS['mosConfig_absolute_path'] .
        '/administrator/components/com_jp/jp.config.php');

if (file_exists($mosConfig_absolute_path .
    '/administrator/components/com_jp/jp.sef.php') and JEConfig::get("SEF.sef_enable",
    'com_jp') == '1') {
    require_once ($mosConfig_absolute_path .
        '/administrator/components/com_jp/jp.sef.php');
} else {
    require_once ($mosConfig_absolute_path . '/includes/sef.php');
}

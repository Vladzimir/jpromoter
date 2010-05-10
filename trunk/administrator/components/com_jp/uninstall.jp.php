<?php
/**
 * @version     $Id$
 * @package JPromoter for Joostina
 * @copyright Авторские права (C) JPromoter team & (C) Joostina team &. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * JPromoter for Joostina - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */
defined('_VALID_MOS') or die('Direct Access to this location is not allowed.');

function com_uninstall()
{

    global $mosConfig_absolute_path;

    @unlink($mosConfig_absolute_path . '/components/com_sef/sef.php');
    @rmdir($mosConfig_absolute_path . '/components/com_sef');

    return true;

}
?>
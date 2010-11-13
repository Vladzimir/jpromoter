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

if (file_exists(JPATH_BASE . '/administrator/components/com_jp/languages/' . Jconfig::getInstance()->config_lang . '.php')) {
require_once (JPATH_BASE . '/administrator/components/com_jp/languages/' . Jconfig::getInstance()->config_lang . '.php');
} else {
require_once (JPATH_BASE.'/administrator/components/com_jp/languages/russian.php');
}
error_reporting(E_ALL);
@session_start();

class JText
{
    function _($text)
    {
        return $text;
    }
}

require_once ('components/com_jp/jp.functions.php');
require_once ('components/com_jp/jp.xajax.php');
require_once ($mainframe->getPath('admin_html'));
require_once ($mainframe->getPath('class'));
require_once ('components/com_jp/jp.config.php');

if (file_exists("components/com_jp/update.php"))
    include_once ("components/com_jp/update.php");

$task = mosGetParam($_REQUEST, 'task', false);
$client = mosGetParam($_REQUEST, 'client', 'site');
$option = mosGetParam($_REQUEST, 'option', 'com_jp');
$skip = mosGetParam($_REQUEST, 'skip', array(0));
$cid = mosGetParam($_REQUEST, 'cid');

if (!is_array($cid)) {
    $cid = array(0);
}
echo '<form action="index2.php" method="post" name="adminForm" enctype="multipart/form-data">';

//print_r($cid);
switch ($task) {
    case 'newsimulate':
        jpSimulate();
        break;

    case 'updateXML':
        jpUpdateXML();
        require_once ('components/com_jp/google.class.php');
        jpGoogleTools();
        break;

    case 'gsm':
        jpGsm();
    case 'google':
        require_once ('components/com_jp/google.class.php');
        jpGoogleTools();
        break;

    case 'applycnf':
        JEConfig::save($option);
    case 'config':
        /*
        $translitINIPath = $GLOBALS['mosConfig_absolute_path'] .
        '/administrator/components/com_jp/sef_translits/';

        $translitINIFiles = array();

        foreach (glob($translitINIPath . '*.ini') as $INIFile) {
        $translitINIFiles[] = substr(basename($INIFile), 0, -4);
        }

        $database->setQuery('UPDATE `#__je_config` SET `values` = "' . implode(',', $translitINIFiles) .
        '" WHERE `name` = "jp_codepage"');
        $database->query();*/

        mosCommonHTML::loadOverlib();
        JEConfig::renderForm();
        break;

    case 'showpage':
        //echo "qq";
        jpShowPageCach();
        break;

    case 'reports':
        jpShowReports();
        break;

    case 'index':
    case 'noindex':
    case 'follow':
    case 'nofollow':
        jpIndex($cid, $task);
        jxListPagesHtml();
        break;

    case 'publish':
    case 'unpublish':
        jpPublish($cid, $task);
        jxListPagesHtml();
        break;

    case 'removesimulation':
        jpDeleteSimulation($cid);
        unset($_SESSION['jpSimulationStatus']);
        jpIndexForm();
        break;

    case 'remove':
        jpDelete($cid);
        jxListPagesHtml();
        break;

    case 'clear':
        jpClear($cid);
        jxListPagesHtml();
        break;

    case 'getrank':
        jpRefreshRank($cid);
        jxListPagesHtml();
        break;

    case 'pagerank':
        jpRefreshRank($cid);
        jxListPagesHtml();
        break;

    case 'hidepage':
        jpHidePage($cid);
        jxListPagesHtml();
        break;
    case 'hidepagesitemap':
        jpHidePageSitemap($cid);
        jxListPagesHtml();
        break;

    case 'optimize':
        jxListPagesHtml();
        break;
    case 'cancelpage':
        jxListPagesHtml();
        break;

    case 'savepage':
        jpSavePageMeta($cid);
        jxListPagesHtml();
        break;

    case 'applypage':
        jpSavePageMeta($cid);
        jpEditPage($cid);
        break;

    case 'addskip':
        jpAddSkipWords(mosGetParam($_REQUEST, 'kwd', array(0)));
    case 'editpage':
        jpEditPage($cid);
        break;

    case 'scan':
        unset($_SESSION['jpSimulationStatus']);
        jpIndexForm();
        break;

    case 'startsimulate':

        if (empty($_SESSION['jpSimulationStatus'])) {
            $_SESSION['jpSimulationStatus'] = 'new';
        }
        ;

        switch ($_SESSION['jpSimulationStatus']) {
            case 'new':
                $_SESSION['jpSimulationDateTime'] = time();
                $_SESSION['jpSimulationCounts'] = array('pages' => 0, 'skip' => 0);
                $_SESSION['jpPagesCount'] = 0;
                $_SESSION['jpSkipLinksCount'] = 0;
                $_SESSION['jpMaxPages'] = $_POST['indcount'];
                $_SESSION['jpLastLink'] = '';

                if (!empty($_POST['skip'])) {
                    $_SESSION['jpSkipComponents'] = $_POST['skip'];
                } else {
                    unset($_SESSION['jpSkipComponents']);
                }

                if (!empty($_POST['dirskip'])) {
                    $_SESSION['jpSkipDirectories'] = explode("\r\n", $_POST['dirskip']);
                } else {
                    unset($_SESSION['jpSkipDirectories']);
                }

                if (!(isset($_SESSION['jpSkipDirectories']) and in_array('/', $_SESSION['jpSkipDirectories']))) {
                    $_SESSION['jpExecuteTime'] = time();
                    $_SESSION['jpPagesCount'] = simulatePage($mosConfig_live_site . '/', false, $_SESSION['jpMaxPages']);
                    $_SESSION['jpExecuteTime'] = time() - $_SESSION['jpExecuteTime'];
                }
                break;
            case 'continue':
                $_SESSION['jpPagesCount'] = 0;
                $_SESSION['jpSkipLinksCount'] = 0;
                $_SESSION['jpExecuteTime'] = time();
                $_SESSION['jpPagesCount'] = simulatePage($_SESSION['jpLastLink'], false, $_SESSION['jpMaxPages']);
                $_SESSION['jpExecuteTime'] = time() - $_SESSION['jpExecuteTime'];
                break;
        }
        ;

        $_SESSION['jpSimulationCounts']['pages'] += $_SESSION['jpPagesCount'];
        $_SESSION['jpSimulationCounts']['skip'] += $_SESSION['jpSkipLinksCount'];

        jpEndSimulate();

        break;

    case 'savecnf':
        JEConfig::save($option);
        jpControlPanel();
        break;
    case 'doindex':
        $indextype = mosGetParam($_REQUEST, 'indextype', 'newpages');

        if ($indextype == 'reindex') {
            jpDropCache();
        }

        jpBotMetaEdit('unpublish');
        indexPage();
        jpBotMetaEdit('publish');

        echo jpMsg('SE Simulation of the site completed!!!');
    default:
        jpControlPanel();
}

function jpDeleteSimulation($id)
{
    global $database;

    $previewDir = $GLOBALS['mosConfig_absolute_path'] .
        '/administrator/components/com_jp/images/preview/';
    //    $previewFileSrc = $mosConfig_absolute_path.$previewDir.strtotime($simtime).'_'.$row['id'].'.jpg';

    foreach ($id as $simtime) {

        $simtime = rawurldecode($simtime);

        $simtimeCode = strtotime($simtime);

        $pwDir = opendir($previewDir);
        while (false !== ($filename = readdir($pwDir))) {

            if (substr($filename, 0, 11) == $simtimeCode) {
                unlink($previewDir . $filename);
            }

        }

        $simtimes[] = '"' . $simtime . '"';

    }

    $ids = implode(",", $simtimes);

    $sql = "DELETE FROM #__jp_links WHERE `simtime` IN({$ids})";

    $database->setQuery($sql);
    $database->query();

    $sql = "DELETE FROM #__jp_images WHERE `simtime` IN({$ids})";

    $database->setQuery($sql);
    $database->query();

}

function jpDropCache()
{
    global $database;
    $sql = "UPDATE #__jp_links SET `status` = 'new' WHERE `type` = 'internal' AND `href` LIKE 'http://%'";
    $database->setQuery($sql);
    $database->query();

}

function jpBotMetaEdit($task)
{
    global $database;
    $task == 'publish' ? $p = 1 : $p = 0;
    $sql = "UPDATE #__mambots SET `published` = $p WHERE `element` = 'jp.metaedit' AND `folder` = 'system'";
    $database->setQuery($sql);
    $database->query();
}

function jpShowPageCach()
{
    global $database;

    $id = mosGetParam($_REQUEST, 'id', false);
    //echo "gdfgsfg";
    if ($id) {
        $sql = "SELECT body FROM #__jp_pages WHERE `id` = '{$id}'";
        $database->setQuery($sql);
        echo stripslashes($database->loadResult());
    }
}

function jpDelete($cid)
{
    global $database;
    if (count($cid) < 1) {
        return;
    }

    if (isset($_POST['doAllComponent'])) {
        $sql = "DELETE FROM #__jp_pages WHERE 1=1 " . getWhere();
    } else {
        $cids = implode(',', $cid);

        $sql = 'SELECT `sef` FROM #__jp_pages WHERE `id` IN ( ' . $cids . ')';
        ;

        $database->setQuery($sql);
        $rows = $database->loadResultArray();
        foreach ($rows as $key => $value) {
            $rows[$key] = '"' . $value . '"';
        }

        $sefs = implode(',', $rows);

        $sql = "DELETE FROM #__jp_pages WHERE `sef` IN({$sefs})";
    }

    $database->setQuery($sql);
    $database->query();
    if (JEConfig::get('SEF.jp_cache', 'com_jp')) {
        if (file_exists(Jconfig::getInstance()->config_cachepath . '/jp/sef.php')) {
            unlink(Jconfig::getInstance()->config_cachepath . '/jp/sef.php');

        }
    }
}

function jpClear($cid)
{
    global $database;
    if (count($cid) < 1) {
        return;
    }

    if (isset($_POST['doAllComponent'])) {
        $sql = "UPDATE #__jp_pages SET `sef`='' WHERE 1=1 " . getWhere();
    } else {
        $cids = implode(',', $cid);

        $sql = 'SELECT `sef` FROM #__jp_pages WHERE `id` IN ( ' . $cids . ')';
        ;

        $database->setQuery($sql);
        $rows = $database->loadResultArray();
        foreach ($rows as $key => $value) {
            $rows[$key] = '"' . $value . '"';
        }

        $sefs = implode(',', $rows);

        $sql = "UPDATE #__jp_pages SET `sef`='' WHERE `sef` IN({$sefs})";
    }

    $database->setQuery($sql);
    $database->query();
    if (JEConfig::get('SEF.jp_cache', 'com_jp')) {
        if (file_exists(Jconfig::getInstance()->config_cachepath . '/jp/sef.php')) {
            unlink(Jconfig::getInstance()->config_cachepath . '/jp/sef.php');

        }
    }
}

function jpPublish($cid, $task)
{
    global $database;
    if (count($cid) < 1) {
        return;
    }

    $task == 'publish' ? $p = 1 : $p = 0;

    if (isset($_POST['doAllComponent'])) {
        $query = "UPDATE #__jp_pages SET published = " . intval($p) . " WHERE 1=1 " .
            getWhere();
    } else {

        $cids = implode(',', $cid);

        $sql = 'SELECT `sef` FROM #__jp_pages WHERE `id` IN ( ' . $cids . ')';
        ;

        $database->setQuery($sql);
        $rows = $database->loadResultArray();
        foreach ($rows as $key => $value) {
            $rows[$key] = '"' . $value . '"';
        }

        $sefs = implode(',', $rows);

        $query = "UPDATE #__jp_pages SET published = " . intval($p) .
            " WHERE `sef` IN ( $sefs )";
    }

    $database->setQuery($query);
    $database->query();
}

function jpIndex($cid, $task)
{
    global $database;

    if (count($cid) < 1) {
        return;
    }

    switch ($task) {
        case "index":
            $t = " robots_index = 'index' ";
            break;

        case "noindex":
            $t = " robots_index = 'noindex' ";
            break;

        case "follow":
            $t = " robots_follow = 'follow' ";
            break;

        case "nofollow":
            $t = " robots_follow = 'nofollow' ";
            break;
    }

    if (isset($_POST['doAllComponent'])) {
        $sql = "UPDATE #__jp_pages SET $t WHERE 1=1 " . getWhere();
    } else {

        $cids = implode(',', $cid);

        $sql = 'SELECT `sef` FROM #__jp_pages WHERE `id` IN ( ' . $cids . ')';
        ;

        $database->setQuery($sql);
        $rows = $database->loadResultArray();
        foreach ($rows as $key => $value) {
            $rows[$key] = '"' . $value . '"';
        }

        $sefs = implode(',', $rows);

        $sql = "UPDATE #__jp_pages SET $t WHERE `sef` IN ( $sefs )";
    }

    $database->setQuery($sql);
    $database->query();
}

function jpSavePageMeta($cid, $sub = false, $over = false)
{
    global $database;
    static $ids = array();

    if (in_array($cid[0], $ids))
        return;

    $ids[] = $cid[0];

    if (count($cid) < 1) {
        return;
    }

    $new_kw = mosGetParam($_REQUEST, 'new_kw', false);
    $new_ds = mosGetParam($_REQUEST, 'new_ds', false);
    $new_tl = mosGetParam($_REQUEST, 'new_tl', false);
    $new_tl2 = mosGetParam($_REQUEST, 'new_tl2', false);

    $published = mosGetParam($_REQUEST, 'rpublish', 0);
    $index = mosGetParam($_REQUEST, 'rindex', 'noindex');
    $follow = mosGetParam($_REQUEST, 'rfollow', 'nofollow');

    $sef = $database->getEscaped(mosGetParam($_REQUEST, 'sef', ''));
    $google_weight = mosGetParam($_REQUEST, 'google_weight', '0.5');
    $hiden_sitemap = mosGetParam($_REQUEST, 'hiden_sitemap', '0');

    $_changefreq = mosGetParam($_REQUEST, 'changefreq', JEConfig::get('general.jp_gsm_changefreq',
        'com_jp'));
    $redirect = $database->getEscaped(mosGetParam($_REQUEST, 'redirect', ''));

    if ($google_weight == '10') {
        $google_weight = '1.0';
    } else {
        $google_weight = '0.' . $google_weight;
    }

    if ($new_kw && $new_ds && $new_tl) {
        $status = "g";
    } elseif ($new_kw || $new_ds || $new_tl) {
        $status = "y";
    } else {
        $status = "r";
    }

    $sql = 'SELECT * FROM #__jp_pages WHERE id = ' . $cid[0];

    $database->setQuery($sql);
    $rows = $database->loadObjectList();

    if (empty($rows)) {
        return true;
    }

    $row = $rows[0];
    $query = "
        UPDATE #__jp_pages SET 
        newmeta_keywords = '{$new_kw}',   
        newmeta_description = '{$new_ds}',  
        newmeta_title = '{$new_tl}',  
        new_title = '{$new_tl2}',  
        status = '{$status}', 
        published = '{$published}',  
        robots_index = '{$index}',  
        robots_follow = '{$follow}',
        sef = '{$sef}',
        google_weight = '{$google_weight}',
        changefreq  = '{$_changefreq}',
        hiden_sitemap  = '{$hiden_sitemap}'
        WHERE  `sef` = '{$row->sef}'";
    $database->setQuery($query);
    $result = $database->query();

    if (!$result) {
        echo $database->stderr();
        exit;
    }
    if (JEConfig::get('SEF.jp_cache', 'com_jp')) {
        if ($sef !== $row->sef) {
            if (file_exists(Jconfig::getInstance()->config_cachepath . '/jp/sef.php')) {
                unlink(Jconfig::getInstance()->config_cachepath . '/jp/sef.php');

            }
        }
    }

}

?>
<input type="hidden" name="option" value="com_jp" />
<input type="hidden" name="task" value="<?php echo $task; ?>" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="hidemainmenu" value="0" />
</form>
<!--
<div align="center" style="clear:both"><BR>
<a href="http://joomlaequipment.com" target="_blank">"JPromoter" by <A href="mailto:support@joomlaequipment.com"><b>Joomla Equipment</b></a> &copy;</A>
</div>
-->
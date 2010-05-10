<?php
/**
 * @version     $Id$
 * @package JPromoter for Joostina
 * @copyright Авторские права (C) JPromoter team & (C) Joostina team &. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * JPromoter for Joostina - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */
define('SP', ' ');
define('NL', '&nbsp;<BR>');
defined('_VALID_MOS') or die('Restricted access');

/**
 *
 *
 */
function simulatePage($url, $isSimulateOnePage = false, $maxPages = 0)
{
    if (empty($url)) {
        return 0;
    }
    ;

    if ($maxPages == 0) {
        $maxPages = 999999;
    }
    ;

    $indexedPagesCount = 0;

    $pageId = jpSavePage($url);

    if ($pageId != 'skip') {
        $indexedPagesCount = 1;
    }
    ;

    if ($isSimulateOnePage) {
        return $indexedPagesCount;
    }
    ;

    global $database, $mosConfig_live_site;

    while (true) {

        $database->setQuery('SELECT `href` FROM `#__jp_links` WHERE `status` = "new" AND `simtime` = FROM_UNIXTIME(' .
            $_SESSION['jpSimulationDateTime'] . ')');
        $result = $database->query();
        if (!$result) {
            echo $database->stderr();
            exit;
        }
        $getUrl = $database->loadResult();
        unset($result);

        if (empty($getUrl)) {
            $_SESSION['jpSimulationStatus'] = 'allfinish';
            return $indexedPagesCount;
        }
        ;

        if ($indexedPagesCount >= $maxPages) {
            $_SESSION['jpSimulationStatus'] = 'stepfinish';
            $_SESSION['jpLastLink'] = $getUrl;
            return $indexedPagesCount;
        }
        ;

        $pageId = jpSavePage($getUrl);

        if ($pageId != 'skip') {
            $indexedPagesCount = $indexedPagesCount + 1;
        }
        ;

    }

}

/**
 *
 *
 */
function getWhere()
{
    global $database;

    $where = '';

    $component = mosGetParam($_REQUEST, 'component', '');
    if ($component != '') {
        $where .= " AND `component` = '{$component}' ";
    }

    $status = mosGetParam($_REQUEST, 'status', false);
    if ($status) {
        $where .= " AND `status` = '{$status}' ";
    }

    $publish = mosGetParam($_REQUEST, 'publish', false);
    if ($publish) {
        $where .= " AND `published` = '" . ($publish == 'Y' ? '1' : '0') . "' ";
    }

    $follow = mosGetParam($_REQUEST, 'follow', false);
    if ($follow) {
        $where .= " AND `robots_follow` = '{$follow}' ";
    }

    $index = mosGetParam($_REQUEST, 'index', false);
    if ($index) {
        $where .= " AND `robots_index` = '{$index}' ";
    }

    $indexed = mosGetParam($_REQUEST, 'indexed', '');
    if ($indexed) {
        $indexed = $indexed == 'yes' ? 1 : 0;
        $where .= " AND `isindexed` = {$indexed} ";
    }

    $showhidden = mosGetParam($_REQUEST, 'showhide', false);
    if ($showhidden) {
        $where .= " AND `ishidden` = 1 ";
    } else {
        $where .= " AND `ishidden` = 0 ";
    }

    $q = mosGetParam($_REQUEST, 'q', false);

    if ($q) {
        $q = $database->getEscaped($q);
        $search_cond = mosGetParam($_REQUEST, 'search_cond', '');

        if ($search_cond == 'all') {
            $where .= ' AND 
              (
                    (`original` LIKE "%' . $q . '%")
                    OR (`sef` LIKE "%' . $q . '%")
                    OR (`title` LIKE "%' . $q . '%")
                    OR (`new_title` LIKE "%' . $q . '%")
              )
              ';
        } else {
            $where .= ' AND (`' . $search_cond . '` LIKE "%' . $q . '%")';
        }
    }

    return $where;
}

function indexPage($url = '', $page_id = 0, $level = 0, $surl = array())
{
    global $database, $mainframe, $mosConfig_live_site;

    $url = strtolower($url);

    //set indexation date.
    if (empty($_SESSION['jpIndexDate']))
        $_SESSION['jpIndexDate'] = date("Y-m-d h:i:00");

    // if there is no $url passed, may be that this function called
    // the first time and we start from homepage
    if (empty($url)) {
        $url = $mosConfig_live_site . '/';
    } else {
        $url = jpAbsUrl($url);
    }

    // First of all mark this url as indexed because there can be no page
    // saved assosiated with this url or it can be skiped and if nor mark
    // plased we get to endless recursive loop.
    jpMarkUrlSaved($url);

    $page_id = jpSavePage($url, $page_id, $level);

    $sql = "SELECT * FROM #__jp_links WHERE `status` = 'new' AND `type` = 'internal'";
    $database->setQuery($sql);
    if (!$result = $database->query()) {
        echo $database->stderr();
        exit;
    }
    $rows = $database->loadObjectList();

    if (!$rows)
        return;

    $level++;
    foreach ($rows as $row) {
        $surl_[] = $row->href;
    }
    $totalu = array_merge($surl, $surl_);
    foreach ($rows as $row) {
        if (in_array($row->href, $surl)) {
            continue;
        }
        indexPage(stripslashes($row->href), $page_id, $level, $totalu);
    }
}

function jpSaveImages($page)
{
    global $database, $mosConfig_live_site, $mosConfig_absolute_path;

    if ($page->status != 'GOOD') {
        return true;
    }
    ;

    $database->setQuery('SELECT `src`, `size` FROM `#__jp_images` WHERE `simtime` = FROM_UNIXTIME(' .
        $_SESSION['jpSimulationDateTime'] . ')');
    $result = $database->query();
    if (!$result) {
        echo $database->stderr();
        exit;
    }
    $oldImages = $database->loadAssocList('src');
    unset($result);

    $newImages = array();

    $imagesSize = 0;

    $imgs = $page->getImages();

    foreach ($imgs as $img) {
        require_once 'components/com_jp/query.php';

        if (isset($oldImages[$img['src']])) {
            $imagesSize = $imagesSize + $oldImages[$img['src']]['size'];
            continue;
        }
        ;

        if (isset($newImages[$img['src']])) {
            continue;
        }
        ;

        $newImages[$img['src']] = $img;

        $file = jpAbsUrl($img['src']);

        $file = str_replace($mosConfig_live_site, $mosConfig_absolute_path, $file);

        $rest = jpCreateTumbnail($file, 200, 50, 50);

        if (is_array($rest))
            $img = array_merge($img, $rest);

        $img['alt'] = isset($img['alt']) ? $img['alt'] : '';
        $img['src'] = isset($img['src']) ? $img['src'] : '';

        $img['src'] = $database->getEscaped(jpAbsUrl($img['src']));
        $img['alt'] = $database->getEscaped($img['alt']);

        $imagesSize = $imagesSize + $img['size'];

        $sql = "INSERT INTO #__jp_images (src, preview, type, width, " .
            "height, alt, size, broken, simtime) VALUES ('" . $img['src'] . "', '" . $img['image'] .
            "', " . "'" . $img['mime'] . "', '" . $img['width'] . "', '" . $img['height'] .
            "', " . "'" . $img['alt'] . "', '" . $img['size'] . "', '" . ($rest ? 0 : 1) .
            "', FROM_UNIXTIME(" . $_SESSION['jpSimulationDateTime'] . "))";
        $database->setQuery($sql);
        if (!$result = $database->query()) {
            continue;
            //          		echo $database->stderr();
            //                  exit;
        }
        $im_id = $database->insertid();

        $p = $mosConfig_absolute_path .
            '/administrator/components/com_jp/images/preview/';
        if (file_exists($p . 'temp_preview.jpg')) {
            rename($p . 'temp_preview.jpg', $p . $_SESSION['jpSimulationDateTime'] . '_' . $im_id .
                '.jpg');
        }
        ;
    }

    $page->images_size = $imagesSize;

}

function jpMarkUrlSaved($url)
{
    global $database;
    $url = $database->getEscaped($url);
    $sql = "UPDATE #__jp_links SET `status` = 'indexed' WHERE `href` = '{$url}'";
    $database->setQuery($sql);
    if (!$result = $database->query()) {
        echo $database->stderr();
        exit;
    }
}

function jpSaveLinks($page)
{
    global $database, $mosConfig_live_site;

    if ($page->status != 'GOOD') {
        return true;
    }
    ;

    $database->setQuery('SELECT `href` FROM `#__jp_links` WHERE `simtime` = FROM_UNIXTIME(' .
        $_SESSION['jpSimulationDateTime'] . ')');
    $result = $database->query();
    if (!$result) {
        echo $database->stderr();
        exit;
    }
    $oldLinks = $database->loadResultArray();
    unset($result);

    $links = $page->getLinks();

    $newLinks = array();

    jpSkipUrl(null); // ��� ���������� ������

    foreach ($links as $link) {

        if (empty($link['href'])) {
            continue;
        }
        ;

        $link['href'] = jpAbsUrl($link['href']);

        if (jpSkipUrl($link['href'], false)) {
            continue;
        }
        ;

        if (!in_array($link['href'], $oldLinks) and (substr($link['href'], 0, 7) ==
            'http://')) {
            $newLinks[] = $link['href'];
        }
    }

    $newLinks = array_unique($newLinks);

    $values = array();

    foreach ($newLinks as $link) {
        $values[] = '("' . $link . '", "new", FROM_UNIXTIME(' . $_SESSION['jpSimulationDateTime'] .
            '))';
    }
    ;

    if (empty($values)) {
        return true;
    }
    ;

    $sql = 'INSERT INTO #__jp_links (`href`, `status`, `simtime`) VALUES ' . implode(',',
        $values);

    $database->setQuery($sql);
    if (!$result = $database->query()) {
        echo $database->stderr();
        exit;
    }

    return true;

}

function jpSkipUrl($url, $updateSefURLs = true)
{

    global $mosConfig_sef;

    if ($url != null) {
        if (strpos($url, '/index2.php') !== false) {
            $_SESSION['jpSkipLinksCount'] = $_SESSION['jpSkipLinksCount'] + 1;
            return true;
        }
        ;

        if (isset($_SESSION['jpSkipDirectories'])) {
            foreach ($_SESSION['jpSkipDirectories'] as $skipDirectory) {
                $fullDirectory = $GLOBALS['mosConfig_live_site'] . $skipDirectory . '/';
                if (substr($url, 0, strlen($fullDirectory)) == $fullDirectory) {
                    $_SESSION['jpSkipLinksCount'] = $_SESSION['jpSkipLinksCount'] + 1;
                    return true;
                }
            }
        }
    }
    ;

    static $sefURLs;

    if ($updateSefURLs and $mosConfig_sef) {

        global $database;

        $database->setQuery('SELECT `sef`,`component` FROM `#__jp_pages`');
        $result = $database->query();
        if (!$result) {
            echo $database->stderr();
            exit;
        }
        $sef = $database->loadAssocList();

        $sefURLs = array();

        foreach ($sef as $value) {
            $sefURLs[$GLOBALS['mosConfig_live_site'] . $value['sef']] = $value['component'];
        }

    }
    ;

    if ($url != null) {

        if (!$mosConfig_sef) {
            if (isset($_SESSION['jpSkipComponents'])) {

                $t = parse_url($url);
                $t2 = array();
                if (isset($t['query']))
                    parse_str($t['query'], $t2);
                if (isset($t2['option']) and isset($_SESSION['jpSkipComponents'][$t2['option']])) {
                    $_SESSION['jpSkipLinksCount'] = $_SESSION['jpSkipLinksCount'] + 1;
                    return true;
                }
            }
            ;
        } else {
            if (isset($_SESSION['jpSkipComponents']) and isset($sefURLs[$url])) {
                if (isset($_SESSION['jpSkipComponents'][$sefURLs[$url]])) {
                    $_SESSION['jpSkipLinksCount'] = $_SESSION['jpSkipLinksCount'] + 1;
                    return true;
                }
            }
        }

    }

    return false;
}

function jpSavePage($url)
{
    global $database, $mosConfig_sef, $mosConfig_live_site;

    require_once 'components/com_jp/query.php';

    $url = jpAbsUrl($url);

    if (jpSkipUrl($url)) {
        return 'skip';
    }
    ;

    $page = new jpParser($url);

    jpSaveImages($page);

    $database->setQuery('SELECT `id` FROM `#__jp_links` WHERE `status` = "new" AND `href` = "' .
        $url . '" AND `simtime` = FROM_UNIXTIME(' . $_SESSION['jpSimulationDateTime'] .
        ')');
    $result = $database->query();
    if (!$result) {
        echo $database->stderr();
        exit;
    }
    $indexedURLId = $database->loadResult();
    unset($result);
    $page->ptitle = $database->getEscaped($page->ptitle);
    if (empty($indexedURLId)) {
        $sql = "
           INSERT INTO #__jp_links (`href`, `title`, `type`, `status`, `simtime`, `contenttype`, `errcode`, `moved`, `tagtitle`, `pagesize`, `imagessize`)
           VALUES ('$url', '$page->ptitle', '$page->type', 'indexed', FROM_UNIXTIME(" .
            $_SESSION['jpSimulationDateTime'] . "),
                   '$page->contentType', '$page->errorcode', '$page->location', '', $page->page_size, $page->images_size)";

        $database->setQuery($sql);
        if (!$result = $database->query()) {
            echo $database->stderr();
            exit;
        }

        $indexedURLId = $database->insertid();

    } else {

        $sql = "
           UPDATE #__jp_links SET 
             `title` = '$page->ptitle', 
             `type` = '$page->type', 
             `status` = 'indexed', 
             `simtime` = FROM_UNIXTIME(" . $_SESSION['jpSimulationDateTime'] .
            "), 
             `contenttype` = '$page->contentType',
             `errcode` = '$page->errorcode',
             `moved` = '" . addslashes($page->location) . "',
             `pagesize` = $page->page_size,
             `imagessize` = $page->images_size
           WHERE `id` = " . $indexedURLId . "
        ";

        $database->setQuery($sql);
        if (!$result = $database->query()) {
            echo $database->stderr();
            exit;
        }

    }

    jpSaveLinks($page);

    if (substr($url, 0, strlen($mosConfig_live_site)) == $mosConfig_live_site) {

        $pathUrl = substr($url, strlen($mosConfig_live_site));

        if ($mosConfig_sef) {
            $where = '`sef` = "' . $pathUrl . '"';
        } else {
            $where = '`original` = "' . $pathUrl . '"';
        }

        $sql = "
           UPDATE #__jp_pages SET 
             `title` = '$page->ptitle', 
             `meta_title` = '$page->mtitle', 
             `meta_description` = '$page->mdescription', 
             `isindexed` = 1, 
             `errcode` = '$page->errorcode', 
             `meta_keywords` = '$page->mkeywords',
             `page_size` = '$page->page_size',
             `images_size` = '$page->images_size',
             `keywords` = '$page->keywords'
           WHERE $where
        ";

        $database->setQuery($sql);
        if (!$result = $database->query()) {
            echo $database->stderr();
        }
        ;

    }
    ;

    return $indexedURLId;
}

function jpCreateTumbnail($imagem, $max_width, $max_height, $quality)
{

    global $mosConfig_absolute_path;

    $out['image'] = '';
    $out['mime'] = '';
    $out['width'] = 0;
    $out['height'] = 0;
    $out['size'] = 0;

    if (!file_exists($imagem))
        return $out;

    $file = basename($imagem);
    $size = getimagesize($imagem);

    $koeffWidth = 1;
    $koeffHeight = 1;

    if ($size[0] > $max_width) {
        $koeffWidth = $size[0] / $max_width;
    }
    ;

    if ($size[1] > $max_height) {
        $koeffHeight = $size[1] / $max_height;
    }
    ;

    $koeff = $koeffHeight > $koeffWidth ? $koeffHeight : $koeffWidth;
    $width = (int)($size[0] / $koeff);
    $height = (int)($size[1] / $koeff);

    switch (strtolower(strrchr($imagem, '.'))) {
        case ".gif":
            $img = @imagecreatefromgif($imagem);
            break;
        case ".png":
            $img = @imagecreatefrompng($imagem);
            break;
        case ".jpg":
        case ".jpeg":
            $img = @imagecreatefromjpeg($imagem);
            break;
    }

    if (!$img) {
        return $out;
    }
    ;

    $mini = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($mini, 255, 255, 255);

    imagefilledrectangle($mini, 0, 0, $width, $height, $white);
    imagecopyresampled($mini, $img, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);

    $tmpfile = $mosConfig_absolute_path .
        '/administrator/components/com_jp/images/preview/temp_preview.jpg';

    imagejpeg($mini, $tmpfile, $quality);

    $out['image'] = '';
    $out['mime'] = $size['mime'];
    $out['width'] = $size[0];
    $out['height'] = $size[1];
    $out['size'] = filesize($imagem);

    imagedestroy($mini);
    imagedestroy($img);

    return $out;
}

function jpAbsUrl($url, $base = '')
{
    global $mosConfig_live_site;

    $url = trim($url);

    $sharpPos = strpos($url, '#');

    if ($sharpPos !== false) {
        $url = substr($url, 0, $sharpPos);
    }

    if (empty($url)) {
        return $mosConfig_live_site . '/';
    }
    ;

    if (substr($url, 0, 7) == 'mailto:' or substr($url, 0, 11) == 'javascript:' or
        substr($url, 0, 5) == 'news:' or substr($url, 0, 4) == 'ftp:' or substr($url, 0,
        5) == 'file:') {
        return $url;
    }

    if (substr($url, 0, 7) == 'http://' or substr($url, 0, 8) == 'https://') {
        return $url;
    }

    if (substr($url, 0, 1) == '/') {
        $url = $mosConfig_live_site . $url;
    } else {
        $url = $mosConfig_live_site . '/' . $url;
    }

    return $url;
}

function jpMsg($text)
{
    return sprintf('<div class="message">%s</div>', JText::_($text));
}

function jpSkipComponent($href)
{
    global $mosConfig_live_site;
    //echo $href."<BR>";
    //var_dump(preg_match("/^".preg_quote($mosConfig_live_site)."\/index2.php?option=com_content&task=view/iU", $href));

    /* My
    if(preg_match("/".preg_quote("index2.php?option=com_content&task=view", "/")."/iU", $href['href']))
    return true;
    if(preg_match("/".preg_quote("index2.php?option=com_content&do_pdf", "/")."/iU", $href['href']))
    return true;
    if(preg_match("/".preg_quote("index2.php?option=com_content&task=emailform", "/")."/iU", $href['href']))
    return true;
    */

    $skip = mosGetParam($_REQUEST, 'skip');
    $dirskip = mosGetParam($_REQUEST, 'dirskip');
    $aliases = mosGetParam($_REQUEST, 'alias');

    if (empty($skip)) {
        return false;
    }
    ;

    // Parse normal Url like ?var=val&var2=val2&

    $qry = parse_url($href);

    if (isset($qry['query'])) {
        parse_str($qry['query'], $options);
        if (isset($options['option'])) {
            if (in_array($options['option'], $skip)) {
                return true;
            }
            ;
        }
    }

    /* My
    
    foreach($aliases AS $component => $alias)
    {
    // Parse Joomla standard SEF URLS
    
    if(preg_match("/option,$component/iU", $href['href']))
    {
    if(in_array($component, $skip)) return true;
    }
    
    //parse all other Joomla Urls
    if((strlen($alias) > 1) && preg_match("/^".preg_quote($mosConfig_live_site)."\/".preg_quote($alias)."/iU", $href))
    {
    if(in_array($component, $skip)) return true;
    }
    }
    
    // skip directopies.
    if($dirskip && (substr($dirskip, 0, 12) != 'For Example:'))
    { 
    $dirs = explode("\n", $dirskip);
    foreach($dirs AS $dir)
    {
    $dir = ereg_replace("^/","",trim($dir));
    if($dir == '') continue;
    if(preg_match("/^".preg_quote($mosConfig_live_site)."\/".preg_quote($dir)."/iU", $href))
    {
    return true;
    }
    }
    }

    */

    return false;
}

function jpGetSkipWords()
{
    global $database;
    $sql = "SELECT word FROM #__jp_skipwords";
    $database->setQuery($sql);
    if (!$result = $database->query()) {
        echo $database->stderr();
        exit;
    }
    return $database->loadResultArray();
}

function jpFormatSize($size)
{

    $kb = 1024;
    $mgb = $kb * 1024;
    $gb = $mgb * 1024;
    $trb = $gb * 1024;

    if ($size > $trb) {
        return number_format($size / $trb, 2, ',', ' ') . " Tb";
    } elseif ($size > $gb) {
        return number_format($size / $gb, 1, ',', ' ') . " Gb";
    } elseif ($size > $mgb) {
        return number_format($size / $mgb, 0, ',', ' ') . " Mb";
    } elseif ($size > $kb) {
        return number_format($size / $kb, 0, ',', ' ') . " Kb";
    } else {
        return $size . " B";
    }
}

function jpGetComponentImageUrl($component)
{
    global $database, $mosConfig_live_site;

    $database->setQuery("SELECT admin_menu_img FROM #__components WHERE `option` = '$component' AND parent = 0");
    $img = $database->loadResult();
    if (!$img) {
        $img = '/includes/js/ThemeOffice/document.png';
    } else {
        $img = '/includes/' . $img;
    }
    return $mosConfig_live_site . $img;
}

function IndexProcessing(&$row, $i, $imgY = 'ignore2.png', $imgX = 'tick.png')
{

    $img = ($row->robots_index == "noindex" ? $imgY : $imgX);

    $task = $row->robots_index == "index" ? 'noindex' : 'index';
    $alt = ($row->robots_index == "index" ? JText::_('Index') : JText::_('No Index'));
    $action = ($row->robots_index == "index" ? JText::_('Make robot index ') : JText::
        _('Make robot noindex'));

    //		$href = ' <a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">		<img src="components/com_jp/images/'. $img .'" border="0" alt="'. $alt .'" /></a>';
    $href = '<img src="components/com_jp/images/' . $img . '" border="0" alt="' . $alt .
        '" title="' . $alt . '" />';

    return $href;
}

function PublishProcessing(&$row, $i)
{
    $img = $row->published ? 'publish_g.png' : 'publish_x.png';
    $task = $row->published ? 'unpublish' : 'publish';
    $alt = $row->published ? 'Published' : 'No published';
    //                $action        = $row->published ? '������ (�� ���������� �� �����)' : '������������ �� �����';

    //                $href = '
    //                <a href="javascript: void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
    //                <img src="images/'. $img .'" border="0" alt="'. $alt .'" />
    //                </a>'
    //                ;

    $href = '<img src="images/' . $img . '" border="0" title="' . $alt . '"  alt="' .
        $alt . '" />';

    return $href;
}
function PublishSitemap(&$row)
{
    $img = $row->hiden_sitemap ? 'publish_x.png' : 'publish_g.png';
    $task = $row->hiden_sitemap ? 'publish' : 'unpublish';
    $alt = $row->hiden_sitemap ? 'No published' : 'Published';

    $href = '<img src="images/' . $img . '" border="0" title="' . $alt . '"  alt="' .
        $alt . '" />';

    return $href;
}

function FollowProcessing(&$row, $i, $imgY = 'ignore2.png', $imgX = 'tick.png')
{

    $img = ($row->robots_follow == "nofollow" ? $imgY : $imgX);

    $task = $row->robots_follow == "follow" ? 'nofollow' : 'follow';
    $alt = ($row->robots_follow == "follow" ? JText::_('follow') : JText::_('nofollow'));
    $action = ($row->robots_follow == "follow" ? JText::_('Make robot follow ') :
        JText::_('Make robot nofollow'));

    //		$href = '		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">		<img src="components/com_jp/images/'. $img .'" border="0" alt="'. $alt .'" /></a>';
    $href = '<img src="components/com_jp/images/' . $img . '" border="0" alt="' . $alt .
        '" title="' . $alt . '" />';

    return $href;
}

function StatusProcessing(&$row, $i, $imgY = 'status_y.png', $imgR =
    'status_r.png', $imgG = 'status_g.png')
{
    switch ($row->status) {

        case 'r':
            $img = $imgR;
            $alt = (JText::_('All Meta is not set'));
            break;

        case 'y':
            $img = $imgY;
            $alt = (JText::_('Some Meta is not set'));
            break;

        case 'g':
            $img = $imgG;
            $alt = (JText::_('All Meta is set'));
            break;
    }

    $src = '<img src="components/com_jp/images/' . $img . '" border="0" alt="' . $alt .
        '" title="' . $alt . '" />';
    return $src;
}

function KyewordProcessing(&$row, $i)
{
    global $my;

    if (isset($row->checked_out) && $row->checked_out != $my->id) {
        $checked = mosCommonHTML::checkedOut($row);
    } else {
        $checked = mosHTML::idBox($i, $row->id, (@$row->checked_out && @$row->
            checked_out != $my->id), 'kwd');
    }

    return $checked;
}

function jpAddSkipWords($kwd)
{
    global $database;
    if (is_array($kwd)) {
        foreach ($kwd as $kw) {
            $kw = strtolower($kw);
            $sql = "INSERT INTO #__jp_skipwords (`word`) VALUES ('$kw')";
            $database->setQuery($sql);
            if (!$result = $database->query()) {
                echo $database->stderr();
                exit;
            }
        }
    }
}

function jpUpdateXML()
{

    function preExtract($p_event, &$p_header)
    {
        if (file_exists($p_header['filename'])) {
            unlink($p_header['filename']);
        }
        return 1;
    }

    global $database;

    require_once ($GLOBALS['mosConfig_absolute_path'] .
        '/administrator/includes/pcl/pclzip.lib.php');

    $xmlURL = JEConfig::get("SEF.jp_sef_xml_url");

    $xmlConfigs = @file_get_contents($xmlURL);

    $filename = $GLOBALS['mosConfig_absolute_path'] .
        '/administrator/components/com_jp/sef_configs/tmp_xml.zip';

    if (!empty($xmlConfigs)) {
        file_put_contents($filename, $xmlConfigs);

        $zipfile = new PclZip($filename);

        $r = $zipfile->extract(PCLZIP_OPT_PATH, dirname($filename),
            PCLZIP_CB_PRE_EXTRACT, 'preExtract');

        if ($r == 0) {
            echo 'File extract error !';
        } else {
            $sql = '  UPDATE `#__jp_google_tools` SET  value = "' . $xmlURL . '",date = "' .
                date("Y-m-d") . '" WHERE id = 5';
            $database->setQuery($sql);
            $database->query();
            unlink($filename);
        }
        ;
    } else {
        echo 'File of SEF integration not found at <a href="' . $xmlURL . '">' . $xmlURL .
            '</a>';
    }
}

function jpGsm()
{

    global $database, $mosConfig_live_site, $mosConfig_absolute_path;

    $sitemap_filename = JEConfig::get("general.jp_sitemap_filename");
    //      if (!is_writable($mosConfig_absolute_path."/".$sitemap_filename))
    //      {
    //          echo "Can not create the <b>{$sitemap_filename}</b> because <b>$mosConfig_absolute_path</b> is not writable";
    //          return FALSE;
    //      }
    $sitemap_file = $mosConfig_live_site . "/" . $sitemap_filename;
    $sitemap_filename = $mosConfig_absolute_path . '/' . $sitemap_filename;

    $dater = date("Y-m-d");

    $changefreq = JEConfig::get('general.jp_gsm_changefreq', 'com_jp');
    $pages = JEConfig::get('general.jp_gsm_pages', 'com_jp');

    $priority = "0.5";

    $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">\n";
    $footer = "</urlset>\n";

    $prefix = "<url>\n<loc>";
    $postfix = "</loc>\n<lastmod>" . $dater . "</lastmod>\n<changefreq>" . $changefreq .
        "</changefreq>\n<priority>" . $priority . "</priority>\n</url>\n";

    if ($GLOBALS['mosConfig_sef']) {
        $sql = "SELECT `sef` AS `url`, IF(google_weight,google_weight,'0.5') as google_weight , if(changefreq != '',changefreq,'$changefreq') as chan FROM `#__jp_pages`";
    } else {
        $sql = "SELECT `original` AS `url`,  IF(google_weight,google_weight,'0.5') as google_weight , if(changefreq != '',changefreq,'$changefreq') as chan FROM `#__jp_pages`";
    }

    if ($pages == "Published Pages")
        $sql .= " WHERE hiden_sitemap = 0 AND published = 1 GROUP BY  `url`";
    elseif ($pages == "Not Hidden Pages")
        $sql .= " WHERE hiden_sitemap = 0 AND  ishidden = 0 GROUP BY  `url`";

    $database->setQuery($sql);
    $rows = $database->loadAssocList();
    $map = $header;

    $repl1 = array('&', "'", '"', '>', '<');
    $repl2 = array('&amp;', '&apos;', '&quot;', '&gt;', '&lt;');

    foreach ($rows as $row) {
        if ($row['url'] != '') {
            $map .= $prefix;
            $map .= $mosConfig_live_site . str_replace($repl1, $repl2, $row['url']);
            $map .= "</loc>\n<lastmod>" . $dater . "</lastmod>\n<changefreq>" . $row['chan'] .
                "</changefreq>\n<priority>" . $row['google_weight'] . "</priority>\n</url>\n";
        }
    }
    $map .= $footer;
    if (!file_put_contents($sitemap_filename, $map)) {
        echo "Can not create the <b>{$sitemap_filename}</b> because <b>$mosConfig_absolute_path</b> is not writable";
        return false;
    }

    $sql = "  UPDATE `#__jp_google_tools` SET  value = '$sitemap_file', date = '$dater' WHERE id = '1'";
    $database->setQuery($sql);
    $database->query();
    return true;
}

function jpRefreshRank($cid)
{
    global $database, $mosConfig_live_site;
    if (count($cid) < 1) {
        return;
    }

    //    $dater        = date ( "Y-m-d" );
    //    $homepage_google_pr = jpRank::getGoogleRank($mosConfig_live_site);
    //    $sql = "  UPDATE `#__jp_google_tools` SET  value = '".($homepage_google_pr ? $homepage_google_pr : 0)."', date = '$dater' WHERE id = '2'";
    //    $database->setQuery($sql);
    //    $database->query();
    //

    //    $gr = JEConfig::get("general.jp_google_pr");
    //    $ar = JEConfig::get("general.jp_alexa_pr");

    if (isset($_POST['doAllComponent'])) {
        $sql = "SELECT * FROM `#__jp_pages` WHERE true " . getWhere() .
            ' GROUP BY `sef`';
    } else {

        $cids = implode(',', $cid);

        $sql = "SELECT * FROM `#__jp_pages` WHERE id IN ($cids)";
    }

    $database->setQuery($sql);
    $rows = $database->loadAssocList();
    foreach ($rows as $row) {
        $google_pr = jpRank::getGoogleRank($mosConfig_live_site . $row['sef']);
        $alexa_pr = jpRank::getAlexaRank($mosConfig_live_site . $row['sef']);
        $sql = "  UPDATE `#__jp_pages` SET  google_pr = '$google_pr', alexa_pr = '$alexa_pr' WHERE `sef` = '$row[sef]'";
        $database->setQuery($sql);
        $database->query();
    }
}

function jpHidePage($cid)
{
    global $database;
    if (count($cid) < 1) {
        return;
    }

    if (isset($_POST['doAllComponent'])) {
        $sql = "UPDATE #__jp_pages SET `ishidden` = (`ishidden`<>1) WHERE true " .
            getWhere();
    } else {

        $cids = implode(',', $cid);

        $sql = 'SELECT `sef` FROM #__jp_pages WHERE `id` IN ( ' . $cids . ')';

        $database->setQuery($sql);
        $rows = $database->loadResultArray();
        foreach ($rows as $key => $value) {
            $rows[$key] = '"' . $value . '"';
        }

        $sefs = implode(',', $rows);

        $sql = 'UPDATE `#__jp_pages` SET `ishidden` = (`ishidden`<>1)  WHERE `sef` IN ( ' .
            $sefs . ')';
    }

    $database->setQuery($sql);
    $database->query();
}
function jpHidePageSitemap($cid)
{
    global $database;
    if (count($cid) < 1) {
        return;
    }

    if (isset($_POST['doAllComponent'])) {
        $sql = "UPDATE #__jp_pages SET `hiden_sitemap` = (`hiden_sitemap`<>1) WHERE true " .
            getWhere();
    } else {

        $cids = implode(',', $cid);

        $sql = 'SELECT `sef` FROM #__jp_pages WHERE `id` IN ( ' . $cids . ')';

        $database->setQuery($sql);
        $rows = $database->loadResultArray();
        foreach ($rows as $key => $value) {
            $rows[$key] = '"' . $value . '"';
        }

        $sefs = implode(',', $rows);

        $sql = 'UPDATE `#__jp_pages` SET `hiden_sitemap` = (`hiden_sitemap`<>1)  WHERE `sef` IN ( ' .
            $sefs . ')';
    }

    $database->setQuery($sql);
    $database->query();
}




?>
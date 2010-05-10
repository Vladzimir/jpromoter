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

function jpSpliceStr($str, $maxLen)
{
    return strlen($str) > $maxLen ? chunk_split($str, $maxLen, ' ') : $str;
}

function jpQuickIcon($link, $image, $text)
{
    global $mainframe;
    //$lang = $mainframe->getLanguage();

?>
		<div style="float:left;">
			<div class="icon">
				<a href="<?php echo $link; ?>">
					<?php echo mosAdminMenus::imageCheckAdmin($image, '', null, null, $text); ?>
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php
}

function jpControlPanel()
{
?>
		<table class="adminheading">
		<tr>
			<th class="cpanel">
			JPromoter Control Panel
			</th>
		</tr>
		</table>
   <table class="adminform">
      <tr valign="top">
      <td width="56%">
  	    <div id="cpanel">
		<?php

    $link = 'index2.php?option=com_jp&amp;task=optimize';
    jpQuickIcon($link, '/administrator/components/com_jp/images/searchtext.png',
        JText::_('SEF URLs'));

    $link = 'index2.php?option=com_jp&amp;task=scan';
    jpQuickIcon($link, '/administrator/images/searchtext.png', JText::_('SE Simulation'));

    $link = 'index2.php?option=com_jp&amp;task=google';
    jpQuickIcon($link, '/administrator/components/com_jp/images/google.png', JText::
        _('SEO Tools'));

    $link = 'index2.php?option=com_jp&amp;task=config';
    jpQuickIcon($link, '/administrator/images/config.png', JText::_('Configuration'));

?>
            </div>
          </td>

          <td width="44%">


<?php
    $tabs = new mosTabs(1);
    $tabs->startPane("configPane");
    $tabs->startTab(JText::_('Summary'), "summaty-page");
?>
<table class="adminform">
  <tr>
    <th colspan="2"><?php echo JText::_('Counts'); ?></th>
  </tr>
  
  <tr class="row1">
    <td width="200px"><?php echo JText::_('Simulate links'); ?></td>
    <td>
    <?php
    global $database;
    $sql = "SELECT COUNT(*) FROM #__jp_links";
    $database->setQuery($sql);
    //$database->query();
    echo $database->loadResult();
?>
    </td>
  </tr>
  
  <tr class="row0">
    <td><B style="color: #0033cc;"><?php echo JText::_('SEF URLs'); ?></B></td>
    <td>
    <?php
    global $database;
    $sql = "SELECT COUNT(*) FROM #__jp_pages";
    $database->setQuery($sql);
    //$database->query();
    echo $database->loadResult();
?>
    </td>
  </tr>

  <tr class="row1">
    <td><?php echo JText::_('Optimized Pages'); ?></td>
    <td>
    <?php
    global $database;
    $sql = "SELECT COUNT(*) FROM #__jp_pages WHERE newmeta_keywords != '' AND  newmeta_description != ''";
    $database->setQuery($sql);
    //$database->query();
    echo $database->loadResult();
?>
    </td>
  </tr>

  <tr class="row0">
    <td><?php echo JText::_('External Links'); ?></td>
    <td>
    <?php
    global $database;
    $sql = "SELECT COUNT(*) FROM #__jp_links WHERE `type` = 'external'";
    $database->setQuery($sql);
    //$database->query();
    echo $database->loadResult();
?>
    </td>
  </tr>

  <tr class="row0">
    <td><?php echo JText::_('Images'); ?></td>
    <td>
    <?php
    global $database;
    $sql = "SELECT COUNT(*) FROM #__jp_images";
    $database->setQuery($sql);
    //$database->query();
    echo $database->loadResult();
?>
    </td>
  </tr>

<!--
  <tr>
    <th colspan="2"><?php echo JText::_('Sizes'); ?></th>
  </tr>

  <tr class="row0">
    <td><?php echo JText::_('Average Images'); ?></td>
    <td>
    </td>
  </tr>

  <tr class="row1">
    <td><?php echo JText::_('Average Pages'); ?></td>
    <td></td>
  </tr>
  -->
</table>
<?php
    $tabs->endTab();

    $tabs->endPane()
?>
          </td>
        </tr>
      </table>
    </div>
 
<?php
}

function jpGetPagesNum()
{
    global $database;
    $sql = "SELECT COUNT(*) FROM #__jp_links";
    $database->setQuery($sql);
    $database->query();
    $num = $database->loadResult();
    return intval($num);
}

function jpIndexForm()
{
?>
<table class="adminheading">
<tr>
	<th class="searchtext">
	SE Simulation
	</th>
</tr>
</table>

<?php

    global $database;

    $sql = "SELECT `simtime`, COUNT(`simtime`) AS `links` FROM `#__jp_links` WHERE `status` = 'indexed' GROUP BY `simtime` ORDER BY `simtime` DESC";
    $database->setQuery($sql);
    $rows = $database->loadObjectList();
    if (count($rows) != 0) {

        $k = 0;
        $i = -1;
?>
    <table class="adminlist">
      <TR>
        <TH width="1%"><input type="checkbox" name="toggle" value="" onclick="checkAll(100);"></TH>
        <TH><?php echo JText::_('Simulation Date'); ?></TH>
        <TH><?php echo JText::_('Indexed links'); ?></TH>
      </TR>

<?php

        foreach ($rows as $row) {
            $k = 1 - $k;
            $i += 1;
            $simtime = rawurlencode($row->simtime);
?>

          <tr <?php echo 'class="row' . $k . '"'; ?>>
            <td><?php echo mosHTML::idBox($i, $simtime) ?></td>
            <td align="center"><a href="<?php echo
'index2.php?option=com_jp&task=reports&simtime=' . $simtime; ?>" title="Show report"><?php echo
$row->simtime ?></a></td>
            <td align="center"><?php echo $row->links ?></td>
          </tr>  

<?php

        }

        echo '</table>';

    } else {
        echo '<span style="padding-bottom: 20px; padding-top: 20px; text-align: center; font-size: 14px; font-weight: bold;">Not completed simulations !</span>';
    }

?>
          


<?php
}

function jpEndSimulate()
{
    $simDate = date('Y-m-d H:i:s', $_SESSION['jpSimulationDateTime']);
    $adminMessage = 'SE Simulation (' . $simDate . ')';
    global $mosConfig_live_site;

    switch ($_SESSION['jpSimulationStatus']) {
        case 'allfinish':
            $infoMessage = 'Simulate execution time ~' . $_SESSION['jpExecuteTime'] . ' sec';
            $infoMessage .= '<br /><br />Indexed pages:' . $_SESSION['jpSimulationCounts']['pages'] .
                '&nbsp;&nbsp;Skip links:' . $_SESSION['jpSimulationCounts']['skip'];
            $statusMessage = 'Current SE Simulation is full complete ! You may <a href="' .
                $mosConfig_live_site .
                '/administrator/index2.php?option=com_jp&task=reports&simtime=' . rawurlencode($simDate) .
                '">finish</a> simulation.';
            break;
        case 'stepfinish':
            $_SESSION['jpSimulationStatus'] = 'continue';
            $infoMessage = 'Simulate execution time ~' . $_SESSION['jpExecuteTime'] . ' sec';
            $infoMessage .= '<br /><br />Indexed pages:' . $_SESSION['jpPagesCount'] .
                '&nbsp;&nbsp;Skip links:' . $_SESSION['jpSkipLinksCount'];
            $infoMessage .= '<br />All indexed pages:' . $_SESSION['jpSimulationCounts']['pages'] .
                '&nbsp;&nbsp;All skip links:' . $_SESSION['jpSimulationCounts']['skip'];
            $statusMessage = 'Current SE Simulation is not complete ! You may ' .
                '<a href="' . $mosConfig_live_site .
                '/administrator/index2.php?option=com_jp&task=startsimulate">continue</a> or ' .
                '<a href="' . $mosConfig_live_site .
                '/administrator/index2.php?option=com_jp&task=reports&simtime=' . rawurlencode($simDate) .
                '">finish</a> simulation.';
            break;
        default:
            $infoMessage = 'No result';
            $statusMessage = 'All pages skipped';
            break;
    }
    ;

?>
    
<table class="adminheading"><tr><th class="searchtext"><?php echo $adminMessage ?></th></tr></table>
<table class="adminform">
<tr><th><?php echo JText::_('Simulation status'); ?></th></tr>
<tr>
<td style="padding-bottom: 20px; padding-top: 20px; text-align: center; font-size: 14px; font-weight: bold;">
<?php echo $infoMessage; ?>
<br/><br/>
<?php echo $statusMessage; ?>
</td>
</tr>
</table>

<?php
}

function jpSimulate()
{
?>
    
<table class="adminheading"><tr><th class="searchtext">New Simulation</th></tr></table>
<table class="adminform">
<tr><th colspan="3"><?php echo JText::_('Simulation Options'); ?></th></tr>
<tr valign="top">
    <TD width="50%">
      <FIELDSET>
        <LEGEND>Skip Directories</LEGEND>
Please, enter skip directories in text area. Each directory must beginning from root - <b>" / "</b>. For example:
<p>
<strong>
/affiliates<br />
/en<br />
/demo/templates<br />
</strong>
</p>
        <TEXTAREA id="skipdir" name="dirskip" class="text_area" style="width: 100%;" rows="12">/administrator</TEXTAREA>
      </FIELDSET>
    </TD>
    <TD width="50%">
      <FIELDSET>
        <LEGEND>Count of indexed pages</LEGEND>
        
<table>
<tr>
	<td>
Depending on server setup and number of site pages, operation of SE simulation may be very slow and take a lot of time. To avoid timeout the whole process is divided into stages. Please choose the maximum number of pages which will be indexed during the one stage.
    </td>
	<td>
<select name="indcount" id="countind" style="width: 100px;">
	<option value="5">5 pages</option>
	<option value="10" SELECTED>10 pages</option>
	<option value="15">15 pages</option>
	<option value="20">20 pages</option>
	<option value="30">30 pages</option>
	<option value="50">50 pages</option>
	<option value="100">100 pages</option>
</select>
	</td>
</tr>
</table>
      </FIELDSET>
      <fieldset>
      <legend><?php echo JText::_('Skip components'); ?> </legend>
Please, mark skip components.
<table>
<?php
    global $mosConfig_absolute_path;
    $root = $mosConfig_absolute_path . '/components/';
    $pointer = opendir($root);
    $cops = array('com_wrapper', 'com_search', 'com_jp', 'com_user', 'com_login',
        'com_poll', 'com_registration');
    $colCount = 4;
    $colFlag = 1;
    while (false !== ($file = readdir($pointer))) {
        if ($file == '.' || $file == '..')
            continue;
        if (is_dir($root . $file)) {
            if ($file == 'components' or $file == 'com_frontpage')
                continue;

            if (in_array($file, $cops)) {
                $s = ' checked=checked ';
            } else {
                $s = '';
            }
            echo $colFlag == 1 ? '<tr>' : '';
?>
                <td>
                <input type="checkbox" <?php echo $s; ?>  id="<?php echo $file ?>" name="skip[<?php echo
$file; ?>]" value="<?php echo
$file ?>" />
                <label for="<?php echo $file ?>"><?php echo $file ?></label>
                </td>
             <?php
            if ($colFlag == $colCount) {
                echo '</tr>';
                $colFlag = 1;
            } else {
                $colFlag += 1;
            }
        }
    }
?>        
</table>
      </fieldset>
    </TD>
</tr>
</table>
    
<?php
}

function jxListPagesHtml()
{
    global $CHARSET, $mosConfig_live_site, $mainframe, $mosConfig_absolute_path, $database;

    $where = '';
?>
    
		<table class="adminheading">
		<tr>
			<th style="background: url(<?php echo $mosConfig_live_site; ?>/administrator/components/com_jp/images/searchtext.png) no-repeat left;">
			SEF URLs
			</th>
		</tr>
		</table>
    
    <table width="100%" border="0">
        <TR>
          <TD align="left">
          <?php
    echo '<input type="checkbox" name="doAllComponent" value="1" onclick="document.adminForm.toggle.checked=this.checked;checkAll(100)"/> Apply to all filtered pages';
?>
          </TD>
          <TD align="right"> Search parameter:
          
          <?php

    $searchCond[] = mosHTML::makeOption('all', JText::_('All'), 'vn', 'tn');
    $searchCond[] = mosHTML::makeOption('original', JText::_('URL'), 'vn', 'tn');
    $searchCond[] = mosHTML::makeOption('sef', JText::_('SEF URL'), 'vn', 'tn');
    $searchCond[] = mosHTML::makeOption('title', JText::_('Title'), 'vn', 'tn');
    $searchCond[] = mosHTML::makeOption('new_title', JText::_('New Title'), 'vn',
        'tn');

    echo mosHTML::selectList($searchCond, 'search_cond', 'class="inputbox" size="1"',
        'vn', 'tn', mosGetParam($_REQUEST, 'search_cond'));

?>

          &nbsp;&nbsp;Search text: <INPUT type="text" name="q" value="<?php echo
mosGetParam($_REQUEST, 'q') ?>" />
          <INPUT type="button" class="button" value="Search" onclick="javascript:submitbutton('optimize')" />
          </TD>
        </TR>
      </table>
      <table width="100%" border="0">
          <tr valign="bottom">
            <td>
                <input type="checkbox" name="showhide" value="1"  onclick="javascript:submitform('optimize')" <?php if (mosGetParam
($_REQUEST, 'showhide', false))
        echo " CHECKED=CHECKED "; ?>/>Show only hidden
                
            </td>
            <td align="right">Filters:
                <?php
    $sql = "SELECT component FROM `#__jp_pages` group by component";
    $database->setQuery($sql);
    $rows = $database->loadObjectList();
    $cmp[] = mosHTML::makeOption(null, JText::_('- Select Component -'), 'i',
        'index');
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            $cmp[] = mosHTML::makeOption($row->component, JText::_('Component') . ' ' . $row->
                component, 'i', 'index');
        }
    }
    echo mosHTML::selectList($cmp, 'component',
        'class="inputbox" size="1" onChange="javascript:submitform(\'optimize\')"', 'i',
        'index', mosGetParam($_REQUEST, 'component'));

    $indexed[] = mosHTML::makeOption('', JText::_('- Select Indexed -'), 'in',
        'indexed');
    $indexed[] = mosHTML::makeOption('yes', JText::_('Yes'), 'in', 'indexed');
    $indexed[] = mosHTML::makeOption('no', JText::_('No'), 'in', 'indexed');
    echo mosHTML::selectList($indexed, 'indexed',
        'class="inputbox" size="1" onChange="javascript:submitform(\'optimize\')"', 'in',
        'indexed', mosGetParam($_REQUEST, 'indexed'));

    $indx[] = mosHTML::makeOption('', JText::_('- Select Index -'), 'i', 'index');
    $indx[] = mosHTML::makeOption('index', JText::_('Index'), 'i', 'index');
    $indx[] = mosHTML::makeOption('noindex', JText::_('No Index'), 'i', 'index');
    echo mosHTML::selectList($indx, 'index',
        'class="inputbox" size="1" onChange="javascript:submitform(\'optimize\')"', 'i',
        'index', mosGetParam($_REQUEST, 'index'));

    $flw[] = mosHTML::makeOption('', JText::_('- Select Follow -'), 'f', 'follow');
    $flw[] = mosHTML::makeOption('follow', JText::_('Follow'), 'f', 'follow');
    $flw[] = mosHTML::makeOption('nofollow', JText::_('No Follow'), 'f', 'follow');
    echo mosHTML::selectList($flw, 'follow',
        'class="inputbox" size="1" onChange="javascript:submitform(\'optimize\')"', 'f',
        'follow', mosGetParam($_REQUEST, 'follow'));

    $publ[] = mosHTML::makeOption('', JText::_('- Select Published -'), 'p',
        'publish');
    $publ[] = mosHTML::makeOption('Y', JText::_('Published'), 'p', 'publish');
    $publ[] = mosHTML::makeOption('N', JText::_('Unpudlished'), 'p', 'publish');
    echo mosHTML::selectList($publ, 'publish',
        'class="inputbox" size="1" onChange="javascript:submitform(\'optimize\')"', 'p',
        'publish', mosGetParam($_REQUEST, 'publish'));

    $stat[] = mosHTML::makeOption('', JText::_('- Select Status -'), 's', 'status');
    $stat[] = mosHTML::makeOption('g', JText::_('Green status'), 's', 'status');
    $stat[] = mosHTML::makeOption('y', JText::_('Yellow status'), 's', 'status');
    $stat[] = mosHTML::makeOption('r', JText::_('Red status'), 's', 'status');
    echo mosHTML::selectList($stat, 'status',
        'class="inputbox" size="1" onChange="javascript:submitform(\'optimize\')"', 's',
        'status', mosGetParam($_REQUEST, 'status'));



?>
            </td>
        </tr>
    </table>
    <style type="text/css">
      <!--
        .hidden
        {
          color:#f0f0f0;
        }
      -->
      </style>
    <table class="adminlist">
      <TR>
        <TH width="1%">
        <input type="checkbox" name="toggle" value=""  onclick="checkAll(100);" />
        </TH>
        <TH style="width: 1%;"><?php echo JText::_('Cmp'); ?></TH>
        <TH><?php echo JText::_('Page'); ?></TH>
        <TH style="width: 1%;"><?php echo JText::_('Sitemap'); ?></TH>
        <TH style="width: 1%;"><?php echo JText::_('Indexed'); ?></TH>
        <TH style="width: 1%;"><?php echo JText::_('Code'); ?></TH>
        <TH style="width: 1%;"><?php echo JText::_('Gr/Ar'); ?></TH>
        <TH style="width: 1%;"><?php echo JText::_('CurrMeta'); ?></TH>
        <TH style="width: 1%;"><?php echo JText::_('NewMeta'); ?></TH>
        <TH style="width: 1%;"><?php echo JText::_('Index'); ?></TH>
        <TH style="width: 1%;"><?php echo JText::_('Follow'); ?></TH>        
        <TH style="width: 1%;"><?php echo JText::_('Publish'); ?></TH>
        <!--
        <TH><?php echo JText::_('Meta Title'); ?></TH>
        <TH><?php echo JText::_('Meta Keywords'); ?></TH>
        <TH><?php echo JText::_('Meta Description'); ?></TH>
        -->
        <TH style="width: 1%;"><?php echo JText::_('Status'); ?></TH>
       </TR>
      <?php


    $where = getWhere();

    $database->setQuery($sql);
    $id = $database->loadResult();

    $sql = "SELECT count(*) FROM `#__jp_pages` WHERE 1=1 $where ";

    $database->setQuery($sql);
    $total = $database->loadResult();
    $option = mosGetParam($_REQUEST, 'option', 'com_jp');

    $limit = $mainframe->getUserStateFromRequest("limit", 'limit', $mainframe->
        getCfg('list_limit'));
    $limitstart = $mainframe->getUserStateFromRequest("$option.limitstart",
        'limitstart', 0);

    require_once ($mosConfig_absolute_path .
        '/administrator/includes/pageNavigation.php');
    $pageNav = new mosPageNav($total, $limitstart, $limit);

    jpPrintRow(0, $where, 0, 0, 1, $id);

?>
    </table> 
 
    <?php echo $pageNav->getListFooter(); ?>
     
    <?php

}

function jpPrintRow($id, $where, $indent, $i, $level = 0, $parent = 0, $title = false)
{
    global $database, $mainframe, $mosConfig_live_site;

    $k = 0;

    if ($title == 'stop_tree')
        return;

    $lv = mosGetParam($_REQUEST, 'lv', 5);

    $option = mosGetParam($_REQUEST, 'option', 'com_jp');

    $limit = $mainframe->getUserStateFromRequest("limit", 'limit', $mainframe->
        getCfg('list_limit'));

    $limitstart = $mainframe->getUserStateFromRequest("$option.limitstart",
        'limitstart', 0);

    $sql = "SELECT * FROM `#__jp_pages` WHERE 1=1 $where ORDER BY `sef`";

    if ($level == 1)
        $database->setQuery($sql, $limitstart, $limit);
    else
        $database->setQuery($sql);

    $rows = $database->loadObjectList();

    if (count($rows) <= 0)
        return;

?>

<style type="text/css" title="ddd">
table.url td 
{
    border-bottom:0px none #E5E5E5;
    padding: 2px 4px 2px 0px;
}
.url
{
    border: 1px solid #DFDFDF;
    padding: 0px 0px 0px 8px;
}
table.ttl td
{
    padding: 0px 0px 0px 0px;
}
.ttl
{
    padding: 0px 0px 0px 0px;
}
</style>

<?php

    $beforeSef = 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzz';

    reset($rows);
    while (list($rowKey, $eachRow) = each($rows)) {
        if ($beforeSef != $eachRow->sef) {
            $beforeSef = $eachRow->sef;
            $row = $eachRow;
            $firstSef = true;
        } else {
            $firstSef = false;
        }

        if ($firstSef) {

            $publish = PublishProcessing($row, $i);
            $index = IndexProcessing($row, $i);
            $follow = FollowProcessing($row, $i);
            $status = StatusProcessing($row, $i);
            $hide = $row->ishidden;

            if ($hide) {
                $class = " class='hidden' ";
            } else {
                $class = '';
            }

            $fullOriginal = stripcslashes($row->original);

            $original = jpSpliceStr($fullOriginal, 50);
            $original = str_replace('&', '&amp;', $original);

            $fullSef = stripcslashes($row->sef);
            $sef = jpSpliceStr($fullSef, 50);

            $pageTitle = $row->isindexed == 0 ?
                '<span style="color: Red; font-weight: bold;">?</span>' : $row->title;
            $pageError = $row->isindexed == 0 ?
                '<span style="color: Red; font-weight: bold;">?</span>' : $row->errcode;

            $gR = empty($row->google_pr) ?
                '<span style="color: Red; font-weight: bold;">?</span>' : $row->google_pr;
            $aR = empty($row->alexa_pr) ?
                '<span style="color: Red; font-weight: bold;">?</span>' : $row->alexa_pr;

            $k = 1 - $k;

?>
          <!-- BEGIN ROW -->
          <tr class="<?php echo 'row1'; ?>">
            <td><?php echo mosCommonHTML::CheckedOutProcessing($row, $i); ?></td>
            <td style="text-align: center;">
            <img src="<?php echo jpGetComponentImageUrl($row->component); ?>" alt="<?php echo
$row->component; ?>" title="<?php echo
$row->component; ?>"/><br />
            </td>
            <td>
            
                    <?php

            echo '<table class="url" cellspacing="0" cellpadding="0" border="0" style="width: 100%; border: 1px solid #1px solid #E5E5E5; margin: 0px 0px 0px 0px;">';
            echo '
                    <tr>
                    <td style="text-align: right; font-weight: bold;">SEF&nbsp;URL:</td>
                    <td colspan="3">
                        <a href="javascript:void(0)" onclick="return listItemTask(\'cb' .
                $i . '\',\'editpage\');" title="Optimize page" style="color: Green;">' . $sef .
                '</a>
                        <a href="' . $mosConfig_live_site . $fullSef .
                '" target="_blank"><img src="components/com_jp/images/opendetail_1.gif" border="0" width="12" height="12" title="Open this page in new window"></a>
                    </td>
                    </tr>
                    ';

?>
                    
                    <tr><td style="text-align: right; width: 1%; font-weight: bold;">URL:</td><td colspan="3"><?php echo
$original; ?></td></tr>
                    
                    <?php

            while (list($nRowKey, $nEachRow) = each($rows)) {
                if ($beforeSef == $nEachRow->sef) {
                    $nOriginal = jpSpliceStr(stripcslashes($nEachRow->original), 50);
                    $nOriginal = str_replace('&', '&amp;', $nOriginal);
?>
                               <tr><td style="text-align: right; width: 1%; font-weight: bold;">:</td><td colspan="3"><?php echo
$nOriginal; ?></td></tr>
                            <?php
                } else {
                    prev($rows);
                    break;
                }
            }

?>
                    
                    <tr>
                    <td valign="top" style="text-align: right; width: 1%; font-weight: bold;">Title:</td>
                    <td valign="top" style="width: 48%; color: Navy;"><?php echo
$pageTitle; ?></td>
                    <td valign="top" style="text-align: right; width: 1%; font-weight: bold;">New:</td>
                    <td valign="top" style="width: 50%; color: Navy;"><?php echo
$row->new_title; ?></td>
                    </tr>
                </table>
            </td>
            <TD align="center"><?php echo PublishSitemap($row); ?></TD>
            <TD align="center"><?php echo $row->isindexed ? 'Yes' : 'No'; ?></TD>
            <TD align="center"><?php echo $pageError; ?></TD>
            <TD align="center"><?php echo $gR . '/' . $aR; ?></TD>
            <td align="center">
            <?php
            if ($row->meta_title)
                echo '<span style="font-weight:bold;color:#cc0000;font-size:12px">T</span>' . SP;
            else
                echo '<span style="font-weight:bold;color:#cccccc;font-size:12px">T</span>' . SP;
            if ($row->meta_keywords)
                echo '<span style="font-weight:bold;color:#0000cc;font-size:12px">K</span>' . SP;
            else
                echo '<span style="font-weight:bold;color:#cccccc;font-size:12px">K</span>' . SP;
            if ($row->meta_description)
                echo '<span style="font-weight:bold;color:#336600;font-size:12px">D</span>' . SP;
            else
                echo '<span style="font-weight:bold;color:#cccccc;font-size:12px">D</span>' . SP;
?>
            </td>
            <TD align="center">
            <?php
            if ($row->newmeta_title)
                echo '<span style="font-weight:bold;color:#cc0000;font-size:12px">T</span>' . SP;
            else
                echo '<span style="font-weight:bold;color:#cccccc;font-size:12px">T</span>' . SP;
            if ($row->newmeta_keywords)
                echo '<span style="font-weight:bold;color:#0000cc;font-size:12px">K</span>' . SP;
            else
                echo '<span style="font-weight:bold;color:#cccccc;font-size:12px">K</span>' . SP;
            if ($row->newmeta_description)
                echo '<span style="font-weight:bold;color:#336600;font-size:12px">D</span>' . SP;
            else
                echo '<span style="font-weight:bold;color:#cccccc;font-size:12px">D</span>' . SP;
?>
            </TD>
            <td align="center"><?php echo $index; ?></td>
            <td align="center"><?php echo $follow; ?></td>        
            <td align="center"><?php echo $publish; ?></td>
            <td align="center"><?php echo $status; ?></td>
          </tr>
          <!-- END ROW -->
        <?php
            $i++;
        }
        //echo str_repeat("+=+", $indent).($row->title ? $row->title : JText::_("No Title"))."\n";

        //        if($tree) jpPrintRow($row->id, $where, ($indent + 1), $i, ($row->level + 1), $row->id, ($title ? 'stop_tree' : ($row->title ? $row->title : 'N/A') ));
    }
}

function jpEditPage($cid)
{
    global $database, $mainframe;

    $query = "SELECT * FROM #__jp_pages WHERE id = '{$cid[0]}'";
    $database->setQuery($query);
    $rows = $database->loadObjectList();

    $fullSef = stripcslashes($rows[0]->sef);
    $sef = jpSpliceStr($fullSef, 50);

    $fullOriginal = stripcslashes($rows[0]->original);
    $original = jpSpliceStr($fullOriginal, 50);

    $original = str_replace('&', '&amp;', $original);

?>
		<table class="adminheading">
		<tr>
			<th class="edit">
			Optimize page <?php echo $rows[0]->isindexed ? '(indexed)' : '(not indexed)'; ?>
			</th>
		</tr>
		<tr>
			<td style="padding-bottom: 8px;"><?php echo 'URL (SEF):&nbsp;&nbsp;<a href="' .
$fullSef . '" target="_blank">' . $sef . '</a>'; ?></td>
		</tr>
<?php

    //
    //		<tr>
    //			<td style="padding-bottom: 8px;"> echo 'URL:&nbsp;&nbsp;' . $original; </td>
    //		</tr>
    //
    //$query = 'SELECT * FROM #__jp_pages WHERE `original`<> "'. $rows[0]->original .'" AND `sef` = "'. $rows[0]->sef .'"';
    //
    //$database->setQuery( $query );
    //$urls = $database->loadObjectList();
    //foreach ($urls as $t)
    //{
    //echo
    //'
    //        <tr>
    //			<td>&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;'. $t->original .'</td>
    //		</tr>
    //';
    //}


?>
		</table>
<?php

    //    $words = jpGetKeyWords($b);

    $keyWords = explode(';', $rows[0]->keywords);

    if ($keyWords[0] != $rows[0]->keywords) {
        foreach ($keyWords as $value) {
            $temp = explode(',', $value);
            if (isset($temp[1])) {
                $words[$temp[0]] = $temp[1];
            }
            ;
        }
        ;
    }
    ;


    $colls = 3;

    $nkw = JEConfig::get('general.jp_kw_per_page');
    $treegroups = mosGetParam($_REQUEST, 'treegroups', 'scaned');

?>

<script language="JavaScript" type="text/javascript">
<!--
function myAppend(tid, text)
{
    element = document.getElementById(tid);
    if (element.value != '') {sp = ' ';} else { sp = '';}
    
    element.value = element.value + sp + text;
}
function myClear(tid)
{
    element = document.getElementById(tid);
    element.value = '';
}

function assignKW()
{
str="";
  for(i = 1; i <= <?php echo $nkw ?>; i++){
    element = document.getElementById('cb'+i);
    if(element.checked)
    {
      myAppend("kw",', '+element.value);
        str +=", "+element.value;
    }
  }
  //myAppend("kw", str);
  
}
function getSel()
{

    var txt = '';
    var nWindow = document.getElementById('pagecache').contentWindow; 
    var nDocument = document.getElementById('pagecache').contentDocument;
    
    if (nWindow.document.getSelection)
    {
        txt = nWindow.document.getSelection();
    }
    else if(nDocument.document.getSelection)
    {
        txt = nDocument.document.getSelection();
    }
    else if(nWindow.document.selection)
    {
        txt = nWindow.document.selection.createRange().text
    }
    else return;
    myAppend('ds', txt);
    //document.getElementById('ds').value = txt;
}

//-->
</script>
<table cellspacing="5" class="adminform">
  <tr valign="top">
    <td width="1%">
       
      <table class="adminform">
        <tr>
          <th><?php echo JText::_('Meta Kyewords'); ?></th>
          <th><?php echo JText::_('Meta Title'); ?></th>
        </tr>
        <tr valign="top">
          <td width="33%">
          <p><span style="font-weight:bold;color:#0000cc;font-size:12px">K</span>: <?php echo
$rows[0]->isindexed ? $rows[0]->meta_keywords : JText::_('<span style=" color: Red; font-weight: bold;">?</span>') ?></p>
          </td>
          <td width="33%">
          <p><span style="font-weight:bold;color:#cc0000;font-size:12px">T</span>: <?php echo
$rows[0]->isindexed ? $rows[0]->meta_title : JText::_('<span style=" color: Red; font-weight: bold;">?</span>') ?></p>
          </td>
        </tr>
        <tr>
          <td><textarea class="text_area" name="new_kw" cols="40" rows="5" id="kw"><?php echo
$rows[0]->newmeta_keywords ?></textarea></td>
          <td><textarea class="text_area" name="new_tl" cols="40" rows="5" id="tl"><?php echo
$rows[0]->newmeta_title ?></textarea></td>
        </tr>
        <tr>
          <td>
          <input onclick="myAppend('kw', '<?php echo $rows[0]->meta_keywords ?>');"  <?php echo
$rows[0]->meta_keywords ? null : 'disabled="disabled"' ?> align="middle" type="button" value="<?php echo
JText::_('Insert Original'); ?>" class="button" />
          <input onclick="myAppend('kw', '<?php echo JEConfig::get('Meta.jp_meta_kwd') ?>');" align="middle" type="button" value="<?php echo
JText::_('Insert Default'); ?>" class="button" />
          <input onclick="myClear('kw')" align="middle" type="button" value="<?php echo
JText::_('Clear'); ?>" class="button" /></td>
          
          <td>
          <input onclick="myAppend('tl', '<?php echo $rows[0]->meta_title ?>');" <?php echo
$rows[0]->meta_title ? null : 'disabled="disabled"' ?> align="middle" type="button" value="<?php echo
JText::_('Insert Original'); ?>" class="button" />
          <input onclick="myAppend('tl', '<?php echo JEConfig::get('Meta.jp_meta_title') ?>');"  align="middle" type="button" value="<?php echo
JText::_('Insert Default'); ?>" class="button" />
          <input onclick="myClear('tl')" align="middle" type="button" value="<?php echo
JText::_('Clear'); ?>" class="button" /></td>
          
        </tr>

        <tr>
          <th><?php echo JText::_('Meta Desription'); ?></th>
          <th><?php echo JText::_('Page Title'); ?></th>
        </tr>
        <tr valign="top">
          
          <td width="33%">
          <p><span style="font-weight:bold;color:#336600;font-size:12px">D</span>: <?php echo
$rows[0]->isindexed ? $rows[0]->meta_description : JText::_('<span style=" color: Red; font-weight: bold;">?</span>') ?></p>
          </td>
          
          <td width="33%">
          <p><?php echo $rows[0]->isindexed ? $rows[0]->title : JText::_('<span style=" color: Red; font-weight: bold;">?</span>') ?></p>
          </td>

        </tr>
        <tr>
          <td><textarea class="text_area" cols="40" name="new_ds" rows="5" id="ds"><?php echo
$rows[0]->newmeta_description ?></textarea></td>
          <td><textarea class="text_area" cols="40" name="new_tl2" rows="5" id="tl2"><?php echo
$rows[0]->new_title ?></textarea></td>
        </tr>
        <tr>
          <td>
          <input onclick="myAppend('ds', '<?php echo $rows[0]->meta_description ?>');"  <?php echo
$rows[0]->meta_description ? null : 'disabled="disabled"' ?> align="middle" type="button" value="<?php echo
JText::_('Insert Original'); ?>" class="button" />
          <input onclick="myAppend('ds', '<?php echo JEConfig::get('Meta.jp_meta_descr') ?>');"  align="middle" type="button" value="<?php echo
JText::_('Insert Default'); ?>" class="button" />
          <input onclick="myClear('ds')" align="middle" type="button" value="<?php echo
JText::_('Clear'); ?>" class="button" /></td>
          <td>
          <input onclick="myAppend('tl2', '<?php echo $rows[0]->title ?>');" <?php echo
$rows[0]->title ? null : 'disabled="disabled"' ?> align="middle" type="button" value="<?php echo
JText::_('Insert Original'); ?>" class="button" />
          <input onclick="myAppend('tl2', '<?php echo JEConfig::get('Meta.jp_page_title') ?>');"  align="middle" type="button" value="<?php echo
JText::_('Insert Default'); ?>" class="button" />
          <input onclick="myClear('tl2')" align="middle" type="button" value="<?php echo
JText::_('Clear'); ?>" class="button" /></td>
          
        </tr>

      </table>

    </td>  
    <td width="200px">
      <?php
    $tab = new mosTabs(1);
    $tab->startPane('mostab');

    $tab->startTab('Properties', 'tab-prop');
?>
        <TABLE class="adminform">
          <TR>
            <TD width="10%" nowrap="nowrap"><?php echo JText::_('Publish:'); ?></TD>
            <TD><input type="checkbox" value="1" name="rpublish" class="inputbox" <?php echo
$rows[0]->published ? 'checked="checked"' : false ?> /></TD>
          </TR>
          <TR>
            <TD width="10%" nowrap="nowrap"><?php echo JText::_('Robot Index:'); ?></TD>
            <TD><input type="checkbox" value="index" name="rindex" class="inputbox" <?php echo
$rows[0]->robots_index == 'noindex' ? false : 'checked="checked"' ?> /></TD>
          </TR>
          <TR>
            <TD width="10%" nowrap="nowrap"><?php echo JText::_('Exclude from Google sitemap:'); ?></TD>
            <TD><input type="checkbox" value="1" name="hiden_sitemap" class="inputbox" <?php echo
$rows[0]->hiden_sitemap == '0' ? false : 'checked="checked"' ?> /></TD>
          </TR>
          <TR>
            <TD width="10%" nowrap="nowrap"><?php echo JText::_('Robot Follow:'); ?></TD>
            <TD><input type="checkbox" value="follow" name="rfollow" class="inputbox" <?php echo
$rows[0]->robots_follow == 'nofollow' ? false : 'checked="checked"' ?> /></TD>
          </TR>
          <TR>
            <TD width="10%" nowrap="nowrap"><?php echo JText::_('SEF url:'); ?></TD>
            <TD><input type="text" size="40" value="<?php echo $rows[0]->sef ?>" name="sef" class="inputbox" <?php echo
$rows[0]->sef ?>/></TD>
          </TR>
          <TR>
            <TD width="10%" nowrap="nowrap"><?php echo JText::_('Individual Google sitemap weight:'); ?></TD>
            <TD>
              <?php
    $gmap[] = mosHTML::makeOption('1', JText::_('0.1'), 'i', 'index');
    $gmap[] = mosHTML::makeOption('2', JText::_('0.2'), 'i', 'index');
    $gmap[] = mosHTML::makeOption('3', JText::_('0.3'), 'i', 'index');
    $gmap[] = mosHTML::makeOption('4', JText::_('0.4'), 'i', 'index');
    $gmap[] = mosHTML::makeOption('5', JText::_('0.5'), 'i', 'index');
    $gmap[] = mosHTML::makeOption('6', JText::_('0.6'), 'i', 'index');
    $gmap[] = mosHTML::makeOption('7', JText::_('0.7'), 'i', 'index');
    $gmap[] = mosHTML::makeOption('8', JText::_('0.8'), 'i', 'index');
    $gmap[] = mosHTML::makeOption('9', JText::_('0.9'), 'i', 'index');
    $gmap[] = mosHTML::makeOption('10', JText::_('1.0'), 'i', 'index');
    echo mosHTML::selectList($gmap, 'google_weight', 'class="inputbox" size="1"',
        'i', 'index', $rows[0]->google_weight * 10);
?>
            </TD>
          </TR>
          <TR>
          	<TD width="10%" nowrap="nowrap"><?php echo JText::_('Individual "changefreq" for Google sitemap:'); ?></TD>
          	<TD><?php
    $chan[] = mosHTML::makeOption('always', JText::_('always'), 'i', 'index');
    $chan[] = mosHTML::makeOption('hourly', JText::_('hourly'), 'i', 'index');
    $chan[] = mosHTML::makeOption('daily', JText::_('daily'), 'i', 'index');
    $chan[] = mosHTML::makeOption('weekly', JText::_('weekly'), 'i', 'index');
    $chan[] = mosHTML::makeOption('monthly', JText::_('monthly'), 'i', 'index');
    $chan[] = mosHTML::makeOption('yearly', JText::_('yearly'), 'i', 'index');
    $chan[] = mosHTML::makeOption('never', JText::_('never'), 'i', 'index');
    if (@$rows[0]->changefreq) {
        $_changefreq = $rows[0]->changefreq;
    } else {
        $_changefreq = JEConfig::get('general.jp_gsm_changefreq', 'com_jp');
    }
    echo mosHTML::selectList($chan, 'changefreq', 'class="inputbox" size="1"', 'i',
        'index', $_changefreq);
?>
          		</TD>
          </TR>

          
        </TABLE>      
      <?php
    $tab->endTab();

    $tab->startTab('Keywords', 'tab-kwd');

    if ($rows[0]->isindexed and (!empty($rows[0]->keywords))) {

?>
        <table class="adminform">
          <tr>
            <th width="10px"><input type="checkbox" name="toggle" value=""  onclick="checkAll(<?php echo
count($words) + 1 ?>);" /></th>
            <th colspan="<?php echo ($colls * 2 - 1) ?>"><?php echo JText::_('Select All'); ?></th>
          </tr>
          <?php
        $c = 1;
        $i = 0;

        foreach ($words as $word => $num) {
            $row->id = $word;
            ++$i;
            if ($c == ($colls + 1))
                $c = 1;
            if ($c == 1)
                echo '<tr>';
?>
            <td width="10"><?php echo KyewordProcessing($row, $i); ?></td>
            <td width="90" nowrap="nowrap">
            <label for="<?php echo "cb" . $i ?>"><?php echo $word; ?> <span style="color: #9900cc;">[<?php echo
$num; ?>]</span></label></td>
          <?php
            if ($c == $colls)
                echo '</tr>';
            $c++;
        }
?>
          <tr>
            <td colspan="<?php echo ($colls * 2) ?>" align="center">
            <input align="middle" type="button" onclick="javascript:submitbutton('addskip')" value="<?php echo
JText::_('Add to Ignore List'); ?>" class="button" />
            <input align="middle" type="button" onclick="javascript:assignKW()" value="<?php echo
JText::_('Assign Keywords'); ?>" class="button" />
            </td>
          </tr>
        </table>

      <?php

    } else {

?>
        <table class="adminform">
          <tr><td style="text-align: center; color: Red; font-weight: bold;">?</td></tr>
        </table>
      <?php

    }

    $tab->endTab();


    //      $tab->startTab('Automation', 'tab-auto');

?>
<!--
        <TABLE class="adminform">
          <TR>
            <TD width="10%" nowrap="nowrap"><label for="aply2children">
                <?php
    //echo JText::_('Apply this to all tree subpages (Children)');

?></label></TD>
            <TD><input id="aply2children" name="aply2children" type="checkbox" class="inputbox" align="middle" value="aply2children" /></TD>
          </TR>
          <TR>
            <TD width="10%" nowrap="nowrap"><label for="aply2title">
                <?php
    echo JText::_('Apply this to all pages with same title');
?></label></TD>
            <TD><input id="aply2title" name="aply2title" type="checkbox" class="inputbox" align="middle" value="aply2title" disabled="disabled" /></TD>
          </TR>
          <TR>
            <TD width="10%" nowrap="nowrap"><label for="overwrite">
                <?php
    echo JText::_('Overwrite all pages');
?></label></TD>
            <TD><input id="overwrite" name="overwrite" type="checkbox" class="inputbox" align="middle" value="overwrite" disabled="disabled" /></TD>
          </TR>
        </TABLE>      
-->         
      
      <?php
    //      $tab->endTab();
    $tab->endPane();
?>
          
    </td> 
  </tr>
</table>
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'tree', 0) ?>" name="tree" />
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'lv', 5) ?>" name="lv" />
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'showhide', 0) ?>" name="showhide" />

<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'errorcode', 200) ?>" name="errorcode" />
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'index') ?>" name="index" />
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'follow') ?>" name="follow" />
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'publish') ?>" name="publish" />
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'status') ?>" name="status" />
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'component') ?>" name="component" />

<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'tree') ?>" name="tree" />
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'lv') ?>" name="lv" />
<input type="hidden" value="<?php echo mosGetParam($_REQUEST, 'treegroups') ?>" name="treegroups" />

<input type="hidden" value="<?php echo $cid[0] ?>" name="cid[]" />
<?php
}

function jpShowReports()
{
    $simtime = isset($_REQUEST['simtime']) ? rawurldecode($_REQUEST['simtime']) : '';

?>
		<table class="adminheading">
		<tr>
			<th style="background: url(/administrator/components/com_jp/images/reports.png) no-repeat left;">
			Reports of SE simulation <?php echo empty($simtime) ? '' : ' (' .
rawurldecode($_GET['simtime']) . ')'; ?>
			</th>
		</tr>
		</table>

<?php
    global $mosConfig_absolute_path;

    $tabs = new mosTabs(1);
    $tabs->startPane('reports');
    $tabs->startTab(JText::_('Counts'), 'tab-counts');
?>
<table class="adminform" cellpadding="0" cellspacing="0" border="1">
  <tr>
    <th><b><?php echo JText::_('Counts') ?></b></th>
    <th><b><?php echo JText::_('Numbers') ?></b></th>    
  </tr>
  <tr class="row1">
  <td style="width: 20%;">Pages in index</td>
  <td>
    <?php
    global $database;
    $sql = "SELECT COUNT(*) FROM #__jp_links WHERE `simtime` = '" . $simtime .
        "' AND status = 'indexed'";
    $database->setQuery($sql);
    $pagesCount = $database->loadResult();
    echo $pagesCount;
?>
  </td>
  </tr>
  <tr class="row0">
    <td><?php echo JText::_('External Links'); ?></td>
    <td>
    <?php
    global $database;
    $sql = "SELECT COUNT(*) FROM #__jp_links WHERE `type` = 'external' AND `simtime` = '" .
        $simtime . "' AND status = 'indexed'";
    $database->setQuery($sql);
    //$database->query();
    echo $database->loadResult();
?>
    </td>
  </tr>

  <tr class="row1">
    <td><?php echo JText::_('Images'); ?></td>
    <td>
    <?php
    global $database;
    $sql = "SELECT COUNT(*) FROM #__jp_images WHERE `simtime` = '" . $simtime . "'";
    $database->setQuery($sql);
    $imagesCount = $database->loadResult();
    echo $imagesCount;
?>
    </td>
  </tr>

</table> 
<?php
    $tabs->endTab();
    $tabs->startTab(JText::_('Pages'), 'tab-pages');
?>
         <table width="100%" border="1"  cellpadding="0" cellspacing="0" class="adminform">
         <tr class="row1">
            <th><b><?php echo JText::_('Page Title') ?></b></th>
            <th width="1%" style="text-align: center;"><b><?php echo JText::_('HTTP&nbsp;Code') ?></b></th>
            <th width="1%" style="text-align: center;"><b><?php echo JText::_('Type') ?></b></th>
            <th width="1%" style="text-align: center;"><b><?php echo JText::_('Content') ?></b></th>
            <th width="1%"><b><?php echo JText::_('Page&nbsp;size') ?></b></th>
            <th width="1%"><b><?php echo JText::_('Images&nbsp;size') ?></b></th>                
            <th width="1%"><b><?php echo JText::_('Speed&nbsp;for&nbsp;page') ?></b></th>
          </tr>
        
          <?php
    $toppage = JEConfig::get("general.jp_toppage");

    global $database;
    $sql = "   SELECT *
                           FROM `#__jp_links`
                          WHERE `simtime` = '" . $simtime .
        "' AND status = 'indexed'
                          LIMIT 0, {$toppage}";
    $database->setQuery($sql);
    $rows = $database->loadAssocList();
    $k = 0;

    foreach ($rows as $row) {
        if (empty($row['title'])) {
            $title = '<a href="' . $row['href'] . '" target="_blank">' . jpSpliceStr($row['href'],
                50) . '</a>';
        } else {
            $title = '<a href="' . $row['href'] . '" target="_blank">' . $row['title'] .
                '</a>';
        }
        ;

        if (!empty($row['moved'])) {
            $title .= '<br />moved&nbsp;to&nbsp;<a href="' . $row['moved'] .
                '" target="_blank" style="color: Navy;">' . jpSpliceStr($row['moved'], 50) .
                '</a>';
        }
        ;

        if (empty($row['errcode'])) {
            $code = 'No answer';
        } else {
            $code = $row['errcode'];
        }

        if (empty($row['contenttype'])) {
            $content = '-';
        } else {
            $content = trim($row['contenttype']);
            if (strpos($content, ';') !== false) {
                $content = substr($content, 0, strpos($content, ';'));
            }
            ;
        }

        $type = $row['type'] == 'internal' ? 'Int' : 'Ext';

        if ($type == 'Ext' or $content != 'text/html' or (in_array($code, array('400',
            '401', '403', '404', '410', '500', '501', 'No answer')))) {
            $pagesize = $imagessize = $allsize = '<td style="text-align: center;">-</td>';
        } else {
            $pagesize = '<td>' . number_format(($row['pagesize'] / 1024), 2) .
                '&nbsp;Kb</td>';
            $imagessize = '<td>' . number_format(($row['imagessize'] / 1024), 2) .
                '&nbsp;Kb</td>';
            $all = $row['pagesize'] + $row['imagessize'];
            $allsize = '<td>' . number_format((($all) * 8) / (28.8 * 1024), 2) .
                'sec&nbsp;@&nbsp;28.8&nbsp;kbs&nbsp;(' . number_format(($all / 1024), 2) .
                '&nbsp;Kb&nbsp;' . ')</td>';
        }

?>
                <tr class="<?php echo "row$k"; ?>">
                    <td><?php echo $title; ?></td>
                    <td style="text-align: center;"><?php echo $code; ?></td>
                    <td style="text-align: center;"><?php echo $type; ?></td>
                    <td style="text-align: center;"><?php echo $content; ?></td>
                    <?php echo $pagesize; ?>
                    <?php echo $imagessize; ?>
                    <?php echo $allsize; ?>
                </tr>                  
              <?php
        $k = 1 - $k;
    }
?>      
        </table>  
<?php
    $tabs->endTab();
    $tabs->startTab(JText::_('Moved'), 'tab-pages');
?>
         <table width="100%" border="1"  cellpadding="0" cellspacing="0" class="adminform">
         <tr class="row1">
            <th><b><?php echo JText::_('Link') ?></b></th>
            <th><b><?php echo JText::_('Moved') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('HTTP&nbsp;Code') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('Type') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('Content') ?></b></th>
          </tr>
        
          <?php
    $toppage = JEConfig::get("general.jp_toppage");
    global $database;
    $sql = "   SELECT *
                           FROM `#__jp_links`
                          WHERE `simtime` = '" . $simtime .
        "' AND status = 'indexed' AND `moved`<>''
                          LIMIT 0, {$toppage}
                          ";
    $database->setQuery($sql);
    $rows = $database->loadAssocList();
    $k = 0;

    foreach ($rows as $row) {
        if (empty($row['title'])) {
            $title = '<a href="' . $row['href'] . '" target="_blank">' . jpSpliceStr($row['href'],
                50) . '</a>';
        } else {
            $title = '<a href="' . $row['href'] . '" target="_blank">' . jpSpliceStr($row['title'],
                50) . '</a>';
        }
        ;


        $moved = '<a href="' . $row['moved'] . '" target="_blank" style="color: Navy;">' .
            jpSpliceStr($row['moved'], 50) . '</a>';

        if (empty($row['errcode'])) {
            $code = 'No answer';
        } else {
            $code = $row['errcode'];
        }

        if (empty($row['contenttype'])) {
            $content = '-';
        } else {
            $content = trim($row['contenttype']);
            if (strpos($content, ';') !== false) {
                $content = substr($content, 0, strpos($content, ';'));
            }
            ;
        }

        $type = $row['type'] == 'internal' ? 'Int' : 'Ext';

?>
                <tr class="<?php echo "row$k"; ?>">
                    <td><?php echo $title; ?></td>
                    <td><?php echo $moved; ?></td>
                    <td style="text-align: center;"><?php echo $code; ?></td>
                    <td style="text-align: center;"><?php echo $type; ?></td>
                    <td style="text-align: center;"><?php echo $content; ?></td>
                </tr>                  
              <?php
        $k = 1 - $k;
    }
?>      
        </table>  
<?php
    $tabs->endTab();
    $tabs->startTab(JText::_('404'), 'tab-pages');
?>
         <table width="100%" border="1"  cellpadding="0" cellspacing="0" class="adminform">
         <tr class="row1">
            <th><b><?php echo JText::_('Link') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('Type') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('Content') ?></b></th>
          </tr>
        
          <?php
    $toppage = JEConfig::get("general.jp_toppage");
    global $database;
    $sql = "   SELECT *
                           FROM `#__jp_links`
                          WHERE `simtime` = '" . $simtime .
        "' AND status = 'indexed' AND `errcode` = '404'
                          LIMIT 0, {$toppage}
                          ";
    $database->setQuery($sql);
    $rows = $database->loadAssocList();
    $k = 0;

    foreach ($rows as $row) {
        $title = '<a href="' . $row['href'] . '" target="_blank">' . jpSpliceStr($row['href'],
            50) . '</a>';

        if (empty($row['contenttype'])) {
            $content = '-';
        } else {
            $content = trim($row['contenttype']);
            if (strpos($content, ';') !== false) {
                $content = substr($content, 0, strpos($content, ';'));
            }
            ;
        }

        $type = $row['type'] == 'internal' ? 'Int' : 'Ext';

?>
                <tr class="<?php echo "row$k"; ?>">
                    <td><?php echo $title; ?></td>
                    <td style="text-align: center;"><?php echo $type; ?></td>
                    <td style="text-align: center;"><?php echo $content; ?></td>
                </tr>                  
              <?php
        $k = 1 - $k;
    }
?>      
        </table>  
<?php
    $tabs->endTab();
    $tabs->startTab(JText::_('No answer'), 'tab-pages');
?>
         <table width="100%" border="1"  cellpadding="0" cellspacing="0" class="adminform">
         <tr class="row1">
            <th><b><?php echo JText::_('Link') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('Type') ?></b></th>
          </tr>
        
          <?php
    $toppage = JEConfig::get("general.jp_toppage");
    global $database;
    $sql = "   SELECT *
                           FROM `#__jp_links`
                          WHERE `simtime` = '" . $simtime .
        "' AND status = 'indexed' AND `errcode`='0'
                         LIMIT 0, {$toppage}
                          ";
    $database->setQuery($sql);
    $rows = $database->loadAssocList();
    $k = 0;

    foreach ($rows as $row) {
        if (empty($row['title'])) {
            $title = '<a href="' . $row['href'] . '" target="_blank">' . jpSpliceStr($row['href'],
                50) . '</a>';
        } else {
            $title = '<a href="' . $row['href'] . '" target="_blank">' . jpSpliceStr($row['title'],
                50) . '</a>';
        }
        ;

        $type = $row['type'] == 'internal' ? 'Int' : 'Ext';

?>
                <tr class="<?php echo "row$k"; ?>">
                    <td><?php echo $title; ?></td>
                    <td style="text-align: center;"><?php echo $type; ?></td>
                </tr>                  
              <?php
        $k = 1 - $k;
    }
?>      
        </table>  
<?php
    $tabs->endTab();
    $tabs->startTab(JText::_('No HTML'), 'tab-pages');
?>
         <table width="100%" border="1"  cellpadding="0" cellspacing="0" class="adminform">
         <tr class="row1">
            <th><b><?php echo JText::_('Page Title') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('HTTP&nbsp;Code') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('Type') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('Content') ?></b></th>
          </tr>
        
          <?php
    $toppage = JEConfig::get("general.jp_toppage");
    global $database;
    $sql = "   SELECT *
                           FROM `#__jp_links`
                          WHERE `simtime` = '" . $simtime .
        "' AND status = 'indexed'
                          ";
    $database->setQuery($sql);
    $rows = $database->loadAssocList();
    $k = 0;

    foreach ($rows as $row) {
        if (empty($row['title'])) {
            $title = '<a href="' . $row['href'] . '" target="_blank">' . jpSpliceStr($row['href'],
                50) . '</a>';
        } else {
            $title = '<a href="' . $row['href'] . '" target="_blank">' . jpSpliceStr($row['title'],
                50) . '</a>';
        }
        ;

        if (!empty($row['moved'])) {
            $title .= '<br />moved&nbsp;to&nbsp;<a href="' . $row['moved'] .
                '" target="_blank" style="color: Navy;">' . jpSpliceStr($row['moved'], 50) .
                '</a>';
        }
        ;

        if (empty($row['errcode'])) {
            $code = 'No answer';
        } else {
            $code = $row['errcode'];
        }

        if (empty($row['contenttype'])) {
            $content = '-';
        } else {
            $content = trim($row['contenttype']);
            if (strpos($content, ';') !== false) {
                $content = substr($content, 0, strpos($content, ';'));
            }
            ;
        }

        if ($content == 'text/html' or $content == '-')
            continue;


        $type = $row['type'] == 'internal' ? 'Int' : 'Ext';

?>
                <tr class="<?php echo "row$k"; ?>">
                    <td><?php echo $title; ?></td>
                    <td style="text-align: center;"><?php echo $code; ?></td>
                    <td style="text-align: center;"><?php echo $type; ?></td>
                    <td style="text-align: center;"><?php echo $content; ?></td>
                </tr>                  
              <?php
        $k = 1 - $k;
    }
?>      
        </table>  
<?php
    $tabs->endTab();
    $tabs->startTab(JText::_('External links'), 'tab-exlinks');
?>
         <table width="100%" border="1" cellpadding="0" cellspacing="0" class="adminform">
         <tr class="row1">
            <th><b><?php echo JText::_('Link') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('HTTP&nbsp;Code') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('Content') ?></b></th>
          </tr>
        
          <?php
    $toppage = JEConfig::get("general.jp_toppage");
    global $database;
    $sql = "   SELECT `href`, `moved`, `errcode`, `contenttype`
                           FROM `#__jp_links`
                          WHERE `simtime` = '" . $simtime .
        "' AND status = 'indexed' AND type = 'external'
                          LIMIT 0, {$toppage}";
    $database->setQuery($sql);
    $rows = $database->loadAssocList();
    $k = 0;

    foreach ($rows as $row) {

        $link = '<a href="' . $row['href'] . '" target="_blank">' . jpSpliceStr($row['href'],
            50) . '</a>';

        if (!empty($row['moved'])) {
            $link .= '<br />moved&nbsp;to&nbsp;<a href="' . $row['moved'] .
                '" target="_blank" style="color: Navy;">' . jpSpliceStr($row['moved'], 50) .
                '</a>';
        }
        ;

        if (empty($row['errcode'])) {
            $code = 'No answer';
        } else {
            $code = $row['errcode'];
        }

        if (empty($row['contenttype'])) {
            $content = '-';
        } else {
            $content = trim($row['contenttype']);
            if (strpos($content, ';') !== false) {
                $content = substr($content, 0, strpos($content, ';'));
            }
            ;
        }

?>
                <tr class="<?php echo "row$k"; ?>">
                    <td><?php echo $link; ?></td>
                    <td style="text-align: center;"><?php echo $code; ?></td>
                    <td style="text-align: center;"><?php echo $content; ?></td>
                </tr>                  
              <?php
        $k = 1 - $k;
    }
?>      
        </table>  
<?php
    $tabs->endTab();
    $tabs->startTab(JText::_('Images'), 'tab-images');
?>
        <table width="100%" cellpadding="0" cellspacing="0" border="1" class="adminform">
          <tr class="row1">
            <th><b><?php echo JText::_('Src of img') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('Type') ?></b></th>
            <th><b><?php echo JText::_('Thumbnail') ?></b></th>
            <th><b><?php echo JText::_('Image size') ?></b></th>
            <th style="text-align: center;"><b><?php echo JText::_('width x height') ?></b></th>                
          </tr>
        
          <?php
    $toppage = JEConfig::get("general.jp_toppage");
    global $database;
    $sql = "   SELECT *
                          FROM `#__jp_images`
                          WHERE `simtime` = '" . $simtime . "'
                       ORDER BY size DESC
                          LIMIT 0, {$toppage}";
    $database->setQuery($sql);
    $rows = $database->loadAssocList();
    $k = 0;
    $previewDir = '/administrator/components/com_jp/images/preview/';
    foreach ($rows as $row) {
        $previewFileSrc = $mosConfig_absolute_path . $previewDir . strtotime($simtime) .
            '_' . $row['id'] . '.jpg';

        if (file_exists($previewFileSrc)) {
            $previewSrc = '<td><img src="' . $previewDir . strtotime($simtime) . '_' . $row['id'] .
                '.jpg' . '" alt="Thumbnail" border="0"></td>';
        } else {
            $previewSrc = '<td style="text-align: center;">-</td>';
        }
        ;

?>
                <tr class="<?php echo "row$k"; ?>" style="vertical-align: middle; height: 50px;">
                    <td><a href="<?php echo $row['src']; ?>" target="_blank"><?php echo
$row['src']; ?></a></td>
                    <td style="text-align: center;"><?php echo empty($row['type']) ?
'-' : $row['type']; ?></td>
                    <?php echo $previewSrc; ?>
                    <td><?php echo number_format(($row['size'] / 1024), 2) .
JText::_(' Kb'); ?></td>
                    <td style="text-align: center;"><?php echo ($row['width'] .
JText::_(' x ') . $row['height']) ?></td>                        
                </tr>                  
              <?php
        $k = 1 - $k;
    }
?>                    
        </table>            
<?php
    $tabs->endTab();
    $tabs->endPane();
}

function jpGoogleTools()
{
    global $database, $mosConfig_live_site;
    $sitemap_filename = JEConfig::get("general.jp_sitemap_filename");
    $sql = " SELECT * FROM `#__jp_google_tools` j ORDER BY id";
    $database->setQuery($sql);
    $rows = $database->loadAssocList();

?>
		<table class="adminheading">
		<tr>
			<th style="background: url(<?php echo $mosConfig_live_site; ?>/administrator/components/com_jp/images/google.png) no-repeat left;">
			SEO Tools
			</th>
		</tr>
		</table>


    <table width="100%" border = "0"  class="adminlist">
        <tr>
            <th width="160" align="left">Name</th>
            <th width="120" align="left">Date</th>
            <th align="left">Information</th>                        
        </tr>
        <tr class="row0">
            <td><?php echo $rows[0]['name']; ?></td>
            <td><?php echo $rows[0]['date']; ?></td>
            <td><?php
    if ($rows[0]['value']) {
        echo $rows[0]['value'];
?>
                   <a href="<?php echo $rows[0]['value'] ?>" target="_blank"><img src="components/com_jp/images/opendetail.png" border="0" align="absmiddle" title="Open this link in new window"></a>
                <?php
    } else {
        JText::_('No site map Generated. Please, click Site Map button on ToolBar to generate one.');
    }
?>
            
            </td>                        
        </tr>
        <!--
        <tr  class="row1">
            <td><?php echo $rows[1]['name']; ?></td>
            <td><?php echo $rows[1]['date']; ?></td>
            <td>
                <table  width="100" cellpadding="0" cellspacing="0" style="padding:0px">
                    <tr>
                        <td bgcolor="#FFFFFF" style="padding:0px; border: 1px solid Gray;"><img src="components/com_jp/images/spacer.gif" width="<?php echo ($rows[1]['value'] *
10); ?>" border="0" height="6" alt=""/></td>
                    </tr>
                </table>
                &nbsp;(<?php echo $rows[1]['value']; ?>/10)
            </td>       
        </tr>        
        <tr class="row0">
            <td><?php echo $rows[2]['name']; ?></td>
            <td><?php echo $rows[2]['date']; ?></td>
            <td><?php echo $rows[2]['value']; ?></td>       
        </tr>
        <tr class="row1">
            <td><?php echo $rows[3]['name']; ?></td>
            <td><?php echo $rows[3]['date']; ?></td>
            <td><?php echo $rows[3]['value']; ?></td>                       
        </tr>
        -->
        <tr class="row0">
            <td><?php echo $rows[4]['name']; ?></td>
            <td><?php echo $rows[4]['date']; ?></td>
            <td>
            <?php
    echo $rows[4]['value'];
    if (!empty($rows[4]['value'])) {
        echo '<a href="' . $rows[4]['value'] .
            '" target="_blank"><img src="components/com_jp/images/opendetail.png" border="0" align="absmiddle" title="Open this link in new window"></a>';
    }
?>
            </td>
        </tr>
    </table>

    <?php

    //$gs = new GoogleSearch();

    //$gs->setKey(JEConfig::get('general.jp_google_key', 'com_jp'));
    //$gs->setQueryString(iconv('windows-1251', 'utf-8', $q));
    //iconv();
    //$gs->setQueryString(iconv("windows-1251","UTF-8", "link:google.com"));
    //$gs->setMaxResults(10);
    //$gs->setSafeSearch(0);
    //$gs->setFilter($google_filter);
    //$gs->setLanguageRestricts($google_lang_r);
    //$gs->setRestrict($google_country_r);
    //$gs->setStartResult(0);


    //$search_result = $gs->doSearch();
    //echo "---".$search_result->getEstimatedTotalResultsCount();


    //UI_Google::search();
    //  $key = 'Y1obZO9QFHKerUDMejNEFhwNSJP+YuWrz8';

}

?>


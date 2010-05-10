<?php
/**
 * @version     $Id$
 * @package JPromoter for Joostina
 * @copyright Авторские права (C) JPromoter team & (C) Joostina team &. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * JPromoter for Joostina - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */
function com_install()
{

    global $database, $mosConfig_absolute_path;

    $database->setQuery("SELECT id FROM #__components WHERE name= 'JPromoter 2'");
    $id = $database->loadResult();

    // remove admin menu images
    $database->setQuery("UPDATE #__components SET admin_menu_link = '' WHERE name = 'JPromoter 2'");
    $database->query();

    // add new admin menu images
    $database->setQuery("UPDATE #__components SET admin_menu_img = 'js/ThemeOffice/blank.png' WHERE parent = '$id'");
    $database->query();
    $database->setQuery("UPDATE #__components SET admin_menu_img = '../administrator/components/com_jp/images/icon16.png' WHERE parent='$id' AND name = 'Control Panel'");
    $database->query();
    $database->setQuery("UPDATE #__components SET admin_menu_img = '../administrator/components/com_jp/images/analize16.gif' WHERE parent='$id' AND name = 'SE Simulation'");
    $database->query();
    $database->setQuery("UPDATE #__components SET admin_menu_img = '../administrator/components/com_jp/images/optimize16.gif' WHERE parent='$id' AND name = 'SEF URLs'");
    $database->query();
    $database->setQuery("UPDATE #__components SET admin_menu_img = '../administrator/components/com_jp/images/google16.png' WHERE parent='$id' AND name = 'SEO Tools'");
    $database->query();
    $database->setQuery("UPDATE #__components SET admin_menu_img = 'js/ThemeOffice/config.png' WHERE parent='$id' AND name = 'Configuration'");
    $database->query();

    @mkdir($mosConfig_absolute_path . '/components/com_sef');
    $s = $mosConfig_absolute_path . '/administrator/components/com_jp/sef.php';
    $d = $mosConfig_absolute_path . '/components/com_sef/sef.php';

    if (@copy($s, $d) == false) {
        echo '<b style="color: Red;">Installation is not full complete !<br />';
        echo 'Manually copy file ' . $s . ' to ' . $d . '</b><br />';
    }
    ;

    // appending event to frontend.php file
    $filearr = array();
    $path = $mosConfig_absolute_path . "/includes/frontend.php";

    $filearr = file($path);
    foreach ($filearr as $line_num => $line) {
        if (strstr($line, "addMetaTag( 'Generator',")) {
            $filearr[$line_num] = "\t" . 'global $_MAMBOTS;' . "\n";
        } elseif (strstr($line, "addMetaTag( 'robots',")) {
            $filearr[$line_num] = "\t" . '$_MAMBOTS->trigger( \'jpMetaEdit\' , NULL);' . "\n";
        }
    }

    if (!file_put_contents($path, implode("", $filearr))) {
?>
        <div align="center" style="font-size: 20px; color: #B22222; font-weight: bolder;"><p>Atention!!! <br> 
        The Installation programm could not hack a Joomla file 
        <b>/includes/frontend.php</b> Please download hack file on 
        <a href="http://joomlaequipment.com/index.php?option=com_docman&task=cat_view&gid=7&Itemid=11">
        www.joomlaequipment.com</a>, or see hack.joomla.1.0.xx.zip in you JPromoter package and replace it manually! 
        If this action will not be made, component 
        will work only for com_content </p></div>
        <?php
    }

    echo '

	<style type="text/css">
     #jpmessage
     {
        font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-size: 12px;
        background-color: #F0F0F0;
        padding: 10px 20px 10px 20px;
        text-align: justify;        
     }
     #jpheader
     {
        font-size: 18px;
        text-align: center;
        font-weight: bold;
        margin: 2px 0px 10px 0px;
     }
     #jpcode
     {
        font-family: "Courier New", Courier, monospace;
        font-weight: bold;
        text-align: left;
     }
     .jpattention
     {
        padding: 6px 16px 6px 16px;
        border: 2px solid Red;
     }
     div.jp
     {
        margin: 10px 0px 10px 0px;
     }
</style>
<div id="jpmessage">

<div id="jpheader">
JPromoter successfully installed !
</div>

<div class="jpattention">

<div class="jp">
<strong>Attention !</strong> Due to technical peculiarities of Joomla inner structure, JPromoter component is <strong>incompatible</strong> with third party SEF components. That is why for correct work of JPromoter it is indispensible to uninstall preceding third party SEF component.
</div>

<div class="jp">
The same, not damage files of JPromoter, it is not allowed to install third party SEF components over JPromoter. Before installation of such components delete JPromoter first. 
</div>

</div>

<br>

<div class="jpattention">

<div class="jp">
<strong>Attention !</strong> Installation package of JPromoter is NOT included all integration configurations. Only for Joomla content component. Therefore, before using JPromoter SEF functions, <strong>strongly recommended</strong> to update <strong>JPromoter 3dp Integration Package</strong> for getting latest version of package.
</div>

<div class="jp">
This is possible using <strong>JPromoter Control Panel -> SEO Tools -> Integrations</strong>.
</div>

</div>

<div class="jpattention">

<div class="jp">
<strong>Attention !</strong> If your want to working with mutibyte codepage properly, you <strong>must</strong> have working mbstring extension in you site.
</div>

</div>

<div class="jp">
For correct functioning of SEF URL JPromoter mechanism you need to setup correctly file <strong>.htaccess</strong> which is placed in the root site catalogue.
For this according to Joomla instructions change the name of file htaccess.txt to .htaccess. Then open this file in text editor and following Joomla instructions comment all lines in the section <strong>"Begin - Joomla! core SEF Section"</strong>. Then you need to uncomment all lines in the section <strong>"Begin - 3rd Party SEF Section"</strong> and save all changes.
</div>

<div class="jp">
If due to some reasons, there is no file htaccess.txt in the root site catalogue, or it is damaged, you need to create new text file and fill it with the lines below:
</div>

<div id="jpcode">
<pre>
DirectoryIndex index.php
RewriteEngine On
#RewriteBase /

RewriteCond %{REQUEST_URI} ^(/component/option,com) [NC,OR]
RewriteCond %{REQUEST_URI} (/|\.htm|\.php|\.html|/[^.]*)$  [NC]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.*) index.php
</pre>
</div>

<div class="jp">
Then save the file to the root directory of the site and name it .htaccess.
</div>

</div>
';
    if (defined('_ISO')) {
        $iso = explode('=', _ISO);
        if (!empty($iso[1])) {
            $iso = $iso[1];
            $translitINIPath = $GLOBALS['mosConfig_absolute_path'] .
                '/administrator/components/com_jp/sef_translits/';

            $translitINIFiles = array();

            foreach (glob($translitINIPath . '*.ini') as $INIFile) {
                $translitINIFiles[] = substr(basename($INIFile), 0, -4);
            }

            if (in_array($iso, $translitINIFiles)) {
                $database->setQuery('UPDATE `#__je_config` SET `selected` = "' . $iso .
                    '" WHERE `name` = "jp_codepage"');
                $database->query();
            }
        }
    }

}
?>
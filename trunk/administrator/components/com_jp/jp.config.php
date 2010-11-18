<?php
/**
 * @version     $Id$
 * @package JPromoter for Joostina
 * @copyright Авторские права (C) JPromoter team & (C) Joostina team &. Все права защищены.
 * @license Лицензия http://www.gnu.org/licenses/gpl-2.0.htm GNU/GPL, или help/license.php
 * JPromoter for Joostina - свободное программное обеспечение распространяемое по условиям лицензии GNU/GPL
 * Для получения информации о используемых расширениях и замечаний об авторском праве, смотрите файл help/copyright.php.
 */
defined( '_VALID_MOS' ) or die( 'Restricted access' ) ;
class JEConfig
{
    function get( $val, $option = '' )
    {
        global $database ;
        if ( !$option ) $option = mosGetParam( $_REQUEST, 'option', false ) ;
        if ( !$option || !$val ) return false ;
        $val = explode( '.', $val ) ;
        $section = $val[0] ;
        $value = $val[1] ;
        /* boston, загрузка конфига в один запрос на группу */
        static $config ;
        if ( !is_array( $config ) or !isset( $config[$section] ) )
        {
            $sql = "SELECT `selected`,`name` FROM #__je_config WHERE `section` = '{$section}'" ;
            $database->setQuery( $sql ) ;
            $config[$section] = $database->loadAssocList( 'name' ) ;
        }
        $rows = $config[$section][$value]['selected'] ;
        return $rows ;
    }
    function renderForm( $form = false )
    {
        global $database ;
?>


		<table class="adminheading">
		<tr>
			<th class="config">
			<?php
        echo JP_CONFIGURATION1 ;
?>
			</th>
		</tr>
		</table>
<?php
        if ( $form ) echo '<form action="index2.php" method="post" id="adminForm" name="adminForm" enctype="">' ;
        //echo "<br>";
        $option = mosGetParam( $_REQUEST, 'option', false ) ;
        if ( !$option ) josRedirect( 'index2.php', JP_CGN ) ;
        $sql = "SELECT name FROM #__je_config WHERE component = '{$option}' ORDER BY `section` ASC, fieldset ASC, type  ASC " ;
        $database->setQuery( $sql ) ;
        $rows = $database->loadAssocList() ;
        $sql = "SELECT component FROM #__jp_pages GROUP BY component" ;
        $database->setQuery( $sql ) ;
        $componentList = $database->loadObjectList() ;
        /*
        foreach ($componentList as $component) {
        $find = false;
        foreach ($rows as $row) {
        if ($row['name'] == $component->component) {
        $find = true;
        }
        }

        if (!$find) {
        $sql = "INSERT INTO `jos_je_config` VALUES ('', 'com_jp', 'Component', '" . $component->
        component . "', '" . $component->component .
        "', 'yesno', 'Exclusion Component', '', '0', 'N', 'Exclusion Component')";
        $database->setQuery($sql);
        $database->query();

        }
        }*/
        $sql = "SELECT * FROM #__je_config WHERE component = '{$option}' ORDER BY `section` ASC, fieldset ASC, type  ASC " ;
        $database->setQuery( $sql ) ;
        $rows = $database->loadObjectList() ;
        $com = '' ;
        $tabs = new mosTabs( 1 ) ;
        $tabs->startPane( 'config' ) ;
        /*
        foreach($rows AS $k => $row)
        {
        if($com != $row->section)
        {
        if($com != '') 
        {
        echo '</table>';
        $tabs->endTab();
        
        }
        $tabs->startTab(ucfirst($row->section), 'tab-'.$row->section);
        echo '<table class="adminform">';
        }
        $com = $row->section;
        echo self::_renderRow($row);
        }
        */
        $set = '' ;
        foreach ( $rows as $k => $row )
        {
            if ( $set != $row->fieldset )
            {
                if ( !empty( $set ) )
                {
                    echo '</table></fieldset>' ;
                }
            }
            if ( $com != $row->section )
            {
                if ( $com != '' )
                {
                    $tabs->endTab() ;
                }
                $tabs->startTab( ucfirst( $row->section ), 'tab-' . $row->section ) ;
            }
            if ( $set != $row->fieldset )
            {
                //if($set != '')
                //{
                //   $tabs .= '</fieldset>';
                //}
                echo '<fieldset style="display:block; width:5%; float:left; clear:both"><LEGEND>' . ucfirst( $row->fieldset ) .
                    '</LEGEND><table width="20%" class="admintable">' ;
            }
            $com = $row->section ;
            $set = $row->fieldset ;
            //$tabs .= "<P>Get it</P>";
            echo JEConfig::_renderRow( $row ) ;
        }
        echo '</table></fieldset>' ;
        $tabs->endTab() ;
        $tabs->endPane() ;
        if ( $form )
        {
            echo '<input type="hidden" name="option" value="com_ftpsearch" />' ;
            echo '<input type="hidden" name="task" value="" />' ;
            echo '<input type="hidden" name="hidemainmenu" value="0" />' ;
            echo '</form>' ;
        }
    }
    function _renderRow( $row )
    {

        if ( $row->description )
        {
            $label = '<SPAN class="editlinktip"><SPAN onmouseover="return ' . 'overlib(\'' . constant( $row->description ) . '\', BELOW, RIGHT, WIDTH, \'280\');" ' .
                'onmouseout="return nd();" style="text-decoration: ' . 'none; color: #333;">' . constant( $row->lable ) . '</SPAN></SPAN>' ;
        }else{
            $label = constant( $row->lable ) ;
        }
        $out = '<tr valign="top"><td width="10%" nowrap="nowrap">' . $label . ':</td><td width="10" nowrap="nowrap">' . JEConfig::_renderElement( $row ) . '</td></tr>' ;
        return $out ;
    }
    function _renderElement( $row )
    {
        switch ( $row->type )
        {
            case "yesno":
                $out = mosHTML::yesnoRadioList( $row->name . $row->id, 'class="inputbox"', $row->selected ) ;
                break ;
            case "selectlist":
                $items = explode( ",", $row->values ) ;
                foreach ( $items as $item )
                {
                    $list[] = mosHTML::makeOption( trim( $item ), trim( $item ) ) ;
                }
                $out = mosHTML::selectList( $list, $row->name . $row->id, 'class="inputbox" size="' . count( $items ) . '" ', 'value', 'text', $row->selected ) ;
                break ;
            case "dropdown":
                $items = explode( ",", $row->values ) ;
                foreach ( $items as $item )
                {
                    $list[] = mosHTML::makeOption( trim( $item ), trim( $item ) ) ;
                }
                $out = mosHTML::selectList( $list, $row->name . $row->id, 'class="inputbox" size="1" ', 'value', 'text', $row->selected ) ;
                break ;
            case "textarea":
                $out = sprintf( '<textarea class="text_area" name="%s" rows="5" cols="40">%s</textarea>', $row->name . $row->id, $row->selected ) ;
                break ;
            default:
                $out = sprintf( '<input type="text" class="inputbox" name="%s" value="%s" size="40">', $row->name . $row->id, $row->selected ) ;
                break ;
        }
        return $out ;
    }
    function Toolbar()
    {
        mosMenuBar::startTable() ;
        //mosMenuBar::title( JText::_( 'JPromoter [Edit Page]'), '../components/com_jp/images/logo.png' );
        //mosMenuBar::spacer();
        //mosMenuBar::custom('panel', '../components/com_jp/images/cpanel.png', NULL, JText::_('cPanel'), false, false );
        //mosMenuBar::spacer();
        //mosMenuBar::divider();
        mosMenuBar::spacer() ;
        mosMenuBar::save( 'savecnf' ) ;
        mosMenuBar::spacer() ;
        mosMenuBar::apply( 'applycnf' ) ;
        mosMenuBar::spacer() ;
        mosMenuBar::cancel() ;
        mosMenuBar::spacer() ;
        //mosMenuBar::help('index.html', 'com_jp');
        mosMenuBar::endTable() ;
    }
    function save( $option = '' )
    {
        global $database ;
        if ( !$option ) $option = mosGetParam( $_REQUEST, 'option', false ) ;
        if ( !$option )
        {
            echo JP_ERROR1 ;
            return ;
        }
        $sql = "SELECT * FROM #__je_config WHERE `component` = '{$option}' AND hidden = 'N'" ;
        //echo $sql;
        $database->setQuery( $sql ) ;
        $rows = $database->loadObjectList() ;
        foreach ( $rows as $row )
        {
            $val = addslashes( mosGetParam( $_REQUEST, $row->name . $row->id ) ) ;
            $sql = "UPDATE #__je_config SET selected = '$val' WHERE `id` = '{$row->id}'" ;
            $database->setQuery( $sql ) ;
            $database->query() ;
        }
    }
}
?>
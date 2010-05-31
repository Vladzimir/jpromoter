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

class jpToolBar
{
    function google()
    {
        mosMenuBar::startTable();
        mosMenuBar::spacer();
        mosMenuBar::custom('panel', '../../../components/com_jp/images/cpanel.png',
            '../../../components/com_jp/images/cpanel.png', JP_TOP_CPANEL, false, false);
        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();

        mosMenuBar::custom('gsm', '../../../components/com_jp/images/sitemap.gif',
            '../../../components/com_jp/images/sitemap.gif', JP_TOP_SITE_MAP, false, false);
        mosMenuBar::spacer();
        mosMenuBar::custom('updateXML', '../../../components/com_jp/images/sitemap.gif',
            '../../../components/com_jp/images/refresh.gif', JP_TOP_INTEGRATION, false, false);
        mosMenuBar::spacer();
        //mosMenuBar::custom('ginfo',  '../../../components/com_jp/images/refresh.gif', '../../../components/com_jp/images/refresh.gif', JText::_('Refresh'), false, false );
        //mosMenuBar::spacer();

        mosMenuBar::help('index.html', 'com_jp');
        mosMenuBar::endTable();
    }

    function index()
    {
        mosMenuBar::startTable();
        //mosMenuBar::title( JText::_( 'SE Simulation'), 'searchtext.png' );
        mosMenuBar::spacer();
        mosMenuBar::custom('panel', '../../../components/com_jp/images/cpanel.png',
            '../../../components/com_jp/images/cpanel.png', JP_TOP_CPANEL, false, false);
        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();
        //            mosMenuBar::custom('doindex', 'apply_f2.png','apply_f2.png', JText::_('Simulate'), false, false );
        mosMenuBar::custom('newsimulate', 'new.png', 'new_f2.png', JP_TOP_NEW, false, false);
        mosMenuBar::spacer();
        mosMenuBar::deleteList('Delete', 'removesimulation', JP_TOP_DELETE);
        //            mosMenuBar::custom('removesimulation', 'delete.png','delete_f2.png', JText::_('Delete'), true, true );
        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();
        mosMenuBar::help('index.html', 'com_jp');
        mosMenuBar::endTable();
    }

    function editpage()
    {
        mosMenuBar::startTable();
        //mosMenuBar::title( JText::_( 'JPromoter [Edit Page]'), '../components/com_jp/images/logo.png' );
        mosMenuBar::spacer();
        mosMenuBar::custom('panel', '../../../components/com_jp/images/cpanel.png',
            '../../../components/com_jp/images/cpanel.png', JP_TOP_CPANEL, false, false);
        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();

        mosMenuBar::save('savepage');
        mosMenuBar::spacer();

        mosMenuBar::apply('applypage');
        mosMenuBar::spacer();

        mosMenuBar::cancel('cancelpage');
        mosMenuBar::spacer();


        mosMenuBar::help('index.html', 'com_jp');
        mosMenuBar::endTable();
    }

    function optimize()
    {
        mosMenuBar::startTable();

        mosMenuBar::custom('hidepagesitemap',
            '../../../components/com_jp/images/hide.gif',
            '../../../components/com_jp/images/hide.gif', JP_TOP_HU, true, false);
        mosMenuBar::spacer();

        //mosMenuBar::title( JText::_( 'JPromoter'), '../components/com_jp/images/logo.png' );
        mosMenuBar::custom('panel', '../../../components/com_jp/images/cpanel.png',
            '../../../components/com_jp/images/cpanel.png', JP_TOP_CPANEL, false, false);
        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();

        //mosMenuBar::custom('pagerank',  '../../../components/com_jp/images/pagerank.gif', '../../../components/com_jp/images/pagerank.gif', JText::_('ReRank'), true, false );
        //mosMenuBar::spacer();
        mosMenuBar::custom('hidepage', '../../../components/com_jp/images/hide.gif',
            '../../../components/com_jp/images/hide.gif', JP_TOP_HU2, true, false);
        mosMenuBar::spacer();

        mosMenuBar::publish();
        mosMenuBar::spacer();

        mosMenuBar::unpublish();
        mosMenuBar::spacer();

        mosMenuBar::custom('index', '../../../components/com_jp/images/index.gif',
            '../../../components/com_jp/images/index.gif', JP_TOP_INDEX, true, false);
        mosMenuBar::spacer();
        mosMenuBar::custom('noindex', '../../../components/com_jp/images/noindex.gif',
            '../../../components/com_jp/images/noindex.gif', JP_TOP_NO_INDEX, true, false);
        mosMenuBar::spacer();

        mosMenuBar::custom('follow', '../../../components/com_jp/images/follow.gif',
            '../../../components/com_jp/images/follow.gif', JP_TOP_FOLLOW, true, false);
        mosMenuBar::spacer();
        mosMenuBar::custom('nofollow', '../../../components/com_jp/images/nofollow.gif',
            '../../../components/com_jp/images/nofollow.gif', JP_TOP_NO_FOLLOW, true, false);
        mosMenuBar::spacer();

        //        mosMenuBar::editList('editpage');
        //        mosMenuBar::spacer();

        mosMenuBar::custom('getrank', '../../../components/com_jp/images/pagerank.gif',
            '../../../components/com_jp/images/pagerank.gif', JP_TOP_GET_RANKS, true, false);
        mosMenuBar::spacer();

        mosMenuBar::custom('clear', 'delete_f2.png', 'delete_f2.png', JP_TOP_CLEAR_SEF, true, false);
        mosMenuBar::spacer();

        mosMenuBar::deleteList('delete');
        mosMenuBar::spacer();

        mosMenuBar::help('index.html', 'com_jp');
        mosMenuBar::endTable();
    }

    function newsimulate()
    {
        mosMenuBar::startTable();
        mosMenuBar::spacer();
        mosMenuBar::custom('panel', '../../../components/com_jp/images/cpanel.png',
            '../../../components/com_jp/images/cpanel.png', JText::_('cPanel'), false, false);
        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();
        mosMenuBar::custom('startsimulate', 'apply.png', 'apply_f2.png', JText::_('Start'), false, false);
        mosMenuBar::spacer();
        //        mosMenuBar::custom('nextsimulate', 'next.png', 'next_f2.png', JText::_('Next'), true, true );
        //        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();
        mosMenuBar::help('index.html', 'com_jp');
        mosMenuBar::endTable();
    }

    function onlycontrol()
    {
        mosMenuBar::startTable();
        mosMenuBar::spacer();
        mosMenuBar::custom('panel', '../../../components/com_jp/images/cpanel.png',
            '../../../components/com_jp/images/cpanel.png', JText::_('cPanel'), false, false);
        mosMenuBar::spacer();
        mosMenuBar::divider();
        mosMenuBar::spacer();
        mosMenuBar::help('index.html', 'com_jp');
        mosMenuBar::endTable();
    }

    function _default()
    {
        mosMenuBar::startTable();
        mosMenuBar::spacer();
        mosMenuBar::help('index.html', 'com_jp');
        mosMenuBar::endTable();
    }
}

?>
<?php
/**
 * template functions for dokubook template
 * 
 * @license:    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author:     Michael Klier <chi@chimeric.de>
 */

if(!defined('DW_LF')) define('DW_LF',"\n");

// load language files
require_once(DOKU_TPLINC.'lang/en/lang.php');
if(@file_exists(DOKU_TPLINC.'lang/'.$conf['lang'].'/lang.php')) {
    require_once(DOKU_TPLINC.'lang/'.$conf['lang'].'/lang.php');
}

/**
 * checks if a file called logo.png or logo.jpg exists
 * and uses it as logo, uses the dokuwiki logo by default
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function tpl_logo() {
    global $conf;
    
    $out = '';

    switch(true) {
        case(@file_exists(DOKU_TPLINC.'images/logo.jpg')):
            $logo = DOKU_TPL.'images/logo.jpg';
            break;
        case(@file_exists(DOKU_TPLINC.'images/logo.jpeg')):
            $logo = DOKU_TPL.'images/logo.jpeg';
            break;
        case(@file_exists(DOKU_TPLINC.'images/logo.png')):
            $logo = DOKU_TPL.'images/logo.png';
            break;
        default:
            $logo = DOKU_TPL.'images/dokuwiki-128.png';
            break;
    }

    $out .= '<a href="' . DOKU_BASE . '" name="dokuwiki__top" id="dokuwiki__top" accesskey="h" title="[ALT+H]">';
    $out .= '  <img class="logo" src="' . $logo . '" alt="' . $conf['title'] . '" /></a>' . DW_LF;

    print ($out);
}

/**
 * generates the sidebar contents
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function tpl_sidebar() {
    global $lang;
    global $ID;
    global $INFO;

    $svID  = cleanID($ID);
    $navpn = tpl_getConf('pagename');
    $path  = explode(':',$svID);
    $found = false;
    $sb    = '';

    if(tpl_getConf('closedwiki') && empty($INFO['userinfo'])) {
        print '<span class="sb_label">' . $lang['toolbox'] . '</span>' . DW_LF;
        print '<div id="toolbox" class="sidebar_box">' . DW_LF;
        tpl_actionlink('login');
        print '</div>' . DW_LF;
        return;
    }

    // main navigation
    print '<span class="sb_label">' . $lang['navigation'] . '</span>' . DW_LF;
    print '<div id="navigation" class="sidebar_box">' . DW_LF;

    while(!$found && count($path) > 0) {
        $sb = implode(':', $path) . ':' . $navpn;
        $found =  @file_exists(wikiFN($sb));
        array_pop($path);
    }

    if(!$found && @file_exists(wikiFN($navpn))) $sb = $navpn;

    if(@file_exists(wikiFN($sb)) && auth_quickaclcheck($sb) >= AUTH_READ) {
        print p_sidebar_xhtml($sb);
    } else {
        print html_index(cleanID($svID));
    }

    print '</div>' . DW_LF;

    // generate the searchbox
    print '<span class="sb_label">' . strtolower($lang['btn_search']) . '</span>' . DW_LF;
    print '<div id="search">' . DW_LF;
    tpl_searchform();
    print '</div>' . DW_LF;

    // generate the toolbox
    print '<span class="sb_label">' . $lang['toolbox'] . '</span>' . DW_LF;
    print '<div id="toolbox" class="sidebar_box">' . DW_LF;
    tpl_actionlink('admin');
    tpl_actionlink('index');
    tpl_actionlink('recent');
    tpl_actionlink('backlink');
    tpl_actionlink('profile');
    tpl_actionlink('login');
    print '</div>' . DW_LF;
}

/**
 * removes the TOC of the sidebar-pages and shows a edit-button if user has enough rights
 * 
 * @author Michael Klier <chi@chimeric.de>
 */
function p_sidebar_xhtml($sb) {
    $data = p_wiki_xhtml($sb,'',false);
    if(auth_quickaclcheck($sb) >= AUTH_EDIT) {
        $data .= '<div class="secedit">' . html_btn('secedit',$sb,'',array('do'=>'edit','rev'=>'','post')) . '</div>';
    }
    // strip TOC
    $data = preg_replace('/<div class="toc">.*?(<\/div>\n<\/div>)/s', '', $data);
    // replace headline ids for XHTML compliance
    $data = preg_replace('/(<h.*?><a.*?id=")(.*?)(">.*?<\/a><\/h.*?>)/','\1sb_'.$pos.'_\2\3', $data);
    return ($data);
}

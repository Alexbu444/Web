<?php 
// *************************************************************************
//  This file is part of SourceBans++.
//
//  Copyright (C) 2014-2016 Sarabveer Singh <me@sarabveer.me>
//
//  SourceBans++ is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, per version 3 of the License.
//
//  SourceBans++ is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with SourceBans++. If not, see <http://www.gnu.org/licenses/>.
//
//  This file is based off work covered by the following copyright(s):  
//
//   SourceBans 1.4.11
//   Copyright (C) 2007-2015 SourceBans Team - Part of GameConnect
//   Licensed under GNU GPL version 3, or later.
//   Page: <http://www.sourcebans.net/> - <https://github.com/GameConnect/sourcebansv1>
//
// *************************************************************************

global $userbank, $theme, $xajax,$user,$start;
$time = microtime();
$time = explode(" ", $time);
$time = $time[1] + $time[0];
$start = $time;
ob_start(); 

if(!defined("IN_SB"))
{
	echo "You should not be here. Only follow links!";
	die();
}

if(isset($_GET['c']) && $_GET['c']  == "settings")
{
	/*$theme->assign('tiny_mce_js', '<script type="text/javascript" src="./includes/tinymce/tiny_mce.js"></script>
					<script language="javascript" type="text/javascript">
					tinyMCE.init({
						mode : "textareas",
						theme : "advanced",
						plugins : "inlinepopups,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,zoom,media,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
						theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontselect,fontsizeselect",
						theme_advanced_buttons2 : "cut,copy,paste,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,anchor,image,help,code,|,forecolor,backcolor",
						theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,charmap,emotions,iespell,media",
						theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_path_location : "bottom",
						extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
					});
					</script>');*/
	$theme->assign('tiny_mce_js', '');
} else
	$theme->assign('tiny_mce_js', '');
	
if($GLOBALS['config']['template.global'] == "1"){
	$def_ch = "";
	$def_body = 'class="toggled sw-toggled"';
}else{
	$def_ch = '
				<li id="toggle-width" class="p-t-5">
					<div class="toggle-switch" data-trigger="hover" data-toggle="popover" data-placement="bottom" data-content="Включить полноэкранную работу шаблона? Ваш браузер запомнит данный выбор." title="" data-original-title="Управление">
						<input id="tw-switch" type="checkbox" hidden="hidden" />
						<label for="tw-switch" class="ts-helper"></label>
					</div>
				</li>';
	$def_body = "";
}

$th_style = $GLOBALS['config']['theme.style'];
$th_style_color = $GLOBALS['config']['theme.style.color'];

if($th_style != "" || $th_style == ""){
	if($th_style_color != ""){
		$th_style = 'style="background-color:'.$th_style_color.';"';
	}else{
		$th_style = 'data-current-skin="'.$th_style.'"';
	}
}

$bg_value = "style=\"";
if($GLOBALS['config']['theme.bg'] != ""){
		if((stristr($GLOBALS['config']['theme.bg'], '#') || stristr($GLOBALS['config']['theme.bg'], 'RGBA(') || stristr($GLOBALS['config']['theme.bg'], 'rgb(') || stristr($GLOBALS['config']['theme.bg'], 'rgba(')) == FALSE){
			$bg_value .= "background-image: url('".$GLOBALS['config']['theme.bg']."');";
		}else{
			$bg_value .= "background-color: ".$GLOBALS['config']['theme.bg'].";";
		}
	}
	
if($GLOBALS['config']['theme.bg.rep'] != ""){
	$bg_value .= "background-repeat: ".$GLOBALS['config']['theme.bg.rep'].";";
	}

if($GLOBALS['config']['theme.bg.att'] != ""){
	$bg_value .= "background-attachment: ".$GLOBALS['config']['theme.bg.att'].";";
	}

if($GLOBALS['config']['theme.bg.pos'] != ""){
	$bg_value .= "background-position: ".$GLOBALS['config']['theme.bg.pos'].";";
	}

if($GLOBALS['config']['theme.bg.size'] != ""){
	$bg_value .= "-moz-background-size: ".$GLOBALS['config']['theme.bg.size'].";";
    $bg_value .= "-webkit-background-size: ".$GLOBALS['config']['theme.bg.size'].";";
    $bg_value .= "-o-background-size: ".$GLOBALS['config']['theme.bg.size'].";";
    $bg_value .= "background-size: ".$GLOBALS['config']['theme.bg.size'].";";
	}

$bg_value .= "\"";

/////////
/////////
/////////

function toCommunityID($id) {
    if (preg_match('/^STEAM_/', $id)) {
        $parts = explode(':', $id);
        return bcadd(bcadd(bcmul($parts[2], '2'), '76561197960265728'), $parts[1]);
    } elseif (is_numeric($id) && strlen($id) < 16) {
        return bcadd($id, '76561197960265728');
    } else {
        return $id; // We have no idea what this is, so just return it.
    }
}

$res = $GLOBALS['db']->Execute("SELECT count(user) FROM `".DB_PREFIX."_admins` WHERE `support` = '1'");
$Suppoert_Count = (int)$res->fields[0];

$res = $GLOBALS['db']->Execute("SELECT authid, vk, comment, skype, user FROM `".DB_PREFIX."_admins` WHERE `support` = '1'");
$supports = array();
while (!$res->EOF)
{
    $suppurt_inf = array();
	
	$suppurt_inf['user'] = stripslashes($res->fields['user']);
	$suppurt_inf['comment'] = $res->fields['comment'];
	$suppurt_inf['vk'] = $res->fields['vk'];
	$suppurt_inf['skype'] = $res->fields['skype'];
	$suppurt_inf['authid'] = toCommunityID($res->fields['authid']);
	$suppurt_inf['avatarka'] = GetUserAvatar($res->fields['authid']);

	
	array_push($supports,$suppurt_inf);
	$res->MoveNext();
}


$theme->assign('supports_list', $supports);
$theme->assign('supports_count', $Suppoert_Count);
////////
////////
////////

$theme->assign('avatar', GetUserAvatar($userbank->GetProperty('authid')));
$theme->assign('theme_bg',  $bg_value);
$theme->assign('theme_color',  $th_style);
$theme->assign('def_ch_chenger',  $def_ch);
$theme->assign('def_body_chenger',  $def_body);
$theme->assign('xajax_functions',  $xajax->printJavascript("scripts", "xajax.js"));
$theme->assign('header_title', $GLOBALS['config']['template.title']);
$theme->assign('vay4er_act', $GLOBALS['config']['page.vay4er']);
$theme->assign('header_logo', $GLOBALS['config']['template.logo']);
$theme->assign('username', $userbank->GetProperty("user"));
$theme->assign('logged_in', $userbank->is_logged_in());
$theme->assign('theme_name', isset($GLOBALS['config']['config.theme'])?$GLOBALS['config']['config.theme']:'default');
$theme->display('page_header.tpl');
?>
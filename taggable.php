<?php
/*
Plugin Name: Taggable
Plugin URI: http://taggable.com
Description: Taggable allows you to tag your Facebook friends on pages & posts.
Version: 1.1.1
Author: The Start Project
Author URI: http://thestartproject.com

Copyright 2010 Taggable, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$gsTaggableDomain = 'taggable.com';

function taggableFilter_appendToContent($sContent)
{
	global $gsTaggableDomain;
	global $wp_version;
	$iVersion = $wp_version;
	$sUrl = get_permalink(); // we're "in the loop", so it uses current post
	$sDisplayStyle = get_option('Taggable_sDisplayStyle');

	if($sDisplayStyle == 'names')
	{
		$sDisplayStyle = 'text';
		$sShowNamesCode = 'var Taggable_bShowNames = true;';
	}

	$sContent .= <<<EOM
<script type="text/javascript">
var Taggable_iWpVersion = '$iVersion';
var Taggable_sUrlOfPage = '$sUrl';
var Taggable_sDisplayStyle = '$sDisplayStyle';
var Taggable_bTaggableIcon = true;
$sShowNamesCode
</script>
<script src="http://$gsTaggableDomain/js/button.js" type="text/javascript"></script>
EOM;

	return $sContent;
}

function taggableAction_createTaggableBox()
{
	add_meta_box('taggable_entry', 'Taggable - People Tagging', 'taggable_boxContents', 'post', 'normal', 'high' );
	add_meta_box('taggable_entry', 'Taggable - People Tagging', 'taggable_boxContents', 'page', 'normal', 'high' );
}

function taggable_boxContents()
{
	global $gsTaggableDomain;
	$sUrl = get_permalink(); // will be blank if it's new
	if (!strlen($sUrl)) // new post/page, can't do anything without the permalink
	{
		print "This is a new post.  After publishing, you will be able to tag your Facebook friends.";
	}
	else
	{
/* old version
<iframe name= "Taggable_iframeList" id="Taggable_iframeList" src="http://$gsTaggableDomain/?action=iFramedEditLink&mode=wordpress&url=$sEncodedUrl"
	style="margin:0; padding:0; width:100%; height:60px; margin:0; padding:0;">
</iframe>
*/  
		
		$sEncodedUrl = urlencode($sUrl);
		print <<<EOM
<div id="taggableFrameHolder_wordpress" style="margin-top: 10px;"></div>
<script type="text/javascript">
var Taggable_sUrlOfPage = '$sEncodedUrl';
frame = document.createElement('iframe');
frame.setAttribute("id", "taggableIframe_wordpress");
frame.setAttribute("frameBorder","0");
frame.setAttribute("marginwidth","0");
frame.setAttribute("marginheight","0");
//frame.setAttribute("width","450");
frame.setAttribute("height","350");
frame.setAttribute("scrolling","no");
frame.setAttribute("src", "http://$gsTaggableDomain/buttonDetails.php?service=wpAdmin&version=$iVersion&url="+Taggable_sUrlOfPage);
frame.setAttribute("style",' width: 98%; height: 350px; margin-right: 20px;');
document.getElementById('taggableFrameHolder_wordpress').appendChild(frame);
</script>
EOM;
	}
}

function taggable_adminMenu()
{
	add_submenu_page('options-general.php', 'Taggable Options', 'Taggable',
		'edit_plugins', 'taggable.php', 'taggable_submenu');
}

function taggable_submenu()
{
	if ($_REQUEST['save'])
	{
		update_option('Taggable_sDisplayStyle', $_REQUEST['Taggable_sDisplayStyle']);

		echo '<div id="message" class="updated fade"><p>Saved changes.</p></div>';
	}

	// load options from db to display
	$sDisplayStyle = get_option('Taggable_sDisplayStyle');
	if (!strlen($sDisplayStyle))
		$sDisplayStyle = 'names';

	$sStyleButtonCheck = $sDisplayStyle == 'button' ? 'checked' : '';
	$sStyleTextCheck = $sDisplayStyle == 'text' ? 'checked' : '';
	$sStyleNamesCheck = $sDisplayStyle == 'names' ? 'checked' : '';
	$sStyleThumbsCheck = $sDisplayStyle == 'thumbs' ? 'checked' : '';

	// display options
	print <<<EOM
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Taggable Settings</h2>

<form action="{$_SERVER['REQUEST_URI']}" method="post">
<input name="save" value="1" type="hidden">
<p>Taggable offers several options to allow visitors to view and tag people on your
	blog.  Try them out!</p>

<input type="radio" name="Taggable_sDisplayStyle" value="button" $sStyleButtonCheck /> Simple Button with tag count <br />
<input type="radio" name="Taggable_sDisplayStyle" value="text" $sStyleTextCheck /> Text Link with tag count <br />
<input type="radio" name="Taggable_sDisplayStyle" value="names" $sStyleNamesCheck /> Text Link with names of people tagged <br />
<input type="radio" name="Taggable_sDisplayStyle" value="thumbs" $sStyleThumbsCheck /> Text Link with thumbnails of people tagged <br />

<p class="submit">
	<input name="Submit" class="button-primary" value="Save Changes" type="submit">
</p>

</form>
</div>

<div class="wrap">
<p>
More information about <a href="http://taggable.com/">Taggable</a>.
</p>
</div>
EOM;

}

add_filter('the_content', 'taggableFilter_appendToContent');
add_action('admin_menu', 'taggableAction_createTaggableBox');
add_action('admin_menu', 'taggable_adminMenu');

?>

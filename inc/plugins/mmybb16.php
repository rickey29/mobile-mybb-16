<?php
/*
	project: Mobile MyBB 1.6 (MMyBB16)
	file:    MYBB_ROOT/inc/plugins/mmybb16.php
	version: 1.0.0
	author:  Rickey Gu
	web:     http://flexplat.com
	email:   rickey29@gmail.com
*/

// Disallow direct access to this file for security reasons
if ( !defined("IN_MYBB") )
{
	die("Direct initialization of this file is not allowed.");
}


$plugins->add_hook('error', 'mphpbb16_error');
$plugins->add_hook('global_end', 'mphpbb16_global');
$plugins->add_hook('global_start', 'mphpbb16_style');
$plugins->add_hook('index_end', 'mphpbb16_index');
$plugins->add_hook('member_do_login_end', 'mphpbb16_login');
$plugins->add_hook('member_profile_end', 'mphpbb16_profile');
$plugins->add_hook('member_register_end', 'mphpbb16_register');
$plugins->add_hook('newreply_start', 'mphpbb16_newreply');
$plugins->add_hook('newthread_start', 'mphpbb16_newthread');
$plugins->add_hook('postbit', 'mphpbb16_postbit');
$plugins->add_hook('pre_output_page', 'mphpbb16_output_page');
$plugins->add_hook('redirect', 'mphpbb16_redirect');
$plugins->add_hook('showthread_end', 'mphpbb16_showthread');


function mmybb16_info()
{
	return array(
		"name"          => "Mobile MyBB 1.6",
		"description"   => "Mobile MyBB 1.6 (MMyBB16) is a mobile-friendly MyBB 1.6 theme.",
		"website"       => "http://flexplat.com/mobile-mybb-16",
		"author"        => "Rickey Gu",
		"authorsite"    => "http://flexplat.com",
		"version"       => "1.0.0",
		"guid"          => str_replace('.php', '', basename(__FILE__)),
		"codename"      => str_replace('.php', '', basename(__FILE__)),
		"compatibility" => "16*"
	);
}

function mmybb16_install()
{
}

function mmybb16_is_installed()
{
	$file = MYBB_ROOT . 'inc/plugins/mmybb16/Mobile MyBB 1.6-theme.xml';
	if ( !file_exists($file) )
	{
		return false;
	}

	$file = MYBB_ROOT . 'inc/languages/english/mmybb16.lang.php';
	if ( !file_exists($file) )
	{
		return false;
	}

	return true;
}

function mmybb16_uninstall()
{
}

function mmybb16_activate()
{
	require(MYBB_ADMIN_DIR . '/inc/functions_themes.php');

	$file = MYBB_ROOT . 'inc/plugins/mmybb16/Mobile MyBB 1.6-theme.xml';
	if ( !file_exists($file) )
	{
		flash_message('Mobile MyBB 1.6 theme file is NOT exist.', 'error');
		admin_redirect('index.php?module=config/plugins');
	}

	$xml = @file_get_contents($file);
	if ( empty($xml) )
	{
		return;
	}

	$options = array(
		'force_name_check' => true,
		'version_compat' => 1,
		'no_templates' => 0,
		'parent' => 1,
		'no_stylesheets' => 1,
	);

	import_theme_xml($xml, $options);
}

function mmybb16_deactivate()
{
	global $db;

	$name = 'Mobile MyBB 1.6';
	$query = $db->simple_select("themes", "tid", "name='".$db->escape_string($name)."'", array("limit" => 1));
	$theme = $db->fetch_array($query);
	$db->delete_query("themes", "tid='{$theme['tid']}'");

	$title = 'Mobile MyBB 1.6 Templates';
	$query = $db->simple_select("templatesets", "sid", "title='".$db->escape_string($title)."'", array("limit" => 1));
	$templateset = $db->fetch_array($query);
	$db->delete_query("templatesets", "sid='{$templateset['sid']}'");
	$db->delete_query("templates", "sid='{$templateset['sid']}'");
}


function mphpbb16_inline_error($errors, $title)
{
	global $lang;

	if ( empty($errors) )
	{
		return '';
	}

	if ( empty($title) )
	{
		$title = $lang->please_correct_errors;
	}

	$inline_errors = '
<li data-theme="e">
' . $title;

	$pattern = '#<a[^>]*>\s*(.*)\s*</a>#isU';
	foreach ( $errors as $error )
	{
		$error = preg_replace($pattern, '$1', $error);

		$error = str_replace('<br /><br />', '<br />', $error);

		$inline_errors .= '
<br />
' . $error;
	}

	$inline_errors .= '
</li>';

	return $inline_errors;
}


function mphpbb16_error($error)
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	$error2 = '';

	$pattern = '#^\s*<a[^>]*>\s*(.*)\s*</a>\s*$#isU';
	$pattern2 = '#<a[^>]*>\s*(.*)\s*</a>#isU';
	foreach ( explode("\n", $error) as $e )
	{
		if ( !preg_match($pattern, $e) )
		{
			$e = preg_replace($pattern2, '$1', $e);
		}

		if ( !empty($error2) )
		{
			$error2 .= "\n";
		}

		$error2 .= $e;
	}

	$error2 = str_replace('<br /><br />', '<br />', $error2);
	$error2 = str_replace('<p>', '<br />', $error2);

	return $error2;
}

function mphpbb16_global()
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	global $mybb, $lang, $templates;
	global $header_personal, $headerinclude;

	if ( $lang->settings['rtl'] == 1 )
	{
		$headerinclude = str_replace('jquery.mobile-1.3.2.min.css', 'jquery.mobile-1.3.2.rtl.min.css', $headerinclude);
	}

	if ( empty($mybb->input['m-mode']) || $mybb->input['m-mode'] != 'option' )
	{
		if ( !empty($lang->personal_header) )
		{
			eval('$header_personal = "'.$templates->get('header_personal').'";');
		}
	}
}

function mphpbb16_index()
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	global $mybb, $lang, $templates;
	global $footer_personal, $header_option, $index_panel, $welcomeblock;

	if ( empty($mybb->input['m-mode']) || $mybb->input['m-mode'] != 'option' )
	{
		if ( !empty($lang->personal_footer) )
		{
			eval('$footer_personal = "'.$templates->get('footer_personal').'";');
		}
	}
	else
	{
		eval('$header_option = "'.$templates->get('header_option').'";');
		eval('$index_panel = "'.$templates->get('index_panel').'";');
		$templates->cache['index'] = $templates->get('index_option', 0);
	}
}

function mphpbb16_login()
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	global $errors, $title, $inline_errors;

	$inline_errors = mphpbb16_inline_error($errors, $title);
}

function mphpbb16_newreply()
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	global $lang;
	global $post_errors, $title, $reply_errors;

	$pattern = '#</?(b|strong)>#isU';
	$lang->options_sig = preg_replace($pattern, '$1', $lang->options_sig);
	$lang->options_disablesmilies = preg_replace($pattern, '$1', $lang->options_disablesmilies);
	$lang->close_thread = preg_replace($pattern, '$1', $lang->close_thread);
	$lang->stick_thread = preg_replace($pattern, '$1', $lang->stick_thread);

	$lang->thread_subscription_method = str_replace('<strong>', '', $lang->thread_subscription_method);
	$lang->thread_subscription_method = str_replace('</strong>', '', $lang->thread_subscription_method);
	$lang->thread_subscription_method = str_replace('<span class="smalltext">', '', $lang->thread_subscription_method);
	$lang->thread_subscription_method = str_replace('</span>', '', $lang->thread_subscription_method);

	$reply_errors = mphpbb16_inline_error($post_errors, $title);
}

function mphpbb16_newthread()
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	global $lang;
	global $post_errors, $title, $thread_errors;

	$pattern = '#</?(b|strong)>#isU';
	$lang->options_sig = preg_replace($pattern, '$1', $lang->options_sig);
	$lang->options_disablesmilies = preg_replace($pattern, '$1', $lang->options_disablesmilies);
	$lang->close_thread = preg_replace($pattern, '$1', $lang->close_thread);
	$lang->stick_thread = preg_replace($pattern, '$1', $lang->stick_thread);

	$lang->thread_subscription_method = str_replace('<strong>', '', $lang->thread_subscription_method);
	$lang->thread_subscription_method = str_replace('</strong>', '', $lang->thread_subscription_method);
	$lang->thread_subscription_method = str_replace('<span class="smalltext">', '', $lang->thread_subscription_method);
	$lang->thread_subscription_method = str_replace('</span>', '', $lang->thread_subscription_method);

	$thread_errors = mphpbb16_inline_error($post_errors, $title);
}

function mphpbb16_output_page($contents)
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	$pattern = '#<!--.*-->#isU';
	$contents = preg_replace($pattern, '', $contents);

	$pattern = '#<title>\s*(.*)\s*</title>#isU';
	if ( preg_match($pattern, $contents, $matches) )
	{
		$pattern2 = '#(<h1>)\s*(.*)\s*(</h1>)#isU';
		$contents = preg_replace($pattern2, '$1' . $matches[1] . '$3', $contents);
	}

	$pattern = '#<p>\s*</p>#isU';
	$contents = preg_replace($pattern, '', $contents);

	$pattern = '#<div style=\"text-align: center;\">.*</div>#isU';
	$contents = preg_replace($pattern, '', $contents);


	$contents2 = '';

	$contents = explode("\n", $contents);
	foreach ( $contents as $line )
	{
		$line = trim($line);
		if ( empty($line) )
		{
			continue;
		}

		if ( !empty($contents2) )
		{
			$contents2 .= "\n";
		}
		$contents2 .= $line;
	}

	$contents = $contents2;

	return $contents;
}

function mphpbb16_postbit($post)
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	global $templates;
	global $ignored_message, $ignored_users;

	if ( is_array($ignored_users) && $post['uid'] != 0 && isset($ignored_users[$post['uid']]) && $ignored_users[$post['uid']] == 1 )
	{
		$pattern = '#<a[^>]*>\s*(.*)\s*</a>#isU';
		$ignored_message = preg_replace($pattern, '$1', $ignored_message);

		$templates->cache['postbit'] = $templates->get('postbit_ignored', 0);
	}
	else
	{
		$templates->cache['postbit'] = $templates->get('postbit_normal', 0);
	}
}

function mphpbb16_profile()
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	global $warning_level, $buddy_options;

	$pattern = '#<span[^>]*>\s*(.*)\s*</span>#isU';
	$warning_level = preg_replace($pattern, '$1', $warning_level);

	$buddy_options2 = '';

	$pattern = '#<a\shref="([^"]+)"><img[^>]+>\s(.+)</a>#isU';
	foreach ( explode("<br />", $buddy_options) as $buddy_option )
	{
		$buddy_option = preg_replace($pattern, '<a href="' . '$1' . '" rel="external">' . '$2' . '</a>', $buddy_option);

		if ( empty($buddy_option) )
		{
			continue;
		}

		if ( !empty($buddy_options2) )
		{
			$buddy_options2 .= "\n";
		}

		$buddy_options2 .= '<li>
' . $buddy_option . '
</li>';
	}

	$buddy_options = $buddy_options2;
}

function mphpbb16_redirect($redirect_args)
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	$pattern = '#<a[^>]*>\s*(.*)\s*</a>#isU';
	$redirect_args['message'] = preg_replace($pattern, '$1', $redirect_args['message']);

	$redirect_args['message'] = str_replace('<br /><br />', '<br />', $redirect_args['message']);

	return $redirect_args;
}

function mphpbb16_register()
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	global $errors, $title, $regerrors;

	$regerrors = mphpbb16_inline_error($errors, $title);
}

function mphpbb16_showthread()
{
	if ( !defined('MMYBB16') )
	{
		return;
	}

	global $mybb, $templates;
	global $headerinclude_showthread;

	eval('$headerinclude_showthread = "'.$templates->get('headerinclude_showthread').'";');
}

function mphpbb16_style()
{
	require(MYBB_ROOT . 'inc/plugins/mmybb16/lib/detection.php');

	global $mybb, $lang, $db;

	if ( isset($mybb->user['style']) && (int)$mybb->user['style'] != 0 )
	{
		return;
	}

	if ( defined("IN_ADMINCP") )
	{
		return;
	}

	$redirection = !empty($mybb->input['m-redirection']) ? $mybb->input['m-redirection'] : '';
	if ( !empty($redirection) && $redirection != 'mobile' )
	{
		// make the cookie expires in a year time: 60 * 60 * 24 * 365 = 31,536,000
		my_setcookie('mybb[m_style]', 'desktop');

		return;
	}

	$style = !empty($mybb->cookies['mybb']['m_style']) ? $mybb->cookies['mybb']['m_style'] : '';
	if ( empty($redirection) && !empty($style) && $style == 'desktop' )
	{
		return;
	}

	if ( empty($style) || $style == 'desktop' )
	{
		$data = array();
		$data['user_agent'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$data['accept'] = !empty($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
		$data['profile'] = !empty($_SERVER['HTTP_PROFILE']) ? $_SERVER['HTTP_PROFILE'] : '';

		$device = m_get_device($data);

		if ( $device == 'desktop' || $device == 'bot' )
		{
			// make the cookie expires in a year time: 60 * 60 * 24 * 365 = 31,536,000
			my_setcookie('mybb[m_style]', 'desktop');

			return;
		}

		$style = 'Mobile';

		// make the cookie expires in a year time: 60 * 60 * 24 * 365 = 31,536,000
		my_setcookie('mybb[m_style]', $style);
	}

	define('MMYBB16', $style);

	$name = 'Mobile MyBB 1.6';
	$query = $db->simple_select("themes", "tid", "name='".$db->escape_string($name)."'", array("limit" => 1));
	$theme = $db->fetch_array($query);
	$mybb->user['style'] = $theme['tid'];

	$lang->load("mmybb16");
}
?>
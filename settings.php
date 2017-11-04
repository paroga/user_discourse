<?php

OCP\User::checkAdminUser();
OCP\Util::addScript('user_discourse', 'settings');
OCP\Util::addStyle('user_discourse', 'settings');
$tmpl = new OCP\Template('user_discourse', 'settings');
$tmpl->assign( 'discourse_api_key', OCP\Config::getAppValue('user_discourse', 'discourse_api_key', ''));
$tmpl->assign( 'discourse_url', OCP\Config::getAppValue('user_discourse', 'discourse_url', ''));
$tmpl->assign( 'discourse_sso_secret', OCP\Config::getAppValue('user_discourse', 'discourse_sso_secret', ''));
return $tmpl->fetchPage();

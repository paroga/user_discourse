<?php

OCP\User::checkAdminUser();

// CSRF check
OCP\JSON::callCheck();

$ok = true;
$params = array('discourse_api_key', 'discourse_url', 'discourse_sso_secret');
foreach($params as $param) {
  if (isset($_POST[$param]))
    OCP\Config::setAppValue('user_discourse', $param, $_POST[$param]);
}

OCP\JSON::success(array("data" => array()));

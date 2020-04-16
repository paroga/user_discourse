<?php

OCP\User::checkAdminUser();

// CSRF check
\OC_JSON::callCheck();

$ok = true;
$params = array('discourse_api_key', 'discourse_url', 'discourse_sso_secret');
foreach($params as $param) {
  if (isset($_POST[$param]))
    \OC::$server->getConfig()->setAppValue('user_discourse', $param, $_POST[$param]);
}

\OC_JSON::success(array("data" => array()));

<?php

if (OCP\App::isEnabled('user_discourse')) {
	$ocVersion = implode('.',OCP\Util::getVersion());
	if (version_compare($ocVersion,'5.0','<')) {
		if ( ! function_exists('p')) {
			function p($string) {
				print(OC_Util::sanitizeHTML($string));
			}
		}
	}

	require_once 'user_discourse/user_discourse.php';

	OCP\App::registerAdmin('user_discourse', 'settings');
	OC_App::registerLogIn(array("name" => 'Discourse', "href" => "?app=user_discourse"));

	// register user backend
	OC_User::useBackend( 'Discourse' );

	$forceLogin = OC::$server->getConfig()->getAppValue('user_discourse', 'discourse_force_discourse_login', true)
		&& shouldEnforceAuthentication();

	if( (isset($_GET['app']) && $_GET['app'] == 'user_discourse') || (!OCP\User::isLoggedIn() && $forceLogin && !isset($_GET['admin_login'])) ) {

		$discourse_url = OC::$server->getConfig()->getAppValue('user_discourse', 'discourse_url', '');
		$discourse_sso_secret = OC::$server->getConfig()->getAppValue('user_discourse', 'discourse_sso_secret', '');

		if (!isset($_GET['sso']) && !isset($_GET['sig'])) {

			$request = OC::$server->getRequest();
			$me = $request->getServerProtocol().'://'.$request->getServerHost().$request->getRequestUri();

			$nonce = OC::$server->getSecureRandom()->generate(30);
			OC::$server->getSession()->set('user_discourse_nonce', $nonce);

			$payload = base64_encode( http_build_query( array (
					'nonce' => $nonce,
					'return_sso_url' => $me
				)
			) );

			$request = array(
				'sso' => $payload,
				'sig' => hash_hmac('sha256', $payload, $discourse_sso_secret )
			);
			$query = http_build_query($request);

			header("Location: $discourse_url/session/sso_provider?$query");
			exit();
		}

		if (OC::$server->getUserSession()->login('', '')) {
			$request = OC::$server->getRequest();
			$uid = OC::$server->getUserSession()->getUser()->getUID();
			OC::$server->getUserSession()->createSessionToken($request, $uid, $uid);

			OC::$REQUESTEDAPP = '';
			OC_Util::redirectToDefaultPage();
		}

		$error = true;
		OCP\Util::writeLog('discourse','Error trying to authenticate the user', OCP\Util::DEBUG);
	}

}


/*
 * Checks if requiring SAML authentication on current URL makes sense when
 * forceLogin is set.
 *
 * Disables it when using the command line too
 */
function shouldEnforceAuthentication()
{
	if (OC::$CLI) {
		return false;
	}

	// Exceptions for the richdocuments app
	$request_uri = $_SERVER['REQUEST_URI'];
	if (strpos($request_uri, '/index.php/') === 0)
		$request_uri = substr($request_uri, 10);
	if (strpos($request_uri, '/apps/richdocuments/wopi/') === 0)
		return false;
	if (strpos($request_uri, '/ocs/') === 0)
		return false;

	$script = basename($_SERVER['SCRIPT_FILENAME']);
	return !in_array($script,
		array(
			'cron.php',
			'public.php',
			'remote.php',
			'status.php',
		)
	);
}

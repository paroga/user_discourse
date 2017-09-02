<?php

class OC_USER_DISCOURSE extends OC_User_Backend {

	public $autocreate;

	public function __construct() {
		$this->autocreate = true;
	}


	public function checkPassword($uid, $password) {
		if (!isset($_GET['sso']) || !isset($_GET['sig']))
			return false;

		$discourse_sso_secret = OCP\Config::getAppValue('user_discourse', 'discourse_sso_secret', '');
		$sso = $_GET['sso'];
		$sig = $_GET['sig'];

		// validate sso
		if (hash_hmac('sha256', urldecode($sso), $discourse_sso_secret) !== $sig)
			return false;

		$query = array();
		parse_str(base64_decode(urldecode($sso)), $query);

		// verify nonce with generated nonce
		$nonce = OC::$server->getSession()->get('user_discourse_nonce');
		if ($query['nonce'] != $nonce)
			return false;

		$uid = $query['username'];

		if (preg_match( '/[^a-zA-Z0-9 _\.@\-]/', $uid)) {
			OCP\Util::writeLog('discourse','Invalid username "'.$uid.'", allowed chars "a-zA-Z0-9" and "_.@-" ',OCP\Util::DEBUG);
			return false;
		}

		OCP\Util::writeLog('discourse','Authenticated user '.$uid, OCP\Util::DEBUG);
		if (!OCP\User::userExists($uid) && $this->autocreate) {
			$random_password = OCP\Util::generateRandomBytes(64);
			OCP\Util::writeLog('discourse','Creating new user: '.$uid, OCP\Util::DEBUG);
			OC::$server->getUserManager()->createUser($uid, $random_password);
		}

		return $uid;
	}
}

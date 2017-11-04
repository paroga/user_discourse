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
		$user = OC::$server->getUserManager()->get($uid);
		if (!$user && $this->autocreate) {
			$random_password = OCP\Util::generateRandomBytes(64);
			OCP\Util::writeLog('discourse','Creating new user: '.$uid, OCP\Util::DEBUG);
			$user = OC::$server->getUserManager()->createUser($uid, $random_password);
		}

		if (!$user)
			return false;

		$info = $this->getUserInfo($uid);
		if (!$info)
			return false;

		if ($avatar = $this->getUserAvatar($info)) {
			$image = new \OC_Image();
			$image->loadFromData($avatar);
			$image->readExif($avatar);
			$image->fixOrientation();

			OC::$server->getAvatarManager()->getAvatar($uid)->set($image);
		}

		$gm = OC::$server->getGroupManager();
		$groups_old = $gm->getUserGroupIds($user);
		$groups_new = $this->getUserGroups($info);

		foreach ($groups_old as $group) {
			if (in_array($group, $groups_new))
				continue;
			$gm->get($group)->removeUser($user);
		}

		foreach ($groups_new as $group) {
			if (in_array($group, $groups_old))
				continue;

			$g = $gm->get($group);
			if (!$g)
				$g = $gm->createGroup($group);
			$g->addUser($user);
		}

		return $uid;
	}

	private function httpGet($path) {
		$discourse_url = OCP\Config::getAppValue('user_discourse', 'discourse_url', '');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $discourse_url . $path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$body = curl_exec($ch);
		$rc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($rc != 200)
			return false;

		return $body;
	}

	private function getUserAvatar($info) {
		$path = str_replace('{size}', '160', $info->user->avatar_template);

		$body = $this->httpGet($path);
		if (!$body)
			return false;

		return $body;
	}

	private function getUserGroups($info) {
		$groups = [];
		foreach($info->user->groups as $group) {
			$name = $group->name;
			// match Discours admins to Nextcloud admin
			if ($name == 'admins')
				$name = 'admin';
			$groups[] = $name;
		}

		return $groups;
	}

	private function getUserInfo($uid) {
		$discourse_api_key = OCP\Config::getAppValue('user_discourse', 'discourse_api_key', '');

		$paramArray = array(
			'api_key' => $discourse_api_key,
			'api_username' => 'system'
		);
		$query = http_build_query($paramArray);

		$body = $this->httpGet("/users/$uid.json?$query");
		if (!$body)
			return false;

		return json_decode($body);
	}
}

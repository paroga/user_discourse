<div class="section" id="discourse_settings">
	<form id="discourse_settings_form" class="discourse_settings">
		<h2><?php p($l->t('Discourse login settings')) ?>:</h2>
		<p><span id="discourse_settings_msg" class="msg"></span></p>

		<label for="discourse_url"><?php p($l->t('Discourse URL')); ?>
		<input type="text" name="discourse_url" id="discourse_url" value="<?php p($_['discourse_url']); ?>">
		<p><em>The base URL of your forum, for example http://discourse.example.com</em></p>
		<br>
		<label for="discourse_sso_secret"><?php p($l->t('SSO Secret Key')); ?>
		<input type="text" name="discourse_sso_secret" id="discourse_sso_secret" value="<?php p($_['discourse_sso_secret']); ?>">
		<p><em>The secret key used to verify Discourse SSO requests. Set it to a string of text, at least 10 characters long.</em></p>
	</form>
</div>

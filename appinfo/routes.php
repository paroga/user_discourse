<?php
/**
 * ownCloud - push notifications app
 *
 * @author Frank Karlitschek
 * @copyright 2014 Frank Karlitschek frank@owncloud.org
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */
$this->create('user_discourse_ajax_save', 'ajax/save.php')
        ->actionInclude('user_discourse/ajax/save.php');

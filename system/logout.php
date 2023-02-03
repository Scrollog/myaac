<?php

if(isset($account_logged) && $account_logged->isLoaded()) {
	if($hooks->trigger(HOOK_LOGOUT, ['account_id' => $account_logged->getId()])) {
		unsetSession('account');
		unsetSession('password');
		unsetSession('remember_me');

		$logged = false;
		unset($account_logged);

		if(isset($_REQUEST['redirect']))
		{
			header('Location: ' . urldecode($_REQUEST['redirect']));
			exit;
		}
	}
}
<?php
	/* Define Constants */
	if(!defined('INBOX_APP'))
	{
		define('INBOX_APP', APP . DS . 'plugins' . DS . 'inbox');
	}

	if(!defined('INBOX_CONFIGS'))
	{
		define('INBOX_CONFIGS', INBOX_APP . DS . 'config');
	}

	if(!defined('ATTACHMENTS'))
	{
		define('ATTACHMENTS', INBOX_APP . DS . 'attachments');
	}

	/* Include Config File */
	require_once(INBOX_CONFIGS . DS . 'inbox.php');

	/* Setup Permissions */
	try {
		$parent = $this->addPermission('inbox', 'Inbox');
	} catch(Exception $e) {
		// TODO: Do something
	}

	/* Add Listeners */
	// No Listeners
?>

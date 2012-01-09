<?php
class DigestShell extends Shell
{
	var $uses = array(
		'Digest',
		'User',
		'Inbox',
		'InboxHash',
	);

	var $components = array(
		'Messaging',
	);

	function _loadComponents(&$object)
	{
		foreach($object->components as $component)
		{
			App::import('Component', $component);	

			$componentCn = $component . 'Component';
			$object->{$component} =& new $componentCn(null);
			$object->{$component}->enabled = true;

			if(isset($object->{$component}->components))
			{
				$this->_loadComponents($object->{$component});
			}
		}
	}

	function initialize()
	{
		$this->_loadComponents($this);

		App::import('Core', array('Router', 'Configure', 'Controller'));
		require_once CONFIGS . 'routes.php';
		require_once CONFIGS . 'core.php';

		define('FULL_BASE_URL', Configure::read('Site.full'));

		$this->Controller =& new Controller();
		$this->Messaging->initialize($this->Controller);

		parent::initialize();
	}

	function main()
	{
		$digests = $this->Digest->find('all');
		if(empty($digests))
		{
			return;
		}

		foreach($digests as $digest)
		{
			$email = $this->User->field('email', array('User.id' => $digest['Digest']['user_id']));
			if(empty($email))
			{
				$this->Digest->del($digest['Digest']['id']);
				continue;
			}

			$inbox = $this->Inbox->find('first', array(
				'conditions' => array(
					'Inbox.id' => $digest['Digest']['inbox_id'],
				),
				'contain' => array(
					'Message',
				),
			));
			if(empty($inbox))
			{
				$this->Digest->del($digest['Digest']['id']);
				continue;
			}

			$hash = $this->InboxHash->generate($digest['Digest']['inbox_id']);
			if(empty($hash))
			{
				continue;
			}

			$template = array(
				'name' => $inbox['Inbox']['template'],
				'data' => (array) json_decode($inbox['Inbox']['template_data']),
			);
			$template['data']['inbox_id'] = $digest['Digest']['inbox_id'];
			$template['data']['hash'] = $hash;

			$token = $this->Messaging->encode($digest['Digest']['inbox_id'], $hash);
			$replyto = 'm+' . $token . '@' . Configure::read('Mail.domain');

			//TODO: Check the output of this function
			//TODO: Handle attachments?
			$this->Messaging->email($email, $inbox['Message']['body'], $inbox['Message']['subject'], $template, array(), $replyto);

			$this->Digest->del($digest['Digest']['id']);
		}
	}
}
?>

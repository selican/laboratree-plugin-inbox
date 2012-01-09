<?php
/* TODO : Migrate Email Functions Here */
class MessagingComponent extends Object
{
	var $uses = array(
		'Inbox.InboxHash',
		'Inbox.Digest',
		'User',
		'GroupsUsers',
		'ProjectsUsers',
	);

	var $components = array(
		'Email',
		'Session',
		'Inbox.InboxCmp',
	);

	function _loadModels(&$object)
	{
		foreach($object->uses as $modelClass)
		{
			$plugin = null;

			if(strpos($modelClass, '.') !== false)
			{
				list($plugin, $modelClass) = explode('.', $modelClass);
				$plugin = $plugin . '.';
			}

			App::import('Model', $plugin . $modelClass);
			$this->{$modelClass} = new $modelClass();

			if(!$this->{$modelClass})
			{
				return false;
			}
		}
	}

			
	function initialize(&$controller, $settings = array())
	{
		$this->Controller =& $controller;
		$this->_loadModels($this);

		$this->Email->initialize($controller);
		$this->InboxCmp->initialize($controller);
	}

	function startup(&$controller) {}

	/**
	 * Generate email address extension for Inbox Message
	 *
	 * @todo Make this less of a hack
	 * 
	 * @param integer $inbox_id   Inbox ID
	 * @param string  $inbox_hash Inbox Hash
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return string Address Extension
	 */
	function encode($inbox_id, $inbox_hash)
	{
		if(empty($inbox_id) || !is_numeric($inbox_id) || $inbox_id < 1)
		{
			throw new InvalidArgumentException('Invalid inbox id.');
		}

		if(empty($inbox_hash) || !is_string($inbox_hash) || strlen($inbox_hash) != 40)
		{
			throw new InvalidArgumentException('Invalid inbox hash.');
		}

		$hashval = array_sum(array_map('ord', str_split($inbox_hash)));
		$inbox_id += $hashval;
		$inbox_id = strrev(sprintf('%011d', $inbox_id));
		$inbox_hash = strrev(str_rot13($inbox_hash));

		$token  = $inbox_id . $inbox_hash;
		$token .= substr(md5(uniqid()), 0, 64 - strlen($token));

		return $token;
	}

	/**
	 * Decodes the email address extension for an Inbox Message
	 *
	 * @todo Make this match encode() if it is changed
	 *
	 * @param string $token Address Extension
	 *
	 * @throws InvalidArgmentException
	 *
	 * @return array Inbox Message Data
	 */
	function decode($token)
	{
		if(empty($token) || !is_string($token) || strlen($token) != 64)
		{
			throw new InvalidArgumentException('Invalid token.');
		}

		$inbox_id = strrev(substr($token, 0, 11));
		$inbox_hash = str_rot13(strrev(substr($token, 11, 40)));

		$hashval = array_sum(array_map('ord', str_split($inbox_hash)));
		$inbox_id -= $hashval;

		return array($inbox_id, $inbox_hash);
	}

	/**
	 * Send a Inbox Message
	 *
	 * @param array   $recipients  Recipients
	 * @param array   $message     Message
	 * @param array   $attachments Attachments
	 * @param integer $sender User ID of Sender
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	function send($recipients, $message, $attachments = array(), $sender = null)
	{
		if(empty($recipients) || !is_array($recipients))
		{
			throw new InvalidArgumentException('Invalid recipients.');
		}

		if(isset($recipients['receiver_id']))
		{
			$recipients = array($recipients);
		}

		if(empty($message) || !is_array($message))
		{
			throw new InvalidArgumentException('Invalid message.');
		}

		if(!is_array($attachments))
		{
			throw new InvalidArgumentException('Invalid attachments.');
		}

		if(!empty($sender) && !is_numeric($sender))
		{
			throw new InvalidArgumentException('Invalid sender.');
		}

		if(isset($recipients['receiver_id']))
		{
			$recipients = array($recipients);
		}

		if(isset($attachments['name']))
		{
			$attachments = array($attachments);
		}

		if(empty($sender))
		{
			$sender = $this->Session->read('Auth.User.id');
		}

		$message['subject'] = trim($message['subject']);
		$message['body'] = trim($message['body']);

		if(empty($message['subject']))
		{
			$message['subject'] = 'No Subject';
		}

		try
		{
			$messages = $this->InboxCmp->send($recipients, $message, $attachments, $sender);
		}
		catch(Exception $e)
		{
			throw new RuntimeException($e->getMessage());
		}

		if(empty($messages))
		{
			throw new RuntimeException('Unable to create inbox entries.');
		}

		$tmpdir = TMP . 'attachments' . DS . uniqid('', true);
		while(is_dir($tmpdir))
		{
			$tmpdir = TMP . 'attachments' . DS . uniqid('', true);
		}

		$files = array();
		foreach($attachments as $attachment)
		{
			if(isset($attachment['filename']) && !empty($attachment['filename']))
			{
				$filename = $attachment['filename'];
			}
			else if(isset($attachment['tmp_name']) && !empty($attachment['tmp_name']))
			{
				$filename = $attachment['tmp_name'];
			}
			else if(isset($attachment['content']))
			{
				$filename = TMP . DS . $attachment['name'];
				file_put_contents($filename, $attachment['content']);
			}
			else
			{
				continue;
			}

			$filedir = $tmpdir . DS . uniqid('', true);
			mkdir($filedir, 0770, true);

			$link = $filedir . DS . $attachment['name'];
			if(symlink($filename, $link))
			{
				$files[] = $link;
			}
		}

		foreach($recipients as $recipient)
		{
			if(!isset($recipient['template']))
			{
				continue;
			}

			if(!isset($recipient['preference']))
			{
				$recipient['preference'] = 'Always';
			}

			if($recipient['receiver_type'] == 'user')
			{
				if(!isset($messages[$recipient['receiver_id']]))
				{
					continue;
				}

				$inbox_id = $messages[$recipient['receiver_id']];

				if($recipient['preference'] == 'Digest')
				{
					$this->digest($recipient['receiver_id'], $inbox_id);
					continue;
				}
				else if($recipient['preference'] == 'Never')
				{
					continue;
				}

				$this->User->id = $recipient['receiver_id'];
				$email = $this->User->field('email');

				if(empty($email))
				{
					continue;
				}

				try {
					$hash = $this->InboxHash->generate($inbox_id);
				} catch(Exception $e) {
					throw new RuntimeException($e->getMessage());
				}
				if(empty($hash))
				{
					continue;
				}

				$template = $recipient['template'];
				$template['data']['inbox_id'] = $inbox_id;
				$template['data']['hash'] = $hash;

				$token = $this->encode($inbox_id, $hash);
				$replyto = 'm+' . $token . '@' . Configure::read('Mail.domain');
				//TODO: Check the output of this function
				$this->email($email, $message['body'], $message['subject'], $template, $files, $replyto);
			}
			else if($recipient['receiver_type'] == 'email')
			{
				if(!isset($messages[$recipient['receiver_id']]))
				{
					continue;
				}

				$inbox_id = $messages[$recipient['receiver_id']];

				try {
					$hash = $this->InboxHash->generate($inbox_id);
				} catch(Exception $e) {
					throw new RuntimeException($e->getMessage());
				}
				if(empty($hash))
				{
					continue;
				}

				$template = $recipient['template'];
				$template['data']['inbox_id'] = $inbox_id;
				$template['data']['hash'] = $hash;
				$token = $this->encode($inbox_id, $hash);
				$replyto = 'm+' . $token . '@' . Configure::read('Mail.domain');
				//TODO: Check the output of this function
				$this->email($recipient['receiver_id'], $message['body'], $message['subject'], $template, $files, $replyto);
			}
			else
			{
				if($recipient['receiver_type'] == 'group')
				{
					$this->Group->id = $recipient['receiver_id'];
					$name = $this->Group->field('name');
					if(empty($name))
					{
						continue;
					}

					$message['subject'] = '[' . $name . '] ' . $message['subject'];

					try {
						$users = $this->GroupsUsers->users($recipient['receiver_id']);
					} catch(Exception $e) {
						throw new RuntimeException($e->getMessage());
					}
				}
				else if($recipient['receiver_type'] == 'project')
				{
					$this->Project->id = $recipient['receiver_id'];
					$name = $this->Project->field('name');
					if(empty($name))
					{
						continue;
					}

					$message['subject'] = '[' . $name . '] ' . $message['subject'];

					try {
						$users = $this->ProjectsUsers->users($recipient['receiver_id']);
					} catch(Exception $e) {
						throw new RuntimeException($e->getMessage());
					}
				}

				foreach($users as $user)
				{
					if(!isset($messages[$user['User']['id']]))
					{
						continue;
					}

					$inbox_id = $messages[$user['User']['id']];

					// TODO: Check Preferences?

					if(empty($user['User']['email']))
					{
						continue;
					}

					try {
						$hash = $this->InboxHash->generate($inbox_id);
					} catch(Exception $e) {
						throw new RuntimeException($e->getMessage());
					}
					if(empty($hash))
					{
						continue;
					}

					$template = $recipient['template'];
					$template['data']['inbox_id'] = $inbox_id;
					$template['data']['hash'] = $hash;

					$token = $this->encode($inbox_id, $hash);
					$replyto = 'm+' . $token . '@' . Configure::read('Mail.domain');
					//TODO: Check the output of this function
					$this->email($user['User']['email'], $message['body'], $message['subject'], $template, $files, $replyto);
				}
			}
		}

		foreach($files as $file)
		{
			$filedir = dirname($file);
			if(file_exists($file))
			{
				unlink($file);
			}

			if(is_dir($filedir))
			{
				rmdir($filedir);
			}
		}

		if(is_dir($tmpdir))
		{
			rmdir($tmpdir);
		}
	}

	/**
	 * Generate a Digest entry for an Inbox Message
	 *
	 * @param integer $user_id  User ID
	 * @param integer $inbox_id Inbox ID
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return mixed Digest Data
	 */
	function digest($user_id, $inbox_id)
	{
		if(empty($user_id) || !is_numeric($user_id) || $user_id < 1)
		{
			throw new InvalidArgumentException('Invalid user.');
		}

		if(empty($inbox_id) || !is_numeric($inbox_id) || $inbox_id < 1)
		{
			throw new InvalidArgumentException('Invalid inbox.');
		}

		$data = array(
			'Digest' => array(
				'user_id' => $user_id,
				'inbox_id' => $inbox_id,
			),
		);
		$this->Digest->create();
		return $this->Digest->save($data);
	}

	/**
	 * Sends an email
	 *
	 * @todo Add a unit test for this function
	 *
	 * @param string $email       Email Address
	 * @param string $message     Message Body
	 * @param string $subject     Message Subject
	 * @param array  $attachments List of Filenames to Attach
	 * @param string $replto      Reply To Email Address
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return boolean Success
	 */
	function email($email, $message, $subject, $template, $attachments = array(), $replyto = '')
	{
		if(empty($email) || !is_string($email) || !preg_match(VALID_EMAIL, $email))
		{
			throw new InvalidArgumentException('Invalid email.');
		}

		if(!is_string($message))
		{
			throw new InvalidArgumentException('Invalid message.');
		}

		if(!is_string($subject))
		{
			throw new InvalidArgumentException('Invalid subject.');
		}

		if(empty($template) || !is_array($template))
		{
			throw new InvalidArgumentException('Invalid template.');
		}

		if(!is_array($attachments))
		{
			throw new InvalidArgumentException('Invalid attachments.');
		}

		if(!is_string($replyto))
		{
			throw new InvalidArgumentException('Invalid reply to.');
		}

		$this->Email->reset(); 

		if(empty($replyto))
		{
			$replyto = Configure::read('Site.name') . '<no-reply@' . Configure::read('Mail.domain') . '>';
		}

		$this->Email->to = $email;
		$this->Email->subject = $subject;
		$this->Email->replyTo = $replyto;
		$this->Email->return = 'no-reply@' . Configure::read('Mail.domain');
		$this->Email->from = Configure::read('Site.name') . ' <no-reply@' . Configure::read('Mail.domain') . '>';
		$this->Email->template = $template['name'];
		$this->Email->sendAs = 'both';
		$this->Email->xMailer = 'LTMail';
		$this->Email->attachments = $attachments;

		$this->Email->headers = array(
			'Laboratree' => 'LTMail',
			'Priority' => 3,
		);
		
		$this->Email->Controller->set('data', $template['data']);
		$this->Email->Controller->set('message', $message);

		$this->Email->additionalParams = '-fno-reply@' . Configure::read('Mail.domain');
		return $this->Email->send($message);
	}
}
?>

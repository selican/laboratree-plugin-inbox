<?php
class ReplyShell extends Shell {
	var $uses = array(
		'Inbox',
		'InboxHash',
	);

	var $components = array(
		'Messaging',
		'Template',
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
		$this->Template->initialize($this->Controller);

		require_once 'Mail/mimeDecode.php';

		parent::initialize();
	}

	function parts($parts, &$body, &$attachments)
	{
		foreach($parts as $part)
		{
			if(isset($part->parts))
			{
				$this->parts($part->parts, $body, $attachments);
			}

			if(isset($part->disposition) && $part->disposition == 'attachment')
			{
				if(strpos($part->headers['content-type'], ';'))
				{
					$sections = explode(';', $part->headers['content-type']);
					$part->headers['content-type'] = array_shift($sections);
				}

				$attachments[] = array(
					'mimetype' => $part->headers['content-type'],
					'name' => $part->d_parameters['filename'],
					'content' => $part->body,
				);
			}

			if($part->ctype_primary == 'text')
			{
				if(!isset($part->disposition) || $part->disposition == 'inline')
				{
					if($part->ctype_secondary == 'plain')
					{
						$body = $part->body;
					}
					else if($part->ctype_secondary == 'html')
					{
						if(empty($body))
						{
							$body = $part->body;
						}
					}
				}
			}
		}
	}

	function main()
	{
		$this->_checkArgs(1, 'reply');

		$token = $this->args[0];

		if(!preg_match('/^\d{11}[A-Za-z0-9]{53}$/', $token))
		{
			$this->error('Invalid Token', 'The token was invalid.');
		}

		list($inbox_id, $hash) = $this->Messaging->decode($token);

		$conditions = array(
			'InboxHash.inbox_id' => $inbox_id,
			'InboxHash.hash' => $hash,
		);

		$hash = $this->InboxHash->find('first', array('conditions' => $conditions, 'recursive' => -1));
		if(empty($hash))
		{
			$this->error('Invalid Token', 'The token was invalid.');
		}

		$conditions = array(
			'Inbox.id' => $inbox_id,
		);

		$contain = array(
                        'Parent',
			'ReceiverUser',
                );

                $inbox = $this->Inbox->find('first', array('conditions' => $conditions, 'contain' => $contain, 'recursive' => 1));
		if(empty($inbox))
		{
			$this->error('Invalid Inbox Message', 'The inbox message was invalid.');
		}

		$message = null;
		while(!feof($this->Dispatch->stdin))
		{
			$message .= fgets($this->Dispatch->stdin, 4096);
		}

		$message = str_replace('\n', "\n", $message);

		$params['include_bodies'] = true;
		$params['decode_bodies'] = true;
		$params['decode_headers'] = true;

		$decoder = new Mail_mimeDecode($message);
		$structure = $decoder->decode($params);

		$subject = null;
		$body = null;
		$attachments = array();

		if(isset($structure->headers['subject']))
		{
			$subject = $structure->headers['subject'];	
		}

		if(!isset($structure->parts))
		{
			if(isset($structure->body))
			{
				$body = $structure->body;
			}
			else
			{
				return;
			}
		}
		else
		{
			$this->parts($structure->parts, $body, $attachments);
		}

		$sender = array(
			'id' => $inbox['Inbox']['receiver_id'],
			'name' => $inbox['ReceiverUser']['name'],
		);

		$template = $this->Template->user_message($sender);

		$recipient = array(
			'receiver_type' => 'user',
			'receiver_id' => $inbox['Inbox']['sender_id'],
			'template' => $template,
		);

		if(!empty($inbox['Inbox']['parent_id']))
		{
			$template = $this->Template->build($inbox['Parent']['template'], $inbox['Parent']['template_data'], $sender);

			$recipient['receiver_type'] = $inbox['Parent']['receiver_type'];
			$recipient['receiver_id'] = $inbox['Parent']['receiver_id'];
			$recipient['template'] = $template;
		}

		$message = array(
			'subject' => $subject,
			'body' => $body,
		);

		$this->Messaging->send($recipient, $message, $attachments, $inbox['Inbox']['receiver_id']);

		$this->InboxHash->delete($hash['InboxHash']['id']);
	}
}
?>

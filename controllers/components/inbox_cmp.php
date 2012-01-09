<?php
class InboxCmpComponent extends Object
{
	var $uses = array(
		'Inbox.Inbox',
		'Inbox.Message',
		'Inbox.Attachment',
		'Inbox.MessageArchive',
		'GroupsUsers',
		'ProjectsUsers',
	);

	var $components = array(
		'Session',
	);

	var $sender_id     = null;
	var $receiver_id   = null;
	var $receiver_type = null;
	var $message_id    = null;
	var $status        = 'unread';
	var $trash         = 0;
	var $type          = null;
	var $parent_id     = null;
	var $email         = null;

	var $subject  = 'No Subject';
	var $body     = null;

	var $template = 'user_message';
	var $template_data = null;

	var $attachments = array();
	var $attached = false;

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
	}

	function startup(&$controller) {}

	/**
	 * Send an Inbox Message to Recipients
	 *
	 * @param array   $recipients  Recipients
	 * @param array   $message     Message
	 * @param array   $attachments Inbox Message Attachments
	 * @param integer $sender      User ID of Sender
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 *
	 * @return Inbox Messages
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
		$message['date'] = date('Y-m-d H:i:s');

		if(empty($message['subject']))
		{
			$message['subject'] = 'No Subject';
		}

		$data = array(
			'Message' => $message,
		);

		$this->Message->create();
		if(!$this->Message->save($data))
		{
			throw RuntimeException('Unable to create message.');
		}

		$message_id = $this->Message->id;

		if(!empty($attachments))
		{
			try {
				$this->Attachment->attach($message_id, $attachments);
			} catch(Exception $e) {
				throw new RuntimeException($e->getMessage());
			}
		}

		$messages = array();
		foreach($recipients as $recipient)
		{
			if(!isset($recipient['template']))
			{
				continue;
			}

			$template = $recipient['template'];

			if($recipient['receiver_type'] == 'user')
			{
				$this->reset();
				$this->sender_id = $sender;
				$this->receiver_id = $recipient['receiver_id'];
				$this->receiver_type = $recipient['receiver_type'];
				$this->message_id = $message_id;

				$this->template = $template['name'];
				$this->template_data = $template['data'];
	
				$this->id = null;
				$this->status = 'read';
				$this->type = 'sent';
				try
				{
					$this->save();
				}
				catch(Exception $e)
				{
					throw new RuntimeException($e->getMessage());
				}
	
				$this->id = null;
				$this->status = 'unread';
				$this->type = 'received';
				try
				{
					$inbox_id = $this->save();
				}
				catch(Exception $e)
				{
					throw new RuntimeException($e->getMessage());
				}
	
				if(!empty($inbox_id))
				{
					$messages[$recipient['receiver_id']] = $inbox_id;
				}
			}
			else if($recipient['receiver_type'] == 'group')
			{
				$this->reset();
				$this->sender_id = $sender;
				$this->receiver_id = $recipient['receiver_id'];
				$this->receiver_type = $recipient['receiver_type'];
				$this->message_id = $message_id;

				$this->template = $template['name'];
				$this->template_data = $template['data'];

				$this->id = null;
				$this->status = 'read';
				$this->type = 'sent';
				try
				{
					$this->save();
				}
				catch(Exception $e)
				{
					throw new RuntimeException($e->getMessage());
				}
	
				$this->id = null;
				$this->status = 'unread';
				$this->type = 'received';
				try
				{
					$parent_id = $this->save();
				}
				catch(Exception $e)
				{
					throw new RuntimeException($e->getMessage());
				}
	
				// archive the message
				try
				{
					$this->_archive($this->sender_id, $this->receiver_id, $this->receiver_type, $this->message_id, $message['subject'], $message['body']);
				}
				catch(Exception $e)
				{
					throw new RuntimeException($e->getMessage());
				}

				if(empty($parent_id))
				{
					continue;
				}
	
				try {
					$users = $this->GroupsUsers->users($recipient['receiver_id']);
				} catch(Exception $e) {
					throw new RuntimeException($e->getMessage());
				}
				foreach($users as $user)
				{
					$this->id = null;
					$this->receiver_id = $user['User']['id'];
					$this->receiver_type = 'user';
					$this->status = 'unread';
					$this->type = 'received';
					$this->parent_id = $parent_id;
					try
					{
						$inbox_id = $this->save();
					}
					catch(Exception $e)
					{
						throw new RuntimeException($e->getMessage());
					}

					if(!empty($inbox_id))
					{
						$messages[$user['User']['id']] = $inbox_id;
					}
				}
			}
			else if($recipient['receiver_type'] == 'project')
			{
				$this->reset();
				$this->sender_id = $sender;
				$this->receiver_id = $recipient['receiver_id'];
				$this->receiver_type = $recipient['receiver_type'];
				$this->message_id = $message_id;

				$this->template = $template['name'];
				$this->template_data = $template['data'];
	
				$this->id = null;
				$this->status = 'read';
				$this->type = 'sent';
				try
				{
					$this->save();
				}
				catch(Exception $e)
				{
					throw new RuntimeException($e->getMessage());
				}
	
				$this->id = null;
				$this->status = 'unread';
				$this->type = 'received';
				try
				{
					$parent_id = $this->save();
				}
				catch(Exception $e)
				{
					throw new RuntimeException($e->getMessage());
				}

				try
				{
					$this->_archive($this->sender_id, $this->receiver_id, $this->receiver_type, $this->message_id, $message['subject'], $message['body']);
				}
				catch(Exception $e)
				{
					throw new RuntimeException($e->getMessage());
				}
	
				try {
					$users = $this->ProjectsUsers->users($recipient['receiver_id']);
				} catch(Exception $e) {
					throw new RuntimeException($e->getMessage());
				}
				foreach($users as $user)
				{
					$this->id = null;
					$this->receiver_id = $user['User']['id'];
					$this->receiver_type = 'user';
					$this->status = 'unread';
					$this->type = 'received';
					$this->parent_id = $parent_id;
					try
					{
						$inbox_id = $this->save();
					}
					catch(Exception $e)
					{
						throw new RuntimeException($e->getMessage());
					}

					if(!empty($inbox_id))
					{
						$messages[$user['User']['id']] = $inbox_id;
					}
				}
			}
			else if($recipient['receiver_type'] == 'email')
			{
				$this->reset();
				$this->sender_id = $sender;
				$this->email = $recipient['receiver_id'];
				$this->receiver_type = $recipient['receiver_type'];
				$this->message_id = $message_id;

				$this->template = $template['name'];
				$this->template_data = $template['data'];

				$this->id = null;
				$this->status = 'read';
				$this->type = 'sent';

				try
				{
					$inbox_id = $this->save();
				}
				catch(Exception $e)
				{
					throw new RuntimeException($e->getMessage());
				}

				if(!empty($inbox_id))
				{
					$messages[$recipient['receiver_id']] = $inbox_id;
				}
			}
		}

		return $messages;
	}

	/**
	 * Reset Component Variables
	 */
	function reset()
	{
		$this->id = null;

		$this->sender_id     = null;
		$this->receiver_id   = null;
		$this->receiver_type = null;
		$this->message_id    = null;
		$this->status        = 'unread';
		$this->trash         = 0;
		$this->type          = null;
		$this->parent_id     = null;

		$this->subject  = 'No Subject';
		$this->body     = null;

		$this->template = null;
		$this->template_data = null;

		$this->attachments = array();
		$this->attached    = false;

		return true;
	}

	/**
	 * Saves inbox message
	 *
	 * Creates an inbox message if necessary.
	 *
	 * @param string $content  Message Content
	 * @param string $template Message Template
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 *
	 * @return integer Inbox ID
	 */
	function save($content = null)
	{
		if(!empty($content))
		{
			if(!is_string($content))
			{
				throw new InvalidArgumentException('Invalid content.');
			}

			$this->body = $content;
		}

		if(!is_numeric($this->message_id) || $this->message_id < 1)
		{
			try
			{
				$this->_message();
			}
			catch(Exception $e)
			{
				throw new RuntimeException($e->getMessage());
			}
		}

		if(!empty($this->attachments) && !$this->attached)
		{
			try
			{
				$this->_attachments();
			}
			catch(Exception $e)
			{
				throw new RuntimeException($e->getMessage());
			}
		}

		try
		{
			$this->id = $this->_inbox();
		}
		catch(Exception $e)
		{
			throw new RuntimeException($e->getMessage());
		}

		return $this->id;
	}

	/**
	 * Creates a Message record
	 *
	 * @internal
	 *
	 * @return integer Message ID
	 */
	function _message()
	{
		$data = array(
			'Message' => array(
				'subject' => $this->subject,
				'body' => $this->body,
				'date' => date('Y-m-d H:i:s'),
			),
		);
		if(!$this->Message->save($data))
		{
			throw new RuntimeException('Unable to save message.');
		}

		$this->message_id = $this->Message->id;
		return $this->message_id;
	}

	/**
	 * Creates a Attachment record
	 *
	 * @internal
	 *
	 * @return integer Message ID
	 */
	function _attachments()
	{
		if(empty($this->message_id))
		{
			return;
		}

		try
		{
			$this->Attachment->attach($this->message_id, $this->attachments);
		}
		catch(Exception $e)
		{
			throw new RuntimeException($e->getMessage());
		}

		$this->attached = true;
	}

	/**
	 * Creates an Inbox record
	 *
	 * @internal
	 *
	 * @return boolean Success
	 */
	function _inbox()
	{
		$template_data = json_encode($this->_template_data($this->template_data));

		$data = array(
			'Inbox' => array(
				'sender_id' => $this->sender_id,
				'receiver_type' => $this->receiver_type,
				'message_id' => $this->message_id,
				'template' => $this->template,
				'template_data' => $template_data,
				'status' => $this->status,
				'trash' => $this->trash,
				'type' => $this->type,
				'parent_id' => $this->parent_id,
				'email' => $this->email,
			),
		);

		if(!empty($this->receiver_id))
		{
			$data['Inbox']['receiver_id'] = $this->receiver_id;
		}

		$this->Inbox->create();
		if(!$this->Inbox->save($data))
		{
			throw new RuntimeException('Unable to save inbox.');
		}

		return $this->Inbox->id;
	}

	/*
	 * Flattens Template Data
	 *
	 * @internal
	 *
	 * @param mixed $data Template Data
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return string Flattened Template Data
	 */
	function _template_data($data)
	{
		if(is_array($data))
		{
			foreach($data as $key => $entry)
			{
				$data[$key] = $this->_template_data($entry);
			}
		}
		else if(is_integer($data))
		{
			$data = strval($data);
		}

		if(!is_scalar($data))
		{
			throw new InvalidArgumentException('Invalid Data');
		}

		return $data;
	}

	/**
	 * Adds a copy of a message to the archive
	 *
	 * @internal
	 *
	 * @param integer $sender_id     Sender ID
	 * @param integer $receiver_id   Receiver ID
	 * @param string  $receiver_type Receiver Type
	 * @param integer $message_id    Message ID
	 * @param string  $subject       Message Subject
	 * @param string  $body          Message Body
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 *
	 * @return boolean Success
	 */
	function _archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject = null, $body = null)
	{
		if(empty($sender_id) || !is_numeric($sender_id) || $sender_id < 1)
		{
			throw new InvalidArgumentException('Invalid sender id.');
		}

		if(empty($receiver_id) || !is_numeric($receiver_id) || $receiver_id < 1)
		{
			throw new InvalidArgumentException('Invalid receiver id.');
		}

		if(empty($receiver_type) || !is_string($receiver_type))
		{
			throw new InvalidArgumentException('Invalid receiver type.');
		}

		if(empty($message_id) || !is_numeric($message_id) || $message_id < 1)
		{
			throw new InvalidArgumentException('Invalid message id.');
		}

		if(!empty($subject) && !is_string($subject))
		{
			throw new InvalidArgumentException('Invalid subject.');
		}

		if(!empty($body) && !is_string($body))
		{
			throw new InvalidArgumentException('Invalid body.');
		}

		$data = array(
			'MessageArchive' => array(
				'message_id' => $message_id,
				'sender_id' => $sender_id,
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'subject' => $subject,
				'body' => $body,
				'date' => date('Y-m-d H:i:s'),
			),
		);

		$this->MessageArchive->create();
		if(!$this->MessageArchive->save($data))
		{
			throw new RuntimeException('Unable to save message archive.');
		}

		return $this->MessageArchive->id;
	}
}
?>

<?php
class Inbox extends InboxAppModel
{
	var $name   = 'Inbox';
	var $actsAs = array('Containable');

	var $validate = array(
		'sender_id' => array(
			'sender_id-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Sender ID must not be empty.',
			),
			'sender_id-2' => array(
				'rule' => 'numeric',
				'message' => 'Sender ID must be a number.',
			),
			'sender_id-3' => array(
				'rule' => array('maxLength', 10),
				'message' => 'Sender ID must be 10 characters or less.',
			),
		),
		'receiver_id' => array(
			'receiver_id-1' => array(
				'rule' => 'numeric',
				'message' => 'Receiver ID must be a number.',
			),
			'receiver_id-2' => array(
				'rule' => array('maxLength', 10),
				'message' => 'Receiver ID must be 10 characters or less.',
			),
		),
		'receiver_type' => array(
			'receiver_type-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Receiver type must not be empty.',
			),
			'receiver_type-2' => array(
				'rule' => array('inList', array('user', 'group', 'project', 'email')),
				'message' => 'Receiver type must be user, group, project, or email.',
			),
		),
		'message_id' => array(
			'message_id-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Message ID must not be empty.',
			),
			'message_id-2' => array(
				'rule' => 'numeric',
				'message' => 'Message ID must be a number.',
			),
			'message_id-3' => array(
				'rule' => array('maxLength', 10),
				'message' => 'Message ID must be 10 characters or less.',
			),
		),
		'status' => array(
			'status-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Status must not be empty.',
			),
			'status-2' => array(
				'rule' => array('inList', array('unread', 'read')),
				'message' => 'Status must be unread or read.',
			),
		),
		'trash' => array(
			'rule' => array('boolean'),
			'message' => 'Trash must be true (1) or false (0)',
		),
		'type' => array(
			'type-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Type must not be empty.',
			),
			'type-2' => array(
				'rule' => array('inList', array('sent', 'received')),
				'message' => 'Type must be sent or received.',
			),
		),
	);

	var $belongsTo = array(
		'Sender' => array(
			'className' => 'User',
			'foreignKey' => 'sender_id',
		),
		'ReceiverUser' => array(
			'className' => 'User',
			'foreignKey' => 'receiver_id',
		),
		'ReceiverGroup' => array(
			'className' => 'Group',
			'foreignKey' => 'receiver_id',
		),
		'ReceiverProject' => array(
			'className' => 'Project',
			'foreignKey' => 'receiver_id',
		),
		'Message' => array(
			'className' => 'Message',
			'foreignKey' => 'message_id',
		),
		'Parent' => array(
			'className' => 'Inbox',
			'foreignKey' => 'parent_id',
		),
	);

	/**
	 * Converts a record to a ExtJS Store node
	 *
	 * @param array $inbox  Inbox Message
	 * @param array $params Parameters
	 *
	 * @return array ExtJS Store Node
	 */
	function toNode($inbox, $params = array())
	{
		if(!$inbox)
		{
			throw new InvalidArgumentException('Invalid Inbox');
		}

		if(!is_array($inbox))
		{
			throw new InvalidArgumentException('Invalid Inbox');
		}

		if(!is_array($params))
		{
			throw new InvalidArgumentException('Invalid Parameters');
		}

		if(!isset($params['model']))
		{
			$params['model'] = $this->name;
		}

		$model = $params['model'];

		if(!isset($inbox[$model]))
		{
			throw new InvalidArgumentException('Invalid Model Key');
		}

		$required = array(
			'id',
			'status',
			'trash',
			'sender_id',
			'receiver_id',
			'receiver_type',
			'type',
			'template',
		);

		foreach($required as $key)
		{
			if(!array_key_exists($key, $inbox[$model]))
			{
				throw new InvalidArgumentException('Missing ' . strtoupper($key) . ' Key');
			}
		}

		$node = array(
			'id' => $inbox[$model]['id'],
			'from' => $inbox['Sender']['name'],
			'subject' => $inbox['Message']['subject'],
			'body' => nl2br($inbox['Message']['body']),
			'date' => $inbox['Message']['date'],
			'status' => $inbox[$model]['status'],
			'trash' => $inbox[$model]['trash'],

			'sender_id' => $inbox[$model]['sender_id'],
			'receiver_type' => $inbox[$model]['receiver_type'],
			'receiver_id' => $inbox[$model]['receiver_id'],
			'type' => $inbox[$model]['type'],
			'template' => $inbox[$model]['template'],
			'template_data' => '',

			'sessions' => array(
				'from' => 'user:' . $inbox['Sender']['id'],
			),

			'attachments' => array(),
		);

		if(!empty($inbox[$model]['parent_id']))
		{
			if($inbox['Parent']['receiver_type'] == 'group')
			{
				if(isset($inbox['Parent']['ReceiverGroup']))
				{
					$node['group'] = $inbox['Parent']['ReceiverGroup']['name'];
					$node['group_id'] = $inbox['Parent']['receiver_id'];
					$node['sessions']['parent'] = 'group:' . $inbox['Parent']['receiver_id'];

					//$node['from'] = $inbox['Parent']['ReceiverGroup']['name'];
				}
			}
			else if($inbox['Parent']['receiver_type'] == 'project')
			{
				if(isset($inbox['Parent']['ReceiverProject']))
				{
					$node['project_id'] = $inbox['Parent']['receiver_id'];
					$node['project'] = $inbox['Parent']['ReceiverProject']['name'];
					$node['sessions']['parent'] = 'project:' . $inbox['Parent']['receiver_id'];

					//$node['from'] = $inbox['Parent']['ReceiverProject']['name'];
				}
			}
		}

		if($inbox[$model]['receiver_type'] == 'user')
		{
			$node['to']             = $inbox['ReceiverUser']['name'];
			$node['sessions']['to'] = 'user:' . $inbox['ReceiverUser']['id'];
		}
		else if($inbox[$model]['receiver_type'] == 'group')
		{
			$node['to']             = $inbox['ReceiverGroup']['name'];
			$node['sessions']['to'] = 'group:' . $inbox['ReceiverGroup']['id'];
		}
		else if($inbox[$model]['receiver_type'] == 'project')
		{
			$node['to']             = $inbox['ReceiverProject']['name'];
			$node['sessions']['to'] = 'project:' . $inbox['ReceiverProject']['id'];
		}
		else if($inbox[$model]['receiver_type'] == 'email')
		{
			$node['to'] = $inbox[$model]['email'];
			$node['sessions']['to'] = 'external:' . $inbox[$model]['email'];
		}

		if(!empty($inbox[$model]['template_data']))
		{
			$node['template_data'] = (array) json_decode($inbox[$model]['template_data']);
		}

		if(!empty($inbox['Message']['Attachment']))
		{
			foreach($inbox['Message']['Attachment'] as $attachment)
			{
				list($mimetype) = explode(';', $attachment['mimetype']);

				$replace = array(
					'/' => '-',
					'.' => '-',
					'+' => 'p',
				);

				$class = str_replace(array_keys($replace), array_values($replace), $mimetype);

				$node['attachments'][] = array(
					'id' => $attachment['id'],
					'name' => $attachment['name'],
					'mimetype' => $mimetype,
					'class' => 'mimetype-' . $class,
				);
			}
		}

		return $node;
	}
}
?>

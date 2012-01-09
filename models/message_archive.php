<?php
class MessageArchive extends InboxAppModel
{
	var $name = 'MessageArchive';

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
		'subject' => array(
			'rule' => 'notEmpty',
			'message' => 'Subject must not be empty.',
		),
		'date' => array(
			'rule' => 'notEmpty',
			'message' => 'Date must not be empty.',
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
	);

	/**
	 * Converts a record to a ExtJS Store node
	 *
	 * @param array $message Archive Message
	 * @param array $params  Parameters
	 *
	 * @return array ExtJS Store Node
	 */
	function toNode($message, $params = array())
	{
		if(!$message)
		{
			throw new InvalidArgumentException('Invalid Message');
		}

		if(!is_array($message))
		{
			throw new InvalidArgumentException('Invalid Message');
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

		if(!isset($message[$model]))
		{
			throw new InvalidArgumentException('Invalid Model Key');
		}

		$required = array(
			'id',
			'subject',
			'body',
			'date',
		);

		foreach($required as $key)
		{
			if(!array_key_exists($key, $message[$model]))
			{
				throw new InvalidArgumentException('Missing ' . strtoupper($key) . ' Key');
			}
		}

		$node = array(
			'id' => $message[$model]['id'],
			'from' => $message['Sender']['name'],
			'subject' => $message[$model]['subject'],
			'body' => nl2br($message[$model]['body']),
			'date' => $message[$model]['date'],
		);

		if($message[$model]['receiver_type'] == 'user')
		{
			$node['to'] = $message['ReceiverUser']['name'];
		}
		else if($message[$model]['receiver_type'] == 'group')
		{
			$node['to'] = $message['ReceiverGroup']['name'];
		}
		else if($message[$model]['receiver_type'] == 'project')
		{
			$node['to'] = $message['ReceiverProject']['name'];
		}

		return $node;
	}
}
?>

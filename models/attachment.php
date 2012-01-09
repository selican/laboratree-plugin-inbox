<?php
class Attachment extends InboxAppModel
{
	var $name = 'Attachment';

	var $validate = array(
		'message_id' => array(
			'message_id-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Message ID must not be empty.',
			),
			'message_id-2' => array(
				'rule' => 'numeric',
				'message' => 'Message ID must be number.',
			),
			'message_id-3' => array(
				'rule' => array('maxLength', 10),
				'message' => 'Messge ID must be 10 characters or less.',
			),
		),
		'name' => array(
			'name-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Name must not be empty.',
			),
			'name-2' => array(
				'rule' => array('maxLength', 255),
				'message' => 'Name must be 255 characters or less.',
			),
		),
		'mimetype' => array(
			'mimetype-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Type must not be empty.',
			),
			'mimetype-2' => array(
				'rule' => array('maxLength', 255),
				'message' => 'Mimetype must be 255 characters or less.',
			),
		),
		'filename' => array(
			'filename-1' => array(
				'rule' => 'notEmpty',
				'message' => 'Filename must not be empty.',
			),
			'filename-2' => array(
				'rule' => 'isUnique',
				'message' => 'Filename must be unique.',
			),
			'filename-3' => array(
				'rule' => array('maxLength', 40),
				'message' => 'Filename must be 40 characters or less.',
			),
		),
	);

	var $belongsTo = array(
		'Message' => array(
			'className' => 'Message',
			'foreignKey' => 'message_id',
		),
	);

	/**
	 * Converts a record to a ExtJS Store node
	 *
	 * @param array $attachment Attachment
	 * @param array $params     Parameters
	 *
	 * @return array ExtJS Store Node
	 */
	function toNode($attachment, $params = array())
	{
		if(!$attachment)
		{
			throw new InvalidArgumentException('Invalid Attachment');
		}

		if(!is_array($attachment))
		{
			throw new InvalidArgumentException('Invalid Attachment');
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

		if(!isset($attachment[$model]))
		{
			throw new InvalidArgumentException('Invalid Model Key');
		}

		$required = array(
			'id',
			'name',
			'mimetype',
		);

		foreach($required as $key)
		{
			if(!array_key_exists($key, $attachment[$model]))
			{
				throw new InvalidArgumentException('Missing ' . strtoupper($key) . ' Key');
			}
		}

		$node = array(
			'id' => $attachment[$model]['id'],
			'name' => $attachment[$model]['name'],
			'mimetype' => $attachment[$model]['mimetype'],
		);

		return $node;
	}

	/**
	 * Attach Files to a Message
	 *
	 * @param integer $message_id Message ID
	 * @param array   $files      List of Filenames
	 */
	function attach($message_id, $files)
	{
		if(!is_numeric($message_id) || $message_id < 1)
		{
			throw new InvalidArgumentException('Invalid message id.');
		}

		if(!is_array($files))
		{
			throw new InvalidArgumentException('Invalid files.');
		}

		if(isset($files['name']))
		{
			$files = array($files);
		}

		$attached = array();
		foreach($files as $file)
		{
			if(!isset($file['name']))
			{
				continue;
			}

			if(isset($file['filename']))
			{
				if(file_exists($file['filename']))
				{
					$file['content'] = file_get_contents($file['filename']);
				}
			}

			if(isset($file['tmp_name']))
			{
				if(file_exists($file['tmp_name']))
				{
					$file['content'] = file_get_contents($file['tmp_name']);
				}
			}

			$file['filename'] = md5(uniqid('', true));

			if(!isset($file['content']))
			{
				continue;
			}	

			if(!isset($file['mimetype']) || empty($file['mimetype']))
			{
				$finfo = new finfo(FILEINFO_MIME);
				$file['mimetype'] = $finfo->buffer($file['content']);
			}

			$destination = APP . DS . 'attachments' . DS . $file['filename'];

			// TODO: Check output of these functions
			$fp = fopen($destination, 'wb');
			fwrite($fp, $file['content']);
			fclose($fp);

			chmod($destination, 0660);

			$attached[] = $destination;

			$data = array();
			$data[$this->name] = array(
				'message_id' => $message_id,
				'name' => $file['name'],
				'mimetype' => $file['mimetype'],
				'filename' => $file['filename'],
			);	
			$this->create();
			if(!$this->save($data))
			{
				if(file_exists($destination))
				{
					unlink($destination);
				}
			}
		}

		return $attached;
	}
}
?>

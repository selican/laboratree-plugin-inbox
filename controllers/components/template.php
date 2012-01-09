<?php
class TemplateComponent extends Object
{
	var $uses = array(
		'User',
		'Group',
		'Project',
	);

	var $components = array(
		'Session',
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
	}

	function startup(&$controller) {}

	/**
	 * Generate Template for User Message
	 *
	 * @param array $sender Sender
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 * 
	 * @return array Template
	 */
	function user_message($sender = array())
	{
		if(!empty($sender))
		{
			if(!is_array($sender))
			{
				throw new InvalidArgumentException('Invalid sender.');
			}
		}

		try {
			$template = $this->build('user_message', array(), $sender);
		} catch(Exception $e) {
			throw new RuntimeException($e);
		}

		return $template;
	}

	/**
	 * Generates Template for Group Message
	 *
	 * @param integer $group_id Group ID
	 * @param array   $sender   Sender
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 *
	 * @return array Template
	 */
	function group_message($group_id, $sender = array())
	{
		if(empty($group_id) || !is_numeric($group_id) || $group_id < 1)
		{
			throw new InvalidArgumentException('Invalid group id.');
		}

		$this->Group->id = $group_id;
		$group = $this->Group->field('name');
		if(empty($group))
		{
			throw new InvalidArgumentException('Invalid group id.');
		}

		if(!empty($sender))
		{
			if(!is_array($sender))
			{
				throw new InvalidArgumentException('Invalid sender.');
			}
		}

		try {
			$template = $this->build('group_message', array(
				'group' => $group,
				'group_id' => $group_id,
			), $sender);
		} catch(Exception $e) {
			throw new RuntimeException($e);
		}

		return $template;
	}

	/**
	 * Generates Template for Project Message
	 *
	 * @param integer $project_id Project ID
	 * @param array   $sender     Sender
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 *
	 * @return array Template
	 */
	function project_message($project_id, $sender = array())
	{
		if(empty($project_id) || !is_numeric($project_id) || $project_id < 1)
		{
			throw new InvalidArgumentException('Invalid project id.');
		}

		$this->Project->id = $project_id;
		$project = $this->Project->field('name');
		if(empty($project))
		{
			throw new InvalidArgumentException('Invalid project id.');
		}

		if(!empty($sender))
		{
			if(!is_array($sender))
			{
				throw new InvalidArgumentException('Invalid sender.');
			}
		}

		try {
			$template = $this->build('project_message', array(
				'project' => $project,
				'project_id' => $project_id,
			), $sender);
		} catch(Exception $e) {
			throw new RuntimeException($e);
		}

		return $template;
	}

	/**
	 * Generates Template for Group Add
	 *
	 * @param integer $group_id Group ID
	 * @param array   $sender   Sender
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 *
	 * @return array TEmplate
	 */
	function group_add($group_id, $sender = array())
	{
		if(empty($group_id) || !is_numeric($group_id) || $group_id < 1)
		{
			throw new InvalidArgumentException('Invalid group id.');
		}

		$this->Group->id = $group_id;
		$group = $this->Group->field('name');
		if(empty($group))
		{
			throw new InvalidArgumentException('Invalid group id.');
		}

		if(!empty($sender))
		{
			if(!is_array($sender))
			{
				throw new InvalidArgumentException('Invalid sender.');
			}
		}

		$template = $this->build('group_add', array(
			'group' => $group,
			'group_id' => $group_id,
		), $sender);

		return $template;
	}

	/**
	 * Generates Tempalte for Project Add
	 *
	 * @param integer $project_id Project ID
	 * @param array   $sender     Sender
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 *
	 * @return array Template
	 */
	function project_add($project_id, $sender = array())
	{
		if(empty($project_id) || !is_numeric($project_id) || $project_id < 1)
		{
			throw new InvalidArgumentException('Invalid project id.');
		}

		$this->Project->id = $project_id;
		$project = $this->Project->field('name');
		if(empty($project))
		{
			throw new InvalidArgumentException('Invalid project id.');
		}

		if(!empty($sender))
		{
			if(!is_array($sender))
			{
				throw new InvalidArgumentException('Invalid sender.');
			}
		}

		$template = $this->build('project_add', array(
			'project' => $project,
			'project_id' => $project_id,
		), $sender);

		return $template;
	}

	/**
	 * Generates a Template based on Arguments
	 *
	 * @param string $name   Template Name
	 * @param array  $data   Template Data
	 * @param array  $sender Sender
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 *
	 * @return array Template
	 */
	function build($name, $data = array(), $sender = array())
	{
		if(empty($name) || !is_string($name))
		{
			throw new InvalidArgumentException('Invalid name.');
		}

		if(!empty($data))
		{
			if(is_string($data))
			{
				$data = (array) json_decode($data);
			}

			if(!is_array($data))
			{
				throw new InvalidArgumentException('Invalid data.');
			}
		}

		if(!is_array($sender))
		{
			throw new InvalidArgumentException('Invalid sender.');
		}

		if(!isset($data['sender']) || empty($data['sender']))
		{
			$data['sender'] = $this->Session->read('Auth.User.name');
			$data['sender_id'] = $this->Session->read('Auth.User.id');
		}

		if(!empty($sender))
		{
			$data['sender'] = $sender['name'];
			$data['sender_id'] = $sender['id'];
		}

		$template = array(
			'name' => $name,
			'data' => $data,
		);

		return $template;
	}

	/**
	 * Builds Sender Data
	 *
	 * @param array $sender Sender
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return array Sender
	 */
	function sender($sender = array())
	{
		if(!empty($sender))
		{
			if(!is_array($sender))
			{
				throw new InvalidArgumentException('Invalid Sender');
			}
		}

		$default = array(
			'id' => $this->Session->read('Auth.User.id'),
			'name' => $this->Session->read('Auth.User.name'),
		);

		return (!empty($sender)) ? $sender : $default;
	}
}
?>

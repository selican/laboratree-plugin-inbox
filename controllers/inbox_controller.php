<?php
class InboxController extends InboxAppController
{
	var $name = 'Inbox';

	var $uses = array(
		'Inbox.Inbox',
		'Inbox.Attachment',
		'Inbox.Message',
		'Inbox.MessageArchive',
		'Inbox.InboxHash',
		'User',
		'Group',
		'Project',
		'GroupsUsers',
		'ProjectsUsers',
		'Navigation',
	);

	var $components = array(
		'Auth',
		'Security',
		'Session',
		'RequestHandler',
		'InboxCmp',
		'Messaging',
		'Template',
		'Plugin',
	);

	function beforeFilter()
	{
		$this->Auth->allow('link');

		$this->Security->validatePost = false;	

		parent::beforeFilter();
	}

	function index()
	{
		$this->redirect('/inbox/received');
		return;
	}

	/**
	 * Lists User Contacts
	 */
	function contacts()
	{
		if(!$this->RequestHandler->prefers('json'))
		{
			$this->cakeError('error404');
			return;
		}

		$limit = 10;
		if(isset($this->params['form']['limit']))
		{
			$limit = $this->params['form']['limit'];
		}

		if(!is_numeric($limit) || $limit < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Limit'));
			return;
		}

		$start = 0;
		if(isset($this->params['form']['start']))
		{
			$start = $this->params['form']['start'];
		}

		if(!is_numeric($start) || $start < 0)
		{
			$this->cakeError('invalid_field', array('field' => 'Start'));
			return;
		}

		$query = null;
		if(isset($this->params['form']['query']))
		{
			$query = $this->params['form']['query'];
		}

		$nodes = array();

		$contacts = $this->User->contacts($this->Session->read('Auth.User.id'), $query);

		foreach($contacts as $contact)
		{
			if(!isset($contact['User']['type']))
			{
				$contact['User']['type'] = 'user';
			}

			$node = array(
				'id' => $contact['User']['id'],
				'name' => $contact['User']['name'],
				'username' => $contact['User']['username'],
				'token' => $contact['User']['type'] . ':' . $contact['User']['id'],
				'type' => 'User',
				'image' => Router::url('/img/users/default_small.png'),
			);

			if(!empty($user['User']['picture']))
			{
				$node['image'] = Router::url('/img/users/' . $user['User']['picture'] . '_thumb.png');
			}

			$nodes[] = $node;
		}

		$groups = $this->GroupsUsers->find('all', array(
			'conditions' => array(
				'Group.name LIKE' => '%' . $query . '%',
				'GroupsUsers.user_id' => $this->Session->read('Auth.User.id'),
			),
			'recursive' => 1,
		));
		foreach($groups as $group)
		{
			$node = array(
				'id' => $group['Group']['id'],
				'username' => strtolower(Inflector::camelize(Inflector::slug($group['Group']['name']))),
				'name' => $group['Group']['name'],
				'token' => 'group:' . $group['Group']['id'],
				'type' => 'Group',
				'image' => Router::url('/img/groups/default_small.png'),
			);

			if(!empty($group['Group']['picture']))
			{
				$node['image'] = Router::url('/img/groups/' . $group['Group']['picture'] . '_thumb.png');
			}

			$nodes[] = $node;
		}

		$projects = $this->ProjectsUsers->find('all', array(
			'conditions' => array(
				'Project.name LIKE' => '%' . $query . '%',
				'ProjectsUsers.user_id' => $this->Session->read('Auth.User.id'),
			),
			'recursive' => 1,
		));
		foreach($projects as $project)
		{
			$node = array(
				'id' => $project['Project']['id'],
				'username' => strtolower(Inflector::camelize(Inflector::slug($project['Project']['name']))),
				'name' => $project['Project']['name'],
				'token' => 'project:' . $project['Project']['id'],
				'type' => 'Project',
				'image' => Router::url('/img/projects/default_small.png'),
			);

			if(!empty($project['Project']['picture']))
			{
				$node['image'] = Router::url('/img/projects/' . $project['Project']['picture'] . '_thumb.png');
			}

			$nodes[] = $node;
		}

		$total = sizeof($nodes);

		$response = array(
			'success' => true,
			'contacts' => array_slice($nodes, $start, $limit),
			'total' => $total,
		);

		$this->set('contacts', $response);
	}

	/** 
	 * Sends an Inbox Message
	 *
	 * @param integer $inbox_id Inbox ID
	 * @param integer $parent   Parent Inbox ID
	 */
	function send($inbox_id = '', $parent = '')
	{
		if(!empty($inbox_id))
		{
			if(!is_numeric($inbox_id) || $inbox_id < 1)
			{
				$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
				return;
			}

			$inbox = $this->Inbox->find('first', array(
				'conditions' => array(
					'Inbox.id' => $inbox_id,
				),
				'recursive' => -1,
			)); 
			if(empty($inbox)) 
			{ 
				$this->cakeError('invalid_field', array('field' => 'Inbox ID')); 
			} 
		}

		if(!empty($parent))
		{
			$parent = true;
		}

		$this->pageTitle = 'Send New Message - ' . $this->Session->read('Auth.User.name');
		$this->set('pageName', $this->Session->read('Auth.User.name') . ' - Send New Message');
		$this->set('inbox_id', $inbox_id);
		$this->set('parent', $parent);

		$context = array(
			'inbox_id' => $inbox_id,
			'parent_id' => $parent,
		);
		$this->set('context', $context);

		if(!empty($this->data))
		{
			$recipients = array();
			foreach($this->data['Inbox']['tokens'] as $token)
			{
				if(preg_match('/^(user|group|project|email):(\S+)$/', $token, $matches))
				{
					$valid = false;
					try {
						switch($matches[1])
						{
							case 'user':
								$valid = $this->User->find('count', array(
									'conditions' => array(
										'User.id' => $matches[2],
									),
								));
								$template = $this->Template->user_message();
								break;
							case 'group':
								$valid = $this->Group->find('count', array(
									'conditions' => array(
										'Group.id' => $matches[2],
									),
								));
								$template = $this->Template->group_message($matches[2]);
								break;
							case 'project':
								$valid = $this->Project->find('count', array(
									'conditions' => array(
										'Project.id' => $matches[2],
									),
								));
								$template = $this->Template->project_message($matches[2]);
								break;
							case 'email':
								$valid = true;
								$template = $this->Template->user_message();
								break;
						}
					} catch(Exception $e) {
						$this->cakeError('internal_error', array('action' => 'Send', 'resource' => 'Message'));
						return;
					}

					if(!$valid)
					{
						continue;
					}

					if(empty($template))
					{
						continue;
					}

					$recipients[] = array(
						'receiver_type' => $matches[1],
						'receiver_id' => $matches[2],
						'template' => $template,
					);
				}
			}

			if(!empty($recipients))
			{
				$attachments = array();
				if(isset($this->data['Attachment']['attachment']))
				{
					$attachments = $this->data['Attachment']['attachment'];
					unset($this->data['Attachment']);
				}

				try {
					$this->Messaging->send($recipients, $this->data['Message'], $attachments);
				} catch(Exception $e) {
					$this->cakeError('internal_error', array('action' => 'Send', 'resource' => 'Message'));
					return;
				}

				try {
					$this->Plugin->broadcastListeners('inbox.send', array(
						$recipients,
						$this->data['Message'],
						$attachments,
					));
				} catch (Exception $e) {
					$this->cakeError('internal_error', array('action' => 'Send', 'resource' => 'Message'));
					return;
				}

				$this->Session->setFlash('Message Sent Successfully.', 'default', array(), 'status');
				$this->redirect('/inbox/sent');
			}
		}
	}

	/**
	 * Lists Received Inbox Messages
	 */
	function received()
	{
		$limit = 23;
		if(isset($this->params['form']['limit']))
		{
			$limit = $this->params['form']['limit'];
		}

		if(!is_numeric($limit) || $limit < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Limit'));
			return;
		}

		$start = 0;
		if(isset($this->params['form']['start']))
		{
			$start = $this->params['form']['start'];
		}

		if(!is_numeric($start) || $start < 0)
		{
			$this->cakeError('invalid_field', array('field' => 'Start'));
			return;
		}

		$sort_fields = array(
			'from' => 'Sender.name',
			'to' => 'ReceiverUser.name',
			'subject' => 'Message.subject',
			'date' => 'Message.date',
			'status' => 'Inbox.status',
		);

		$sort_field = 'date';
		if(isset($this->params['form']['sort']))
		{
			$sort_field = $this->params['form']['sort'];
		}

		if(!is_string($sort_field) || !isset($sort_fields[$sort_field]))
		{
			$this->cakeError('invalid_field', array('field' => 'Sort Field'));
			return;
		}

		$sort = $sort_fields[$sort_field];

		$dir = 'DESC';
		if(isset($this->params['form']['dir']))
		{
			$dir = $this->params['form']['dir'];
		}

		if(!is_string($dir) || !in_array($dir, array('DESC', 'ASC')))
		{
			$this->cakeError('invalid_field', array('field' => 'Sort Direction'));
			return;	
		}

		$this->pageTitle = 'Received Messages - Inbox - ' . $this->Session->read('Auth.User.name');
		$this->set('pageName', $this->Session->read('Auth.User.name') . ' - Inbox - Received Messages');

		if($this->RequestHandler->prefers('json'))
		{
			$conditions = array(
			);

			$contain = array(
			);

			$messages = $this->Inbox->find('all', array(
				'conditions' => array(
					'Inbox.receiver_id' => $this->Session->read('Auth.User.id'),
					'Inbox.receiver_type' => 'user',
					'Inbox.type' => 'received',
					'Inbox.trash' => 0,
				),
				'contain' => array(
					'Sender',
					'ReceiverUser',
					'ReceiverGroup',
					'ReceiverProject',
					'Message',
					'Message.Attachment',
					'Parent.ReceiverGroup',
					'Parent.ReceiverProject',
				),
				'order' => $sort . ' ' . $dir,
				'limit' => $limit,
				'offset' => $start,
			));

			try {
				$list = $this->Inbox->toList('messages', $messages);
			} catch(Exception $e) {
				$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Inbox Messages'));
				return;
			}

			$list['total'] = $this->Inbox->find('count', array(
				'conditions' => array(
					'Inbox.receiver_id' => $this->Session->read('Auth.User.id'),
					'Inbox.receiver_type' => 'user',
					'Inbox.type' => 'received',
					'Inbox.trash' => 0,
				),
			));
			$this->set('messages', $list);
		}
	}

	/**
	 * Lists Sent Inbox Messages
	 */
	function sent()
	{
		$limit = 23;
		if(isset($this->params['form']['limit']))
		{
			$limit = $this->params['form']['limit'];
		}

		if(!is_numeric($limit) || $limit < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Limit'));
			return;
		}

		$start = 0;
		if(isset($this->params['form']['start']))
		{
			$start = $this->params['form']['start'];
		}

		if(!is_numeric($start) || $start < 0)
		{
			$this->cakeError('invalid_field', array('field' => 'Start'));
			return;
		}

		$sort_fields = array(
			'from' => 'Sender.name',
			'to' => 'ReceiverUser.name',
			'subject' => 'Message.subject',
			'date' => 'Message.date',
			'status' => 'Inbox.status',
		);

		$sort_field = 'date';
		if(isset($this->params['form']['sort']))
		{
			$sort_field = $this->params['form']['sort'];
		}

		if(!is_string($sort_field) || !isset($sort_fields[$sort_field]))
		{
			$this->cakeError('invalid_field', array('field' => 'Sort Field'));
			return;
		}

		$sort = $sort_fields[$sort_field];

		$dir = 'DESC';
		if(isset($this->params['form']['dir']))
		{
			$dir = $this->params['form']['dir'];
		}

		if(!is_string($dir) || !in_array($dir, array('DESC', 'ASC')))
		{
			$this->cakeError('invalid_field', array('field' => 'Sort Direction'));
			return;	
		}

		$fields = array(
			'from' => array('Sender.name'),
			'to' => array(
				'ReceiverUser.name',
				'ReceiverGroup.name',
				'ReceiverProject.name',
			),
			'subject' => array('Message.subject'),
			'date' => array('Message.date'),
			'status' => array('Inbox.status'),
		);

		$this->pageTitle = 'Sent Messages - Inbox - ' . $this->Session->read('Auth.User.name');
		$this->set('pageName', $this->Session->read('Auth.User.name') . ' - Inbox - Sent Messages');

		if($this->RequestHandler->prefers('json'))
		{
			$messages = $this->Inbox->find('all', array(
				'conditions' => array(
					'Inbox.sender_id' => $this->Session->read('Auth.User.id'),
					'Inbox.type' => 'sent',
					'Inbox.trash' => 0
				),
				'order' => $sort . ' ' . $dir,
				'contain' => array(
					'Sender',
					'ReceiverUser',
					'ReceiverGroup',
					'ReceiverProject',
					'Message',
					'Message.Attachment',
					'Parent.ReceiverGroup',
					'Parent.ReceiverProject',
				),
				'limit' => $limit,
				'offset' => $start,
			));

			try {
				$list = $this->Inbox->toList('messages', $messages);
			} catch(Exception $e) {
				$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Inbox Messages'));
				return;
			}
			$list['total'] = $this->Inbox->find('count', array(
				'conditions' => array(
					'Inbox.sender_id' => $this->Session->read('Auth.User.id'),
					'Inbox.type' => 'sent',
					'Inbox.trash' => 0
				),
			));
			
			$this->set('messages', $list);
		}
	}

	/**
	 * Lists Trashed Inbox Messages
	 */
	function trash() 
	{
		$limit = 23;
		if(isset($this->params['form']['limit']))
		{
			$limit = $this->params['form']['limit'];
		}

		if(!is_numeric($limit) || $limit < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Limit'));
			return;
		}

		$start = 0;
		if(isset($this->params['form']['start']))
		{
			$start = $this->params['form']['start'];
		}

		if(!is_numeric($start) || $start < 0)
		{
			$this->cakeError('invalid_field', array('field' => 'Start'));
			return;
		}

		$sort_fields = array(
			'from' => 'Sender.name',
			'to' => 'ReceiverUser.name',
			'subject' => 'Message.subject',
			'date' => 'Message.date',
			'status' => 'Inbox.status',
		);

		$sort_field = 'date';
		if(isset($this->params['form']['sort']))
		{
			$sort_field = $this->params['form']['sort'];
		}

		if(!is_string($sort_field) || !isset($sort_fields[$sort_field]))
		{
			$this->cakeError('invalid_field', array('field' => 'Sort Field'));
			return;
		}

		$sort = $sort_fields[$sort_field];

		$dir = 'DESC';
		if(isset($this->params['form']['dir']))
		{
			$dir = $this->params['form']['dir'];
		}

		if(!is_string($dir) || !in_array($dir, array('DESC', 'ASC')))
		{
			$this->cakeError('invalid_field', array('field' => 'Sort Direction'));
			return;	
		}

		$this->pageTitle = 'Trash - Inbox - ' . $this->Session->read('Auth.User.name');
		$this->set('pageName', $this->Session->read('Auth.User.name') . ' - Inbox - Trash');

		if($this->RequestHandler->prefers('json'))
		{
			$messages = $this->Inbox->find('all', array(
				'conditions' => array(
					'OR' => array(
						array(
							'Inbox.sender_id' => $this->Session->read('Auth.User.id'),
							'Inbox.type' => 'sent',
						),
						array(
							'Inbox.receiver_type' => 'user',
							'Inbox.receiver_id' => $this->Session->read('Auth.User.id'),
							'Inbox.type' => 'received',
						),
					),
					'Inbox.trash' => 1,
				),
				'contain' => array(
					'Sender',
					'ReceiverUser',
					'ReceiverGroup',
					'ReceiverProject',
					'Message',
					'Message.Attachment',
					'Parent.ReceiverGroup',
					'Parent.ReceiverProject',
				),
				'order' => $sort . ' ' . $dir,
				'limit' => $limit,
				'offset' => $start,
			));

			try {
				$list = $this->Inbox->toList('messages', $messages);
			} catch(Exception $e) {
				$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Inbox Messages'));
				return;
			}
			$list['total'] = $this->Inbox->find('count', array(
				'conditions' => array(
					'OR' => array(
						array(
							'Inbox.sender_id' => $this->Session->read('Auth.User.id'),
							'Inbox.type' => 'sent',
						),
						array(
							'Inbox.receiver_type' => 'user',
							'Inbox.receiver_id' => $this->Session->read('Auth.User.id'),
							'Inbox.type' => 'received',
						),
					),
					'Inbox.trash' => 1,
				),
			));
			
			$this->set('messages', $list);
		}
	}

	/**
	 * View Inbox Message
	 *
	 * @param integer $inbox_id Inbox ID
	 */
	function view($inbox_id = '')
	{
		if(empty($inbox_id))
		{
			$this->cakeError('missing_field', array('field' => 'Message ID'));
			return;
		}

		if(!is_numeric($inbox_id) || $inbox_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		$contain = array(
			'Sender',
			'ReceiverUser',
			'ReceiverGroup',
			'ReceiverProject',
			'Message',
			'Message.Attachment',
			'Parent.ReceiverGroup',
			'Parent.ReceiverProject',
		);

		$inbox = $this->Inbox->find('first', array(
			'conditions' => array(
				'Inbox.id' => $inbox_id,
			),
			'recursive' => 2,
		));
		if(empty($inbox))
		{
			$this->cakeError('invalid_field', array('field' => 'Message ID'));
			return;
		}

		if($inbox['Inbox']['sender_id'] != $this->Session->read('Auth.User.id'))
		{
			if($inbox['Inbox']['receiver_type'] != 'user' || $inbox['Inbox']['receiver_id'] != $this->Session->read('Auth.User.id'))
			{
				$this->cakeError('access_denied', array('action' => 'View', 'resource' => 'Message'));
				return;
			}
		}

		$this->pageTitle = 'View Message - ' . $this->Session->read('Auth.User.name');
		$this->set('pageName', $this->Session->read('Auth.User.name') . ' - View Message');

		$this->set('message', $inbox);

		$this->set('inbox_id', $inbox_id);

		$context = array(
			'inbox_id' => $inbox_id,
		);
		$this->set('context', $context);

		if(empty($inbox['Inbox']['template']))
		{
			$inbox['Inbox']['template_type'] = 'user_message';
		}

		$template_data = (array) json_decode($inbox['Inbox']['template_data']);

		switch($inbox['Inbox']['template'])
		{
			case 'group_request':
				$group = $this->Group->find('first', array(
					'conditions' => array(
						'Group.id' => $template_data['group_id'],
					),
					'recursive' => -1,
				));
				$this->set('group', $group);
				break;
			case 'project_request':
				$project = $this->Project->find('first', array(
					'conditions' => array(
						'Project.id' => $template_data['project_id'],
					),
					'recursive' => -1,
				));
				$this->set('project', $project);
				break;
		}

		$this->Inbox->id = $inbox_id;
		$this->Inbox->saveField('status', 'read');

		if($this->RequestHandler->prefers('json'))
		{
			try {
				$node = $this->Inbox->toNode($inbox);
			} catch(Exception $e) {
				$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Inbox Message'));
				return;
			}

			$response = array(
				'success' => true,
				'message' => $node,
				'neighbors' => array(),
			);

			if($inbox['Inbox']['type'] == 'sent')
			{
				$conditions = array(
					'Inbox.sender_id' => $this->Session->read('Auth.User.id'),
					'Inbox.type' => 'sent',
				);
			}
			else if($inbox['Inbox']['type'] == 'received')
			{
				$conditions = array(
					'Inbox.receiver_id' => $this->Session->read('Auth.User.id'),
					'Inbox.receiver_type' => 'user',
					'Inbox.type' => 'received',
				);
			}

			$conditions['Inbox.trash'] = $inbox['Inbox']['trash'];

			$neighbors = $this->Inbox->find('neighbors', array(
				'conditions' => $conditions,
				'field' => 'id',
				'value' => $inbox_id,
				'order' => 'Message.date ASC',
			));
		
			if(!empty($neighbors['prev']))
			{
				$response['neighbors']['prev'] = $neighbors['prev']['Inbox']['id'];
			}
		
			if(!empty($neighbors['next']))
			{
				$response['neighbors']['next'] = $neighbors['next']['Inbox']['id'];
			}

			$this->set('response', $response);
		}
	}

	/**
	 * List Neighbors of the specified message in the Inbox
	 * Used for Previous and Next Buttons
	 *
	 * @param integer $inbox_id Inbox ID
	 */
	function neighbors($inbox_id = '')
	{
		if(!$this->RequestHandler->prefers('json'))
		{
			$this->cakeError('error404');
			return;
		}

		if(empty($inbox_id))
		{
			$this->cakeError('missing_field', array('field' => 'Message ID'));
			return;
		}

		$inbox = $this->Inbox->find('first', array(
			'conditions' => array(
				'Inbox.id' => $inbox_id,
			),
			'recursive' => 1,
		));
		if(empty($inbox))
		{
			$this->cakeError('invalid_field', array('field' => 'Message ID'));
			return;
		}

		if($inbox['Inbox']['sender_id'] != $this->Session->read('Auth.User.id'))
		{
			if($inbox['Inbox']['receiver_type'] != 'user' || $inbox['Inbox']['receiver_id'] != $this->Session->read('Auth.User.id'))
			{
				$this->cakeError('access_denied', array('action' => 'View', 'resource' => 'Message'));
				return;
			}
		}

		if($inbox['Inbox']['type'] == 'sent')
		{
			$conditions = array(
				'Inbox.sender_id' => $this->Session->read('Auth.User.id'),
				'Inbox.type' => 'sent',
			);
		}
		else if($inbox['Inbox']['type'] == 'received')
		{
			$conditions = array(
				'Inbox.receiver_id' => $this->Session->read('Auth.User.id'),
				'Inbox.receiver_type' => 'user',
				'Inbox.type' => 'received',
			);
		}

		$conditions['Inbox.trash'] = $inbox['Inbox']['trash'];

		$neighbors = $this->Inbox->find('neighbors', array(
			'conditions' => $conditions,
			'field' => 'id',
			'value' => $inbox_id,
			'order' => 'Message.date ASC',
		));
		
		$data = array();
		if(!empty($neighbors['prev']))
		{
			$data['prev'] = $neighbors['prev']['Inbox']['id'];
		}
		
		if(!empty($neighbors['next']))
		{
			$data['next'] = $neighbors['next']['Inbox']['id'];
		}

		$this->set('neighbors', $data);
	}

	/**
	 * Downloads the Attachment
	 *
	 * @todo Convert to File Component Download Function
	 * 
	 * @param integer $attachment_id Attachment ID
	 */
	function attachment($attachment_id = '')
	{
		if(empty($attachment_id))
		{
			$this->cakeError('missing_field', array('field' => 'Attachment ID'));
			return;
		}

		$attachment = $this->Attachment->find('first', array(
			'conditions' => array(
				'Attachment.id' => $attachment_id,
			),
			'recursive' => -1,
		));
		if(empty($attachment))
		{
			$this->cakeError('invalid_field', array('field', 'Attachment ID'));
			return;
		}

		$inbox = $this->Inbox->find('count', array(
			'conditions' => array(
				'OR' => array(
					'Inbox.sender_id' => $this->Session->read('Auth.User.id'),
					'AND' => array(
						'Inbox.receiver_type' => 'user',
						'Inbox.receiver_id' => $this->Session->read('Auth.User.id'),
					),
				),
				'Inbox.message_id' => $attachment['Attachment']['message_id'],
			),
		));
		if(empty($inbox))
		{
			$this->cakeError('access_denied', array('action' => 'Download', 'resource' => 'Attachment'));
			return;
		}

		$filename = ATTACHMENTS . DS . $attachment['Attachment']['filename'];
		if(!file_exists($filename))
		{
			$this->cakeError('invalid_field', array('field' => 'Attachment File'));
			return;
		}

		header('Content-Type: ' . addslashes($attachment['Attachment']['mimetype']));
		header('Content-Length: ' . filesize($filename));
		header('Content-Disposition: attachment; filename="' . addslashes($attachment['Attachment']['name']) . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

		if(ob_get_length())
		{
			ob_clean();
		}
		flush();

		readfile($filename);
		exit();
	}

	/**
	 * Entry point for email links to Inbox Messages
	 *
	 * @param integer $inbox_id Inbox ID
	 * @param string  $hash     Inbox Hash
	 * @param string  $action   Action
	 */
	function link($inbox_id = '', $hash = '', $action = 'view')
	{
		if(empty($inbox_id))
		{
			$this->cakeError('invalid_field', array('field' => 'Link'));
			return;
		}

		if(empty($hash))
		{
			$this->cakeError('invalid_field', array('field' => 'Link'));
			return;
		}

		$message = $this->Inbox->find('first', array(
			'conditions' => array(
				'Inbox.id' => $inbox_id,
			),
			'recursive' => -1,
		));
		if(empty($message))
		{
			$this->cakeError('invalid_field', array('field' => 'Link'));
			return;
		}

		$inbox_hash = $this->InboxHash->find('first', array(
			'conditions' => array(
				'InboxHash.inbox_id' => $inbox_id,
				'InboxHash.hash' => $hash,
			),
			'recursive' => -1,
		));
		if(empty($inbox_hash))
		{
			$this->cakeError('invalid_field', array('field' => 'Link'));
			return;
		}

		if(!$this->Auth->user())
		{
			if($action == 'view')
			{
				$url = base64_encode('/l/' . $inbox_id . '/' . $hash);

				$this->redirect('/users/login/' . $url);
				return;
			}
			else if($action == 'register')
			{
				$url = base64_encode('/l/' . $inbox_id . '/' . $hash);

				$this->redirect('/users/register/' . $url);
				return;
			}

			$this->cakeError('invalid_field', array('field' => 'Link'));
			return;
		}

		/* Assign Sent Message to User */
		$data = array(
			'Inbox' => array(
				'id' => $inbox_id,
				'receiver_type' => 'user',
				'receiver_id' => $this->Session->read('Auth.User.id'),
				'email' => null,
			),
		);
		$this->Inbox->save($data);

		/* Delete Inbox Hash */
		$this->InboxHash->delete($inbox_hash['InboxHash']['id']);

		/* Find Received Message */
		$received = $this->Inbox->find('first', array(
			'conditions' => array(
				'Inbox.message_id' => $message['Inbox']['message_id'],
				'Inbox.sender_id' => $message['Inbox']['sender_id'],
				'Inbox.receiver_type' => 'user',
				'Inbox.receiver_id' => $this->Session->read('Auth.User.id'),
				'Inbox.type' => 'received',
			),
			'recursive' => -1,
		));
		if(empty($received))
		{
			try {
				/* Create Received Message */
				$this->InboxCmp->reset();
				$this->InboxCmp->sender_id = $message['Inbox']['sender_id'];
				$this->InboxCmp->receiver_id = $this->Session->read('Auth.User.id');
				$this->InboxCmp->receiver_type = 'user';
				$this->InboxCmp->message_id = $message['Inbox']['message_id'];
				$this->InboxCmp->template = $message['Inbox']['template'];
				$this->InboxCmp->template_data = json_decode($message['Inbox']['template_data']);
				$this->InboxCmp->parent_id = $message['Inbox']['parent_id'];

				$this->InboxCmp->status = 'read';
				$this->InboxCmp->type = 'received';
				$this->InboxCmp->save();

				$inbox_id = $this->InboxCmp->id;
			} catch(Exception $e) {
				$this->cakeError('internal_error', array('action' => 'Create', 'resource' => 'Inbox Message'));
				return;
			}
		}
		else
		{
			$inbox_id = $received['Inbox']['id'];
		}

		$this->redirect('/inbox/view/' . $inbox_id);
		return;
	}

	/**
	 * Marks a Inbox Message as Read
	 *
	 * @param integer $inbox_id Inbox ID
	 */
	function read($inbox_id)
	{
		if(!$this->RequestHandler->prefers('json'))
		{
			$this->cakeError('error404');
			return;
		}

		if(empty($inbox_id))
		{
			$this->cakeError('missing_field', array('field' => 'Inbox ID'));
			return;
		}

		if(!is_numeric($inbox_id) || $inbox_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		$inbox = $this->Inbox->find('first', array(
			'conditions' => array(
				'Inbox.id' => $inbox_id,
			),
			'recursive' => 1,
		));
		if(empty($inbox))
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		if($inbox['Inbox']['sender_id'] != $this->Session->read('Auth.User.id'))
		{
			if($inbox['Inbox']['receiver_type'] != 'user' || $inbox['Inbox']['receiver_id'] != $this->Session->read('Auth.User.id'))
			{
				$this->cakeError('access_denied', array('action' => 'Modify', 'resource' => 'Inbox Message'));
				return;
			}
		}

		$this->Inbox->id = $inbox_id;
		$this->Inbox->saveField('status', 'read');

		try {
			$this->Plugin->broadcastListeners('inbox.read', array(
				$inbox_id,
			));
		} catch(Exception $e) {
			$this->cakeError('internal_error', array('action' => 'Modify', 'resource' => 'Inbox Message'));
			return;
		}

		$response = array(
			'success' => true,
		);

		$this->set('response', $response);
	}

	/**
	 * Marks an Inbox Message as Unread
	 *
	 * @param integer $inbox_id Inbox ID
	 */
	function unread($inbox_id)
	{
		if(!$this->RequestHandler->prefers('json'))
		{
			$this->cakeError('error404');
			return;
		}

		if(empty($inbox_id))
		{
			$this->cakeError('missing_field', array('field' => 'Inbox ID'));
			return;
		}

		if(!is_numeric($inbox_id) || $inbox_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		$inbox = $this->Inbox->find('first', array(
			'conditions' => array(
				'Inbox.id' => $inbox_id,
			),
			'recursive' => 1,
		));
		if(empty($inbox))
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		if($inbox['Inbox']['sender_id'] != $this->Session->read('Auth.User.id'))
		{
			if($inbox['Inbox']['receiver_type'] != 'user' || $inbox['Inbox']['receiver_id'] != $this->Session->read('Auth.User.id'))
			{
				$this->cakeError('access_denied', array('action' => 'Modify', 'resource' => 'Inbox Message'));
				return;
			}
		}

		$this->Inbox->id = $inbox_id;
		$this->Inbox->saveField('status', 'unread');

		try {
			$this->Plugin->broadcastListeners('inbox.unread', array(
				$inbox_id,
			));
		} catch(Exception $e) {
			$this->cakeError('internal_error', array('action' => 'Modify', 'resource' => 'Inbox Message'));
			return;
		}


		$response = array(
			'success' => true,
		);

		$this->set('response', $response);
	}

	/**
	 * Deletes an Inbox Message
	 *
	 * @param integer $inbox_id Inbox ID
	 */
	function delete($inbox_id = '')
	{
		if(!$this->RequestHandler->prefers('json'))
		{
			$this->cakeError('error404');
			return;
		}

		if(empty($inbox_id))
		{
			$this->cakeError('missing_field', array('field' => 'Inbox ID'));
			return;
		}

		if(!is_numeric($inbox_id) || $inbox_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		$inbox = $this->Inbox->find('first', array(
			'conditions' => array(
				'Inbox.id' => $inbox_id,
			),
			'recursive' => 1,
		));
		if(empty($inbox))
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		if($inbox['Inbox']['sender_id'] != $this->Session->read('Auth.User.id'))
		{
			if($inbox['Inbox']['receiver_type'] != 'user' || $inbox['Inbox']['receiver_id'] != $this->Session->read('Auth.User.id'))
			{
				$this->cakeError('access_denied', array('action' => 'Delete', 'resource' => 'Inbox Message'));
				return;
			}
		}

		if($inbox['Inbox']['trash'] == 0)
		{
			$this->Inbox->id = $inbox_id;
			$this->Inbox->saveField('trash', 1);
		}
		else
		{
			$this->Inbox->delete($inbox_id);

			$inboxes = $this->Inbox->find('count', array(
				'conditions' => array(
					'Inbox.message_id' => $inbox['Inbox']['message_id'],
				),
			));
			if(empty($inboxes))
			{
				$this->Message->delete($inbox['Inbox']['message_id'], true);
			}
		}

		try {
			$this->Plugin->broadcastListeners('inbox.delete', array(
				$inbox_id,
			));
		} catch(Exception $e) {
			$this->cakeError('internal_error', array('action' => 'Delete', 'resource' => 'Inbox Message'));
			return;
		}

		$response = array(
			'success' => true,
		);

		$this->set('response', $response);
	}

	/**
	 * Restores an Inbox Message out of Trash
	 *
	 * @param integer $inbox_id Inbox ID
	 */
	function restore($inbox_id = '')
	{
		if(!$this->RequestHandler->prefers('json'))
		{
			$this->cakeError('error404');
			return;
		}

		if(empty($inbox_id))
		{
			$this->cakeError('missing_field', array('field' => 'Inbox ID'));
			return;
		}

		if(!is_numeric($inbox_id) || $inbox_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		$inbox = $this->Inbox->find('first', array(
			'conditions' => array(
				'Inbox.id' => $inbox_id,
			),
			'recursive' => 1,
		));
		if(empty($inbox))
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		if($inbox['Inbox']['sender_id'] != $this->Session->read('Auth.User.id'))
		{
			if($inbox['Inbox']['receiver_type'] != 'user' || $inbox['Inbox']['receiver_id'] != $this->Session->read('Auth.User.id'))
			{
				$this->cakeError('access_denied', array('action' => 'Restore', 'resource' => 'Inbox Message'));
				return;
			}
		}

		if($inbox['Inbox']['trash'] == 0)
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		$this->Inbox->id = $inbox_id;
		$this->Inbox->saveField('trash', 0);

		try {
			$this->Plugin->broadcastListeners('inbox.restore', array(
				$inbox_id,
			));
		} catch(Exception $e) {
			$this->cakeError('internal_error', array('action' => 'Restore', 'resource' => 'Inbox Message'));
			return;
		}

		$response = array(
			'success' => true,
		);

		$this->set('response', $response);
	}

	/**
	 * Retrieve Inbox Message Data
	 *
	 * @todo Remove this
	 * @deprecated
	 *
	 * @param integer $inbox_id Inbox ID
	 */
	function data($inbox_id = '')
	{
		if(!$this->RequestHandler->prefers('json'))
		{
			$this->cakeError('error404');
			return;
		}

		if(empty($inbox_id))
		{
			$this->cakeError('missing_field', array('field' => 'Inbox ID'));
			return;
		}

		if(!is_numeric($inbox_id) || $inbox_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		$inbox = $this->Inbox->find('first', array(
			'conditions' => array(
				'Inbox.id' => $inbox_id,
			),
			'contain' => array(
				'Sender',
				'ReceiverUser',
				'ReceiverGroup',
				'ReceiverProject',
				'Message',
				'Message.Attachment',
				'Parent.ReceiverGroup',
				'Parent.ReceiverProject',
			),
		));
		if(empty($inbox))
		{
			$this->cakeError('invalid_field', array('field' => 'Inbox ID'));
			return;
		}

		if($inbox['Inbox']['sender_id'] != $this->Session->read('Auth.User.id'))
		{
			if($inbox['Inbox']['receiver_type'] != 'user' || $inbox['Inbox']['receiver_id'] != $this->Session->read('Auth.User.id'))
			{
				$this->cakeError('access_denied', array('action' => 'View', 'resource' => 'Inbox Data'));
				return;
			}
		}

		try {
			$node = $this->Inbox->toNode($inbox);
		} catch(Exception $e) {
			$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Inbox Message'));
			return;
		}

		$this->set('node', $node);
	}

	/**
	 * List Archived Inbox Messages for a Group or Project
	 *
	 * @param string  $table_type Table Type
	 * @param integer $table_id   Table ID
	 */
	function archives($table_type = '', $table_id = '')
	{
		$limit = 11;
		if(isset($this->params['form']['limit']))
		{
			$limit = $this->params['form']['limit'];
		}

		if(!is_numeric($limit) || $limit < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Limit'));
			return;
		}

		$start = 0;
		if(isset($this->params['form']['start']))
		{
			$start = $this->params['form']['start'];
		}

		if(!is_numeric($start) || $start < 0)
		{
			$this->cakeError('invalid_field', array('field' => 'Start'));
			return;
		}

		$sort_fields = array(
			'from' => 'Sender.name',
			'subject' => 'MessageArchive.subject',
			'date' => 'MessageArchive.date',
		);

		$sort_field = 'date';
		if(isset($this->params['form']['sort']))
		{
			$sort_field = $this->params['form']['sort'];
			
		}

		if(!is_string($sort_field) || !isset($sort_fields[$sort_field]))
		{
			$this->cakeError('invalid_field', array('field' => 'Sort Field'));
			return;
		}

		$sort = $sort_fields[$sort_field];

		$dir = 'DESC';
		if(isset($this->params['form']['dir']))
		{
			$dir = $this->params['form']['dir'];
		}

		if(!is_string($dir) || !in_array($dir, array('DESC', 'ASC')))
		{
			$this->cakeError('invalid_field', array('field' => 'Sort Direction'));
			return;	
		}

		if(empty($table_type))
		{
			$this->cakeError('missing_field', array('field' => 'Table Type'));
			return;
		}

		if(!is_string($table_type) || !in_array($table_type, array('user', 'group', 'project')))
		{
			$this->cakeError('invalid_field', array('field' => 'Table Type'));
			return;
		}

		if(empty($table_id))
		{
			$this->cakeError('missing_field', array('field' => 'Table ID'));
			return;
		}

		if(!is_numeric($table_id) || $table_id < 1)
		{
			$this->cakeError('invalid_field', array('field' => 'Table ID'));
			return;
		}

		$role = $this->get_role($table_type, $table_id);
		if($role === false)
		{
			$this->cakeError('access_denied', array('action' => 'View', 'resource' => 'Message Archives'));
			return;
		}

		$name = null;
		switch($table_type)
		{
			case 'user':
				$this->User->id = $table_id;
				$name = $this->User->field('name');
				break;
			case 'group':
				$this->Group->id = $table_id;
				$name = $this->Group->field('name');
				$this->set('group_id', $table_id);
				break;
			case 'project':
				$this->Project->id = $table_id;
				$name = $this->Project->field('name');
				$group_id = $this->Project->field('group_id');

				$this->set('project_id', $table_id);

				$group = $this->Group->find('first', array(
					'conditions' => array(
						'Group.id' => $group_id,
					),
					'recursive' => -1,
				));
				if(empty($group))
				{
					$this->cakeError('internal_error', array('action' => 'View', 'resource' => 'Message Archives'));
					return;
				}

				$this->set('group_name', $group['Group']['name']);
				$this->set('group_id', $group['Group']['id']);
				break;
			default:
				$this->cakeError('invalid_field', array('field' => 'Table Type'));
				return;
		}

		$this->pageTitle = 'Message Archives - ' . $name;
		$this->set('pageName', $name . ' - Message Archive');

		$this->set('name', $name);

		$this->set('table_type', $table_type);
		$this->set('table_id', $table_id);

		$context = array(
			'table_type' => $table_type,
			'table_id' => $table_id,
		);
		$this->set('context', $context);

		if($this->RequestHandler->prefers('json'))
		{
			$action = 'list';
			if(isset($this->params['form']['action']))
			{
				$action = $this->params['form']['action'];
			}

			$response = array(
				'success' => false,
			);

			switch($action)
			{
				case 'list':
					$messages = $this->MessageArchive->find('all', array(
						'conditions' => array(
							'MessageArchive.receiver_type' => $table_type,
							'MessageArchive.receiver_id' => $table_id,
						),
						'order' => $sort . ' ' . $dir,
						'recursive' => 1,
						'limit' => $limit,
						'offset' => $start,
					));
					try {
						$response = $this->MessageArchive->toList('messages', $messages);
					} catch(Exception $e) {
						$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Message Archives'));
						return;
					}

					$response['total'] = $this->MessageArchive->find('count', array(
						'conditions' => array(
							'MessageArchive.receiver_type' => $table_type,
							'MessageArchive.receiver_id' => $table_id,
						),
					));
					break;
				case 'view':
					if(!isset($this->params['form']['archive_id']))
					{
						$this->cakeError('missing_field', array('field' => 'Archive ID'));
						return;
					}

					$archive_id = $this->params['form']['archive_id'];

					if(!is_numeric($archive_id) || $archive_id < 1)
					{
						$this->cakeError('invalid_field', array('field' => 'Archive ID'));
						return;
					}

					$message = $this->MessageArchive->find('first', array(
						'conditions' => array(
							'MessageArchive.id' => $archive_id,
						),
						'recursive' => 1,
					));
					if(empty($message))
					{
						$this->cakeError('invalid_field', array('field' => 'Archive ID'));
						return;
					}

					try {
						$node = $this->MessageArchive->toNode($message);
					} catch(Exception $e) {
						$this->cakeError('internal_error', array('action' => 'Convert', 'resource' => 'Message Archive'));
						return;
					}
					$response = array(
						'success' => true,
						'message' => $node,
					);
					break;
				default:
					$this->cakeError('invalid_field', array('field' => 'Action'));
					return;
			}

			$this->set('response', $response);
		}
	}

	/**
	 * Help for Send
	 */
	function help_send()
	{
		$this->pageTitle = 'Help - Inbox - Send';
		$this->set('pageName', 'Send - Inbox - Help');
	}

	/**
	 * Help for Received
	 */
	function help_received()
	{
		$this->pageTitle = 'Help - Inbox - Received';
		$this->set('pageName', 'Received - Inbox - Help');
	}

	/**
	 * Help for Sent
	 */
	function help_sent()
	{
		$this->pageTitle = 'Help - Inbox - Sent';
		$this->set('pageName', 'Sent - Inbox - Help');
	}

	/**
	 * Help for Trash
	 */
	function help_trash()
	{
		$this->pageTitle = 'Help - Inbox - Trash';
		$this->set('pageName', 'Trash - Inbox - Help');
	}

	/**
	 * Help for View
	 */
	function help_view()
	{
		$this->pageTitle = 'Help - View - Inbox';
		$this->set('pageName', 'Inbox - View - Help');
	}

	/**
	 * Help for Archives
	 */
	function help_archives()
	{
		$this->pageTitle = 'Help - Archives - Inbox';
		$this->set('pageName', 'Inbox - Archives - Help');
	}
}
?>

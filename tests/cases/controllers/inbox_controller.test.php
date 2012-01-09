<?php
App::import('Controller','Inbox');
App::import('Component', 'RequestHandler');
App::import('Component', 'Messaging');

Mock::generatePartial('RequestHandlerComponent', 'InboxControllerMockRequestHandlerComponent', array('prefers'));

class InboxControllerTestMessagingComponent extends MessagingComponent {
	function initialize(&$controller, $settings = array())
	{
		$this->Controller =& $controller;

		parent::initialize($controller, $settings);
	}

	function startup(&$controller) {}

	function email($email, $message, $subject, $template, $attachments = array(), $replyto = '')
	{
		return true;
	}
}

class InboxControllerTestInboxController extends InboxController {
	var $name = 'Inbox';
	var $autoRender = false;

	var $redirectUrl = null;
	var $components = array(
		'InboxControllerTestMessaging',
	);
	var $renderedAction = null;
	var $error = null;
	var $stopped = null;
	
	function redirect($url, $status = null, $exit = true)
	{
		$this->redirectUrl = $url;
	}
	function render($action = null, $layout = null, $file = null)
	{
		$this->renderedAction = $action;
	}

	function cakeError($method, $messages = array())
	{
		if(!isset($this->error))
		{
			$this->error = $method;
		}
	}
	function _stop($status = 0)
	{
		$this->stopped = $status;
	}
}

class InboxControllerTest extends CakeTestCase {
	var $Inbox = null;
	var $fixtures = array('app.helps', 'app.app_category', 'app.app_data', 'app.application', 'app.app_module', 'app.attachment', 'app.digest', 'app.discussion', 'app.doc', 'app.docs_permission', 'app.docs_tag', 'app.docs_type_data', 'app.docs_type_field', 'app.docs_type', 'app.docs_type_row', 'app.docs_version', 'app.group', 'app.groups_address', 'app.groups_association', 'app.groups_award', 'app.groups_interest', 'app.groups_phone', 'app.groups_projects', 'app.groups_publication', 'app.groups_setting', 'app.groups_url', 'app.groups_users', 'app.inbox', 'app.inbox_hash', 'app.interest', 'app.message_archive', 'app.message', 'app.note', 'app.ontology_concept', 'app.preference', 'app.project', 'app.projects_association', 'app.projects_interest', 'app.projects_setting', 'app.projects_url', 'app.projects_users', 'app.role', 'app.setting', 'app.site_role', 'app.tag', 'app.type', 'app.url', 'app.user', 'app.users_address', 'app.users_association', 'app.users_award', 'app.users_education', 'app.users_interest', 'app.users_job', 'app.users_phone', 'app.users_preference', 'app.users_publication', 'app.users_url', 'app.ldap_user');
	
	function startTest() {
		$this->Inbox = new InboxControllerTestInboxController();
		$this->Inbox->constructClasses();
		$this->Inbox->Component->initialize($this->Inbox);
		
		$this->Inbox->Session->write('Auth.User', array(
			'id' => 1,
			'name' => 'Test User',
			'username' => 'testuser',
			'changepass' => 0,
		));
	}
	
	function testInboxControllerInstance() {
		$this->assertTrue(is_a($this->Inbox, 'InboxController'));
	}

	function testIndex()
	{
		$this->Inbox->params = Router::parse('inbox/index/');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->index();
		
		$this->assertEqual($this->Inbox->redirectUrl, '/inbox/received');
	}

	function testContacts()
	{
		$this->Inbox->params = Router::parse('inbox/contacts.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->contacts();
		$this->assertTrue(isset($this->Inbox->viewVars['contacts']));
		$contacts = $this->Inbox->viewVars['contacts'];

		$expected = array(
			'success' => 1,
			'contacts' => array(
				array(
					'id' => $contacts['contacts'][0]['id'],
					'name' => 'Another User',
					'username' => 'anotheruser',
					'token' => 'user:2',
					'type' => 'User',
					'image' => '/img/users/default_small.png',
				),
				array(
					'id' => $contacts['contacts'][1]['id'],
					'name' => 'Fifth User',
					'username' => 'fifthuser',
					'token' => 'user:5',
					'type' => 'User',
					'image' => '/img/users/default_small.png',
				),
				array(
					'id' => $contacts['contacts'][2]['id'],
					'name' => 'Fourth User',
					'username' => 'fourthuser',
					'token' => 'user:4',
					'type' => 'User',
					'image' => '/img/users/default_small.png',
				),
                                array(
                                        'id' => $contacts['contacts'][3]['id'],
                                        'name' => 'Selenium Tester',
                                        'username' => 'selenium1',
                                        'token' => 'user:9090',
                                        'type' => 'User',
                                        'image' => '/img/users/default_small.png',
                                ),

				array(
					'id' => $contacts['contacts'][4]['id'],
					'name' => 'Sixth User',
					'username' => 'sixthuser',
					'token' => 'user:6',
					'type' => 'User',
					'image' => '/img/users/default_small.png',
				),
				array(
					'id' => $contacts['contacts'][5]['id'],
					'name' => 'Third User',
					'username' => 'thirduser',
					'token' => 'user:3',
					'type' => 'User',
					'image' => '/img/users/default_small.png',
				),
				array(
					'id' => $contacts['contacts'][6]['id'],
					'username' => 'privatetestgroup',
					'name' => 'Private Test Group',
					'token' => 'group:1',
					'type' => 'Group',
					'image' => '/img/groups/default_small.png',
				),
				array(
					'id' => $contacts['contacts'][7]['id'],
					'username' => 'anotherprivatetestgroup',
					'name' => 'Another Private Test Group',
					'token' => 'group:3',
					'type' => 'Group',
					'image' => '/img/groups/default_small.png',
				),
				array(
					'id' => $contacts['contacts'][8]['id'],
					'username' => 'privatetestproject',
					'name' => 'Private Test Project',
					'token' => 'project:1',
					'type' => 'Project',
					'image' => '/img/projects/default_small.png',
				),
				array(
					'id' => $contacts['contacts'][9]['id'],
					'username' => 'anotherprivatetestproject',
					'name' => 'Another Private Test Project',
					'token' => 'project:3',
					'type' => 'Project',
					'image' => '/img/projects/default_small.png',
				),

			),
			'total' => $contacts['total'],
		);

		$this->assertEqual($expected, $contacts);
	}

	function testContactsNotJson()
	{
		$this->Inbox->params = Router::parse('inbox/contacts');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', false);

		$this->Inbox->contacts();

		$this->assertEqual($this->Inbox->error, 'error404');
	}

	function testContactsInvalidLimit()
	{
		$this->Inbox->params = Router::parse('inbox/contacts.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['limit'] = 'invalid';

		$this->Inbox->contacts();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testContactsInvalidStart()
	{
		$this->Inbox->params = Router::parse('inbox/contacts.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['start'] = 'invalid';

		$this->Inbox->contacts();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testSend()
	{
		$inbox_id = 9;
		$parent = null;
		$body = 'Testing the send function.';
		$subject = 'Inbox Send Test Message';

		$this->Inbox->data = array(
			'Inbox' => array(
				'tokens' => array(
					'group:1',
				),
			),
			'Message' => array(
				'body' => $body,
				'subject' => $subject,
			),
		);

		$this->Inbox->params = Router::parse('inbox/send/' . $inbox_id . '/' . $parent);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		/*
		 * We can't mock up the MessagingComponent because
		 * it relies on several other components that
		 * wouldn't be loaded. Instead, we extend the
		 * messaging component with our custom messaging
		 * component, and replace it here.
		 */
		$this->Inbox->Messaging = $this->Inbox->InboxControllerTestMessaging;

		$this->Inbox->send($inbox_id, $parent);

		//Check Message Table
		$conditions = array(
			'Message.subject' => $subject,
			'Message.body' => $body,
		);
		$this->Inbox->Message->recursive = -1;
		$result = $this->Inbox->Message->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$message_id = $result['Message']['id'];

		$expected = array(
			'Message' => array(
				'id' => $message_id,
				'subject' => $subject,
				'body' => $body,
				'date' => $result['Message']['date'],
			),
		);
		$this->assertEqual($result, $expected);

		//Check Inbox Table
		$conditions = array(
			'Inbox.sender_id' => $this->Inbox->Session->read('Auth.User.id'),
			'Inbox.receiver_id' => 1,
			'Inbox.receiver_type' => 'group',
			'Inbox.template' => 'group_message',
			'Inbox.type' => 'received',
			'Inbox.message_id' => $message_id,
		);
		$this->Inbox->Inbox->recursive = -1;
		$result = $this->Inbox->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$inbox_id = $result['Inbox']['id'];

		$expected = array(
			'Inbox' => array(
				'id' => $inbox_id,
				'sender_id' => $this->Inbox->Session->read('Auth.User.id'),
				'receiver_id' => 1,
				'receiver_type' => 'group',
				'message_id' => $message_id,
				'template' => 'group_message',
				'template_data' => $result['Inbox']['template_data'],
				'status' => 'unread',
				'trash' => 0,
				'type' => 'received',
				'email' => null,
				'parent_id' => null,
			),
		);
		$this->assertEqual($result, $expected);

		//Check User Inbox Table
		$conditions = array(
			'Inbox.sender_id' => $this->Inbox->Session->read('Auth.User.id'),
			'Inbox.receiver_type' => 'user',
			'Inbox.template' => 'group_message',
			'Inbox.type' => 'received',
			'Inbox.message_id' => $message_id,
			'Inbox.parent_id' => $inbox_id,
		);
		$this->Inbox->Inbox->recursive = -1;
		$results = $this->Inbox->Inbox->find('all', array('conditions' => $conditions));
		$this->assertFalse(empty($results));

		$expected = array(
			array(
				'Inbox' => array(
					'id' => $results[0]['Inbox']['id'],
					'sender_id' => $this->Inbox->Session->read('Auth.User.id'),
					'receiver_id' => $results[0]['Inbox']['receiver_id'],
					'receiver_type' => 'user',
					'message_id' => $message_id,
					'template' => 'group_message',
					'template_data' => $result['Inbox']['template_data'],
					'status' => 'unread',
					'trash' => 0,
					'type' => 'received',
					'email' => null,
					'parent_id' => $inbox_id,
				),
			),
			array(
				'Inbox' => array(
					'id' => $results[1]['Inbox']['id'],
					'sender_id' => $this->Inbox->Session->read('Auth.User.id'),
					'receiver_id' => $results[1]['Inbox']['receiver_id'],
					'receiver_type' => 'user',
					'message_id' => $message_id,
					'template' => 'group_message',
					'template_data' => $result['Inbox']['template_data'],
					'status' => 'unread',
					'trash' => 0,
					'type' => 'received',
					'email' => null,
					'parent_id' => $inbox_id,
				),
			),
		);
		$this->assertEqual($results, $expected);
		
		$this->assertEqual($this->Inbox->redirectUrl, '/inbox/sent');
	}

	function testSendNullInboxId()
	{
		$inbox_id = null;
		$parent = null;

		$this->Inbox->params = Router::parse('inbox/send/' . $inbox_id . '/' . $parent);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->send($inbox_id, $parent);
		
		$this->assertEqual($this->Inbox->error, null);
	}

	function testSendInvalidInboxId()
	{
		$inbox_id = 'invalid';
		$parent = null;

		$this->Inbox->params = Router::parse('inbox/send/' . $inbox_id . '/' . $parent);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->send($inbox_id, $parent);
		
		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testSendInvalidInboxIdNotFound()
	{
		$inbox_id = 9000;
		$parent = null;

		$this->Inbox->params = Router::parse('inbox/send/' . $inbox_id . '/' . $parent);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->send($inbox_id, $parent);
		
		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testReceived()
	{
		$this->Inbox->params = Router::parse('inbox/received.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->received();

		$this->assertTrue(isset($this->Inbox->viewVars['messages']));
		$messages = $this->Inbox->viewVars['messages'];
		$this->assertTrue($messages['success']);

		$expected = array(
			'success' => 1,
			'messages' => array(
				array(
					'id' => $messages['messages'][0]['id'],
					'from' => 'Test User',
					'subject' => 'Test',
					'body' => 'Test',
					'date' => $messages['messages'][0]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '1',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'user_message',
					'template_data' => array(
						'sender' => 'Test User',
						'sender_id' => '1',
					),
					'sessions' => array(
						'from' => 'user:1',
						'to' => 'user:1',
					),
					'attachments' => array(
						array(
							'id' => '1',
							'name' => 'first.png',
							'mimetype' => 'image/png',
							'class' => 'mimetype-image-png',
						),
					),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][1]['id'],
					'from' => 'Another User',
					'subject' => 'Group Invite',
					'body' => 'Group Invite From User 2 to User 1 for Group 1',
					'date' => $messages['messages'][1]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '2',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'group_invite',
					'template_data' => array(
						'sender' => 'Another User',
						'sender_id' => '2',
						'group' => 'Public Test Group',
						'group_id' => '2',
					),
					'sessions' => array(
						'from' => 'user:2',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][2]['id'],
					'from' => 'Test User',
					'subject' => 'Group Invite',
					'body' => 'Group Invite From User 2 to User 1 for Group 1',
					'date' => $messages['messages'][2]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '1',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'group_invite',
					'template_data' => array(
						'sender' => 'Test User',
						'sender_id' => '1',
						'group' => 'Test Group',
						'group_id' => '1',
					),
					'sessions' => array(
						'from' => 'user:1',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][3]['id'],
					'from' => 'Third User',
					'subject' => 'Group Invite',
					'body' => 'Group Invite From User 2 to User 1 for Group 1',
					'date' => $messages['messages'][3]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '3',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'group_request',
					'template_data' => array(
						'sender' => 'Third User',
						'sender_id' => '3',
						'group' => 'Test Group',
						'group_id' => '1',
					),
					'sessions' => array(
						'from' => 'user:3',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][4]['id'],
					'from' => 'Test User',
					'subject' => 'Group Invite',
					'body' => 'Group Invite From User 2 to User 1 for Group 1',
					'date' => $messages['messages'][4]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '1',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'group_request',
					'template_data' => array(
						'sender' => 'Test User',
						'sender_id' => '1',
						'group' => 'Test Group',
						'group_id' => '1',
					),
					'sessions' => array(
						'from' => 'user:1',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][5]['id'],
					'from' => 'Another User',
					'subject' => 'Group Invite',
					'body' => 'Group Invite From User 2 to User 1 for Group 1',
					'date' => $messages['messages'][5]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '2',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'user_message',
					'template_data' => array(
						'sender' => 'Another User',
						'sender_id' => '2',
					),
					'sessions' => array(
						'from' => 'user:2',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][6]['id'],
					'from' => 'Another User',
					'subject' => 'RE: Test',
					'body' => 'Reply to Test',
					'date' => $messages['messages'][6]['date'],
					'status' => 'read',
					'trash' => '0',
					'sender_id' => '2',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'user_message',
					'template_data' => array(
						'sender' => 'Another User',
						'sender_id' => '2',
					),
					'sessions' => array(
						'from' => 'user:2',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][7]['id'],
					'from' => 'Another User',
					'subject' => 'Group Invite',
					'body' => 'Group Invite From User 2 to User 1 for Group 1',
					'date' => $messages['messages'][7]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '2',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'project_invite',
					'template_data' => array(
						'sender' => 'Another User',
						'sender_id' => '2',
						'project' => 'Public Test Project',
						'project_id' => '2',
					),
					'sessions' => array(
						'from' => 'user:2',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][8]['id'],
					'from' => 'Test User',
					'subject' => 'Group Invite',
					'body' => 'Group Invite From User 2 to User 1 for Group 1',
					'date' => $messages['messages'][8]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '1',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'project_invite',
					'template_data' => array(
						'sender' => 'Test User',
						'sender_id' => '1',
						'group' => 'Test Project',
						'project_id' => '1',
					),
					'sessions' => array(
						'from' => 'user:1',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][9]['id'],
					'from' => 'Third User',
					'subject' => 'Group Invite',
					'body' => 'Group Invite From User 2 to User 1 for Group 1',
					'date' => $messages['messages'][9]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '3',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'project_request',
					'template_data' => array(
						'sender' => 'Third User',
						'sender_id' => '3',
						'group' => 'Test Project',
						'project_id' => '1',
					),
					'sessions' => array(
						'from' => 'user:3',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
				array(
					'id' => $messages['messages'][10]['id'],
					'from' => 'Test User',
					'subject' => 'Group Invite',
					'body' => 'Group Invite From User 2 to User 1 for Group 1',
					'date' => $messages['messages'][10]['date'],
					'status' => 'unread',
					'trash' => '0',
					'sender_id' => '1',
					'receiver_type' => 'user',
					'receiver_id' => '1',
					'type' => 'received',
					'template' => 'project_request',
					'template_data' => array(
						'sender' => 'Test User',
						'sender_id' => '1',
						'group' => 'Test Project',
						'project_id' => '1',
					),
					'sessions' => array(
						'from' => 'user:1',
						'to' => 'user:1',
					),
					'attachments' => array(),
					'to' => 'Test User',
				),
			),
			'total' => $messages['total'],
		);

		$this->assertEqual($messages, $expected);
	}

	function testReceivedInvalidLimit()
	{
		$limit = 'invalid';

		$this->Inbox->params = Router::parse('inbox/received.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['limit'] = $limit;
		$this->Inbox->received();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}
	
	function testReceivedInvalidStart()
	{
		$start = 'invalid';

		$this->Inbox->params = Router::parse('inbox/received.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['start'] = $start;
		$this->Inbox->received();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testReceivedInvalidSortField()
	{
		$sort = 'invalid';

		$this->Inbox->params = Router::parse('inbox/received.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['sort'] = $sort;
		$this->Inbox->received();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testReceivedInvalidSortDirection()
	{
		$dir = 'invalid';

		$this->Inbox->params = Router::parse('inbox/received.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['dir'] = $dir;
		$this->Inbox->received();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testSent()
	{
		$this->Inbox->params = Router::parse('inbox/sent.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->sent();

		$this->assertTrue(isset($this->Inbox->viewVars['messages']));
		$messages = $this->Inbox->viewVars['messages'];
		$this->assertTrue($messages['success']);
	
		$expected = array(
			'success' => 1,
			'messages' => array(
				array( 
					'id' => $messages['messages'][0]['id'],
					'from' => 'Test User',
					'subject' => 'Test',
					'body' => 'Test',
					'date' => $messages['messages'][0]['date'],
					'status' => 'unread',
					'trash' => 0,
					'sender_id' => 1,
					'receiver_type' => 'user',
					'receiver_id' => 1,
					'type' => 'sent',
					'template' => 'user_message',
					'template_data' => array(
						'sender' => 'Test User',
						'sender_id' => 1,
					),
					'sessions' => array(
						'from' => 'user:1',
						'to' => 'user:1',
					),
					'attachments' => array(
					),
					'to' => 'Test User',
				),
			),
			'total' => $messages['total'],
		);
		$this->assertEqual($messages, $expected);
	}

	function testSentInvalidLimit()
	{
		$limit = 'invalid';

		$this->Inbox->params = Router::parse('inbox/sent.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['limit'] = $limit;
		$this->Inbox->sent();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}
	
	function testSentInvalidStart()
	{
		$start = 'invalid';

		$this->Inbox->params = Router::parse('inbox/sent.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['start'] = $start;
		$this->Inbox->sent();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testSentInvalidSortField()
	{
		$sort = 'invalid';

		$this->Inbox->params = Router::parse('inbox/sent.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['sort'] = $sort;
		$this->Inbox->sent();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testSentInvalidSortDirection()
	{
		$dir = 'invalid';

		$this->Inbox->params = Router::parse('inbox/sent.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['dir'] = $dir;
		$this->Inbox->sent();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testTrash()
	{
		$this->Inbox->params = Router::parse('inbox/trash.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->trash();

		$this->assertTrue(isset($this->Inbox->viewVars['messages']));
		$messages = $this->Inbox->viewVars['messages'];
		$this->assertTrue($messages['success']);
	
		$expected = array(
			'success' => 1,
			'messages' => array(
				array( 
					'id' => $messages['messages'][0]['id'],
					'from' => 'Another User',
					'subject' => 'RE: Test',
					'body' => 'Reply to Test',
					'date' => $messages['messages'][0]['date'],
					'status' => 'read',
					'trash' => 1,
					'sender_id' => 2,
					'receiver_type' => 'user',
					'receiver_id' => 1,
					'type' => 'received',
					'template' => 'user_message',
					'template_data' => array(
						'sender' => 'Another User',
						'sender_id' => 2,
					),
					'sessions' => array(
						'from' => 'user:2',
						'to' => 'user:1',
					),
					'attachments' => array(
					),
					'to' => 'Test User',
				),
			),
			'total' => $messages['total'],
		);
		$this->assertEqual($messages, $expected);
	}

	function testTrashInvalidLimit()
	{
		$limit = 'invalid';

		$this->Inbox->params = Router::parse('inbox/trash.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['limit'] = $limit;
		$this->Inbox->trash();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}
	
	function testTrashInvalidStart()
	{
		$start = 'invalid';

		$this->Inbox->params = Router::parse('inbox/trash.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['start'] = $start;
		$this->Inbox->trash();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testTrashInvalidSortField()
	{
		$sort = 'invalid';

		$this->Inbox->params = Router::parse('inbox/trash.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['sort'] = $sort;
		$this->Inbox->trash();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testTrashInvalidSortDirection()
	{
		$dir = 'invalid';

		$this->Inbox->params = Router::parse('inbox/trash.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['dir'] = $dir;
		$this->Inbox->trash();

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testView()
	{
		$inbox_id = 9;

		$this->Inbox->params = Router::parse('inbox/view/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->view($inbox_id);

		$this->assertTrue(isset($this->Inbox->viewVars['response']));
		$response = $this->Inbox->viewVars['response'];
		$this->assertTrue($response['success']);

		$expected = array(
			'success' => 1,
			'message' => array(
				'id' => $response['message']['id'],
				'from' => 'Another User',
				'subject' => 'RE: Test',
				'body' => 'Reply to Test',
				'date' => $response['message']['date'],
				'status' => 'read',
				'trash' => 0,
				'sender_id' => 2,
				'receiver_type' => 'user',
				'receiver_id' => 1,
				'type' => 'received',
				'template' => 'user_message',
				'template_data' => array(
					'sender' => 'Another User',
					'sender_id' => 2,
				),
				'sessions' => array(
					'from' => 'user:2',
					'to' => 'user:1',
				),
				'attachments' => array(
				),
				'to' => 'Test User',
			),
			'neighbors' => array(
				'prev' => $response['neighbors']['prev'],
				'next' => $response['neighbors']['next'],
			),
		);

		$this->assertEqual($response, $expected);
	}

	function testViewNullInboxId()
	{
		$inbox_id = null;

		$this->Inbox->params = Router::parse('inbox/view/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->view($inbox_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testViewInvalidInboxId()
	{
		$inbox_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/view/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->view($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testViewInvalidInboxIdNotFound()
	{
		$inbox_id = 9000;

		$this->Inbox->params = Router::parse('inbox/view/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->view($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testViewAccessDenied()
	{
		$inbox_id = 9;

		$this->Inbox->Session->write('Auth.User', array(
			'id' => 3,
			'username' => 'thirduser',
			'name' => 'Third User',
			'changepass' => 0,
			'email' => 'thirduser@example.com',
		));

		$this->Inbox->params = Router::parse('inbox/view/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->view($inbox_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}

	function testRead()
	{
		$inbox_id = 9;

		$this->Inbox->params = Router::parse('inbox/read/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read($inbox_id);

		$this->assertTrue(isset($this->Inbox->viewVars['response']));
		$response = $this->Inbox->viewVars['response'];
		$this->assertTrue($response['success']);

		$conditions = array(
			'Inbox.id' => $inbox_id,
		);
		$this->Inbox->Inbox->recursive = -1;
		$result = $this->Inbox->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$this->assertEqual($result['Inbox']['status'], 'read');
	}

	function testReadNotJson()
	{
		$inbox_id = 9;

		$this->Inbox->params = Router::parse('inbox/read/' . $inbox_id);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', false);

		$this->Inbox->read($inbox_id);

		$this->assertEqual($this->Inbox->error, 'error404');
	}

	function testReadNullInboxId()
	{
		$inbox_id = null;

		$this->Inbox->params = Router::parse('inbox/read/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read($inbox_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testReadInvalidInboxId()
	{
		$inbox_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/read/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testReadInvalidInboxIdNotFound()
	{
		$inbox_id = 9000;

		$this->Inbox->params = Router::parse('inbox/read/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testReadAccessDenied()
	{
		$inbox_id = 9;

		$this->Inbox->Session->write('Auth.User', array(
			'id' => 3,
			'username' => 'thirduser',
			'name' => 'Third User',
			'changepass' => 0,
			'email' => 'thirduser@example.com',
		));

		$this->Inbox->params = Router::parse('inbox/read/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read($inbox_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}

	function testUnread()
	{
		$inbox_id = 8;

		$this->Inbox->params = Router::parse('inbox/unread/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->unread($inbox_id);

		$this->assertTrue(isset($this->Inbox->viewVars['response']));
		$response = $this->Inbox->viewVars['response'];
		$this->assertTrue($response['success']);

		$conditions = array(
			'Inbox.id' => $inbox_id,
		);
		$this->Inbox->Inbox->recursive = -1;
		$result = $this->Inbox->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$this->assertEqual($result['Inbox']['status'], 'unread');
	}

	function testUnreadNotJson()
	{
		$inbox_id = 9;

		$this->Inbox->params = Router::parse('inbox/unread/' . $inbox_id);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', false);

		$this->Inbox->unread($inbox_id);

		$this->assertEqual($this->Inbox->error, 'error404');
	}

	function testUnreadNullInboxId()
	{
		$inbox_id = null;

		$this->Inbox->params = Router::parse('inbox/unread/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->unread($inbox_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testUnreadInvalidInboxId()
	{
		$inbox_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/unread/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->unread($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testUnreadInvalidInboxIdNotFound()
	{
		$inbox_id = 9000;

		$this->Inbox->params = Router::parse('inbox/unread/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->unread($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testUnreadAccessDenied()
	{
		$inbox_id = 9;

		$this->Inbox->Session->write('Auth.User', array(
			'id' => 3,
			'username' => 'thirduser',
			'name' => 'Third User',
			'changepass' => 0,
			'email' => 'thirduser@example.com',
		));

		$this->Inbox->params = Router::parse('inbox/unread/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->unread($inbox_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}

	function testDeletePermanently()
	{
		$inbox_id = 10;

		$this->Inbox->params = Router::parse('inbox/delete/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->delete($inbox_id);

		$this->assertTrue(isset($this->Inbox->viewVars['response']));
		$response = $this->Inbox->viewVars['response'];
		$this->assertTrue($response['success']);

		$conditions = array(
			'Inbox.id' => $inbox_id,
		);
		$this->Inbox->Inbox->recursive = -1;
		$result = $this->Inbox->Inbox->find('first', array('conditions' => $conditions));
		$this->assertTrue(empty($result));
	}

	function testDeleteToTrash()
	{
		$inbox_id = 1;

		$this->Inbox->params = Router::parse('inbox/delete/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->delete($inbox_id);

		$this->assertTrue(isset($this->Inbox->viewVars['response']));
		$response = $this->Inbox->viewVars['response'];
		$this->assertTrue($response['success']);

		$conditions = array(
			'Inbox.id' => $inbox_id,
		);
		$this->Inbox->Inbox->recursive = -1;
		$result = $this->Inbox->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$this->assertTrue($result['Inbox']['trash']);
	}

	function testDeleteNotJson()
	{
		$inbox_id = 9;

		$this->Inbox->params = Router::parse('inbox/delete/' . $inbox_id);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', false);

		$this->Inbox->delete($inbox_id);

		$this->assertEqual($this->Inbox->error, 'error404');
	}

	function testDeleteNullInboxId()
	{
		$inbox_id = null;

		$this->Inbox->params = Router::parse('inbox/delete/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->delete($inbox_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testDeleteInvalidInboxId()
	{
		$inbox_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/delete/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->delete($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testDeleteInvalidInboxIdNotFound()
	{
		$inbox_id = 9000;

		$this->Inbox->params = Router::parse('inbox/delete/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->delete($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testDeleteAccessDenied()
	{
		$inbox_id = 9;

		$this->Inbox->Session->write('Auth.User', array(
			'id' => 3,
			'username' => 'thirduser',
			'name' => 'Third User',
			'changepass' => 0,
			'email' => 'thirduser@example.com',
		));

		$this->Inbox->params = Router::parse('inbox/delete/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->delete($inbox_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}

	function testRestore()
	{
		$inbox_id = 10;

		$this->Inbox->params = Router::parse('inbox/restore/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->restore($inbox_id);

		$this->assertTrue(isset($this->Inbox->viewVars['response']));
		$response = $this->Inbox->viewVars['response'];
		$this->assertTrue($response['success']);

		$conditions = array(
			'Inbox.id' => $inbox_id,
		);
		$this->Inbox->Inbox->recursive = -1;
		$result = $this->Inbox->Inbox->find('first', array('conditions' => $conditions));
		//$this->assertFalse(empty($result));

		$this->assertFalse($result['Inbox']['trash']);
	}

	function testRestoreNotJson()
	{
		$inbox_id = 10;

		$this->Inbox->params = Router::parse('inbox/restore/' . $inbox_id);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', false);

		$this->Inbox->restore($inbox_id);

		$this->assertEqual($this->Inbox->error, 'error404');
	}

	function testRestoreNullInboxId()
	{
		$inbox_id = null;

		$this->Inbox->params = Router::parse('inbox/restore/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->restore($inbox_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testRestoreInvalidInboxId()
	{
		$inbox_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/restore/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->restore($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testRestoreInvalidInboxIdNotFound()
	{
		$inbox_id = 9000;

		$this->Inbox->params = Router::parse('inbox/restore/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->restore($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testRestoreInvalidInboxIdNotTrash()
	{
		$inbox_id = 9;

		$this->Inbox->params = Router::parse('inbox/restore/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->restore($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testRestoreAccessDenied()
	{
		$inbox_id = 9;

		$this->Inbox->Session->write('Auth.User', array(
			'id' => 3,
			'username' => 'thirduser',
			'name' => 'Third User',
			'changepass' => 0,
			'email' => 'thirduser@example.com',
		));

		$this->Inbox->params = Router::parse('inbox/restore/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->restore($inbox_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}

	function testData()
	{
		$inbox_id = 9;

		$this->Inbox->params = Router::parse('inbox/data/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->data($inbox_id);

		$this->assertTrue(isset($this->Inbox->viewVars['node']));
		$node = $this->Inbox->viewVars['node'];

		$expected = array(
			'id' => $node['id'],
			'from' => 'Another User',
			'subject' => 'RE: Test',
			'body' => 'Reply to Test',
			'date' => $node['date'],
			'status' => 'read',
			'trash' => 0,
			'sender_id' => 2,
			'receiver_type' => 'user',
			'receiver_id' => 1,
			'type' => 'received',
			'template' => 'user_message',
			'template_data' => array(
				'sender' => 'Another User',
				'sender_id' => 2,
			),
			'sessions' => array(
				'from' => 'user:2',
				'to' => 'user:1',
			),
			'attachments' => array(
			),
			'to' => 'Test User',
		);
		$this->assertEqual($node, $expected);
	}

	function testDataNotJson()
	{
		$inbox_id = 9;

		$this->Inbox->params = Router::parse('inbox/data/' . $inbox_id);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', false);

		$this->Inbox->data($inbox_id);

		$this->assertEqual($this->Inbox->error, 'error404');
	}

	function testDataNullInboxId()
	{
		$inbox_id = null;

		$this->Inbox->params = Router::parse('inbox/data/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->data($inbox_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testDataInvalidInboxId()
	{
		$inbox_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/data/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->data($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testDataInvalidInboxIdNotFound()
	{
		$inbox_id = 9000;

		$this->Inbox->params = Router::parse('inbox/data/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->data($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testDataAccessDenied()
	{
		$inbox_id = 9;

		$this->Inbox->Session->write('Auth.User', array(
			'id' => 3,
			'username' => 'thirduser',
			'name' => 'Third User',
			'changepass' => 0,
			'email' => 'thirduser@example.com',
		));

		$this->Inbox->params = Router::parse('inbox/data/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->data($inbox_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}

	function testAcceptNullInboxId()
	{
		$inbox_id = null;

		$this->Inbox->params = Router::parse('inbox/accept/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->accept($inbox_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testAcceptInvalidInboxId()
	{
		$inbox_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/accept/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->accept($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testAcceptInvalidInboxIdNotFound()
	{
		$inbox_id = 9000;

		$this->Inbox->params = Router::parse('inbox/accept/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->accept($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testAcceptAccessDenied()
	{
		$inbox_id = 12;

		$this->Inbox->Session->write('Auth.User', array(
			'id' => 2,
			'username' => 'anotheruser',
			'name' => 'Another User',
			'changepass' => 0,
			'email' => 'anotheruser@example.com',
		));

		$this->Inbox->params = Router::parse('inbox/accept/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->accept($inbox_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}

	function testDeny()
	{
		$inbox_id = 11;

		$this->Inbox->params = Router::parse('inbox/deny/' . $inbox_id);
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->deny($inbox_id);

		$conditions = array(
			'Inbox.id' => $inbox_id,
		);
		$this->Inbox->Inbox->recursive = -1;
		$result = $this->Inbox->Inbox->find('first', array('conditions' => $conditions));
		$this->assertTrue(empty($result));

		$this->assertTrue($this->Inbox->redirectUrl, '/inbox/received/');
	}

	function testDenyNullInboxId()
	{
		$inbox_id = null;

		$this->Inbox->params = Router::parse('inbox/deny/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->deny($inbox_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testDenyInvalidInboxId()
	{
		$inbox_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/deny/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->deny($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testDenyInvalidInboxIdNotFound()
	{
		$inbox_id = 9000;

		$this->Inbox->params = Router::parse('inbox/deny/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->deny($inbox_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testDenyAccessDenied()
	{
		$inbox_id = 12;

		$this->Inbox->Session->write('Auth.User', array(
			'id' => 2,
			'username' => 'anotheruser',
			'name' => 'Another User',
			'changepass' => 0,
			'email' => 'anotheruser@example.com',
		));

		$this->Inbox->params = Router::parse('inbox/deny/' . $inbox_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->deny($inbox_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}

	function testArchives()
	{
		$table_type = 'group';
		$table_id = 1;

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->archives($table_type, $table_id);
		
		$this->assertTrue(isset($this->Inbox->viewVars['response']));
		$response = $this->Inbox->viewVars['response'];
		$this->assertTrue($response['success']);
		
		$expected = array(
			'success' => 1,
			'messages' => array(
				array(
					'id'  => 1,
					'from' => 'Test User',
					'subject'  => 'First Test Group Message',
					'body'  => 'First Test Group Message',
					'date'  => $response['messages'][0]['date'],
					'to' => 'Private Test Group',
				),
				array(
					'id'  => 2,
					'from' => 'Another User',
					'subject'  => 'Second Test Group Message',
					'body'  => 'Second Test Group Message',
					'date'  => $response['messages'][1]['date'],
					'to' => 'Private Test Group',
				),
				array(
					'id'  => 3,
					'from' => 'Another User',
					'subject'  => 'Third Test Group Message',
					'body'  => 'Third Test Group Message',
					'date'  => $response['messages'][2]['date'],
					'to' => 'Private Test Group',
				),
			),
			'total' => 3,
		);

		$this->assertEqual($expected, $response);
	}

	function testArchivesInvalidLimit()
	{
		$table_type = 'group';
		$table_id = 1;
		$limit = 'invalid';

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['limit'] = $limit;
		$this->Inbox->archives($table_type, $table_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}
	
	function testArchivesInvalidStart()
	{
		$table_type = 'group';
		$table_id = 1;
		$start = 'invalid';

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['start'] = $start;
		$this->Inbox->archives($table_type, $table_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testArchivesInvalidSortField()
	{
		$table_type = 'group';
		$table_id = 1;
		$sort = 'invalid';

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['sort'] = $sort;
		$this->Inbox->archives($table_type, $table_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testArchivesInvalidSortDirection()
	{
		$table_type = 'group';
		$table_id = 1;
		$dir = 'invalid';

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->params['form']['dir'] = $dir;
		$this->Inbox->archives($table_type, $table_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testArchivesNullTableType()
	{
		$table_type = null;
		$table_id = 1;

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->archives($table_type, $table_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testArchivesInvalidTableType()
	{
		$table_type = 'invalid';
		$table_id = 1;

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->archives($table_type, $table_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testArchivesNullTableId()
	{
		$table_type = 'group';
		$table_id = null;

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->archives($table_type, $table_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testArchivesInvalidTableId()
	{
		$table_type = 'group';
		$table_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->archives($table_type, $table_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testArchivesAccessDenied()
	{
		$table_type = 'user';
		$table_id = '2';

		$this->Inbox->params = Router::parse('inbox/archives/' . $table_type . '/' . $table_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->archives($table_type, $table_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}

	// TODO: Figure out what read_only is doing and how it works. 
	/*
	function testReadOnly()
	{
		$message_id = 1;

		$this->Inbox->params = Router::parse('inbox/read_only/' . $message_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read_only($message_id);

		$this->assertTrue(isset($this->Inbox->viewVars['response']));
		$response = $this->Inbox->viewVars['response'];
		$this->assertTrue($response['success']);

	}

	function testReadOnlyNullMessageId()
	{
		$message_id = null;

		$this->Inbox->params = Router::parse('inbox/read_only/' . $message_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read_only($message_id);

		$this->assertEqual($this->Inbox->error, 'missing_field');
	}

	function testReadOnlyInvalidMessageId()
	{
		$message_id = 'invalid';

		$this->Inbox->params = Router::parse('inbox/read_only/' . $message_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read_only($message_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testReadOnlyInvalidMessageIdNotFound()
	{
		$message_id = 9000;

		$this->Inbox->params = Router::parse('inbox/read_only/' . $message_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read_only($message_id);

		$this->assertEqual($this->Inbox->error, 'invalid_field');
	}

	function testReadOnlyAccessDenied()
	{
		$message_id = 1;

		$this->Inbox->Session->write('Auth.User', array(
			'id' => 3,
			'username' => 'thirduser',
			'name' => 'Third User',
			'changepass' => 0,
			'email' => 'thirduser@example.com',
		));

		$this->Inbox->params = Router::parse('inbox/read_only/' . $message_id . '.json');
		$this->Inbox->beforeFilter();
		$this->Inbox->Component->startup($this->Inbox);

		$this->Inbox->RequestHandler = new InboxControllerMockRequestHandlerComponent();
		$this->Inbox->RequestHandler->setReturnValue('prefers', true);

		$this->Inbox->read_only($message_id);

		$this->assertEqual($this->Inbox->error, 'access_denied');
	}
	*/

	function endTest() {
		unset($this->Inbox);
		ClassRegistry::flush();	
	}
}
?>

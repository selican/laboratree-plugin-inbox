<?php
App::import('Controller', 'App');
App::import('Component', 'Messaging');
App::import('Component', 'Email');

Mock::generatePartial('EmailComponent', 'MessagingComponentTestEmailComponent', array(
	'send',
));

class MessagingComponentTestMessagingComponent extends MessagingComponent
{
	var $emails = array();

	function initialize(&$controller, $settings = array())
	{
		parent::initialize($controller, $settings);
	}

	function startup(&$controller)
	{
		parent::startup($controller);
	}

	function email($email, $message, $subject, $template, $attachments = array(), $replyto = '')
	{
		$this->emails[] = array(
			$email,
			$message,
			$subject,
			$template,
			$attachments,
			$replyto,
		);

		return true;
	}
}

class MessagingComponentTestController extends AppController {
	var $name = 'Test';
	var $uses = array();
	var $components = array(
		'Messaging',
		'MessagingComponentTestMessaging',
	);
}

class MessagingTest extends CakeTestCase
{
	var $fixtures = array('app.helps', 'app.app_category', 'app.app_data', 'app.application', 'app.app_module', 'app.attachment', 'app.digest', 'app.discussion', 'app.doc', 'app.docs_permission', 'app.docs_tag', 'app.docs_type_data', 'app.docs_type_field', 'app.docs_type', 'app.docs_type_row', 'app.docs_version', 'app.group', 'app.groups_address', 'app.groups_association', 'app.groups_award', 'app.groups_interest', 'app.groups_phone', 'app.groups_projects', 'app.groups_publication', 'app.groups_setting', 'app.groups_url', 'app.groups_users', 'app.inbox', 'app.inbox_hash', 'app.interest', 'app.message_archive', 'app.message', 'app.note', 'app.ontology_concept', 'app.preference', 'app.project', 'app.projects_association', 'app.projects_interest', 'app.projects_setting', 'app.projects_url', 'app.projects_users', 'app.role', 'app.setting', 'app.site_role', 'app.tag', 'app.type', 'app.url', 'app.user', 'app.users_address', 'app.users_association', 'app.users_award', 'app.users_education', 'app.users_interest', 'app.users_job', 'app.users_phone', 'app.users_preference', 'app.users_publication', 'app.users_url', 'app.ldap_user');

	function startTest()
	{
		$this->Controller = new MessagingComponentTestController();
		$this->Controller->constructClasses();
		$this->Controller->Component->initialize($this->Controller);

		$this->Controller->Messaging = $this->Controller->MessagingComponentTestMessaging;
	}

	function testMessagingInstance() {
		$this->assertTrue(is_a($this->Controller->Messaging, 'MessagingComponent'));
	}

	function testEncodeDecode()
	{
		$inbox_id = 1;
		$inbox_hash = 'HASHHASHHASHHASHHASHHASHHASHHASHHASHHASH';

		$token = $this->Controller->Messaging->encode($inbox_id, $inbox_hash);
		$decoded = $this->Controller->Messaging->decode($token);
		$expected = array(
			$inbox_id,
			$inbox_hash,
		);
		$this->assertEqual($decoded, $expected);
	}

	function testEncodeNullInboxId()
	{
		$inbox_id = '';
		$inbox_hash = 'HASHHASHHASHHASHHASHHASHHASHHASHHASHHASH';

		try
		{
			$this->Controller->Messaging->encode($inbox_id, $inbox_hash);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testEncodeInvalidInboxId()
	{
		$inbox_id = array(
			'invalid' => 'invalid',
		);
		$inbox_hash = 'HASHHASHHASHHASHHASHHASHHASHHASHHASHHASH';

		try
		{
			$this->Controller->Messaging->encode($inbox_id, $inbox_hash);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testEncodeNullInboxHash()
	{
		$inbox_id = 1;
		$inbox_hash = null;

		try
		{
			$this->Controller->Messaging->encode($inbox_id, $inbox_hash);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testEncodeInvalidInboxHash()
	{
		$inbox_id = 1;
		$inbox_hash = array(
			'invalid' => 'invalid',
		);

		try
		{
			$this->Controller->Messaging->encode($inbox_id, $inbox_hash);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testDecodeNullToken()
	{
		$token = null;

		try
		{
			$this->Controller->Messaging->decode($token);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testDecodeInvalidToken()
	{
		$token = array(
			'invalid' => 'invalid',
		);

		try
		{
			$this->Controller->Messaging->decode($token);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testSendUser()
	{
		$receiver_id = 2;
		$receiver_type = 'user';
		$template = array(
			'name' => 'user_message',
			'data' => array(
				'sender_id' => 1,
				'sender' => 'Test User',
			),
		);

		$recipients = array(
			array(
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'template' => $template,
			),
		);

		$message = array(
			'subject' => 'Send Test',
			'body' => 'Send Test',
		);

		$attachments = array(
			'name' => 'Test Image.png',
			'filename' => '/data/laboratree/images/test.png',
		);

		$sender = 1;

		$this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
		$results = $this->Controller->Messaging->emails;

		$expected = array(
			array(
				'anotheruser@example.com',
				$message['body'],
				$message['subject'],
				array(
					'name' => $template['name'],
					'data' => $results[0][3]['data'],
				),
				$results[0][4],
				$results[0][5],
			),
		);

		$this->assertEqual($results, $expected);
	}

	function testSendGroup()
	{
		$receiver_id = 1;
		$receiver_type = 'group';
		$template = array(
			'name' => 'group_message',
			'data' => array(
				'sender_id' => 1,
				'sender' => 'Test User',
				'group_id' => 1,
				'group' => 'Test Group',
			),
		);

		$recipients = array(
			array(
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'template' => $template,
			),
		);

		$message = array(
			'subject' => 'Send Test',
			'body' => 'Send Test',
		);

		$attachments = array(
			'name' => 'Test Image.png',
			'filename' => '/data/laboratree/images/test.png',
		);

		$sender = 1;

		$this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
		$results = $this->Controller->Messaging->emails;

		$expected = array(
			array(
				'anotheruser@example.com',
				$message['body'],
				$results[0][2],
				array(
					'name' => $template['name'],
					'data' => $results[0][3]['data'],
				),
				$results[0][4],
				$results[0][5],
			),
			array(
				'testuser@example.com',
				$message['body'],
				$results[1][2],
				array(
					'name' => $template['name'],
					'data' => $results[1][3]['data'],
				),
				$results[1][4],
				$results[1][5],
			),
		);

		$this->assertEqual($results, $expected);
	}

	function testSendProject()
	{
		$receiver_id = 1;
		$receiver_type = 'project';
		$template = array(
			'name' => 'project_message',
			'data' => array(
				'sender_id' => 1,
				'sender' => 'Test User',
				'project_id' => 1,
				'project' => 'Test Project',
			),
		);

		$recipients = array(
			array(
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'template' => $template,
			),
		);

		$message = array(
			'subject' => 'Send Test',
			'body' => 'Send Test',
		);

		$attachments = array(
			'name' => 'Test Image.png',
			'filename' => '/data/laboratree/images/test.png',
		);

		$sender = 1;

		$this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
		$results = $this->Controller->Messaging->emails;

		$expected = array(
			array(
				'anotheruser@example.com',
				$message['body'],
				$results[0][2],
				array(
					'name' => $template['name'],
					'data' => $results[0][3]['data'],
				),
				$results[0][4],
				$results[0][5],
			),
			array(
				'testuser@example.com',
				$message['body'],
				$results[1][2],
				array(
					'name' => $template['name'],
					'data' => $results[1][3]['data'],
				),
				$results[1][4],
				$results[1][5],
			),
		);

		$this->assertEqual($results, $expected);
	}

	function testSendEmail()
	{
		$receiver_id = 'test@example.com';
		$receiver_type = 'email';
		$template = array(
			'name' => 'user_message',
			'data' => array(
				'sender_id' => 1,
				'sender' => 'Test User',
			),
		);

		$recipients = array(
			array(
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'template' => $template,
			),
		);

		$message = array(
			'subject' => 'Send Test',
			'body' => 'Send Test',
		);

		$attachments = array(
			'name' => 'Test Image.png',
			'filename' => '/data/laboratree/images/test.png',
		);

		$sender = 1;

		$this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
		$results = $this->Controller->Messaging->emails;

		$expected = array(
			array(
				'test@example.com',
				$message['body'],
				$message['subject'],
				array(
					'name' => $template['name'],
					'data' => $results[0][3]['data'],
				),
				$results[0][4],
				$results[0][5],
			),
		);

		$this->assertEqual($results, $expected);
	}

	function testSendNullRecipients()
	{
		$recipients = null;

		$message = array(
			'subject' => 'Send Test',
			'body' => 'Send Test',
		);

		$attachments = array(
			'name' => 'Test Image.png',
			'filename' => '/data/laboratree/images/test.png',
		);

		$sender = 1;

		try
		{	
			$results = $this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testSendInvalidRecipients()
	{
		$recipients = 'invalid';

		$message = array(
			'subject' => 'Send Test',
			'body' => 'Send Test',
		);

		$attachments = array(
			'name' => 'Test Image.png',
			'filename' => '/data/laboratree/images/test.png',
		);

		$sender = 1;

		try
		{	
			$results = $this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testSendNullMessage()
	{
		$receiver_id = 2;
		$receiver_type = 'user';
		$template = array(
			'name' => 'user_message',
			'data' => array(
				'sender_id' => 1,
				'sender' => 'Test User',
			),
		);

		$recipients = array(
			array(
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'template' => $template,
			),
		);

		$message = null;

		$attachments = array(
			'name' => 'Test Image.png',
			'filename' => '/data/laboratree/images/test.png',
		);

		$sender = 1;

		try
		{	
			$results = $this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testSendInvalidMessage()
	{
		$receiver_id = 2;
		$receiver_type = 'user';
		$template = array(
			'name' => 'user_message',
			'data' => array(
				'sender_id' => 1,
				'sender' => 'Test User',
			),
		);

		$recipients = array(
			array(
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'template' => $template,
			),
		);

		$message = 'invalid';

		$attachments = array(
			'name' => 'Test Image.png',
			'filename' => '/data/laboratree/images/test.png',
		);

		$sender = 1;

		try
		{	
			$results = $this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testSendInvalidAttachments()
	{
		$receiver_id = 2;
		$receiver_type = 'user';
		$template = array(
			'name' => 'user_message',
			'data' => array(
				'sender_id' => 1,
				'sender' => 'Test User',
			),
		);

		$recipients = array(
			array(
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'template' => $template,
			),
		);

		$message = array(
			'subject' => 'Send Test',
			'body' => 'Send Test',
		);

		$attachments = 'invalid';

		$sender = 1;

		try
		{	
			$results = $this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testSendInvalidSender()
	{
		$receiver_id = 2;
		$receiver_type = 'user';
		$template = array(
			'name' => 'user_message',
			'data' => array(
				'sender_id' => 1,
				'sender' => 'Test User',
			),
		);

		$recipients = array(
			array(
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'template' => $template,
			),
		);

		$message = array(
			'subject' => 'Send Test',
			'body' => 'Send Test',
		);

		$attachments = array(
			'name' => 'Test Image.png',
			'filename' => '/data/laboratree/images/test.png',
		);

		$sender = 'invalid';

		try
		{	
			$results = $this->Controller->Messaging->send($recipients, $message, $attachments, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testDigestEmptyUserId() {
                try
                {
                        $results = $this->Controller->Messaging->digest(null, 1);
                        $this->fail('InvalidArgumentException was expected.');
                }
                catch(InvalidArgumentException $e)
                {
                        $this->pass();
                }
	}

	function testDigestBoolUserId() {
                try
                {
                        $results = $this->Controller->Messaging->digest(true, 1);
                        $this->fail('InvalidArgumentException was expected.');
                }
                catch(InvalidArgumentException $e)
                {
                        $this->pass();
                }
        }

	function testDigestStringUserId() {
                try
                {
                        $results = $this->Controller->Messaging->digest('string', 1);
                        $this->fail('InvalidArgumentException was expected.');
                }
                catch(InvalidArgumentException $e)
                {
                        $this->pass();
                }
        }

	function testDigestNegativeUserId() {
                try
                {
                        $results = $this->Controller->Messaging->digest(-1, 1);
                        $this->fail('InvalidArgumentException was expected.');
                }
                catch(InvalidArgumentException $e)
                {
                        $this->pass();
                }
        }

	function testDigestEmptyInboxId() {
                try
                {
                        $results = $this->Controller->Messaging->digest(1, null);
                        $this->fail('InvalidArgumentException was expected.');
                }
                catch(InvalidArgumentException $e)
                {
                        $this->pass();
                }
        }

	function testDigestBoolInboxId() {
                try
                {
                        $results = $this->Controller->Messaging->digest(1, true);
                        $this->fail('InvalidArgumentException was expected.');
                }
                catch(InvalidArgumentException $e)
                {
                        $this->pass();
                }
        }

	function testDigestStringInboxId() {
                try
                {
                        $results = $this->Controller->Messaging->digest(1, 'string');
                        $this->fail('InvalidArgumentException was expected.');
                }
                catch(InvalidArgumentException $e)
                {
                        $this->pass();
                }
        }

	function testDigestNegativeInboxId() {
                try
                {
                        $results = $this->Controller->Messaging->digest(1, -1);
                        $this->fail('InvalidArgumentException was expected.');
                }
                catch(InvalidArgumentException $e)
                {
                        $this->pass();
                }
        }

	function testDigestValid() {
                        $results = $this->Controller->Messaging->digest(1, 1);
	}

	function testEmail() {
		$this->Controller->Messaging->email('ethan.thomason@yahoo.com', 'test', 'test',array('name' => 'name', 'data' => 'data',) , null, 'ethan.thomason@yahoo.com');
	}

	function endTest() {
		unset($this->Controller);
		ClassRegistry::flush();	
	}
}
?>

<?php
App::import('Controller', 'App');
App::import('Component', 'InboxCmp');

class InboxCmpComponentTestController extends AppController {
	var $name = 'Test';
	var $uses = array();
	var $components = array(
		'InboxCmp',
	);
}

class InboxCmpTest extends CakeTestCase
{
	var $fixtures = array('app.helps', 'app.app_category', 'app.app_data', 'app.application', 'app.app_module', 'app.attachment', 'app.digest', 'app.discussion', 'app.doc', 'app.docs_permission', 'app.docs_tag', 'app.docs_type_data', 'app.docs_type_field', 'app.docs_type', 'app.docs_type_row', 'app.docs_version', 'app.group', 'app.groups_address', 'app.groups_association', 'app.groups_award', 'app.groups_interest', 'app.groups_phone', 'app.groups_projects', 'app.groups_publication', 'app.groups_setting', 'app.groups_url', 'app.groups_users', 'app.inbox', 'app.inbox_hash', 'app.interest', 'app.message_archive', 'app.message', 'app.note', 'app.ontology_concept', 'app.preference', 'app.project', 'app.projects_association', 'app.projects_interest', 'app.projects_setting', 'app.projects_url', 'app.projects_users', 'app.role', 'app.setting', 'app.site_role', 'app.tag', 'app.type', 'app.url', 'app.user', 'app.users_address', 'app.users_association', 'app.users_award', 'app.users_education', 'app.users_interest', 'app.users_job', 'app.users_phone', 'app.users_preference', 'app.users_publication', 'app.users_url', 'app.ldap_user');

	function startTest()
	{
		$this->Controller = new InboxCmpComponentTestController();
		$this->Controller->constructClasses();
		$this->Controller->Component->initialize($this->Controller);
	}

	function testInboxCmpInstance() {
		$this->assertTrue(is_a($this->Controller->InboxCmp, 'InboxCmpComponent'));
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

		$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
		$this->assertFalse(empty($results));

		$conditions = array(
			'Inbox.id' => $results[$receiver_id],
		);
		$this->Controller->InboxCmp->Inbox->recursive = -1;
		$result = $this->Controller->InboxCmp->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Inbox' => array(
				'id'  => $result['Inbox']['id'],
				'sender_id'  => 1,
				'receiver_id'  => $receiver_id,
				'receiver_type' => $receiver_type,
				'message_id'  => $result['Inbox']['message_id'],
				'template'  => $template['name'],
				'template_data'  => $result['Inbox']['template_data'],
				'status' => 'unread',
				'trash'  => 0,
				'type' => 'received',
				'email'  => null,
				'parent_id'  => null
			),
		);

		$this->assertEqual($result, $expected);

		$conditions = array(
			'Message.id' => $result['Inbox']['message_id'],
		);
		$this->Controller->InboxCmp->Message->recursive = -1;
		$result = $this->Controller->InboxCmp->Message->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Message' => array(
				'id' => $result['Message']['id'],
				'subject' => 'Send Test',
				'body' => 'Send Test',
				'date' => $result['Message']['date'],
			),
		);
		$this->assertEqual($result, $expected);

		$message_id = $result['Message']['id'];

		$conditions = array(
			'Attachment.message_id' => $message_id,
		);
		$this->Controller->InboxCmp->Attachment->recursive = -1;
		$result = $this->Controller->InboxCmp->Attachment->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Attachment' => array(
				'id' => $result['Attachment']['id'],
				'message_id' => $message_id,
				'name' => 'Test Image.png',
				'mimetype' => 'image/png; charset=binary',
				'filename' => $result['Attachment']['filename'],
			),
		);
		$this->assertEqual($result, $expected);
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
				'group_id' => $receiver_id,
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

		$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
		$this->assertFalse(empty($results));

		$user_id = array_shift(array_keys($results));

		$conditions = array(
			'Inbox.id' => $results[$user_id],
		);
		$this->Controller->InboxCmp->Inbox->recursive = -1;
		$result = $this->Controller->InboxCmp->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Inbox' => array(
				'id'  => $result['Inbox']['id'],
				'sender_id'  => 1,
				'receiver_id'  => $user_id,
				'receiver_type' => 'user',
				'message_id'  => $result['Inbox']['message_id'],
				'template'  => $template['name'],
				'template_data'  => $result['Inbox']['template_data'],
				'status' => 'unread',
				'trash'  => 0,
				'type' => 'received',
				'email'  => null,
				'parent_id'  => $result['Inbox']['parent_id'],
			),
		);

		$this->assertEqual($result, $expected);

		$conditions = array(
			'Message.id' => $result['Inbox']['message_id'],
		);
		$this->Controller->InboxCmp->Message->recursive = -1;
		$result = $this->Controller->InboxCmp->Message->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Message' => array(
				'id' => $result['Message']['id'],
				'subject' => 'Send Test',
				'body' => 'Send Test',
				'date' => $result['Message']['date'],
			),
		);
		$this->assertEqual($result, $expected);

		$message_id = $result['Message']['id'];

		$conditions = array(
			'Attachment.message_id' => $message_id,
		);
		$this->Controller->InboxCmp->Attachment->recursive = -1;
		$result = $this->Controller->InboxCmp->Attachment->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Attachment' => array(
				'id' => $result['Attachment']['id'],
				'message_id' => $message_id,
				'name' => 'Test Image.png',
				'mimetype' => 'image/png; charset=binary',
				'filename' => $result['Attachment']['filename'],
			),
		);
		$this->assertEqual($result, $expected);

		$conditions = array(
			'MessageArchive.message_id' => $message_id,
		);
		$this->Controller->InboxCmp->MessageArchive->recursive = -1;
		$result = $this->Controller->InboxCmp->MessageArchive->find('first', array('conditions' => $conditions));

		$expected = array(
			'MessageArchive' => array(
				'id' => $result['MessageArchive']['id'],
				'message_id' => $message_id,
				'sender_id' => 1,
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'subject' => 'Send Test',
				'body' => 'Send Test',
				'date' => $result['MessageArchive']['date'],
			),
		);
		$this->assertEqual($result, $expected);
	}

	function testSendProject()
	{
		$receiver_id = 1;
		$receiver_type = 'group';
		$template = array(
			'name' => 'group_message',
			'data' => array(
				'sender_id' => 1,
				'sender' => 'Test User',
				'project_id' => $receiver_id,
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

		$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
		$this->assertFalse(empty($results));

		$user_id = array_shift(array_keys($results));

		$conditions = array(
			'Inbox.id' => $results[$user_id],
		);
		$this->Controller->InboxCmp->Inbox->recursive = -1;
		$result = $this->Controller->InboxCmp->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Inbox' => array(
				'id'  => $result['Inbox']['id'],
				'sender_id'  => 1,
				'receiver_id'  => $user_id,
				'receiver_type' => 'user',
				'message_id'  => $result['Inbox']['message_id'],
				'template'  => $template['name'],
				'template_data'  => $result['Inbox']['template_data'],
				'status' => 'unread',
				'trash'  => 0,
				'type' => 'received',
				'email'  => null,
				'parent_id'  => $result['Inbox']['parent_id'],
			),
		);

		$this->assertEqual($result, $expected);

		$conditions = array(
			'Message.id' => $result['Inbox']['message_id'],
		);
		$this->Controller->InboxCmp->Message->recursive = -1;
		$result = $this->Controller->InboxCmp->Message->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Message' => array(
				'id' => $result['Message']['id'],
				'subject' => 'Send Test',
				'body' => 'Send Test',
				'date' => $result['Message']['date'],
			),
		);
		$this->assertEqual($result, $expected);

		$message_id = $result['Message']['id'];

		$conditions = array(
			'Attachment.message_id' => $message_id,
		);
		$this->Controller->InboxCmp->Attachment->recursive = -1;
		$result = $this->Controller->InboxCmp->Attachment->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Attachment' => array(
				'id' => $result['Attachment']['id'],
				'message_id' => $message_id,
				'name' => 'Test Image.png',
				'mimetype' => 'image/png; charset=binary',
				'filename' => $result['Attachment']['filename'],
			),
		);
		$this->assertEqual($result, $expected);

		$conditions = array(
			'MessageArchive.message_id' => $message_id,
		);
		$this->Controller->InboxCmp->MessageArchive->recursive = -1;
		$result = $this->Controller->InboxCmp->MessageArchive->find('first', array('conditions' => $conditions));

		$expected = array(
			'MessageArchive' => array(
				'id' => $result['MessageArchive']['id'],
				'message_id' => $message_id,
				'sender_id' => 1,
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'subject' => 'Send Test',
				'body' => 'Send Test',
				'date' => $result['MessageArchive']['date'],
			),
		);
		$this->assertEqual($result, $expected);
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

		$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
		$this->assertFalse(empty($results));

		$user_id = array_shift(array_keys($results));

		$conditions = array(
			'Inbox.id' => $results[$user_id],
		);
		$this->Controller->InboxCmp->Inbox->recursive = -1;
		$result = $this->Controller->InboxCmp->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Inbox' => array(
				'id'  => $result['Inbox']['id'],
				'sender_id'  => 1,
				'receiver_id'  => null,
				'receiver_type' => 'email',
				'message_id'  => $result['Inbox']['message_id'],
				'template'  => $template['name'],
				'template_data'  => $result['Inbox']['template_data'],
				'status' => 'read',
				'trash'  => 0,
				'type' => 'sent',
				'email'  => $receiver_id,
				'parent_id'  => $result['Inbox']['parent_id'],
			),
		);

		$this->assertEqual($result, $expected);

		$conditions = array(
			'Message.id' => $result['Inbox']['message_id'],
		);
		$this->Controller->InboxCmp->Message->recursive = -1;
		$result = $this->Controller->InboxCmp->Message->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Message' => array(
				'id' => $result['Message']['id'],
				'subject' => 'Send Test',
				'body' => 'Send Test',
				'date' => $result['Message']['date'],
			),
		);
		$this->assertEqual($result, $expected);

		$message_id = $result['Message']['id'];

		$conditions = array(
			'Attachment.message_id' => $message_id,
		);
		$this->Controller->InboxCmp->Attachment->recursive = -1;
		$result = $this->Controller->InboxCmp->Attachment->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Attachment' => array(
				'id' => $result['Attachment']['id'],
				'message_id' => $message_id,
				'name' => 'Test Image.png',
				'mimetype' => 'image/png; charset=binary',
				'filename' => $result['Attachment']['filename'],
			),
		);
		$this->assertEqual($result, $expected);
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
			$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
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
			$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
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
			$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
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
			$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
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
			$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
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
			$results = $this->Controller->InboxCmp->send($recipients, $message, $attachments, $sender);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testSave()
	{
		$content = 'Save Test';

		$this->Controller->InboxCmp->message_id = 1;

		$this->Controller->InboxCmp->sender_id = 1;
		$this->Controller->InboxCmp->receiver_type = 'user';
		$this->Controller->InboxCmp->receiver_id = 1;

		$this->Controller->InboxCmp->template = 'user_message';
		$this->Controller->InboxCmp->template_data = array(
			'sender' => 'Test User',
			'sender_id' => 1,
			);

		$this->Controller->InboxCmp->type = 'received';

		$inbox_id = $this->Controller->InboxCmp->save($content);
		$this->assertFalse(empty($inbox_id));

		$conditions = array(
			'Inbox.id' => $inbox_id,
		);
		$this->Controller->InboxCmp->Inbox->recursive = -1;
		$result = $this->Controller->InboxCmp->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));
	
		debug($result['Inbox']['id']);

		$expected = array(
			'Inbox' => array(
				'id'  => $result['Inbox']['id'],
				'sender_id'  => 1,
				'receiver_id'  => null,
				'receiver_type' => 'email',
				'message_id'  => 1,
				'template'  => 'user_message',
				'template_data'  => $result['Inbox']['template_data'],
				'status' => 'unread',
				'trash'  => 0,
				'type' => 'received',
				'email'  => null,
				'parent_id'  => null,
			),
		);
		$this->assertEqual($result, $expected);
	}

	function testSendInvalidContent()
	{
		$content = array(
			'invalid' => 'invalid',
		);

		$this->Controller->InboxCmp->message_id = 1;

		$this->Controller->InboxCmp->sender_id = 1;
		$this->Controller->InboxCmp->receiver_type = 'user';
		$this->Controller->InboxCmp->receiver_id = 2;

		$this->Controller->InboxCmp->template = 'user_message';
		$this->Controller->InboxCmp->template_data = array(
			'sender_id' => 1,
			'sender' => 'Test User',
		);

		$this->Controller->InboxCmp->type = 'received';

		try
		{
			$inbox_id = $this->Controller->InboxCmp->save($content);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testMessage()
	{
		$this->Controller->InboxCmp->subject = 'Subject Test';
		$this->Controller->InboxCmp->body = 'Message Test';

		$message_id = $this->Controller->InboxCmp->_message();
		$this->assertFalse(empty($message_id));

		$conditions = array(
			'Message.id' => $message_id,
		);
		$this->Controller->InboxCmp->Message->recursive = -1;
		$result = $this->Controller->InboxCmp->Message->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Message' => array(
				'id' => $message_id,
				'subject' => 'Send Test',
				'body' => 'Send Test',
				'date' => $result['Message']['date'],
			),
		);
		$this->assertEqual($result, $expected);
	}

	function testAttachments()
	{
		$this->Controller->InboxCmp->message_id = 1;
		$this->Controller->InboxCmp->_attachments();
		$this->assertTrue($this->Controller->InboxCmp->attached);
	}

	function testInbox()
	{
		$this->Controller->InboxCmp->sender_id = 1;
		$this->Controller->InboxCmp->receiver_type = 'user';
		$this->Controller->InboxCmp->message_id = 1;

		$this->Controller->InboxCmp->template = 'user_message';
		$this->Controller->InboxCmp->template_data = array(
			'sender_id' => 1,
			'sender' => 'Test User',
		);

		$this->Controller->InboxCmp->type = 'received';
		$this->Controller->InboxCmp->receiver_id = 2;

		$inbox_id = $this->Controller->InboxCmp->_inbox();
		$this->assertFalse(empty($inbox_id));
		
		$conditions = array(
			'Inbox.id' => $inbox_id,
		);
		$this->Controller->InboxCmp->Inbox->recursive = -1;
		$result = $this->Controller->InboxCmp->Inbox->find('first', array('conditions' => $conditions));
		$this->assertFalse(empty($result));

		$expected = array(
			'Inbox' => array(
				'id'  => $result['Inbox']['id'],
				'sender_id'  => 1,
				'receiver_id'  => 2,
				'receiver_type' => 'user',
				'message_id'  => 1,
				'template'  => 'user_message',
				'template_data'  => $result['Inbox']['template_data'],
				'status' => 'unread',
				'trash'  => 0,
				'type' => 'received',
				'email'  => null,
				'parent_id'  => null,
			),
		);
		$this->assertEqual($result, $expected);
	}

	function testTemplateData()
	{
		$data = array(
			'test' => '1',
		);

		$result = $this->Controller->InboxCmp->_template_data($data);
		$expected = array(
			'test' => 1,
		);
		$this->assertEqual($result, $expected);
	}

	function testArchive()
	{
		$sender_id = 1;
		$receiver_id = 2;
		$receiver_type = 'user';
		$message_id = 1;
		$subject = 'Archive Test';
		$body = 'Archive Test';

		$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
		$this->assertFalse(empty($archive_id));

		$conditions = array(
			'MessageArchive.id' => $archive_id,
		);
		$this->Controller->InboxCmp->MessageArchive->recursive = -1;
		$result = $this->Controller->InboxCmp->MessageArchive->find('first', array('conditions' => $conditions));

		$expected = array(
			'MessageArchive' => array(
				'id' => $result['MessageArchive']['id'],
				'message_id' => $message_id,
				'sender_id' => 1,
				'receiver_id' => $receiver_id,
				'receiver_type' => $receiver_type,
				'subject' => 'Archive Test',
				'body' => 'Archive Test',
				'date' => $result['MessageArchive']['date'],
			),
		);
	}

	function testArchiveNullSenderId()
	{
		$sender_id = null;
		$receiver_id = 2;
		$receiver_type = 'user';
		$message_id = 1;
		$subject = 'Archive Test';
		$body = 'Archive Test';

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testArchiveInvalidSenderId()
	{
		$sender_id = 'invalid';
		$receiver_id = 2;
		$receiver_type = 'user';
		$message_id = 1;
		$subject = 'Archive Test';
		$body = 'Archive Test';

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testArchiveNullReceiverId()
	{
		$sender_id = 1;
		$receiver_id = null;
		$receiver_type = 'user';
		$message_id = 1;
		$subject = 'Archive Test';
		$body = 'Archive Test';

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testArchiveInvalidReceiverId()
	{
		$sender_id = 1;
		$receiver_id = 'invalid';
		$receiver_type = 'user';
		$message_id = 1;
		$subject = 'Archive Test';
		$body = 'Archive Test';

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testArchiveNullReceiverType()
	{
		$sender_id = 1;
		$receiver_id = 2;
		$receiver_type = null;
		$message_id = 1;
		$subject = 'Archive Test';
		$body = 'Archive Test';

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testArchiveInvalidReceiverType()
	{
		$sender_id = 1;
		$receiver_id = 2;
		$receiver_type = array(
			'invalid' => 'invalid',
		);
		$message_id = 1;
		$subject = 'Archive Test';
		$body = 'Archive Test';

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testArchiveNullMessageId()
	{
		$sender_id = 1;
		$receiver_id = 2;
		$receiver_type = 'user';
		$message_id = null;
		$subject = 'Archive Test';
		$body = 'Archive Test';

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testArchiveInvalidMessageId()
	{
		$sender_id = 1;
		$receiver_id = 2;
		$receiver_type = 'user';
		$message_id = 'invalid';
		$subject = 'Archive Test';
		$body = 'Archive Test';

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testArchiveInvalidSubject()
	{
		$sender_id = 1;
		$receiver_id = 2;
		$receiver_type = 'user';
		$message_id = 1;
		$subject = array(
			'invalid' => 'invalid',
		);
		$body = 'Archive Test';

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testArchiveInvalidBody()
	{
		$sender_id = 1;
		$receiver_id = 2;
		$receiver_type = 'user';
		$message_id = 1;
		$subject = 'Archive Test';
		$body = array(
			'invalid' => 'invalid',
		);

		try
		{
			$archive_id = $this->Controller->InboxCmp->_archive($sender_id, $receiver_id, $receiver_type, $message_id, $subject, $body);
			$this->fail('InvalidArgumentException was expected.');
		}
		catch(InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function endTest() {
		unset($this->Controller);
		ClassRegistry::flush();	
	}
}
?>

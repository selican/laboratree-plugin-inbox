<?php 
/* SVN FILE: $Id$ */
/* Attachment Test cases generated on: 2010-12-20 14:53:46 : 1292856826*/
App::import('Model', 'Attachment');

class AttachmentTestCase extends CakeTestCase {
	var $Attachment = null;
	var $fixtures = array('app.helps', 'app.app_category', 'app.app_data', 'app.application', 'app.app_module', 'app.attachment', 'app.discussion', 'app.doc', 'app.docs_permission', 'app.docs_tag', 'app.docs_type_data', 'app.docs_type_field', 'app.docs_type', 'app.docs_type_row', 'app.docs_version', 'app.group', 'app.groups_address', 'app.groups_association', 'app.groups_award', 'app.groups_interest', 'app.groups_phone', 'app.groups_projects', 'app.groups_publication', 'app.groups_setting', 'app.groups_url', 'app.groups_users', 'app.inbox', 'app.inbox_hash', 'app.interest', 'app.message_archive', 'app.message', 'app.note', 'app.ontology_concept', 'app.preference', 'app.project', 'app.projects_association', 'app.projects_interest', 'app.projects_setting', 'app.projects_url', 'app.projects_users', 'app.role', 'app.setting', 'app.site_role', 'app.tag', 'app.type', 'app.url', 'app.user', 'app.users_address', 'app.users_association', 'app.users_award', 'app.users_education', 'app.users_interest', 'app.users_job', 'app.users_phone', 'app.users_preference', 'app.users_publication', 'app.users_url');

	function startTest() {
		$this->Attachment =& ClassRegistry::init('Attachment');
	}

	function testAttachmentInstance() {
		$this->assertTrue(is_a($this->Attachment, 'Attachment'));
	}

	function testAttachmentFind() {
		$this->Attachment->recursive = -1;
		$results = $this->Attachment->find('first');
		$this->assertTrue(!empty($results));

		$expected = array('Attachment' => array(
			'id'  => 1,
			'message_id'  => 1,
			'name'  => 'first.png',
			'mimetype'  => 'image/png',
			'filename'  => 'AAAA'
		));
		$this->assertEqual($results, $expected);
	}

	function testToNode() {
		$this->Attachment->recursive = -1;
		$results = $this->Attachment->find('first');
		$node = $this->Attachment->toNode($results);

		$expected = array(
			'id'  => 1,
			'name'  => 'first.png',
			'mimetype'  => 'image/png',
		);

		$this->assertEqual($node, $expected);
	}

	function testToNodeNull() {
		try
		{
			$node = $this->Attachment->toNode(null);
			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testToNodeNotArray() {
		try
		{
			$node = $this->Attachment->toNode('string');
			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}	
	}

	function testToNodeMissingModel() {
		try
		{
			$node = $this->Attachment->toNode(array('id' => 1));
			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testToNodeMissingKey() {
		try
		{
			$node = $this->Attachment->toNode(array('Attachment' => array('test' => 1)));
			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testAttach() {
		$message_id = 1;

		$attached = $this->Attachment->attach($message_id, array(
			array(
				'name' => 'test.png',
				'filename' => '/data/laboratree/images/test.png',
				'mimetype' => 'image/png',
			)
		));
		$this->assertFalse(empty($attached));

		$filename  = $attached[0];
		$this->assertTrue(file_exists($filename));

		$expected = array(
			'Attachment' => array(
				'id'  => 2,
				'message_id' => $message_id,
				'name'  => 'test.png',
				'mimetype'  => 'image/png',
				'filename' => basename($filename),
			),
		);

		$this->Attachment->recursive = -1;
		$node = $this->Attachment->find('first', array('conditions' => array(
			'message_id' => $message_id,
			'name' => 'test.png',
		)));
		$this->assertEqual($node, $expected);

		foreach($attached as $filename)
		{
			if(file_exists($filename))
			{
				unlink($filename);
			}
		}
	}

	function testAttachNullMessageId()
	{
		try
		{
			$node = $this->Attachment->attach(null, array(
				array(
					'name' => 'test.png',
					'filename' => '/data/laboratree/images/test.png',
					'mimetype' => 'image/png',
				)
			));

			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testAttachInvalidMessageId()
	{
		try
		{
			$node = $this->Attachment->attach('invalid', array(
				array(
					'name' => 'test.png',
					'filename' => '/data/laboratree/images/test.png',
					'mimetype' => 'image/png',
				)
			));

			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testAttachNullFiles()
	{
		$message_id = 1;

		try
		{
			$node = $this->Attachment->attach($message_id, null);

			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}

	function testAttachInvalidFiles()
	{
		$message_id = 1;

		$attached = $this->Attachment->attach($message_id, array(
			array(
				'name' => 'test.png',
				'filename' => '/data/laboratree/images/invalid.png',
				'mimetype' => 'image/png',
			)
		));

		$this->assertTrue(empty($attached));
	}
}
?>

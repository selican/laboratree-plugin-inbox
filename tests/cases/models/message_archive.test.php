<?php 
/* SVN FILE: $Id$ */
/* MessageArchive Test cases generated on: 2010-12-20 14:58:09 : 1292857089*/
App::import('Model', 'MessageArchive');

class MessageArchiveTestCase extends CakeTestCase {
	var $MessageArchive = null;
	var $fixtures = array('app.helps', 'app.app_category', 'app.app_data', 'app.application', 'app.app_module', 'app.attachment', 'app.discussion', 'app.doc', 'app.docs_permission', 'app.docs_tag', 'app.docs_type_data', 'app.docs_type_field', 'app.docs_type', 'app.docs_type_row', 'app.docs_version', 'app.group', 'app.groups_address', 'app.groups_association', 'app.groups_award', 'app.groups_interest', 'app.groups_phone', 'app.groups_projects', 'app.groups_publication', 'app.groups_setting', 'app.groups_url', 'app.groups_users', 'app.inbox', 'app.inbox_hash', 'app.interest', 'app.message_archive', 'app.message', 'app.note', 'app.ontology_concept', 'app.preference', 'app.project', 'app.projects_association', 'app.projects_interest', 'app.projects_setting', 'app.projects_url', 'app.projects_users', 'app.role', 'app.setting', 'app.site_role', 'app.tag', 'app.type', 'app.url', 'app.user', 'app.users_address', 'app.users_association', 'app.users_award', 'app.users_education', 'app.users_interest', 'app.users_job', 'app.users_phone', 'app.users_preference', 'app.users_publication', 'app.users_url');

	function startTest() {
		$this->MessageArchive =& ClassRegistry::init('MessageArchive');
	}

	function testMessageArchiveInstance() {
		$this->assertTrue(is_a($this->MessageArchive, 'MessageArchive'));
	}

	function testMessageArchiveFind() {
		$this->MessageArchive->recursive = -1;
		$results = $this->MessageArchive->find('first');
		$this->assertTrue(!empty($results));

		$expected = array(
			'MessageArchive' => array(
				'id'  => 1,
				'message_id'  => 1,
				'sender_id'  => 1,
				'receiver_id'  => 1,
				'receiver_type' => 'group',
				'subject'  => 'First Test Group Message',
				'body'  => 'First Test Group Message',
				'date'  => '2010-12-20 14:58:09'
			),
		);
		$this->assertEqual($results, $expected);
	}

	function testToNode() {
		$this->MessageArchive->recursive = 1;
		$results = $this->MessageArchive->find('first');
		$node = $this->MessageArchive->toNode($results);
		$expected = array(
			'id'  => 1,
			'from' => 'Test User',
			'subject' => 'First Test Group Message',
			'body' => 'First Test Group Message',
			'date' => '2010-12-20 14:58:09',
			'to' => 'Private Test Group',
		);

		$this->assertEqual($node, $expected);
	}

	function testToNodeNull() {
		try
		{
			$node = $this->MessageArchive->toNode(null);
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
			$node = $this->MessageArchive->toNode('string');
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
			$node = $this->MessageArchive->toNode(array('id' => 1));
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
			$node = $this->MessageArchive->toNode(array('MessageArchive' => array('test' => 1)));
			$this->fail('InvalidArgumentException was expected');
		}
		catch (InvalidArgumentException $e)
		{
			$this->pass();
		}
	}
}
?>

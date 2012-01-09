<?php 
/* SVN FILE: $Id$ */
/* InboxHash Test cases generated on: 2010-12-20 14:57:42 : 1292857062*/
App::import('Model', 'InboxHash');

class InboxHashTestCase extends CakeTestCase {
	var $InboxHash = null;
	var $fixtures = array('app.helps', 'app.inbox_hash');

	function startTest() {
		$this->InboxHash =& ClassRegistry::init('InboxHash');
	}

	function testInboxHashInstance() {
		$this->assertTrue(is_a($this->InboxHash, 'InboxHash'));
	}

	function testInboxHashFind() {
		$this->InboxHash->recursive = -1;
		$results = $this->InboxHash->find('first');
		$this->assertTrue(!empty($results));

		$expected = array(
			'InboxHash' => array(
				'id'  => 1,
				'inbox_id'  => 3,
				'hash'  => 'HASHHASHHASHHASHHASHHASHHASHHASHHASHHASH',
			),
		);
		$this->assertEqual($results, $expected);
	}

	function testGenerate()
	{
		$inbox_id = 2;

		$hash = $this->InboxHash->generate($inbox_id);
		$this->assertFalse(empty($hash));

		$this->InboxHash->recursive = -1;
		$result = $this->InboxHash->find('first', array(
			'conditions' => array(
				'InboxHash.inbox_id' => $inbox_id,
			),
		));

		$this->assertFalse(empty($result));

		$this->assertEqual($result['InboxHash']['hash'], $hash);
	}

	function testGenerateNullInboxId()
	{
		$inbox_id = null;
		$hash = $this->InboxHash->generate($inbox_id);
		$this->assertTrue(is_null($hash));
	}

	function testGenerateInvalidInboxId()
	{
		$inbox_id = 'invalid';
		$hash = $this->InboxHash->generate($inbox_id);
		$this->assertTrue(is_null($hash));
	}
}
?>

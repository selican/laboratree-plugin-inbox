<?php 
/* SVN FILE: $Id$ */
/* MessageArchive Fixture generated on: 2010-12-20 14:58:09 : 1292857089*/

class MessageArchiveFixture extends CakeTestFixture {
	var $name = 'MessageArchive';
	var $table = 'message_archives';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'message_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10),
		'sender_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10),
		'receiver_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10),
		'receiver_type' => array('type'=>'string', 'null' => false, 'default' => 'user'),
		'subject' => array('type'=>'string', 'null' => false, 'default' => NULL),
		'body' => array('type'=>'text', 'null' => false, 'default' => NULL),
		'date' => array('type'=>'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
	var $records = array(
		array(
			'id'  => 1,
			'message_id'  => 1,
			'sender_id'  => 1,
			'receiver_id'  => 1,
			'receiver_type' => 'group',
			'subject'  => 'First Test Group Message',
			'body'  => 'First Test Group Message',
			'date'  => '2010-12-20 14:58:09'
		),
		array(
			'id'  => 2,
			'message_id'  => 1,
			'sender_id'  => 2,
			'receiver_id'  => 1,
			'receiver_type' => 'group',
			'subject'  => 'Second Test Group Message',
			'body'  => 'Second Test Group Message',
			'date'  => '2010-12-20 14:58:09'
		),
		array(
			'id'  => 3,
			'message_id'  => 1,
			'sender_id'  => 2,
			'receiver_id'  => 1,
			'receiver_type' => 'group',
			'subject'  => 'Third Test Group Message',
			'body'  => 'Third Test Group Message',
			'date'  => '2010-12-20 14:58:09'
		),
	);
}
?>

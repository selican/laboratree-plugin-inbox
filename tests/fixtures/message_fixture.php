<?php 
/* SVN FILE: $Id$ */
/* Message Fixture generated on: 2010-12-20 14:58:15 : 1292857095*/

class MessageFixture extends CakeTestFixture {
	var $name = 'Message';
	var $table = 'messages';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'subject' => array('type'=>'text', 'null' => false, 'default' => NULL),
		'body' => array('type'=>'text', 'null' => false, 'default' => NULL),
		'date' => array('type'=>'datetime', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'date' => array('column' => 'date', 'unique' => 0))
	);
	var $records = array(
		array(
			'id'  => 1,
			'subject'  => 'Test',
			'body'  => 'Test',
			'date'  => '2010-12-20 14:58:15'
		),
		array(
			'id' => 2,
			'subject' => 'Group Invite',
			'body' => 'Group Invite From User 2 to User 1 for Group 1',
			'date' => '2010-12-20 14:58:15'
		),
		array(
			'id'  => 3,
			'subject'  => 'Test',
			'body'  => 'Test',
			'date'  => '2010-12-20 14:58:15'
		),
		array(
			'id' => 4,
			'subject' => 'RE: Test',
			'body' => 'Reply to Test',
			'date' => '2010-12-20 14:58:15'
		),
		array(
			'id' => 5,
			'subject' => 'Project Invite',
			'body' => 'Project Invite From User 3 to User 1 for Project 3',
			'date' => '2010-12-20 14:58:15'
		),
		array(
			'id' => 6,
			'subject' => 'Project Request',
			'body' => 'Project Request From User 1 to User 3 for Project 3',
			'date' => '2010-12-20 14:58:15'
		),

	);
}
?>

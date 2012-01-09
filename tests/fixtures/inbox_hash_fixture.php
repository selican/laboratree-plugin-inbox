<?php 
/* SVN FILE: $Id$ */
/* InboxHash Fixture generated on: 2010-12-20 14:57:42 : 1292857062*/

class InboxHashFixture extends CakeTestFixture {
	var $name = 'InboxHash';
	var $table = 'inbox_hashes';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'inbox_id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'hash' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'inbox_id' => array('column' => array('inbox_id', 'hash'), 'unique' => 0), 'hash' => array('column' => 'hash', 'unique' => 0))
	);
	var $records = array(
		array(
			'id'  => 1,
			'inbox_id'  => 3,
			'hash'  => 'HASHHASHHASHHASHHASHHASHHASHHASHHASHHASH',
		),
		array(
			'id'  => 3,
			'inbox_id'  => 4,
			'hash'  => 'HASHHASHHASHHASHHASHHASHHASHHASHHASHHASH',
		)
	);
}
?>

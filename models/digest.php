<?php
class Digest extends InboxAppModel
{
	var $name = 'Digest';

	var $belongsTo = array(
		'User' => array(
			'className'=> 'User',
			'foreignKey' => 'user_id',
		),
		'Inbox' => array(
			'className' => 'Inbox',
			'foreignKey' => 'inbox_id',
		),
	);
}
?>

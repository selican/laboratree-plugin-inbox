<?php
class Message extends InboxAppModel
{
	var $name = 'Message';
	var $html = array(
		'body',
	);

	var $validate = array(
		'subject' => array(
			'rule' => 'notEmpty',
			'message' => 'Subject must not be empty.',
		),
		'date' => array(
			'rule' => 'notEmpty',
			'message' => 'Date must not be empty.',
		),
	);

	var $hasMany = array(
		'Inbox' => array(
			'className' => 'Inbox',
			'foreignKey' => 'message_id',
			'dependent' => true,
			'exclusive' => true,
		),
		'Attachment' => array(
			'className' => 'Attachment',
			'foreignKey' => 'message_id',
			'dependent' => true,
			'exclusive' => true,
		),
	);
}
?>

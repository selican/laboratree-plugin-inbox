<?php
class InboxHash extends InboxAppModel
{
	var $name = 'InboxHash';

	/**
	 * Generates an Inbox Hash
	 *
	 * @param integer $inbox_id Inbox Id
	 *
	 * @return string Inbox Hash
	 */
	function generate($inbox_id)
	{
		if(!is_numeric($inbox_id) || $inbox_id < 1)
		{
			throw new InvalidArgumentException('Inbox ID');
		}

		$hash = sha1(uniqid('', true));
		$data = array();
		$data[$this->name] = array(
			'inbox_id' => $inbox_id,
			'hash' => $hash,
		);

		$this->create();
		if(!$this->save($data))
		{
			throw new RuntimeException('Unable to save inbox hash');
		}

		return $hash;
	}
}
?>

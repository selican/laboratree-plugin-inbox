<?php
	$html->addCrumb('Inbox', '/inbox/index'); 

	if($message['Inbox']['trash'])
	{
		$html->addCrumb('Trash', '/inbox/trash');
	}
	else if($message['Inbox']['type'] == 'sent')
	{
		$html->addCrumb('Sent', '/inbox/sent');
	}
	else if($message['Inbox']['type'] == 'received')
	{
		$html->addCrumb('Received', '/inbox/received');
	}

	$html->addCrumb('View Message', '/inbox/view/' . $message['Inbox']['id']);
?>
<div id="inbox-div"></div>
<script type="text/javascript">
	laboratree.inbox.makeView('inbox-div', '<?php echo $html->url('/inbox/view/' . $message['Inbox']['id'] . '.json'); ?>', '<?php echo $message['Inbox']['id']; ?>');
</script>

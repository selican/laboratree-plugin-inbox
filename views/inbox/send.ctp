<?php
	$html->addCrumb('Inbox', '/inbox/index');
	$html->addCrumb('New Message', '/inbox/send');

	echo $javascript->link('extjs/ux/FileUploadField.js', false);
?>
<div id="inbox-div"></div>
<script type="text/javascript">
	laboratree.inbox.makeSend('inbox-div', '<?php echo $html->url('/inbox/contacts.json'); ?>', '<?php echo $inbox_id; ?>', '<?php echo $parent; ?>');
</script>

<?php $html->addCrumb('Inbox', '/inbox/index'); ?>
<?php $html->addCrumb('Sent', '/inbox/sent'); ?>
<div id="inbox-div"></div>
<script type="text/javascript">
	laboratree.inbox.makeDashboard('inbox-div', '<?php echo $html->url('/inbox/sent.json'); ?>', 'Sent');
</script>

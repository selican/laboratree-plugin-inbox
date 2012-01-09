<?php $html->addCrumb('Inbox', '/inbox/index'); ?>
<?php $html->addCrumb('Received', '/inbox/received'); ?>
<div id="inbox-div"></div>
<script type="text/javascript">
	laboratree.inbox.makeDashboard('inbox-div', '<?php echo $html->url('/inbox/received.json'); ?>', 'Received');
</script>

<?php $html->addCrumb('Inbox', '/inbox/index'); ?>
<?php $html->addCrumb('Trash', '/inbox/trash'); ?>
<div id="inbox-div"></div>
<script type="text/javascript">
	laboratree.inbox.makeDashboard('inbox-div', '<?php echo $html->url('/inbox/trash.json'); ?>', 'Trash');
	laboratree.inbox.dashboard.addRestore();
</script>

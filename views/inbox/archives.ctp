<?php 
	$type = Inflector::pluralize(Inflector::humanize($table_type));
	$controller = Inflector::pluralize($table_type);
	if(isset($group_id) && !empty($group_id) && $table_type == 'project')
	{
		$html->addCrumb('Groups', '/groups/index'); 
		$html->addCrumb($group_name, '/groups/dashboard/' . $group_id);
	}
	$html->addCrumb($type, '/' . $controller . '/index');
	$html->addCrumb($name, '/' . $controller . '/dashboard/' . $table_id);
	$html->addCrumb('Message Archives', '/inbox/archives/' . $table_type . '/' . $table_id);
?>
<div id="archive-div"></div>
<script type="text/javascript">
	laboratree.inbox.makeArchives('archive-div', '<?php echo $html->url('/inbox/archives/' . $table_type . '/' . $table_id . '.json'); ?>');
</script>

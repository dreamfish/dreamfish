<?php if (empty($user)) { ?> <span style="color:#F00;font-weight:bold;">Note: You must be logged in to edit</span> <?php } ?>
<h1>All Projects</h1>
<?php foreach($projects as $project) { ?>
<a href="<?php echo url_for('project', 'edit', $project->id) ?>"><?php  echo $project->name ?></a><br>
<?php } ?>
<br>
<h1>My Projects</h1>
<?php foreach($myProjects as $project) { ?>
<a href="<?php echo url_for('project', 'edit', $project->id) ?>"><?php  echo $project->name ?></a><br>
<?php } ?>

<h2><a href="<?php echo url_for('project', 'new') ?>">Create New</a></h2>

<h1><?php echo $project->getName() ?></h1>
<p><?php echo $project->getDescription() ?></p>
<p>      
Created at: <?php echo $project->getCreatedAt() ?>
</p>
<p>
Updated at: <?php echo $project->getUpdatedAt() ?>
</p>
<?php foreach($project->getRequests() as $req): ?>
<?php echo $req->getRequest() ?> <?php echo $req->getDeadline() ?><br>
<?php endforeach; ?>
<hr />

<a href="<?php echo url_for('project/edit?id='.$project->getId()) ?>">Edit</a>
&nbsp;
<a href="<?php echo url_for('project/index') ?>">List</a>

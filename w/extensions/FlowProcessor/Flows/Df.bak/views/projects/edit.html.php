<style>
  label { display: block; width: 150px; float: left;}
  br { clear: both; }
  textarea { font-family: arial; }
</style>
<h1>Create / Edit Project </h1>
<form method="post" action="<?php echo url_for('project', 'save') ?>">
<label>Project Name: </label><input type="text" name="name" value="<?php echo $project->name?>"><br>
<label>Description:</label><textarea name="description" style="width:300px;height:80px;"><?php echo $project->description?></textarea><br>
<label>Members: </label> 
<select style="height:100px" multiple="true" name="members[]">
<?php echo HtmlHelper::multiple_select_list($users, "user_name", $project->members) ?>
</select> (you may select multiple)
<br>
<input type="hidden" name="id" value="<?php echo $project->id?>">
<input type="submit" name="action" value="Save">
<input type="submit" name="action" value="Delete" onClick="javascript:return confirm('Are you sure you wish to delete?');">
<a href="<?php echo url_for('project') ?>">Cancel</a>
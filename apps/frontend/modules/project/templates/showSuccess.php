<table>
  <tbody>
    <tr>
      <th>Project:</th>
      <td><?php echo $project->getProjectId() ?></td>
    </tr>
    <tr>
      <th>Title:</th>
      <td><?php echo $project->getTitle() ?></td>
    </tr>
    <tr>
      <th>Response wanted:</th>
      <td><?php echo $project->getResponseWanted() ?></td>
    </tr>
    <tr>
      <th>Description:</th>
      <td><?php echo $project->getDescription() ?></td>
    </tr>
  </tbody>
</table>

<hr />

<a href="<?php echo url_for('project/edit?project_id='.$project->getProjectId()) ?>">Edit</a>
&nbsp;
<a href="<?php echo url_for('project/index') ?>">List</a>

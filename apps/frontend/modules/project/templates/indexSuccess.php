<h1>Projects List</h1>

<table>
  <thead>
    <tr>
      <th>Project</th>
      <th>Title</th>
      <th>Response wanted</th>
      <th>Description</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($projects as $project): ?>
    <tr>
      <td><a href="<?php echo url_for('project/show?project_id='.$project->getProjectId()) ?>"><?php echo $project->getProjectId() ?></a></td>
      <td><?php echo $project->getTitle() ?></td>
      <td><?php echo $project->getResponseWanted() ?></td>
      <td><?php echo $project->getDescription() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <a href="<?php echo url_for('project/new') ?>">New</a>

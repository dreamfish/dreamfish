<h1>Projects List</h1>

<table>
  <thead>
    <tr>
      <th>Id</th>
      <th>Project type</th>
      <th>Description</th>
      <th>Stage</th>
      <th>Wiki page</th>
      <th>Contact</th>
      <th>Created at</th>
      <th>Updated at</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($projects as $project): ?>
    <tr>
      <td><a href="<?php echo url_for('project/show?id='.$project->getId()) ?>"><?php echo $project->getId() ?></a></td>
      <td><?php echo $project->getProjectTypeId() ?></td>
      <td><?php echo $project->getDescription() ?></td>
      <td><?php echo $project->getStageId() ?></td>
      <td><?php echo $project->getWikiPage() ?></td>
      <td><?php echo $project->getContactId() ?></td>
      <td><?php echo $project->getCreatedAt() ?></td>
      <td><?php echo $project->getUpdatedAt() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <a href="<?php echo url_for('project/new') ?>">New</a>

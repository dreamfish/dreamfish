<table>
  <tbody>
    <tr>
      <th>Id:</th>
      <td><?php echo $project->getId() ?></td>
    </tr>
    <tr>
      <th>Project type:</th>
      <td><?php echo $project->getProjectType() ?></td>
    </tr>
    <tr>
      <th>Description:</th>
      <td><?php echo $project->getDescription() ?></td>
    </tr>
    <tr>
      <th>Stage:</th>
      <td><?php echo $project->getStage() ?></td>
    </tr>
    <tr>
      <th>Wiki page:</th>
      <td><?php echo $project->getWikiPage() ?></td>
    </tr>
    <tr>
      <th>Contact:</th>
      <td><?php echo $project->getContactId() ?></td>
    </tr>
    <tr>
      <th>Created at:</th>
      <td><?php echo $project->getCreatedAt() ?></td>
    </tr>
    <tr>
      <th>Updated at:</th>
      <td><?php echo $project->getUpdatedAt() ?></td>
    </tr>
  </tbody>
</table>

<hr />

<a href="<?php echo url_for('project/edit?id='.$project->getId()) ?>">Edit</a>
&nbsp;
<a href="<?php echo url_for('project/index') ?>">List</a>

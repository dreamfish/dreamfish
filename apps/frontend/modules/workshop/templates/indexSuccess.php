<h1>Workshops List</h1>

<table>
  <thead>
    <tr>
      <th>Id</th>
      <th>Offer type</th>
      <th>Avatar</th>
      <th>Description</th>
      <th>Welcome status</th>
      <th>Embedded video</th>
      <th>Contact</th>
      <th>Payment method</th>
      <th>Created at</th>
      <th>Updated at</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($workshops as $workshop): ?>
    <tr>
      <td><a href="<?php echo url_for('workshop/show?id='.$workshop->getId()) ?>"><?php echo $workshop->getId() ?></a></td>
      <td><?php echo $workshop->getOfferTypeId() ?></td>
      <td><?php echo $workshop->getAvatarId() ?></td>
      <td><?php echo $workshop->getDescription() ?></td>
      <td><?php echo $workshop->getWelcomeStatusId() ?></td>
      <td><?php echo $workshop->getEmbeddedVideo() ?></td>
      <td><?php echo $workshop->getContactId() ?></td>
      <td><?php echo $workshop->getPaymentMethodId() ?></td>
      <td><?php echo $workshop->getCreatedAt() ?></td>
      <td><?php echo $workshop->getUpdatedAt() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <a href="<?php echo url_for('workshop/new') ?>">New</a>

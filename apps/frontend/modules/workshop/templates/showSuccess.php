<table>
  <tbody>
    <tr>
      <th>Id:</th>
      <td><?php echo $workshop->getId() ?></td>
    </tr>
    <tr>
      <th>Offer type:</th>
      <td><?php echo $workshop->getOfferTypeId() ?></td>
    </tr>
    <tr>
      <th>Avatar:</th>
      <td><?php echo $workshop->getAvatarId() ?></td>
    </tr>
    <tr>
      <th>Description:</th>
      <td><?php echo $workshop->getDescription() ?></td>
    </tr>
    <tr>
      <th>Welcome status:</th>
      <td><?php echo $workshop->getWelcomeStatusId() ?></td>
    </tr>
    <tr>
      <th>Embedded video:</th>
      <td><?php echo $workshop->getEmbeddedVideo() ?></td>
    </tr>
    <tr>
      <th>Contact:</th>
      <td><?php echo $workshop->getContactId() ?></td>
    </tr>
    <tr>
      <th>Payment method:</th>
      <td><?php echo $workshop->getPaymentMethodId() ?></td>
    </tr>
    <tr>
      <th>Created at:</th>
      <td><?php echo $workshop->getCreatedAt() ?></td>
    </tr>
    <tr>
      <th>Updated at:</th>
      <td><?php echo $workshop->getUpdatedAt() ?></td>
    </tr>
  </tbody>
</table>

<hr />

<a href="<?php echo url_for('workshop/edit?id='.$workshop->getId()) ?>">Edit</a>
&nbsp;
<a href="<?php echo url_for('workshop/index') ?>">List</a>

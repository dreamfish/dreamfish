<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>
<?php echo stylesheet_tag('datePicker.css') ?>
<?php echo javascript_include_tag('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js'); ?>
<?php echo javascript_include_tag('date.js', 'jquery.datePicker.js', 'datepicker.js') ?>

<form action="<?php echo url_for('project/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          &nbsp;<a href="<?php echo url_for('project/index') ?>">Back to list</a>
          <?php if (!$form->getObject()->isNew()): ?>
            &nbsp;<?php echo link_to('Delete', 'project/delete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure?')) ?>
          <?php endif; ?>
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>

    <?php echo $form['id'] ?>
    <?php echo $form['_csrf_token'] ?> 

    <?php echo $form['name']->renderRow() ?>
    <?php echo $form['request']->renderRow() ?>
    <?php echo $form['deadline']->renderRow() ?>
    <?php echo $form['description']->renderRow() ?>
    <?php echo $form['skills_list']->renderRow() ?>
    <tr>
      <td colspan="2">
        Value creation: What value can this project create? What value can you offer a worker? You can select up to two per category
      </td>
    </tr>
<!--    <?php echo $form['values_list']->renderRow() ?> -->
    <?php echo $form['value_security_list']->renderRow() ?>
    <?php echo $form['value_achievement_list']->renderRow() ?>
    <?php echo $form['value_learning_list']->renderRow() ?>
    <?php echo $form['value_global_list']->renderRow() ?>





    </tbody>
  </table>
</form>

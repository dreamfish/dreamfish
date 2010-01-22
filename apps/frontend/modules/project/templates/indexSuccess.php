<style>
  .project-request-work { font-size: 1.1em; }
  #project-list { border:1px solid #999999; width:500px; }
  #project-list th, #project-list td { padding: 5px; }
  #project-tabs { margin-top: 20px; }
  #project-tabs a { 
    display: block; float:left; border:1px solid #999999;
    width:150px; height: 20px;
    background: #f0f0f0; margin-right: 10px;
    color: #000000;
    border-bottom: 0px;
    text-align: center; vertical-align: middle;
  }
  #project-tabs a.selected { background-color:#c0c0c0; }
  .stripe { background-color: rgb(227,243,249); }
  th { text-align:left; }
</style>
 <?php echo javascript_include_tag('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js'); ?>
<script>
  $(function() {
    $('#project-list tr:odd').addClass('stripe');
  });
</script>
<span class="project-request-label">
You have a project to do -> <a href="<?php echo url_for('project/new') ?>">Request a good work</a>
</span>

<div id="project-tabs">
<a class="selected" href="<?php echo url_for('project/')?>">Project Requests</a> 
<a href="<?php echo url_for('project/all')?>">All Projects</a>
</div>
<br style="clear: both">

<div id="project-list">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <thead>
    <tr>
      <th>Dreamfisher</th>
      <th>Project Requests</th>
      <th>Reply by</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($projects as $project): ?>
    <tr>
      <td>
        <img src="http://www.gravatar.com/avatar/3b3be63a4c2a439b013787725dfce802.jpg"></img>
        <br>
        Test
      </td>

      <td>
      
        <?php echo $project->getRequest() ?>
        <a href="<?php echo url_for('project/show?id='.$project->getId()) ?>">View</a></td>
      
      </td>

      <td style="width:75px">
        <?php echo $project->getDeadline() ?><br>
        <a href="<?php echo url_for('project/reply?id='.$project->getId())?>">
          Reply
        </a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>


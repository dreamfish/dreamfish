    <head>
    <?php include_http_metas() ?>    
    <?php include_metas() ?>    
    <?php include_stylesheets() ?>    
    <?php include_javascripts() ?> 
    <style>
    /*move to standard css*/
.error, .notice, .success {padding:.8em;margin-bottom:1em;border:2px solid #ddd;}
.error {background:#FBE3E4;color:#8a1f11;border-color:#FBC2C4;}
.notice {background:#FFF6BF;color:#514721;border-color:#FFD324;}
.success {background:#E6EFC2;color:#264409;border-color:#C6D880;}
.error a {color:#8a1f11;}
.notice a {color:#514721;}
.success a {color:#264409;}
    </style>
    </head>
    <?php if ($sf_user->hasFlash('success')): ?>
      <div class="success">
      <?php echo $sf_user->getFlash('success') ?>
      </div>
    <?php endif; ?>
    <?php echo $sf_content ?>

<?php
$asset = $view->helper('Asset');

// If page title has been set by sub-template
if($pageTitle = $view->head()->title()) {
	$title = $pageTitle . " - Alloy Framework";
} else {
	$title = "Alloy Framework App";
  $pageTitle = "Page Title";
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Styles -->
    <?php echo $asset->stylesheet('bootstrap.min.css'); ?>
    <?php echo $asset->stylesheet('app.css'); ?>

    <!-- Fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
  </head>

  <body>

    <div class="container">
      <div class="row">
        <div class="span12">
          <div class="page-header">
            <h1><?php echo $pageTitle; ?></h1>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="span12">
          <div class="content">
          
            <?php
            // Display errors
            if($errors = $view->errors()):
            ?>
            <div class="alert alert-error">
              <p><strong>Oops!</strong> There were some errors with your request:</p>
              <ul>
              <?php foreach($errors as $field => $fieldErrors): ?>
                <?php foreach($fieldErrors as $error): ?>
                  <li><?php echo $error; ?></li>
                <?php endforeach; ?>
              <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>

            <?php
            // Display main content
            echo $content;
            ?>

          </div>
        </div>
      </div>

      <footer>
        <p>&copy; Company <?php echo date('Y'); ?></p>
      </footer>
    </div>

    <!-- Javascripts -->
    <?php echo $asset->script('bootstrap.min.js'); ?>

  </body>
</html>
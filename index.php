<?php
session_start();
include 'functions.php';

//check that we've been given a slug to resolve
if ( isset( $_GET['goto'] ) ) {
          
  goto_redirect( $_GET['goto'] );

} else {

  http_response_code(403);
  $code = "403 Forbidden";

}

$connection->close()
?>
<!doctype html>
<html lang="en">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $code ?></title>

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.14.1/dist/css/uikit.min.css" />

    <!-- UIkit JS -->
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.14.1/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.14.1/dist/js/uikit-icons.min.js"></script>

  </head>
  <body>
    <div class="uk-container">

      <h1><?php echo $code ?></h1>

      <p>You have reached this page in error.  Please check your url.</p>

    </div>
  </body>
</html>
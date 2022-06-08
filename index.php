<?php
session_start();
include 'admin/functions.php';
?>
<!doctype html>
<html lang="en">
  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>URL Shortener</title>

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.14.1/dist/css/uikit.min.css" />

    <!-- UIkit JS -->
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.14.1/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3.14.1/dist/js/uikit-icons.min.js"></script>
    
  </head>
  <body>

    <div class="uk-container">
      <h1>Link Redirection</h1>

      <?php
        //what to do if we've been given a slug to resolve
        if ( isset( $_GET['goto'] ) ) {
          
          goto_redirect( $_GET['goto'] );

        }
      ?>

      <p>You have reached this page in error.</p>

    </div>
  </body>
</html>
<?php
  mysqli_close( $connection );
?>
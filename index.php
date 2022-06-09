<?php
session_start();
include 'admin/functions.php';

//check that we've been given a slug to resolve
if ( isset( $_GET['goto'] ) ) {
          
  goto_redirect( $_GET['goto'] );

} else {

  http_response_code(404);

}

$connection->close()
?>
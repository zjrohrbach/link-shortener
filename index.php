<?php
session_start();
include 'functions.php';
use_this_db();


//check that we've been given a slug to resolve
if ( isset( $_GET['goto'] ) ) {
          
  goto_redirect( $_GET['goto'] );

} else {

  echo "Redirect slug not given.";

}

$connection->close()
?>
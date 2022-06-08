<?php

  //config
  $base_url = "http://localhost:3000/index.php?goto=";
  $alert_timeout = 3000;

  //password protection for this page (this is very poor security)
  //(it is recommended that you use Apache or Nginx's security instead)
  $correct_pwd = "password";
  $password_required = false;

  // Database Connection Information
  $dbhost = '127.0.0.1';
  $dbuser = 'link-maker';
  $dbpass = 'rohrbachsci';
  $dbname = 'links';

  $connection = mysqli_connect( $dbhost, $dbuser, $dbpass, $dbname );

  if ( ! $connection ) {
    die( "Could not connect to database: " . mysqli_error($connection) );
  }

  //================== SEND ALERT TO UIKIT ==============================
  function print_alert_js ( $message, $status ) {
    echo '
      <script>
        sendAlert(
          "' . $message . '",
          "' . $status . '"
        );
      </script>
    ';
  }

  //================== REDIRECT TO A SLUG ==============================
  function goto_redirect ( $slug ) {
    global $connection;

    //find the db entry for the slug
    $query = 'SELECT url, link_id FROM redirects WHERE slug = "' . $slug . '";';
    $result = mysqli_query( $connection, $query );
    $row = mysqli_fetch_array( $result );

    //make sure the slug exists
      if ( $row ) {

        $redirect_link = $row[0];
        $link_id = $row[1];

        if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
          $referer = $_SERVER['HTTP_REFERER'];
        } else {
          $referer = 'unreferred';
        }
        
        //register the visit
        $query = 'INSERT INTO visits (link_id, referer, visit_date, ip_addr) VALUES (' . $link_id . ', "' . $referer . '", NOW(), "' . $_SERVER['REMOTE_ADDR'] . '");';
        $result = mysqli_query( $connection, $query );
        
        //redirect
        header( 'Location: ' . $redirect_link );

      } else {

        //if slug doesn't exit
        echo "Unfortunately, that is an invalid link.";

      }

  }

  //================== ADD NEW LINK ==============================
  function add_new_link ( $slug, $url ) {
    global $connection;

    //make sure slug doesn't exist
    $query = 'SELECT COUNT(slug) FROM redirects WHERE slug="' . $slug . '";';
    $result = mysqli_query( $connection, $query ); 
    $row = mysqli_fetch_array( $result );

    if ( $row[0] != 0 ) {

      print_alert_js (
        'Unfortunately, the slug <em>' . $slug . '</em> has already been taken.',
        'danger'
      );

      echo '
        <script>
          /*repopulate form values*/
          window.onload = function() {
            document.getElementById("url").value = "' . $url . '";
            document.getElementById("slug").value = "' . $slug . '";
            document.getElementById("slug").classList.add("uk-form-danger");
          }
        </script>
      ';

    } else {

      $query = 'INSERT INTO redirects (slug, url, date_created) VALUES ("' . $slug . '", "' . $url . '", NOW() );';
      $result = mysqli_query( $connection, $query );    
    
      if ( $result ) {

        print_alert_js (
          'The url for <em>' . $slug . '</em> has been added.',
          'success'
        );

      } else {

        print_alert_js (
          'There was an error with the database: ' . mysqli_error( $result ),
          'danger'
        );
      
      }
    } 
  }

  //================== DELETE LINK ==============================
  function delete_link ( $link_id ) {
    global $connection;

    $query = 'DELETE FROM redirects WHERE link_id=' . $link_id . ';';
        $result = mysqli_query( $connection, $query );    
        
        if ( $result ) {

          print_alert_js (
            'The record has been deleted',
            'warning'
          );

        } else {

          print_alert_js (
            'There was an error with the database: ' . mysqli_error( $result ),
            'danger'
          );

        }
  }

  //================== SHOW DETAILS ==============================
  function show_details ( $link_id ) {
    global $connection;

    $query = '
            SELECT redirects.link_id, redirects.slug, redirects.url, redirects.date_created, visits.visit_date, visits.ip_addr, visits.referer
              FROM redirects
              LEFT JOIN visits
            ON redirects.link_id = visits.link_id
            WHERE redirects.link_id=' . $link_id . '
            ORDER BY visit_date DESC;';
    $result = mysqli_query( $connection, $query );

    $array = array();
    
    while ( $row = mysqli_fetch_array( $result ) ) {
        $slug = $row['slug'];
        $url = $row['url'];
        $date_created = $row['date_created'];
        if ( $row['visit_date'] != NULL ) {
          $array[] = '<li>' . $row['visit_date'] .': from ip addr '. $row['ip_addr'] . ', referer: ' . $row['referer'] . '</li>';
        } else {
          $array[] = '<li>No visits... yet!</li>';
        }
    }


    echo '
      <div id="detail-modal" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
          <button class="uk-modal-close-default" type="button" uk-close></button>
          <h2 class="uk-modal-title">Visits to <em>' . $slug . '</em></h2>
          <p class="uk-text-small uk-overflow-hidden">' . $url . '</p>
          <ul>
    ';

    foreach ( $array as $record ) {
      echo $record;
    }
    echo "\n</ul>";

    echo '
        </div>
      </div>
      <script>
        UIkit.modal("#detail-modal").show();
      </script>
    ';
  }


?>
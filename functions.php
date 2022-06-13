<?php
  //config
  $base_url = "http://localhost:3000/index.php?goto=";  //On an apache server, this could be http://localhost:3000/path/to/this/dir/
  $alert_timeout = 3000;
  $dbhost = 'localhost';
  $dbuser = 'link-maker';
  $dbpass = 'rohrbachsci';
  $dbname = 'links';

  $connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

  if ($connection->connect_error) {
    echo 'Connection to database failed: ' . $connection->connect_error;
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
    $query = 'SELECT url, link_id FROM redirects WHERE slug = ?;';
    $statement = $connection->prepare($query);
    $statement->bind_param('s', $slug);
    $statement->execute();
    $result = $statement->get_result();  //->fetch_row();

    if ( $result->num_rows == 1 ) {
      //this if statement makes sure the url exists
      $row = $result->fetch_assoc();
      $redirect_link = $row['url'];
      $link_id = $row['link_id'];

      // set referer and ip address if given; otherwise say so
      $referer = ( isset( $_SERVER['HTTP_REFERER'] ) ) ? filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL) : 'unreferred';
      $ip_addr = ( filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ) ? $ip_addr = $_SERVER['REMOTE_ADDR']  : 'unknown';

      
      //register the visit 
      $query = 'INSERT INTO visits (link_id, referer, visit_date, ip_addr) VALUES (?, ?, NOW(), ?);';
      $result = $connection->prepare($query);
      $result->bind_param('iss', $link_id, $referer, $ip_addr);
      $result->execute();

      //redirect
      header( 'Location: ' . $redirect_link );
      exit();

    } else {
      
      http_response_code(404);
      $code = "404 Not Found";
      
    }

  }

  //================== ADD NEW LINK ==============================
  function add_new_link ( $slug, $url ) {
    global $connection;

    //make sure slug doesn't exist
    $query = 'SELECT COUNT(slug) FROM redirects WHERE slug=?;';
    $statement = $connection->prepare($query);
    $statement->bind_param('s', $slug);
    $statement->execute();
    $result = $statement->get_result(); 
    $row = $result->fetch_row();
    

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

      $query = 'INSERT INTO redirects (`slug`, `url`, `date_created`) VALUES (?, ?, NOW() );';
      $result = $connection->prepare($query);
      $result->bind_param('ss', $slug, $url);
      $result->execute();
    
      if ( $result ) {

        print_alert_js (
          'The url for <em>' . $slug . '</em> has been added.',
          'success'
        );

      } else {

        print_alert_js (
          'There was an error with the database.',
          'danger'
        );
      
      }
    } 
  }

  //================== DELETE LINK ==============================
  function delete_link ( $link_id ) {
    global $connection;

    $query = 'DELETE FROM redirects WHERE link_id=?;';
    $result = $connection->prepare($query);
    $result->bind_param('i', $link_id);
    $result->execute();
        
        if ( $result ) {

          print_alert_js (
            'The record has been deleted.',
            'warning'
          );

        } else {

          print_alert_js (
            'There was an error with the database.',
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
            WHERE redirects.link_id=?
            ORDER BY visit_date DESC;';
    $statement = $connection->prepare($query);
    $statement->bind_param('i', $link_id);
    $statement->execute();
    $result = $statement->get_result();

    $array = array();
    
    while ( $row = $result->fetch_assoc() ) {
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
    echo '
          <li>Created on ' . $date_created . '</li>
        </ul>
    ';

    echo '
        </div>
      </div>
      <script>
        UIkit.modal("#detail-modal").show();
      </script>
    ';
  }


?>
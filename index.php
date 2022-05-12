<?php
session_start();

  //password protection for this page (yes, this is very poor security, 
  //but there's not much trouble anyone can get into if they hack this)
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
      <h1>Link Shortener</h1>

      <?php
        //what to do if we've been given a slug to resolve
        if ( isset( $_GET['goto'] ) ) {
          
          //find the db entry for the slug
          $query = 'SELECT url, link_id FROM redirects WHERE slug = "' . $_GET['goto'] . '";';
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

        //what to do if the form has been submitted
        if ( isset( $_POST['url'] ) ) {

          //make sure slug doesn't exist
          $query = 'SELECT COUNT(slug) FROM redirects WHERE slug="' . $_POST['slug'] . '";';
          $result = mysqli_query( $connection, $query ); 
          $row = mysqli_fetch_array( $result );

          if ( $row[0] != 0 ) {

            echo "That slug's taken!";

          } else {

            $query = 'INSERT INTO redirects (slug, url, date_created) VALUES ("' . $_POST['slug'] . '", "' . $_POST['url'] . '", NOW() );';
            $result = mysqli_query( $connection, $query );    
          
          if ( $result ) {
            echo $_POST['slug'] . ' -> ' . $_POST['url'] . ' has been added.';
            } else {
              echo 'There was an error with the database: ' . mysqli_error( $result );
            }
          } 
        }

        //what to do with delete request
        if ( isset( $_GET['delete'] ) ) {
          $query = 'DELETE FROM redirects WHERE link_id=' . $_GET['delete'] . ';';
          $result = mysqli_query( $connection, $query );    
          
          if ( $result ) {
              echo 'Record has been deleted.';
          } else {
              echo 'There was an error with the database: ' . mysqli_error( $result );
          }
        }

        //showing details for individual link
        if ( isset( $_GET['detail'] ) ) {
          $query = '
            SELECT redirects.link_id, redirects.slug, redirects.url, redirects.date_created, visits.visit_date, visits.ip_addr, visits.referer
              FROM redirects
              LEFT JOIN visits
            ON redirects.link_id = visits.link_id
            WHERE redirects.link_id=' . $_GET['detail'] . '
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

          echo "<h2>Visits to '<em>$slug</em>' ($url)</h2>";
          echo "\n<ul>";

          foreach ( $array as $record ) {
            echo $record;
          }
          echo "\n</ul>";

        }

        //process login
        if ( isset( $_POST['pwd'] ) ) {
          if ( $_POST['pwd'] == $correct_pwd ) {
            $_SESSION['login'] = true;
          } else {
              echo "Incorrect Login.";
          }
        }

        //kick anyone out who isn't supposed to be here
        if ( !isset($_SESSION['login']) && $password_required ) {
            echo '
            <form action="index.php" method="post">
              <div>
                <input type="password" id="pwd" name="pwd" />
                <input type="submit" value="Login" />
              </div>
            </form>
            ';
            exit;
        }

      ?>
      <section class="uk-section uk-section-muted uk-section-large uk-padding-remove-vertical"> 
        <h2>Shorten a new link</h2>
        <form action="index.php" method="post">
          <div class="uk-grid uk-grid-small">
            <div>
              <label class="uk-form-label" for="url">url:</label> 
              <input class="uk-form-input" type="url" id="url" name="url" pattern="https?://.+" placeholder="https://" required />
            </div>
            <div>
              <label class="uk-form-label" for="slug">slug:</label> 
              <input class="uk-form-input" type="text" id="slug" name="slug" pattern="[0-9a-zA-Z\-]+" required />
            </div>
            <div>
              <input class="uk-button uk-button-primary uk-button-small" type="submit" value="Submit" />
            </div>
          </div>
        </form>
      </section>

      <h2>Active slugs and redirects</h2>

      <div class="uk-grid-small" uk-grid>
      <?php
        //pull all the slug-link pairs for display
        $query = '
          SELECT redirects.link_id, redirects.slug, redirects.url, redirects.date_created, COUNT(visits.visit_date) AS num_visits, MAX(visits.visit_date) AS last_visit
            FROM redirects
            LEFT JOIN visits
            ON redirects.link_id = visits.link_id
          GROUP BY link_id;';
        $result = mysqli_query( $connection, $query );


        while ( $row = mysqli_fetch_array( $result ) ) {
          echo '
          <div class="uk-card uk-card-body uk-card-default uk-width-medium uk-card-hover">
          <h3 class="uk-card-title">' . $row['slug'] . '</h3>
          <p><a href="' . $row['url'] . '">' . $row['url'] . '</a><a href="index.php?delete=' . $row['link_id'] . '" class="uk-card-badge uk-label uk-label-danger" uk-close></a></p>
          <p>[<a href="index.php?detail=' . $row['link_id'] . '">list visitors</a>]</p>
          <ul>
            <li>Date Created: ' . $row['date_created'] . '</li>
            <li>Number of Visits: ' . $row['num_visits'] . '</li>
            <li>Last Visit: ' . $row['last_visit'] . '</li>
          </ul>
          </div>
          ';
        }

      ?>
      
      </div>
    </div>
  </body>
</html>
<?php
  mysqli_close( $connection );
?>
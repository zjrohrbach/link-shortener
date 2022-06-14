<?php
  include 'functions.php';

  $query = array (
    'DROP TABLE IF EXISTS visits;',
    'DROP TABLE IF EXISTS redirects;',
    '
    CREATE TABLE redirects (
      link_id SMALLINT PRIMARY KEY AUTO_INCREMENT NOT NULL,
      slug VARCHAR(20),
      url VARCHAR(2048),
      date_created TIMESTAMP,
      INDEX(slug(10))
    );
    ',
    'INSERT INTO redirects (slug, url, date_created) VALUES ("ex", "https://www.example.com", NOW());',
    'INSERT INTO redirects (slug, url, date_created) VALUES ("goog", "https://www.google.com", NOW());',
    '
    CREATE TABLE visits (
      visit_id SMALLINT PRIMARY KEY AUTO_INCREMENT NOT NULL,
      link_id SMALLINT,
      referer VARCHAR(2048),
      visit_date TIMESTAMP,
      ip_addr varchar (15),
      FOREIGN KEY (link_id) 
        REFERENCES redirects (link_id)
        ON DELETE CASCADE
    );
    '
  );
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

      <h1>Database Initialization</h1>
      <?php

        if ( isset( $_POST['start_initialize'] ) ) {

          echo '
            <h2>Executing SQL statements...</h2>
          ';

          foreach ( $query as $this_statement ) {
            $connection->query($this_statement);

            echo '<p><pre><code>' . $this_statement . '</code></pre>';

            if ( $connection->connect_error ) {
              echo '
              <span class="uk-label uk-label-danger">Error</span> <br />' . $connection->connect_error . '</p><hr />
              ';
              break;
            } else {
              echo '
                <span class="uk-label uk-label-success">SUCCESS!</span></p><hr />
              ';
            }

          }

        } else {
          echo '
            <form action="db_initialize.php" method="post">
              <p>
                Submitting this form will override all tables in your database.  
                <strong>Please only procede if you are absolutely sure.</strong>
              </p>
              <button type="button" class="uk-button uk-label uk-label-danger" uk-tooltip="Initialize Database">Initialize Database</button>
              <div class="uk-width-5-6" uk-drop="mode: click">
                <div class="uk-card uk-card-body uk-card-default uk-background-muted">
                  <div class="uk-padding-remove">
                    Are you sure you want to initialize the database?
                    <a class="uk-label uk-drop-close">Cancel</a>
                    <input class="uk-button-danger" type="submit" id="start_initialize" name="start_initialize" value="Confirm Initialize" />
                  </div>
                </div>
              </div>
            </form>
          ';
        }

      ?>

      <p><a href="admin.php">Return to Admin Panel</a></p>
    </div>
  </body>
</html>
<?php $connection->close() ?>
<?php
  include 'functions.php';

  $query = array();

  $query[] = array (
      'Drop Database',
      'DROP DATABASE IF EXISTS ' . $dbname . ';'
  );

  $query[] = array (
    'Create Database',
    'CREATE DATABASE ' . $dbname . ';'
  );

  $query[] = array (
    'USE Database',
    'USE ' . $dbname . ';'
  );

  $query[] = array (
    'Table `redirects` Created',
    '
      CREATE TABLE redirects (
        link_id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
        slug VARCHAR(20),
        url VARCHAR(2048),
        date_created TIMESTAMP
      );
    '
  );

  $query[] = array (
    'Sample Data 1 Inserted',
    'INSERT INTO redirects (slug, url, date_created) VALUES ("ex", "https://www.example.com", NOW());'
  );

  $query[] = array (
    'Sample Data 2 Inserted',
    'INSERT INTO redirects (slug, url, date_created) VALUES ("goog", "https://www.google.com", NOW());'
  );

  $query[] = array (
    'Table `visits` Created',
    '
      CREATE TABLE visits (
        visit_id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
        link_id INTEGER,
        referer VARCHAR(2048),
        visit_date TIMESTAMP,
        ip_addr varchar (15),
        FOREIGN KEY (link_id) REFERENCES redirects (link_id)
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

    <script>

      function sendAlert(thisMessage, thisStatus) {
        /* function for sending alert to UIKit */
        UIkit.notification({
                    message: thisMessage,
                    status: thisStatus,
                    timeout: <?php echo $alert_timeout; ?>
        });
      }
      
      function copyText(inputID) {
        /* Get the text field */
        var textForClipboard = document.getElementById(inputID);

        /* Select the text field */
        textForClipboard.select();
        textForClipboard.setSelectionRange(0, 99999); /* For mobile devices */

        /* Copy the text inside the text field */
        navigator.clipboard.writeText(textForClipboard.value);

        /* Alert the copied text */
        sendAlert("Copied to clipboard.", "success");
      } 


    </script>
  </head>
  <body>
    <div class="uk-container">

      <h1>Database Initialization</h1>

      <?php

        if ( isset( $_POST['start_initialize'] ) ) {

          echo '<ul>';

          foreach ( $query as $this_statement ) {
            $connection->query($this_statement[1]);

            if ( $connection ) {
              echo '
                <li>Query [ ' . $this_statement[0] . ' ]: SUCCESS!</li>
              ';
            } else {
              echo '
                <li>Query [ ' . $this_statement[0] . ' ]: Error ' . $connection->connect_error . '</li>
              ';
              break;
            }

          }

          echo '</ul>';

        } else {
          echo '
            <form action="db_initialize.php" method="post">
              <p>
                Submitting this form will override any database called `links` that you have.  
                Please only procede if you are absolutely sure.
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
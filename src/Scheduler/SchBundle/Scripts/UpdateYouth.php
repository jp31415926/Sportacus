<?php
include_once __DIR__ . '/parameters.php';

/*
+-------------------+
| Tables_in_symfony |
+-------------------+
| AgeGroup          |
| Game              |
| Location          |
| Log               |
| LogGame           |
| Message           |
| MobileProvider    |
| OffAssign         |
| OffPos            |
| OffTeam           |
| Project           |
| Region            |
| Team              |
| Test              |
| fos_user          |
| msg_user          |
| offteam_offpos    |
+-------------------+

User
+--------------------------+--------------+------+-----+---------+----------------+
| Field                    | Type         | Null | Key | Default | Extra          |
+--------------------------+--------------+------+-----+---------+----------------+
| id                       | int(11)      | NO   | PRI | NULL    | auto_increment |
| username                 | varchar(255) | NO   |     | NULL    |                |
| username_canonical       | varchar(255) | NO   | UNI | NULL    |                |
| email                    | varchar(255) | NO   |     | NULL    |                |
| email_canonical          | varchar(255) | NO   | UNI | NULL    |                |
| enabled                  | tinyint(1)   | NO   |     | NULL    |                |
| salt                     | varchar(255) | NO   |     | NULL    |                |
| password                 | varchar(255) | NO   |     | NULL    |                |
| last_login               | datetime     | YES  |     | NULL    |                |
| locked                   | tinyint(1)   | NO   |     | NULL    |                |
| expired                  | tinyint(1)   | NO   |     | NULL    |                |
| expires_at               | datetime     | YES  |     | NULL    |                |
| confirmation_token       | varchar(255) | YES  |     | NULL    |                |
| password_requested_at    | datetime     | YES  |     | NULL    |                |
| roles                    | longtext     | NO   |     | NULL    |                |
| credentials_expired      | tinyint(1)   | NO   |     | NULL    |                |
| credentials_expire_at    | datetime     | YES  |     | NULL    |                |
| created                  | datetime     | NO   |     | NULL    |                |
| updated                  | datetime     | NO   |     | NULL    |                |
| first_name               | varchar(64)  | NO   |     | NULL    |                |
| last_name                | varchar(64)  | NO   |     | NULL    |                |
| phone_home               | varchar(20)  | YES  |     | NULL    |                |
| phone_mobile             | varchar(20)  | YES  | UNI | NULL    |                |
| ayso_id                  | varchar(10)  | NO   | UNI | NULL    |                |
| role_referee             | tinyint(1)   | YES  |     | NULL    |                |
| role_scheduler           | tinyint(1)   | YES  |     | NULL    |                |
| mobile_provider_id       | int(11)      | YES  | MUL | NULL    |                |
| mobile_provider_verified | tinyint(1)   | YES  |     | NULL    |                |
| region_id                | int(11)      | YES  | MUL | NULL    |                |
| option_change_email      | tinyint(1)   | YES  |     | NULL    |                |
| option_change_text       | tinyint(1)   | YES  |     | NULL    |                |
| option_reminder_email    | tinyint(1)   | YES  |     | NULL    |                |
| option_reminder_text     | tinyint(1)   | YES  |     | NULL    |                |
| option_assignment_email  | tinyint(1)   | YES  |     | NULL    |                |
| option_assignment_text   | tinyint(1)   | YES  |     | NULL    |                |
| current_project_id       | int(11)      | YES  | MUL | NULL    |                |
| ayso_my                  | varchar(64)  | YES  |     | NULL    |                |
| role_referee_admin       | tinyint(1)   | YES  |     | NULL    |                |
| is_youth                 | tinyint(1)   | YES  |     | NULL    |                |
+--------------------------+--------------+------+-----+---------+----------------+
*/

if ($argc < 3) {
  echo "Usage: {$argv[0]} <test:0,1> <input filename>\n";
  exit;
}

$test = $argv[1];
$games_csv_file = $argv[2];
$lines = 0;

function DoQuery($mysqli, $query)
{
  $a = array();
  if ($result = $mysqli->query($query . ';')) {
    if ($result !== true) {
      /* fetch associative array */
      while ($row = $result->fetch_assoc()) {
        if (array_key_exists('id', $row)) {
          $a[$row['id']] = $row;
        } else {
          $a[] = $row;
        }
      }

      /* free result set */
      $result->free();
    }
  } else {
    printf("query '%s' failed: %s\n", $query, $mysqli->error);
    return false;
  }

  echo "$query\n";
  return $a;
}


function getRow($handle)
{
  global $line;

  while (($row = fgetcsv($handle)) !== false) {
    $line++;
    // a blank line returns array of one null. if found, skip it.
    if ((1 == count($row)) && (NULL === $row[0]))
      continue;

    return $row;
  }
  return false;
}

$mysqli = new mysqli($mysql_server, $mysql_user, $mysql_password, $myqsl_database);
if ($mysqli->connect_error) {
  printf("Connect failed: %d %s\n", $mysqli->connect_errno, $mysqli->connect_error);
  exit();
}
echo 'Connect success: ' . $mysqli->host_info . "\n";

$csv = fopen($games_csv_file, 'r');
$headers = getRow($csv);
//print_r($headers);

while ($row = getRow($csv)) //$row = getRow($csv);
{
  $skip_count = 0;
  $q = '';
  $a = array_combine($headers, $row);
  //print_r($a);
  $aysoid = $a['AYSOID'];

  $q = "UPDATE fos_user SET is_youth=1 WHERE ayso_id='$aysoid' AND role_referee=1";
  if (!$test)
    DoQuery($mysqli, $q);
  else
    echo $q . "\n";
}

//print_r($a);

$mysqli->close();

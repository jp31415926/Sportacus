<?php
include_once __DIR__ . '/parameters.php';

/*

Imports "Volunteer Certifications Report" from eayso.org

Reads in volunteer certifications report export file (CSV) and updates the MY field of the users that are referees.
It is assumed that if the user is in the file that they are registered for the MY indicated on the command line.

It uses columns AYSOID, CertificationDesc and Membership Year.

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
+--------------------------+--------------+------+-----+---------+----------------+

*/

if ($argc < 3) {
  echo "Usage: {$argv[0]} <test:0,1> <input filename> [<MY2015>]\n";
  exit;
}

$test = $argv[1];
$csv_file = $argv[2];
if ($argc > 3)
  $my = $argv[3];
else
  $my = 'MY0000';
$lines = 0;

$Badges = array(0 => 'None',
               1 => 'U-8 Official',
               2 => 'Assistant',
               3 => 'Regional',
               4 => 'Intermediate',
               5 => 'Advanced',
               6 => 'National'
               );

$Certs = array( 
               'U-8 Official' => 1,
               'Assistant Referee' => 2,
               'Regional Referee' => 3,
               'Regional Referee & Safe Haven Referee' => 3,
               'Intermediate Referee' => 4,
               'Advanced Referee' => 5,
               'National Referee' => 6,
               'National 2 Referee' => 6,
               );

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

//  echo "$query\n";
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

$csv = fopen($csv_file, 'r');
$headers = getRow($csv);
//print_r($headers);

while ($row = getRow($csv)) //$row = getRow($csv);
{
  $skip_count = 0;
  $q = '';
  $a = array_combine($headers, $row);
  //print_r($a);
  $aysoid = $a['AYSOID'];
  $cert = $a['CertificationDesc'];
  $user_my = $a['Membership Year'];
  // find certification in out list
  //echo "CertificationDesc = '$cert'\n";
  if (array_key_exists($cert, $Certs))
    $cert_found = $Certs[$cert];
  else
    $cert_found = FALSE;
  //$cert_found = array_search($cert, $Certs);
  //echo "cert_found = '$cert_found'\n";
  
//  $result = DoQuery($mysqli, "SELECT id,ayso_my,badge FROM fos_user WHERE ayso_id='$aysoid' AND role_referee=1");
  $result = DoQuery($mysqli, "SELECT * FROM fos_user WHERE ayso_id='$aysoid'");
  $user = array_shift($result);
  if (!empty($user)) {
    $user_id = $user['id'];
    $badge = $user['badge'];
    //echo $user['first_name']." ".$user['last_name']." has badge '$badge'\n";
    
    if ($cert_found !== FALSE) {
      $b = array_search($badge, $Badges);
      if ($cert_found > $b) {
        echo "Badge changed from $badge to ";
        $badge = $Badges[$cert_found];
        echo "$badge\n";
        $q = "UPDATE fos_user SET badge='$badge',updated=NOW() WHERE id='$user_id'";
        if (!$test)
          DoQuery($mysqli, $q);
        else
          echo $q . "\n";
      }
    }
    if (($user['ayso_my'] != $user_my) && ($cert_found !== FALSE)) {
      echo $user['first_name'].' '.$user['last_name']." was '".$user['ayso_my']."' but is now '$user_my'\n";
      if (empty($user['ayso_my']) || ($user['ayso_my'][0] == '#') || ($user['ayso_my'] == $my)) {
        $q = "UPDATE fos_user SET ayso_my='$user_my',updated=NOW() WHERE id='$user_id'";
        if (!$test)
          DoQuery($mysqli, $q);
        else
          echo $q . "\n";
      }
    }
  }
}

//print_r($a);

$mysqli->close();

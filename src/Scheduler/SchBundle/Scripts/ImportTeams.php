<?php
include_once __DIR__ . '/parameters.php';

/*
 Expect header like this:

Name,Colors,Division,Coach,Coach Email,Coach Phone,POC Email,Region
R1174-U08C-01-Kimbrough,,U08,Alan Kimbrough,surveyor21780@yahoo.com,R1174

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

Team
+-------------+--------------+------+-----+---------+----------------+
| Field       | Type         | Null | Key | Default | Extra          |
+-------------+--------------+------+-----+---------+----------------+
| id          | int(11)      | NO   | PRI | NULL    | auto_increment |
| agegroup_id | int(11)      | YES  | MUL | NULL    |                |
| name        | varchar(64)  | NO   |     | NULL    |                |
| colors_home | varchar(64)  | YES  |     | NULL    |                |
| colors_away | varchar(64)  | YES  |     | NULL    |                |
| coach_name  | varchar(64)  | YES  |     | NULL    |                |
| region_id   | int(11)      | YES  | MUL | NULL    |                |
| coach_email | varchar(255) | YES  |     | NULL    |                |
| poc_email   | varchar(255) | YES  |     | NULL    |                |
| project_id  | int(11)      | YES  | MUL | NULL    |                |
| coach_phone | varchar(20)  | YES  |     | NULL    |                |
+-------------+--------------+------+-----+---------+----------------+

Region
+----+-------+
| id | name  |
+----+-------+
|  1 | R0160 |
|  2 | R0498 |
|  3 | R0894 |
|  4 | R0914 |
|  5 | R1174 |
|  6 | R0778 |
|  7 | R0773 |
|  8 | CCA   |
|  9 | R0414 |
| 10 | R0622 |
| 11 | R0991 |
| 12 | HC    |
| 13 | Test  |
+----+-------+

AgeGroup
+-------------------+-------------+------+-----+---------+----------------+
| Field             | Type        | Null | Key | Default | Extra          |
+-------------------+-------------+------+-----+---------+----------------+
| id                | int(11)     | NO   | PRI | NULL    | auto_increment |
| name              | varchar(32) | NO   |     | NULL    |                |
| difficulty        | int(11)     | NO   |     | NULL    |                |
| region_id         | int(11)     | YES  | MUL | NULL    |                |
| project_id        | int(11)     | YES  | MUL | NULL    |                |
| points_multiplier | int(11)     | NO   |     | NULL    |                |
| points_ref1       | int(11)     | NO   |     | NULL    |                |
| points_youth_ref1 | int(11)     | NO   |     | NULL    |                |
| points_ref2       | int(11)     | NO   |     | NULL    |                |
| points_youth_ref2 | int(11)     | NO   |     | NULL    |                |
| points_ref3       | int(11)     | NO   |     | NULL    |                |
| points_youth_ref3 | int(11)     | NO   |     | NULL    |                |
| points_team_goal  | int(11)     | NO   |     | NULL    |                |
+-------------------+-------------+------+-----+---------+----------------+

Project
+----+----------------------+
| id | name                 |
+----+----------------------+
|  0 | 2013 Indoor          |
|  1 | 2013 Spring          |
|  2 | 2013 Spring 5C Tourn |
|  3 | 2013 Fall            |
+----+----------------------+

*/

if ($argc < 5) {
  echo "Usage: {$argv[0]} (test:0,1) (allow update:0,1) (project#) (input filename)\n";
  exit;
}

$test = $argv[1];
$allow_update = $argv[2];
$project = $argv[3];
$teams_csv_file = $argv[4];
$lines = 0;

function DoQuery($mysqli, $query)
{
  $a = array();
  if (($result = $mysqli->query($query . ';'))) {
    if ($result !== true) {
      /* fetch associative array */
      while (($row = $result->fetch_assoc())) {
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
    printf("ERR_SQL ? query '%s'\n*** failed: %s\n", $query, $mysqli->error);
    return false;
  }

  echo "DEBUG_SQL $query\n";
  return $a;
}


function getRow($handle)
{
  global $line;

  while (($row = fgetcsv($handle)) !== false) {
    $line++;
    // a blank line returns array of one null. if found, skip it.
    if ((1 == count($row)) && (NULL === $row[0])) {
      continue;
		}
    return $row;
  }
  return false;
}

$mysqli = new mysqli($mysql_server, $mysql_user, $mysql_password, $myqsl_database);
if ($mysqli->connect_error) {
  printf("ERR_SQL 0 Connect failed: %d %s\n", $mysqli->connect_errno, $mysqli->connect_error);
  exit();
}
echo 'Connect success: ' . $mysqli->host_info . "\n";

$result = DoQuery($mysqli, "SELECT name FROM Project WHERE id=$project");
$project_name = array_shift($result);
if (empty($project_name)) {
  echo "ERR_PRJ 0 Can't find project with id=$project\n";
  exit;
}

echo "Adding using Project {$project_name['name']}\n";

$csv = fopen($teams_csv_file, 'r');
$headers = getRow($csv);
$line = 1;
//print_r($headers);
$contains_id = in_array('id', $headers);

// check for manditory headers
$required_headers = 
[
  'Name',
  'Division',
  'Region'
];
foreach ($required_headers as $h) {
  if (!in_array($h, $headers)) {
    echo "ERR_HDR $line Required header $h not found!";
    exit;
  }
}

while ($row = getRow($csv)) {
  ++$line;
  $q = '';
  $a = array_combine($headers, $row);
  
  $result = DoQuery($mysqli, "SELECT id FROM Region WHERE name='{$a['Region']}'");
  $region = array_shift($result);
  if (empty($region)) {
    echo "ERR_RGN $line Region column not found!";
    continue;
  }
  $region_id = $region['id'];
  $division = $a['Division'];
  $div_age = (int)preg_replace('/[^0-9]/', '', $division);

  //print_r($a);
  $result = DoQuery($mysqli, "SELECT id FROM AgeGroup WHERE name='$division' AND project_id=$project AND region_id=$region_id");
  $agegroup = array_shift($result);

  if (empty($agegroup)) {
    $diff = $div_age * 10;
    $q2 = "INSERT INTO AgeGroup SET"
      . " name='$division'"
      . ",project_id=$project"
      . ",region_id=$region_id"
      . ",points_multiplier=1"
      . ",difficulty=$diff"
      . ",points_ref1=1"
      . ",points_youth_ref1=1"
      . ",points_ref2=1"
      . ",points_youth_ref2=1"
      . ",points_ref3=1"
      . ",points_youth_ref3=1"
      . ",points_team_goal=0"
      ;
    if (!$test) {
      DoQuery($mysqli, $q2);
      $result = DoQuery($mysqli, "SELECT id FROM AgeGroup WHERE name='$division' AND project_id=$project AND region_id=$region_id");
      $agegroup = array_shift($result);
    } else {
      echo $q2 . "\n";
    }
  }
  if (!empty($agegroup)) {
    $q .= "agegroup_id='{$agegroup['id']}',";
  }
  
  $coach = trim($a['Coach Name']);
  $phone = preg_replace('/[^0-9]/', '', $a['Coach Phone']);

  $q = $q
    . "region_id='$region_id',"
    . "name='{$a['Name']}',"
    . "project_id=$project,"
    . "colors_home='{$a['Colors Home']}',"
    . "colors_away='{$a['Colors Away']}',"
    . "coach_name='$coach',"
    . "coach_phone='$phone',"
    . "coach_email='{$a['Coach Email']}',"
    . "poc_email='{$a['POC Email']}'";

  if (!empty($a['Name'])) {
    $result = DoQuery($mysqli, "SELECT id FROM Team WHERE name='{$a['Name']}' AND project_id=$project");
    $res = array_shift($result);
    if (!empty($res)) {
      if (!$allow_update) {
        echo "WARNING not inserting new team because team already exists and update is not allowed\n";
        $q = '';
      } else {
        $id = array_shift($res);
        $q = "UPDATE Team SET " . $q . " WHERE id=$id AND project_id=$project";
        echo "INFO Updating id=$id, {$a['Name']}\n";
      }
    } else {
      if ($contains_id && $allow_update) {
        //echo "got id\n";
        $result = DoQuery($mysqli, "SELECT id FROM Team WHERE id='{$a['id']}' AND project_id=$project");
        //print_r($result);
        if ($result !== false) {
          $q = "UPDATE Team SET " . $q . " WHERE id='{$a['id']}' AND project_id=$project";
          echo "INFO Updating id={$a['id']}, {$a['Name']}\n";
        } else {
          $q = "INSERT INTO Team SET " . $q;
          echo "INFO inserting id={$a['id']}, {$a['Name']}\n";
        }
      } else {
        $q = "INSERT INTO Team SET " . $q;
        echo "INFO inserting {$a['Name']}\n";
      }
    }
    if (!$test) {
      DoQuery($mysqli, $q);
    } else {
      echo 'DEBUG_SQL '.$q . "\n";
    }
  }
}

//print_r($a);

$mysqli->close();

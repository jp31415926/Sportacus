<?php
include_once __DIR__ . '/parameters.php';

/*
 Expect header like this:

Name,Colors,Division,Coach,Email,POC,Region
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
  echo "Usage: {$argv[0]} <test:0,1> <project#> <Team Rating Worksheet> <Team Summary Report>\n";
  exit;
}

$test = $argv[1];
$project = $argv[2];
$rating_file = $argv[3];
$summary_file = $argv[4];
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
    printf("query '%s'\n*** failed: %s\n", $query, $mysqli->error);
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

// first read in rating worksheet and get team names and genders
// then read in summary report to get the rest of the info.

$csv = fopen($rating_file, 'r');
$headers = getRow($csv);
//print_r($headers);
$contains_id = in_array('id', $headers);
$last_team = '';
$found_G = false;
$found_B = false;
$teams = array();
$team_gender = '';
$team_nums = array(); // used to make team numbers for teams that don't have any number

while ($row = getRow($csv)) {
  $a = array_combine($headers, $row);
  $team = $a['Team Roster'];
  //echo "Team $team\n";
  if ($team != $last_team) {
    if ($last_team != '') {
      if (($a['Region #'] == 160) && ($team_gender == 'B'))
        $team_gender = 'C';
      $teams[$last_team] = $team_gender;
      //echo sprintf("%s is %s\n", $last_team, $team_gender);
    }
    $last_team = $team;
    $found_B = false;
    $found_G = false;
    $team_gender = '';
  }
  $gender = $a['Gender'];
  echo "$team - Gender $gender, $found_B, $found_G\n";
  $found_B = ($gender == 'B') || $found_B;
  $found_G = ($gender == 'G') || $found_G;
  if ($found_B) {
    if ($found_G) {
      $team_gender = 'C';
    } else {
      $team_gender = 'B';
    }
  } else {
    if ($found_G) {
      $team_gender = 'G';
    }
  }
}

$teams[$last_team] = $team_gender;

fclose($csv);
//print_r($teams);

$mysqli = new mysqli($mysql_server, $mysql_user, $mysql_password, $myqsl_database);
if ($mysqli->connect_error) {
  printf("*** Connect failed: %d %s\n", $mysqli->connect_errno, $mysqli->connect_error);
  exit();
}
echo 'Connect success: ' . $mysqli->host_info . "\n";

$result = DoQuery($mysqli, "SELECT name FROM Project WHERE id=$project");
$project_name = array_shift($result);
if (empty($project_name)) {
  echo "*** Error: can't find project with that id\n";
  exit;
}

echo "Adding using Project {$project_name['name']}\n";

$csv = fopen($summary_file, 'r');
$headers = getRow($csv);
//print_r($headers);
$contains_id = in_array('id', $headers);
$teams_list = array();

while ($row = getRow($csv)) {
  $q = '';
  $a = array_combine($headers, $row);
  $TeamDesignation = $a['TeamDesignation'];
  if (array_key_exists($TeamDesignation, $teams)) {
    $gender = $teams[$TeamDesignation];
  } else {
    $gender = 'C';
  }
  $region_name = sprintf("R%04d", $a['RegionNumber']);
  $division = str_replace('-', '', $a['DivisionName']);
  $div_age = (int)preg_replace('/[^0-9]/', '', $division);
  $coachFN = ucfirst(strtolower($a['TeamCoachFName']));
  $coachLN = ucfirst(strtolower($a['TeamCoachLName']));
  $coachPhone = preg_replace('/[^0-9]/', '', $a['TeamCoachPhone']);
  // try to get the number at the end of the designation
  $designation = 0;
  if (preg_match('/([0-9]+)[^0-9]*$/', $TeamDesignation, $matches)) {
    $designation = $matches[1];
    if ($designation > 40) {
      $designation = 0;
    }
  }
  if ($designation == 0) {
    echo "*** Failed to find digit on end of TeamDesignation for team {$TeamDesignation}\n";
    if (array_key_exists($division, $team_nums)) {
      ++$team_nums[$division];
      $designation = $team_nums[$division];
    } else {
      $team_nums[$division] = 1;
      $designation = 1;
    }
  }
  if (array_key_exists($division, $team_nums)) {
    if ($team_nums[$division] < $designation) {
      $team_nums[$division] = $designation;
    }
  } else {
    $team_nums[$division] = $designation;
  }

  if (empty($coachLN)) {
    $teamName = sprintf('%s-%s%s-%02d', $region_name, $division, $gender, $designation);
  } else {
    $teamName = sprintf('%s-%s%s-%02d-%s', $region_name, $division, $gender, $designation, $coachLN);
  }
  //print_r($a);
  $result = DoQuery($mysqli, "SELECT id FROM Region WHERE name='$region_name'");
  $region = array_shift($result);
  //if (!empty($region)) {
  $region_id = $region['id'];
  $q .= "region_id=$region_id,";
  //}

  // normal query - agegroup per region
  $result = DoQuery($mysqli, "SELECT id FROM AgeGroup WHERE name='$division' AND project_id=$project AND region_id=$region_id");

  // area tournament query - use common region for all agegroups
  //$result = DoQuery($mysqli, "SELECT id FROM AgeGroup WHERE name='$division' AND project_id=$project");
  $agegroup = array_shift($result);
  if (empty($agegroup)) {
    $diff = $div_age * 10;
    $q2 = "INSERT INTO AgeGroup SET name='$division',project_id=$project,region_id=$region_id,points_multiplier=1,difficulty=$diff";
    if (!$test) {
      DoQuery($mysqli, $q2);
      $result = DoQuery($mysqli, "SELECT id FROM AgeGroup WHERE name='$division' AND project_id=$project AND region_id=$region_id");
      $agegroup = array_shift($result);
    } else {
      echo $q2 . "\n";
    }
  }
  if (!empty($agegroup)) {
    $q .= "agegroup_id={$agegroup['id']},";
  }
  $q = $q
    . "project_id=$project,"
    . "name='$teamName',"
    . "colors_home='{$a['TeamColors']}',"
    . "colors_away='',"
    . "coach_name='" . trim($coachFN . ' ' . $coachLN) . "',"
    . "coach_phone='$coachPhone',"
    . "coach_email='{$a['TeamCoachEmail']}'";

  if (!empty($TeamDesignation)) {
    $result = DoQuery($mysqli, "SELECT id FROM Team WHERE name='$teamName' AND project_id=$project");
    $res = array_shift($result);
    if (!empty($res)) {
      //echo "[W] Warning: not inserting new team because team already exists\n";
      $a['id'] = $res['id'];
      $q = "UPDATE Team SET " . $q . " WHERE id={$a['id']}";
      echo "Updating id={$a['id']}, $teamName\n";
    } else {
      if ($contains_id) {
        //echo "got id\n";
        $result = DoQuery($mysqli, "SELECT id FROM Team WHERE id='{$a['id']}'");
        //print_r($result);
        if ($result !== false) {
          $q = "UPDATE Team SET " . $q . " WHERE id='{$a['id']}'";
          echo "Updating id={$a['id']}, $teamName\n";
        } else {
          $q = "INSERT INTO Team SET " . $q;
          echo "inserting id={$a['id']}, $teamName\n";
        }
      } else {
        $q = "INSERT INTO Team SET " . $q;
        echo "inserting $teamName\n";
      }
    }
    if (!$test) {
      DoQuery($mysqli, $q);
    } else {
      echo $q . "\n";
    }
    $teams_list[] = $teamName;
  }
}

echo "Team list:\n";
sort($teams_list);
foreach ($teams_list as $t) {
  echo "$t\n";
}

//print_r($a);

$mysqli->close();

/*
+-------------+-------------+------+-----+---------+----------------+
| Field       | Type        | Null | Key | Default | Extra          |
+-------------+-------------+------+-----+---------+----------------+
| id          | int(11)     | NO   | PRI | NULL    | auto_increment |
| agegroup_id | int(11)     | YES  | MUL | NULL    |                |
| name        | varchar(64) | NO   |     | NULL    |                |
| colors      | varchar(64) | YES  |     | NULL    |                |
| coach       | varchar(64) | YES  |     | NULL    |                |
| region_id   | int(11)     | YES  | MUL | NULL    |                |
+-------------+-------------+------+-----+---------+----------------+

*/


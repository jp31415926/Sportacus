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

Game
+----------------+-------------+------+-----+---------+----------------+
| Field          | Type        | Null | Key | Default | Extra          |
+----------------+-------------+------+-----+---------+----------------+
| id             | int(11)     | NO   | PRI | NULL    | auto_increment |
| team1_id       | int(11)     | YES  | MUL | NULL    |                |
| team2_id       | int(11)     | YES  | MUL | NULL    |                |
| agegroup_id    | int(11)     | YES  | MUL | NULL    |                |
| location_id    | int(11)     | YES  | MUL | NULL    |                |
| idstr          | varchar(64) | YES  |     | NULL    |                |
| date           | date        | NO   |     | NULL    |                |
| time           | time        | NO   |     | NULL    |                |
| length         | int(11)     | YES  |     | NULL    |                |
| ref1_id        | int(11)     | YES  | MUL | NULL    |                |
| ref2_id        | int(11)     | YES  | MUL | NULL    |                |
| published      | tinyint(1)  | NO   |     | NULL    |                |
| region_id      | int(11)     | YES  | MUL | NULL    |                |
| ref3_id        | int(11)     | YES  | MUL | NULL    |                |
| timeslotlength | int(11)     | YES  |     | NULL    |                |
| project_id     | int(11)     | NO   |     | NULL    |                |
| status         | int(11)     | NO   |     | NULL    |                |
| score1         | int(11)     | NO   |     | NULL    |                |
| score2         | int(11)     | NO   |     | NULL    |                |
| ref_notes      | longtext    | YES  |     | NULL    |                |
| created        | datetime    | NO   |     | NULL    |                |
| updated        | datetime    | NO   |     | NULL    |                |
| update_count   | int(11)     | NO   |     | NULL    |                |
| updated_by_id  | int(11)     | YES  | MUL | NULL    |                |
| season_id      | int(11)     | YES  | MUL | NULL    |                |
+----------------+-------------+------+-----+---------+----------------+

*/

$required_headers = array('Date', 'Time', 'Location|Field', 'Home|Home Team', 'Away|Away Team|Visitor Team', 'Division', 'Region');
$optional_headers = array('Length', 'TSLength', 'IDstr', 'ShortNote|Short Note');

if ($argc < 4) {
  echo "Usage: {$argv[0]} <dryrun:0,1> <project#> <input filename> [<team pull project#>]\n\n";
  echo "dryrun is 1=dry run, 0=real\n";
  echo "project# is number of project to use\n";
  echo "input file is CSV file";
  echo "optional team pull project# is project to copy team data from if team not found in first project";
  echo "\n";

  echo "Required headers: ";
  foreach ($required_headers as $header) {
    echo "$header ";
  }
  echo "\nOptional headers: ";
  foreach ($optional_headers as $header) {
    echo "$header ";
  }
  echo "\n";
  exit;
}

$dryrun = $argv[1];
$project = $argv[2];
$games_csv_file = $argv[3];
$pull_project = -1;
if ($argc > 4)
  $pull_project = $argv[4];

$line = 0;

$timeslots = array(
  0 => array('len' => 0, 'ts' => 0),
  5 => array('len' => 30, 'ts' => 60),
  6 => array('len' => 30, 'ts' => 60),
  7 => array('len' => 30, 'ts' => 60),
  8 => array('len' => 40, 'ts' => 60),
  10 => array('len' => 50, 'ts' => 75),
  12 => array('len' => 60, 'ts' => 90),
  14 => array('len' => 70, 'ts' => 90),
  16 => array('len' => 80, 'ts' => 120),
  19 => array('len' => 90, 'ts' => 120),
);

function BuildQuery($mysqli, $parameters, $delimiter = ',')
{
  $q = '';
  if (is_array($parameters)) {
    foreach ($parameters as $n => $v) {
      if (!empty($q))
        $q .= $delimiter;
      if ($v != 'NOW()')
        $q .= "`$n`='" . $mysqli->real_escape_string($v) . "'";
      else
        $q .= "`$n`=NOW()";
    }
  }

  return $q;
}


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


function DoInsert($mysqli, $parameters, $table)
{
  global $dryrun;

  $q = BuildQuery($mysqli, $parameters, ',');
  $query = "INSERT INTO $table SET $q;";
  echo "$query\n";
  if (!$dryrun) {
    $result = $mysqli->query($query);
    if ($result) {
      // now get the record of what we just created
      $q = BuildQuery($mysqli, $parameters, ' AND ');
      if ($result = DoQuery($mysqli, "SELECT * FROM `$table` WHERE $q")) {
        $result = array_shift($result); // get first result
        if (!empty($result)) {
          return $result;
        }
      }
    }
  }
  return FALSE;
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

function findOrCopyAgeGroup($mysqli, $region, $agegroup)
{
  global $project;
  global $pull_project;
  global $dryrun;

  if (!empty($agegroup)) {
    // use this for regular season games
    //$result = DoQuery($mysqli, "SELECT id FROM AgeGroup WHERE name='$agegroup' AND project_id=$project AND region_id=$region");
    // use this for area/state/section games
    $result = DoQuery($mysqli, "SELECT id FROM AgeGroup WHERE name='$agegroup' AND project_id=$project");
    if ($result) {
      $ag = array_shift($result);
      if (!empty($ag)) {
        return $ag['id'];
      }
    }
    if ($pull_project >= 0) {
      // use this for regular season games
      //$result = DoQuery($mysqli, "SELECT * FROM AgeGroup WHERE name='$agegroup' AND project_id=$pull_project AND region_id=$region");
      // use this for area/state/section games
      $result = DoQuery($mysqli, "SELECT * FROM AgeGroup WHERE name='$agegroup' AND project_id=$pull_project");
      if ($result) {
        $ag = array_shift($result); // get first result
        if (!empty($ag)) {
          unset($ag['id']);
          $ag['project_id'] = $project;
          $ag = DoInsert($mysqli, $ag, 'AgeGroup');
          if ($ag) {
            return $ag['id'];
          } else {
            if (!$dryrun) echo "*** Error: Failed to create new AgeGroup {$agegroup}!\n";
            return FALSE;
          }
        }
      }
      echo "*** Error: AgeGroup {$agegroup} not found in pull project!\n";
    }
  }
  return FALSE;
}

function findOrCopyTeam($mysqli, $teamname, $region, $agegroup)
{
  global $project;
  global $pull_project;
  global $dryrun;

  if (!empty($teamname)) {
    // use this for regular season games
    //$result = DoQuery($mysqli, "SELECT id FROM Team WHERE name='$teamname' AND project_id=$project AND region_id=$region");
    // use this for area/state/section games
    $result = DoQuery($mysqli, "SELECT id FROM Team WHERE name='$teamname' AND project_id=$project");
    if ($result) {
      $team = array_shift($result);
      if (!empty($team)) {
        return $team['id'];
      }
    }
    if ($pull_project >= 0) {
      // use this for regular season games
      //$result = DoQuery($mysqli, "SELECT * FROM Team WHERE name='$teamname' AND project_id=$pull_project AND region_id=$region");
      // use this for area/state/section games
      $result = DoQuery($mysqli, "SELECT * FROM Team WHERE name='$teamname' AND project_id=$pull_project");
      if ($result) {
        $team = array_shift($result); // get first result
        if (!empty($team)) {
          $team['project_id'] = $project;
          $ag = findOrCopyAgeGroup($mysqli, $region, $agegroup);
          if (!empty($ag)) {
            $team['agegroup_id'] = $ag;
            unset($team['id']);
            $team = DoInsert($mysqli, $team, 'Team');
            if ($team) {
              return $team['id'];
            } else {
              if (!$dryrun) echo "*** Error: Failed to create new Team {$teamname}!\n";
              return FALSE;
            }
          }
        }
      } else {
        echo "*** Error: Team {$teamname} not found in pull project!\n";
      }
    }
  }
  return FALSE;
}


$mysqli = new mysqli($mysql_server, $mysql_user, $mysql_password, $myqsl_database);
if ($mysqli->connect_error) {
  printf("Connect failed: %d %s\n", $mysqli->connect_errno, $mysqli->connect_error);
  exit();
}
echo 'Connect success: ' . $mysqli->host_info . "\n";

//$teams = DoQuery($mysqli, "SELECT * FROM Team");
//$agegroups = DoQuery($mysqli, "SELECT * FROM AgeGroup");
//$locations = DoQuery($mysqli, "SELECT * FROM Location");

//print_r($teams);

$csv = fopen($games_csv_file, 'r');
$headers = array_map('trim', getRow($csv));
$headers_lcase = array_map('strtolower', $headers);
$error = false;

foreach ($required_headers as $header) {
  $names = explode('|', $header);
  $not_found = true;
  foreach ($names as $name) {
    $k = array_search(strtolower($name), $headers_lcase);
    if ($k !== FALSE) {
      if ($names[0] != $headers[$k]) {
        echo "[I] Replaced header ${headers[$k]} with ${names[0]}\n";
      }
      $headers[$k] = $names[0];
      $not_found = false;
      break;
    }
  }
  if ($not_found) {
    $error = true;
    echo "!!! Error: Required header $header was not found!\n";
  }
}

foreach ($optional_headers as $header) {
  $names = explode('|', $header);
  $not_found = true;
  foreach ($names as $name) {
    $k = array_search(strtolower($name), $headers_lcase);
    if ($k !== FALSE) {
      if ($names[0] != $headers[$k]) {
        echo "[I] Replaced optional header ${headers[$k]} with ${names[0]}\n";
      }
      $headers[$k] = $names[0];
      $not_found = false;
      break;
    }
  }
  if ($not_found) {
    echo "[W] Warning: Optional header $header was not found!\n";
  }
}

if ($error)
  exit(1);

$result = DoQuery($mysqli, "SELECT name FROM Project WHERE id=$project");
$project_info = array_shift($result);
if (empty($project_info)) {
  echo "*** Error: can't find project with that id=$project\n";
  exit;
}
echo "Adding using Project {$project_info['name']}\n";

if ($pull_project >= 0) {
  if ($pull_project == $project) {
    echo "*** Error: Project and pull project can't be the same. Just leave off pull project# if you don't want one.\n";
    exit;
  } else {
    $result = DoQuery($mysqli, "SELECT name FROM Project WHERE id=$pull_project");
    $pull_project_info = array_shift($result);
    if (empty($pull_project_info)) {
      echo "*** Error: can't find project with that id=$pull_project\n";
      exit;
    }
    echo "Pulling missing teams from Project {$pull_project_info['name']}\n";
  }
}

//print_r($headers);
$contains_id = in_array('id', $headers);

while ($row = getRow($csv)) //$row = getRow($csv);
{
  foreach ($row as $value) {
    echo $value . ',';
  }
  echo "\n";
  $skip_count = 0;
  $published = 1;
  $q = '';
  $query = array();
  $a = array_combine($headers, $row);
  //print_r($a);
  $division = str_replace('-', '', $a['Division']);
  $div_age = (int)preg_replace('/[^0-9]/', '', $division);
  if (array_key_exists('Length', $a))
    $len = $a['Length'];
  else
    $len = $timeslots[$div_age]['len'];
  if (array_key_exists('TSLength', $a))
    $ts = $a['TSLength'];
  else
    $ts = $timeslots[$div_age]['ts'];
  if (array_key_exists('ShortNote', $a))
    $sn = $a['ShortNote'];
  else
    $sn = '';

  if (empty($a['Region']))
    continue;

  $result = DoQuery($mysqli, "SELECT id FROM Region WHERE name='{$a['Region']}'");
  $region = array_shift($result);
  $region_id = $region['id'];
  if (!empty($region)) {
    $query['region_id'] = $region_id;
  } else {
    if (!empty($a['Region']))
      echo "!!! Error line $line: Region not found: {$a['Region']}.\n";
    ++$skip_count;
  }

  if (!empty($division)) {
    $agegroup = findOrCopyAgeGroup($mysqli, $region_id, $division);
    if ($agegroup === FALSE) {
      echo "!!! Error line $line: Agegroup not found: $division\n";
      ++$skip_count;
    } else {
      $query['agegroup_id'] = $agegroup;
    }
  } else {
    echo "!!! Error line $line: Agegroup is blank!\n";
    ++$skip_count;
  }

  $team1name = $a['Home'];
  if (!empty($team1name)) {
    $team1 = findOrCopyTeam($mysqli, $team1name, $region_id, $division);
    if ($team1 === FALSE) {
      echo "!!! Error line $line: Home team not found: $team1name\n";
      ++$skip_count;
    } else {
      $query['team1_id'] = $team1;
    }
  } else {
    echo "!!! Error line $line: Home team is blank!\n";
    ++$skip_count;
  }

  $team2name = $a['Away'];
  if (!empty($team2name)) {
    $team2 = findOrCopyTeam($mysqli, $team2name, $region_id, $division);
    if ($team2 === FALSE) {
      echo "!!! Error line $line: Away team not found: $team2name\n";
      ++$skip_count;
    } else {
      $query['team2_id'] = $team2;
    }
  } else {
    echo "!!! Error line $line: Away team is blank!\n";
    ++$skip_count;
  }

  $result = DoQuery($mysqli, "SELECT id FROM Location WHERE name='{$a['Location']}'");
  $location = array_shift($result);
  if (!empty($location)) {
    $query['location_id'] = $location['id'];
  } else {
    if (!empty($a['Location']))
      echo "!!! Error line $line: Location not found: " . $a['Location'] . "\n";
    ++$skip_count;
  }

  if ($skip_count > 1) {
    echo "    Skipping line $line due to errors\n";
    continue;
  }
  if ($skip_count > 0)
    $published = 0;

  $query['date'] = $a['Date'];
  $query['time'] = $a['Time'];
  $query['project_id'] = $project;
  $query['season_id'] = $project;
  $q = BuildQuery($mysqli, $query, ' AND ');

  $result = DoQuery($mysqli, "SELECT id FROM Game WHERE $q");
  $game = array_shift($result);
  if (!empty($game)) {
    echo "WWW Warning line $line: not inserting new game because another game matching exists:  {$a['Home']} vs {$a['Away']}\n";
    continue;
  }

  $query['length'] = $len;
  $query['timeslotlength'] = $ts;
  $query['short_note'] = $sn;
  $query['published'] = $published;
  $query['created'] = 'NOW()';
  $query['updated'] = 'NOW()';
  if (array_key_exists('IDstr', $a))
    $query['idstr'] = $a['IDstr'];

  $q = BuildQuery($mysqli, $query, ',');

  if ($contains_id) {
    //echo "got id\n";
    $result = DoQuery($mysqli, "SELECT id FROM Game WHERE id='{$a['id']}'");
    if ($result !== false) {
      $q = "UPDATE Game SET $q WHERE id='{$a['id']}'";
      echo "Updating id={$a['id']}, {$a['Team1']} vs {$a['Team2']}\n";
    } else {
      $q = "INSERT INTO Game SET $q";
      echo "Creating new game (ignoring ID): {$a['Home']} vs {$a['Away']}\n";
    }
  } else {
    $q = "INSERT INTO Game SET $q";
    echo "Creating new game: {$a['Home']} vs {$a['Away']}\n";
  }
  if (!$dryrun)
    DoQuery($mysqli, $q);
  else
    echo "$q\n";
}

//print_r($a);

$mysqli->close();


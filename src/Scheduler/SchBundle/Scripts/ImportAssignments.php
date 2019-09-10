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

$required_headers = array('id', 'Ref1', 'Ref2', 'Ref3');

if ($argc < 2) {
  echo "Usage: {$argv[0]} <dryrun:0,1> <input filename>\n\n";
  echo "dryrun is 1=dry run, 0=real\n";
  echo "input file is CSV file";
  echo "\n";

  echo "Required headers: ";
  foreach ($required_headers as $header) {
    echo "$header ";
  }
  echo "\n";
  exit;
}

$dryrun = $argv[1];
$games_csv_file = $argv[2];

$lines = 0;

function BuildQuery($mysqli, $parameters, $delimiter = ',', $escape = true)
{
  $q = '';
  if (is_array($parameters)) {
    foreach ($parameters as $n => $v) {
      if (!empty($q))
        $q .= $delimiter;
      if ($escape)
        $q .= "`$n`='" . $mysqli->real_escape_string($v) . "'";
      else
        $q .= "`$n`=$v";
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

function findRef(&$refs, $name)
{
  foreach ($refs as $id => $ref) {
    if (($name == $ref['first_name'] . ' ' . $ref['last_name']) ||
      ($name == $ref['last_name'] . ', ' . $ref['first_name'])
    ) {
      return $id;
    }
  }
  return false;
}


$mysqli = new mysqli($mysql_server, $mysql_user, $mysql_password, $myqsl_database);
if ($mysqli->connect_error) {
  printf("Connect failed: %d %s\n", $mysqli->connect_errno, $mysqli->connect_error);
  exit();
}
echo 'Connect success: ' . $mysqli->host_info . "\n";

$users = DoQuery($mysqli, "SELECT id,first_name,last_name FROM fos_user");
//$agegroups = DoQuery($mysqli, "SELECT * FROM AgeGroup");
//$locations = DoQuery($mysqli, "SELECT * FROM Location");

//print_r($teams);

$csv = fopen($games_csv_file, 'r');
$headers = getRow($csv);
$error = false;

foreach ($required_headers as $header) {
  if (array_search($header, $headers) === FALSE) {
    echo "!!! Error: Required header $header was not found!\n";
    $error = true;
  }
}
if ($error)
  exit(1);

$contains_id = in_array('ID', $headers);

while ($row = getRow($csv)) {
  $q = '';
  $query = array();
  $a = array_combine($headers, $row);
  //print_r($a);

  if (!empty($a['id'])) {
    // find referee assignments

    foreach (array(1, 2, 3) as $r) {
      $id = findRef($users, $a["Ref{$r}"]);
      if ($id) {
        $query["ref{$r}_id"] = $id;
      } else {
        $query["ref{$r}_id"] = 'NULL';
      }
    }

    $q = BuildQuery($mysqli, $query, ',', false);

    $q = "UPDATE Game set $q WHERE id={$a['id']}";

    if (!$dryrun)
      DoQuery($mysqli, $q);
    else
      echo "$q\n";
  }
}

//print_r($a);

$mysqli->close();


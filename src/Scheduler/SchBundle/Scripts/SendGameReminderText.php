<?php
// This is designed to get called every hour and send reminder texts 2 hours before the game.

include_once __DIR__ . '/parameters.php';

/*
+-------------------+
| Tables_in_symfony |
+-------------------+
| AgeGroup          |
| Game              |
| Location          |
| OffAssign         |
| OffPos            |
| OffTeam           |
| Team              |
| fos_user          |
| offteam_offpos    |
+-------------------+

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
| ref3_id        | int(11)     | YES  | MUL | NULL    |                |
| ref1_id        | int(11)     | YES  | MUL | NULL    |                |
| ref2_id        | int(11)     | YES  | MUL | NULL    |                |
| published      | tinyint(1)  | NO   |     | NULL    |                |
| region_id      | int(11)     | YES  | MUL | NULL    |                |
| length         | int(11)     | YES  |     | NULL    |                |
| timeslotlength | int(11)     | YES  |     | NULL    |                |
| project_id     | int(11)     | NO   |     | NULL    |                |
+----------------+-------------+------+-----+---------+----------------+

*/

if ($argc < 2) {
  echo "Usage: {$argv[0]} <test:0,1>\n";
  exit;
}

$test = $argv[1];

if ($test) {
  // dev URL:
  $TROPO_URL = 'http://api.tropo.com/1.0/sessions?action=create&token=' . $TROPO_DEV_KEY;
} else {
  // prod URL:
  $TROPO_URL = 'http://api.tropo.com/1.0/sessions?action=create&token=' . $TROPO_PROD_KEY;
}

function DoQuery($mysqli, $query)
{
  global $test;

  //echo "Query: $query\n";
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

  if ($test)
    echo "$query\n";
  return $a;
}

function sendText($userid, $msg)
{
  global $users;
  global $TROPO_URL;
  global $text;

  if (array_key_exists($userid, $users)) {
    $user = $users[$userid];
    $num = $user['phone_mobile'];
    echo "Send text to {$user['username']} @ $num: $msg\n";
    //$num = '2566942225'; // testing
    $url = $TROPO_URL . "&num=" . urlencode($num) . "&msg=" . urlencode($msg);
    //echo "msg len = ".strlen($msg)."\n";
    if (!$test) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      //echo "Fetching $url\n";
      $xml = curl_exec($curl);
      curl_close($curl);
      usleep(1000000);
    }
  }
}


$mysqli = new mysqli($mysql_server, $mysql_user, $mysql_password, $myqsl_database);
if ($mysqli->connect_error) {
  printf("Connect failed: %d %s\n", $mysqli->connect_errno, $mysqli->connect_error);
  exit();
}
//echo 'Connect success: ' . $mysqli->host_info . "\n";

$d = strtotime('now');
$t1 = strtotime('+1 hour', $d);
$t2 = strtotime('+2 hour', $d);

// TODO: later this should be per project
$games = DoQuery($mysqli, "SELECT * FROM Game WHERE published=1 and status=2 and date='" . date('Y-m-d', $t1) . "' AND time between '" .
  date('H:i:01', $t1) . "' AND '" . date('H:i:00', $t2) . "'");
$teams = DoQuery($mysqli, "SELECT * FROM Team");
$locations = DoQuery($mysqli, "SELECT * FROM Location");
$agegroups = DoQuery($mysqli, "SELECT * FROM AgeGroup");
$users = DoQuery($mysqli, "SELECT * FROM fos_user where option_reminder_text=1");
$count = 0;

foreach ($games as $game) {
  $agegroup = $agegroups[$game['agegroup_id']];
  $difficulty = (int)$agegroup['difficulty'];

//  if ($game['published'] && ($game['status'] == 1) && ($difficulty >= 60)) {
  if ($difficulty >= 60) {
    $d = strtotime($game['date'] . ' ' . $game['time']);
    $home = $teams[$game['team1_id']];
    $away = $teams[$game['team2_id']];
    $location = $locations[$game['location_id']];
    $msg = "Sportacus Game {$game['id']} {$home['name']} vs {$away['name']}, {$location['name']}, " . date('g:i A', $d);

    if (!empty($game['ref1_id'])) {
      sendText($game['ref1_id'], $msg);
    }

    if (!empty($game['ref2_id'])) {
      sendText($game['ref2_id'], $msg);
    }

    if (!empty($game['ref3_id'])) {
      sendText($game['ref3_id'], $msg);
    }

    if (!empty($game['ref4_id'])) {
      sendText($game['ref4_id'], $msg);
    }

    if (!empty($game['ref5_id'])) {
      sendText($game['ref5_id'], $msg);
    }

    $count++;
  }
}

$mysqli->close();

<?php
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

  return $a;
}

function addEmailToList(&$emails, $email, $name = '')
{
  if (!array_key_exists($email, $emails) || empty($emails[$email]))
    $emails[$email] = $name;
}

$mysqli = new mysqli($mysql_server, $mysql_user, $mysql_password, $myqsl_database);
if ($mysqli->connect_error) {
  printf("Connect failed: %d %s\n", $mysqli->connect_errno, $mysqli->connect_error);
  exit();
}
//echo 'Connect success: ' . $mysqli->host_info . "\n";

$d = strtotime('tomorrow');
//$d = strtotime('+2 days');
//$d = strtotime('today');

// TODO: later this should be per project
$games = DoQuery($mysqli, "SELECT * FROM Game WHERE date='" . date('Y-m-d', $d) . "'");
$teams = DoQuery($mysqli, "SELECT * FROM Team");
$locations = DoQuery($mysqli, "SELECT * FROM Location");
$agegroups = DoQuery($mysqli, "SELECT * FROM AgeGroup");
$users = DoQuery($mysqli, "SELECT * FROM fos_user");
$count = 0;

foreach ($games as $game) {
  $agegroup = $agegroups[$game['agegroup_id']];
  $difficulty = (int)$agegroup['difficulty'];

  if ($game['published'] && ($game['status'] == 1) && ($difficulty > 5)) {
    $d = strtotime($game['date'] . ' ' . $game['time']);
    if ($game['team1_id']) {
      $home = $teams[$game['team1_id']];
    } else {
      $home = array('coach_email' => '', 'coach' => '', 'poc_email' => '', 'colors' => '', 'name' => '[None]');
    }
    if ($game['team2_id']) {
      $away = $teams[$game['team2_id']];
    } else {
      $away = array('coach_email' => '', 'coach' => '', 'poc_email' => '', 'colors' => '', 'name' => '[None]');
    }
    $location = $locations[$game['location_id']];
    $to = array();
    addEmailToList($to, $home['coach_email'], $home['coach']);
    addEmailToList($to, $home['poc_email']);
    addEmailToList($to, $away['coach_email'], $away['coach']);
    addEmailToList($to, $away['poc_email']);
    $home_colors = empty($home['colors']) ? 'No colors given' : $home['colors'];
    $away_colors = empty($away['colors']) ? 'No colors given' : $away['colors'];
    $subject = "[Sportac.us] Game Reminder {$home['name']} vs {$away['name']}";
    $msg = '';
    if (($home['colors'] == $away['colors']) && (!empty($home['colors']))) {
      $msg .= "*** Warning: Both teams have the same colors. Home team is responsible for having pinnies available if the referee determines that team's colors are too similar.\r\n\r\n";
    }
    $msg .= ''
      . "The following game is coming up on " . date('l, F j, Y', $d) . ":\r\n\r\n"
      . "Game# {$game['id']}\r\n"
      . "Date: " . date('l, F j, Y', $d) . "\r\n"
      . "Time: " . date('g:i A', $d) . ", {$game['length']} mins\r\n"
      . "Home team: {$home['name']} ($home_colors)\r\n"
      . "Away team: {$away['name']} ($away_colors)\r\n"
      . "Location: {$location['long_name']} ({$location['name']}) http://sportac.us/location/redirect/{$location['id']}\r\n"
      . "\r\n"
      . "Officials assigned to this game:\r\n";

    if (empty($game['ref1_id']) && empty($game['ref2_id']) && empty($game['ref3_id'])) {
      $msg .= "None\r\n";
    } else {
      if (!empty($game['ref1_id'])) {
        $user = $users[$game['ref1_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        addEmailToList($to, $user['email'], $name);
        $msg .= "Referee: $name\r\n";
      }

      if (!empty($game['ref2_id'])) {
        $user = $users[$game['ref2_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        addEmailToList($to, $user['email'], $name);
        $msg .= "Asst Ref 1: $name\r\n";
      }

      if (!empty($game['ref3_id'])) {
        $user = $users[$game['ref3_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        addEmailToList($to, $user['email'], $name);
        $msg .= "Asst Ref 2: $name\r\n";
      }

      if (!empty($game['ref4_id'])) {
        $user = $users[$game['ref4_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        addEmailToList($to, $user['email'], $name);
        $msg .= "4th Official: $name\r\n";
      }

      if (!empty($game['ref5_id'])) {
        $user = $users[$game['ref5_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        addEmailToList($to, $user['email'], $name);
        $msg .= "Standby: $name\r\n";
      }
    }

    $msg .= ''
      . "\r\n"
      . "Officials, please visit http://sportac.us/game/scorecard/{$game['id']} to enter an Official Report after the game. Even if no score is kept, please change the status as appropriate.\r\n"
      . "\r\n"
      . "This email was sent to all contacts associated with this game in Sportac.us.\r\n"
      . "\r\n"
      . "Thanks for using Sportac.us!\r\n"
      . "http://sportac.us\r\n";
    $email_to = '';
    //print_r($to);
    foreach ($to as $email => $name) {
      if (!empty($email)) {
        if (!empty($email_to))
          $email_to .= ', ';
        $email_to .= "$name <$email>";
      }
    }
    $headers = "From: $FROM\r\n";
    if (!empty($CC))
      $headers .= "Cc: $CC\r\n";
    if ($test) {
      $msg = "To: $email_to\r\n\r\n" . $msg;
      $email_to = $CC;
    }
    mail($email_to, $subject, $msg, $headers);
    $count++;
  }
}

$mysqli->close();

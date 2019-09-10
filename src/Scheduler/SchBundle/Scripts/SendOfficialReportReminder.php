<?php
// send reminder to update scorecards for games played yesterday.

include_once __DIR__ . '/parameters.php';

if ($argc < 2) {
  echo "Usage: {$argv[0]} <test:0,1> [date]\n\n";
  echo "If no date is entered, it defaults to yesterday.\m";
  exit;
}

if ($argc == 2) {
  $d = strtotime('yesterday');
} else {
  $d = strtotime($argv[2]);
  if ($d === FALSE) {
    echo "The date you entered was invalid.\n";
    exit;
  }
}

$test = (int)$argv[1];

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
  // only send reminders for games that are published and Normal
  if ($game['published'] && ($game['status'] == 1) && ($difficulty > 5)) {
    $d = strtotime($game['date'] . ' ' . $game['time']);
    $home = $teams[$game['team1_id']];
    $away = $teams[$game['team2_id']];
    $location = $locations[$game['location_id']];
    $home_colors = empty($home['colors']) ? 'No colors given' : $home['colors'];
    $away_colors = empty($away['colors']) ? 'No colors given' : $away['colors'];
    $to = array();
    $subject = '';
    $msg = '';
    if (empty($game['ref1_id']) && empty($game['ref2_id']) && empty($game['ref3_id'])) {
    } else { // there was at least one official assigned to this game
      $subject = "[Sportac.us] Please update the Official Report for Game {$game['id']} ({$home['name']} vs {$away['name']})";
      $msg .= ''
        . "You are receiving this reminder because you are assigned as an official to this game and the status is still \"Normal.\"\r\n"
        . "\r\n"
        . "Please take just a minute and update the Official Report for Game {$game['id']} by going to\r\n"
        . "\r\n"
        . "http://sportac.us/game/scorecard/{$game['id']}\r\n"
        . "\r\n"
        . "When you finish, please change the status to \"Complete\" (or whatever is appropriate for your game). For future reference, you can always get to the official report by clicking on the scores from the schedule page.\r\n"
        . "\r\n"
        . "Game# {$game['id']}\r\n"
        . "Date: " . date('l, F j, Y', $d) . "\r\n"
        . "Time: " . date('g:i A', $d) . ", {$game['length']} mins\r\n"
        . "Home team: {$home['name']} ($home_colors)\r\n"
        . "Away team: {$away['name']} ($away_colors)\r\n"
        . "Location: {$location['long_name']} ({$location['name']})\r\n"
        . "Map URL: http://sportac.us/location/redirect/" . $location['id'] . "\r\n"
        . "\r\n"
        . "Officials assigned to this game:\r\n";

      if (!empty($game['ref1_id'])) {
        $user = $users[$game['ref1_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        addEmailToList($to, $user['email'], $name);
        $msg .= "CR: $name\r\n";
      }

      if (!empty($game['ref2_id'])) {
        $user = $users[$game['ref2_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        addEmailToList($to, $user['email'], $name);
        $msg .= "AR1: $name\r\n";
      }

      if (!empty($game['ref3_id'])) {
        $user = $users[$game['ref3_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        addEmailToList($to, $user['email'], $name);
        $msg .= "AR2: $name\r\n";
      }
    }

    if (!empty($subject)) {
      $msg .= ''
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
}

$mysqli->close();

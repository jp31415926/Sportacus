<?php
// send reminder to update scorecards for games played yesterday.

include_once __DIR__ . '/parameters.php';

$PROJECT = 12;

if ($argc < 2) {
  echo "Usage: {$argv[0]} <test:0,1> [<start date> [<end date>]]\n\n";
  echo "<start date> defaults to one week ago.\n\n";
  echo "<end date> defaults to today.\n\n";
  exit;
}

$test = (int)$argv[1];

if ($argc > 2) {
  $startdate = strtotime($argv[2]);
  if ($argc > 3) {
    $enddate = strtotime($argv[3]);
  } else {
    $enddate = strtotime('today');
  }
} else {
  $startdate = strtotime('-7 days');
  $enddate = strtotime('today');
}

if ($test) {
  echo 'start date = ' . date('Y-m-d', $startdate) . "\n";
  echo 'end date   = ' . date('Y-m-d', $enddate) . "\n";
}

if (($startdate === FALSE) || ($enddate === FALSE)) {
  echo "One or more of the dates you entered were invalid.\n";
  exit;
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

  return $a;
}

function addUserToList(&$emails, $user)
{
  $name = $user['first_name'] . ' ' . $user['last_name'];
  $email = $user['email'];
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
$teams = DoQuery($mysqli, "SELECT * FROM Team");
$locations = DoQuery($mysqli, "SELECT * FROM Location");
$agegroups = DoQuery($mysqli, "SELECT * FROM AgeGroup");
$users = DoQuery($mysqli, "SELECT * FROM fos_user");
$count = 0;

foreach ($users as $ref) {
  $q = "SELECT * FROM Game WHERE " .
    //"project_id = $PROJECT AND ".
    //"ref1_id = {$ref['id']} AND ".
    "(ref1_id = {$ref['id']} OR " .
    "(ref1_id is null AND " .
    "(ref2_id = {$ref['id']} OR ref3_id = {$ref['id']}))) AND " .
    "published = 1 AND " .
    "status = 1 AND " .
    "date BETWEEN '" . date('Y-m-d', $startdate) .
    "' AND '" . date('Y-m-d', $enddate) . "'" .
    "ORDER BY date ASC, time ASC";
//  if ($test)
//    echo "Query: $q\n";
  $games = DoQuery($mysqli, $q);

  if ($games) {
    $subject = "[Sportac.us] Please update the Official Report for game(s) you officiated";
    $to = array();
    addUserToList($to, $ref);
    $msg = ''
      . "You are receiving this reminder because you are assigned as an official to the game(s) between " . date('l, F j, Y', $startdate) . " and " . date('l, F j, Y', $enddate) . " listed below, but the game status is still \"Normal.\"\r\n"
      . "\r\n"
      . "Please confer with the officiating team as needed and update the Official Report for each game by going to the links provided below. Remember to change the status to \"Complete\" (or whatever is appropriate for your game). For future reference, you can always get to the official report by clicking on the scores from the schedule page.\r\n"
      . "\r\n"
      . "Please reply to this email if you need help or have questions about this process. This includes, for example, if you know there were additional referees at the game that are not listed. The goal is to make the record accurate.\r\n";

    foreach ($games as $game) {
      $d = strtotime($game['date'] . ' ' . $game['time']);
      $home = $teams[$game['team1_id']];
      $away = $teams[$game['team2_id']];
      $location = $locations[$game['location_id']];
      $home_colors = empty($home['colors']) ? 'No colors given' : $home['colors'];
      $away_colors = empty($away['colors']) ? 'No colors given' : $away['colors'];
      $msg .= ''
        . "\r\n"
        . "Game# {$game['id']}\r\n"
        . "Official Report URL: http://sportac.us/game/scorecard/{$game['id']}\r\n"
        . "Date: " . date('l, F j, Y', $d) . "\r\n"
        . "Time: " . date('g:i A', $d) . ", {$game['length']} mins\r\n"
        . "Home team: {$home['name']} ($home_colors)\r\n"
        . "Away team: {$away['name']} ($away_colors)\r\n"
        . "Location: {$location['long_name']} ({$location['name']})\r\n"
        . "Officials assigned to this game:\r\n";

      if (!empty($game['ref1_id'])) {
        $user = $users[$game['ref1_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'] . ' <' . $user['email'] . '>';
        $msg .= "Referee: $name\r\n";
      }

      if (!empty($game['ref2_id'])) {
        $user = $users[$game['ref2_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'] . ' <' . $user['email'] . '>';
        $msg .= "Asst Ref 1: $name\r\n";
      }

      if (!empty($game['ref3_id'])) {
        $user = $users[$game['ref3_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'] . ' <' . $user['email'] . '>';
        $msg .= "Asst Ref 2: $name\r\n";
      }

      if (!empty($game['ref4_id'])) {
        $user = $users[$game['ref4_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'] . ' <' . $user['email'] . '>';
        $msg .= "4th Official: $name\r\n";
      }

      if (!empty($game['ref5_id'])) {
        $user = $users[$game['ref5_id']];
        $name = $user['first_name'] . ' ' . $user['last_name'] . ' <' . $user['email'] . '>';
        $msg .= "Standby: $name\r\n";
      }
    } // foreach game

    $msg .= "\r\n"
      . "Note: You can now use a text message to update scores and statuses of games of which you are assigned. Send a text message to (205) 235-5288 that looks like \"sc 1234 5 6 notes\", where \"1234\" is the game id, \"5\" is the home score, \"6\" is the away score and \"notes\" is optional notes.  See http://sportac.us/help/text for more information.\r\n"
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
    $headers = "From: $FROM\r\n"
      . "Priority: Urgent\r\n"
      . "Importance: high\r\n";
    if (!empty($CC))
      $headers .= "Cc: $CC\r\n";
    echo "Sending email to $email_to\n";
    if ($test) {
      $msg = "To: $email_to\r\n\r\n" . $msg;
      $email_to = $CC;
    }
    mail($email_to, $subject, $msg, $headers);
    $count++;
  }
}

$mysqli->close();

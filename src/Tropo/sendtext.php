<?php
// https://api.tropo.com/1.0/sessions?action=create&num=12566942225&msg=testing&token=1c63425033a4cf42b3d74d38a913fb7829aad6522395f663bcf360ea50906740639e57c0a873e5d5337eead1

if (isset($currentCall)) {
  // handle incoming SMS message
  $channel = $currentCall->channel;
  $network = $currentCall->network;
  $callerid = $currentCall->callerID;
  if (strlen($callerid) > 10)
    $callerid = substr($callerid, strlen($callerid)-10);
  $text = trim($currentCall->initialText);
  _log("JPLOG initialText = ".$currentCall->initialText);

  if ($channel == 'TEXT') {
    //$answer = getUrlJson("http://home.gcfl.net/Symfony/web/debug_webpage.php/text/$callerid/".urlencode($text), $params);
    $answer = getUrlJson("http://sportac.us/text/$callerid/".urlencode($text), $params);
    // expect an array of strings
    if (is_array($answer) && !empty($answer)) {
      foreach($answer as $msg) {
        sendATextReply($msg);
      }
    } else { 
      $error = 'ERROR: An error occured communicating with the backend. :(';
      sendATextReply($error);
    }
  } else {
    reject();
  }
} else {
  // handle REST call to send an SMS
  if (isset($num) && isset($msg))
  {
    $callerid = $num;
    sendATextReply($msg);
  }
}

function getUrlJson($url, $params) {
  $curl = curl_init();
  $first = TRUE;
  foreach($params as $k => $v) {
    if ($first) {
      $url .= '?';
      $first = FALSE;
    } else {
      $url .= '&';
    }
    if (empty($v))
      $url .= urlencode($k);
    else
      $url .= urlencode($k).'='.urlencode($v);
  }
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $json = curl_exec($curl);
  curl_close($curl);

  _log("JPLOG Fetching URL: $url");
  _log("JPLOG URL returned $json");

  return json_decode($json);
}


function sendATextReply($msg)
{
  global $callerid;
  message($msg, array('to' => $callerid, 'network' => 'SMS'));
  _log("JPLOG Sent text to $callerid: $msg");
}

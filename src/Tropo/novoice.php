<?php
// reject all voice calls.
if (isset($currentCall)) {
  $callerid = $currentCall->callerID;
  reject();
    message("Sorry, I don't except voice calls! Only text messages.", 
      array(
        'to' => $callerid,
        'network' => 'SMS'
      )
    );
} else {
  reject();
}

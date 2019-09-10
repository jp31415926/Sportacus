<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Email;

use Psr\Http\Message\ServerRequestInterface as Request;

use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Twig\TwigExtension as ViewHelper;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class EmailForm
{
  protected $game;
  protected $user;

  protected $valid  = false;
  protected $posted = false;

  protected $viewHelper;

  protected $tos = [];
  protected $from;
  protected $subject;
  protected $body;

  public function __construct(ViewHelper $viewHelper, TokenStorage $tokenStorage)
  {
    $this->viewHelper = $viewHelper;

    // Does this really belong here?
    $token = $tokenStorage->getToken();
    $user = $token->getUser();
    if (is_object($user)) $this->user = $user;
  }
  public function setGame($game)
  {
    $this->game = $game;

    $viewHelper = $this->viewHelper;

    // From
    if ($this->user) {
      $user = $this->user;
      $this->from = "{$user->getEmail()} {$user->getFirstName()} {$user->getLastName()}";
    }

    // Subject
    $this->subject = sprintf("[Sportac.us] Game #%d, %s, %s",
      $viewHelper->projectGameNumberFilter($game),
      $viewHelper->projectGameDateTimeFilter($game),
      $viewHelper->projectGameLocationNameFilter($game)
    );
    // To, need to do this to prevent the same email from being used more than once
    $tos = [];
    foreach($game['project_game_teams'] as $gameTeam) {
      $team = isset($gameTeam['project_team']) ? $gameTeam['project_team'] : null;
      if ($team) {
        if ($team['coach_email']) {
          $tos[$team['coach_email']] = $team['coach_name'];
        }
        if ($team['poc_email']) {
          $tos[$team['poc_email']] = null;
        }
      }
    }
    foreach($game['project_game_officials'] as $gameOfficial) {
      $official = isset($gameOfficial['project_official']) ? $gameOfficial['project_official'] : null;
      if ($official && $official['email']) {
        $name = "{$official['name_first']} {$official['name_last']}";
        $tos[$official['email']] = $name;
      }
    }
    // Transform to html
    $tosHtml = null;
    foreach($tos as $email => $name)
    {
      if ($name) $tosHtml .= "{$email} {$name}\n";
      else       $tosHtml .= "{$email}\n";
    }
    $this->tos = $tosHtml;

    // Body
    $field = $game['location'];

    $body = <<<EOT
Game: {$viewHelper->projectGameNumberFilter($game)}
Status: {$viewHelper->projectGameStatusFilter($game)}
When: {$viewHelper->projectGameDateTimeFilter($game)}
Where: {$field['long_name']} ({$field['name']}) http://sportac.us/location/redirect/{$field['id']}


EOT;

    foreach($viewHelper->projectGameTeamsSortedFilter($game) as $gameTeam) {

      $body .= sprintf("%s: %s %s %s <%s>\n",
        $viewHelper->projectGameTeamSlotFilter      ($gameTeam),
        $viewHelper->projectGameTeamNameFilter      ($gameTeam),
        $viewHelper->projectGameTeamColorsFilter    ($gameTeam,'Unknown Colors'),
        $viewHelper->projectGameTeamCoachNameFilter ($gameTeam),
        $viewHelper->projectGameTeamCoachEmailFilter($gameTeam)
      );
    }
		// FIXME jp: comment this out until I figure out how to take into account the home and away colors
/*
    if ($viewHelper->projectGameTeamsColorsMatchFilter($game)) {
      $body .=
        "*** Warning: Both teams have the same colors ***." .
        "Home team is responsible for having pinnies available if the referee determines that team's colors are too similar.\n";
    }
*/
    $body .= <<<EOT

Officials assigned to this game:

EOT;

    foreach($viewHelper->projectGameOfficialsSortedFilter($game) as $slot => $gameOfficial) {
      $body .= sprintf("%s: %s <%s>\n",
        $viewHelper->projectGameOfficialSlotFilter ($gameOfficial),
        $viewHelper->projectGameOfficialNameFilter ($gameOfficial),
        $viewHelper->projectGameOfficialEmailFilter($gameOfficial)
      );
    }

    $this->body = $body;
  }
  public function getData()
  {
    // Transform tos
    $tos = [];
    foreach(explode("\n",$this->tos) as $to) {
      $to = trim($to);
      if ($to) {
        //echo "TO: '{$to}'<br>";
        $pos = strpos($to,' ');
        if ($pos === false) $tos[$to] = null;
        else {
          $email = substr($to,0,$pos);
          $name  = substr($to,  $pos + 1);
          $tos[$email] = $name;
        }

      }
    }
    //print_r($tos);
    // Transform from
    $from = [];
    $fromStr = trim($this->from);
    $pos = strpos($fromStr,' ');
    if ($pos === false) $from[$fromStr] = null;
    else {
      $email = substr($fromStr,0,$pos);
      $name  = substr($fromStr,  $pos + 1);
      $from[$email] = $name;
    }
    //print_r($from); die();
    // Done
    return [
      'from'    => $from,
      'tos'     => $tos,
      'subject' => $this->subject,
      'body'    => $this->body,
    ];
  }
  public function isValid() {
    return $this->valid;
  }
  public function handleRequest(Request $request)
  {
    if ($request->getMethod() !== 'POST') {
      return;
    }
    $this->posted = true;

    $post = $request->getParsedBody();

    $this->from    = $post['from'];
    $this->tos     = $post['tos'];
    $this->body    = $post['body'];
    $this->subject = $post['subject'];

    $this->valid = true;
  }
  public function render()
  {
    return <<<TYPEOTHER
<form action="/project-game/{$this->game['id']}/email" method="POST" enctype="application/x-www-form-urlencoded">
<label>Subject<br/>
  <input type="text" name="subject" size="80" value="{$this->subject}"/>
</label>
<label>From<br/>
  <input type="text" name="from" size="80" value="{$this->from}"/>
</label>
<label>To(s)<br/>
  <textarea name="tos" rows="8" cols="80">{$this->tos}</textarea><br/>
</label>
<label>Body<br/>
  <textarea name="body" rows="20" cols="80">{$this->body}</textarea><br/>
</label><br>
<input type="submit" value="Email" name="email"/>
</form>
TYPEOTHER;
  }
}
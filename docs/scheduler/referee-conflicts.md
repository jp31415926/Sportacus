
SchBundle\GameRepository::findOfficialGameConflicts(&$game, $official)

Returns array of conflicting games for a given referee based on date and time.

Called from GameController::offschAction.

Results are stored in Game entity as well as a stats summary array.

offsch.html.twig 

From an initial read of the template code, only the conflict game number is being used.

Basically just need to rewrite the query to use joined officials.

It's tempting to try and roll up the five distinct referee queries (one for each position) into a single query.

$game->getEndTime is used to get time range.  It's only called from the conflict query and always subtracts seconds.

Not sure if cancelled games are properly handled.

My test case is Aug 22, 2015, Dublin, Games 563 and 103 have an intentional conflict.

Once this branch is accepted, do the same thing to team conflicts.

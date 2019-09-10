This branch is to see how close we are to dropping Game.teamx and Game.refx properties.

Already have ProjectGameTeam and ProjectGameOfficial to replace them.

app/console cerad_project_game_update will copy the information from the game properties to the new entities.

The next step is to toss exceptions on all the Game:team properties and then see how challengin it is to update the code
to use the new entities.

Not going to address the conflicts at this point.  That code should probably be moved elsewhere.

### ProjectGameTrait Overrides

* getTeam1, setTeam1, setTeam2, getTeam2, getTeams
* getScore1,getScore2,setScore1,setScore2
* resetForClone,__construct
* getRefX, getOfficials

repo->find does not join the project teams.  
Doctrine 2 will lazy load them.  Somewhat unexpected since I made it public.
But you get a Doctrine\ORM\PersistentCollection.
Just need to access it before the twig template since the twig template goes directly to the property?
Bit strange.

The problem is that resetForClone clears the game id so the lazy query fails.

### Reviewed and Tested

* views/Game/index.html.twig
* views/Game/offsch.html.twig
* views/Game/edit.html.twig - works for score, teams and delete

need to initialize scores to null, not 0

### Twig stuff

In a template, game.score1 seems to be going directly to the attribute and not getScore1?
Because it implements ArrayAccess.

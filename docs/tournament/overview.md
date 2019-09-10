## Tournament Design

A tournament is a project with a group of related games.  
Tournaments typically occur over a short period of time.
Each tournament tends to have their own set of rules.

Games within a tournament are divided into rounds which contain one or more games.

A round has a type such as "pool play" or "semi-final".

A round has a name such as "U10B Core A" which is unique within the project.  

Results for each round are computed based on game results.  

For pool play, each team typically earns points for each game they play.
Round winners are then determined by total points followed by a list of tie breaking rules.

Other rounds such are semi-finals are just one game with the winner determined by the game's score.

The results of the rounds are then used to determine who plays who next.

In most cases, a game applies to one round however there are situations in which a game might apply to different rounds.
Cross-round play can complicate things.
Because of cross round play, we apply round information to the game_team entity instead of the game entity.

For various reasons, it is difficult to model a round entity.  

Instead, we apply round information to the game_team entities as properties (round_type,round_name,round_slot).
Tournament validation services are used to keep things consistent.

For tie breaking, ayso often used conduct information such as sportsmanship, cautions and sendoffs.

We therefore add a conduct array to the game_team entity to keep track of this sort of information.

### Calculators

Two project specific calculators are used to determine the results of pool play rounds.

A points calculator determines points awarded to each team for each game.

A standings calculator calculates the results for each round_name based on project specific tie breaking rules.


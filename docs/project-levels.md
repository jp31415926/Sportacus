## Project Levels

Levels will eventually replace the AgeGroup entity.

The age,gender,division properties will allow for more fine grained querying.

Game slot length and crew type will make it easier to create games.

Levels will be applied to the project_game_team entity allowing games between two different levels.

Levels will be applied to the project_team entity mostly to ease querying.

Levels will also play a role in calculating tournament results.

Try to avoid applying levels to the project_game entity.  
Instead, examine the project_game_team entities to determine game level information.

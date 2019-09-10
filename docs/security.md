## Overview

This commit allows fine tuning the ROLE_SCHEDULER permissions by adding more information(aka grants) to the role string.

### ROLE_SCHEDULER:P19:P20:OR0160:AU16:AU19

* Firewall recognizes the user as having ROLE_SCHEDULER access permissions
* is_granted("ROLE_SCHEDULER") works as before
* is_granted("edit",game) restrict the user to:
    * Only games from projects 18 or 19(2015 Spring and s5games)
    * Only Region 160
    * Only U16 or U19 ages
* The O stands for organization.

You can basically give the user any set of grants you want.  No database changes needed.

The region and age grants currently only look at the game properties, not the individual team properties.  
We can refine the behaviour in the voter later but it's best to keep things simple until we see how this works out.

If a user has ROLE_ADMIN, the ROLE_SCHEDULER grants still apply.  
Remove any ROLE_SCHEDULER grants to give the user full admin scheduling capabilities.

## Implementation

### ProjectBundle/Action/Security/ProjectRole.php

ProjectRole replaces the default Symfony Role and implements the grant checking functionality.

    $role = new ProjectRole('ROLE_SCHEDULER:P19:P20:OR0160:AU16:AU19');
    $this->assertEquals('ROLE_SCHEDULER',$role->getRole());
    $this->assertTrue ($role->isGranted(['project' => 19, 'organization' => 'R0160', 'age' => 'U16']));
    $this->assertFalse($role->isGranted(['project' => 19, 'organization' => 'R0160', 'age' => 'U14']));
    $this->assertFalse($role->isGranted(['project' => 19, 'organization' => 'R0498']));

### ProjectBundle/Action/ProjectGame/Security/ProjectGameVoter.php

### ProjectGameVoter implements the is_granted functionality.  

* [Symfony Voters](http://symfony.com/doc/current/cookbook/security/voters_data_permission.html)
* view - Allows every one to view a game
* edit,create,update,delete - Currently all give the same access but could be refined


## Changes to Scheduler

Updated User::getRoles to return ProjectRole object.

Updated User::hasRole so fos:user:demote command works

Updated offsch.html.twig - use is_granted("edit",game) instead of is_granted("ROLE_SCHEDULER")

Updated offsch.html.twig - added game.region.name to make debugging easier

Updated Game,Team,Project,AgeGroup,Location to use Project traits and to implement ArrayAccess.

Should not change any of their existing behaviours.

Gives ProjectBundle the ability to change behaviour without editing Scheduler files.
    
## Setting the roles

You can use:

    app/console fos:user:promote username role
    app/console fos:user:demote  username role # remove roles
    
    app/console fos:user:promote ahundiak ROLE_SCHEDULER
    
Or from mysql

    select id,username,roles from fos_user where id = 36;
    update fos_user set roles = 'a:0:{}' where id = 36;
    
    'a:0:{}'
    'a:1:{i:0;s:14:"ROLE_SCHEDULER";}'
    'a:1:{i:0;s:18:"ROLE_SCHEDULER:P18";}'
    'a:1:{i:0;s:18:"ROLE_SCHEDULER:P19";}'
    'a:1:{i:0;s:22:"ROLE_SCHEDULER:P18:P19";}'
    'a:1:{i:0;s:21:"ROLE_SCHEDULER:OR0498";}'
    'a:1:{i:0;s:39:"ROLE_SCHEDULER:P18:P19:OR0160:AU16:AU19";}'
    'a:2:{i:0;s:39:"ROLE_SCHEDULER:P18:P19:OR0160:AU16:AU19";i:1;s:10:"ROLE_ADMIN";}'
    
After changing the database, refreshing the browser does not update the roles.  
The user needs to logout then log back in again.
You can use the profiler to verify the user has the expected roles.
The profiler currently does not show the role grants.

The need to logout/login is a little bit strange.  
The token roles are stored in the session along with the user object.
The user object is (optionally) refreshed on each request.
You would kind of think that the token would then refresh it's roles as well.

TODO: Research why this happens and see what the consequences of refreshing the roles on each request are.

## Browser Testing

May 23 - May 30 gives you games from two projects along with different regions and ages.

    ROLE_SCHEDULER      # Works as before
    ROLE_SCHEDULER:P18  # Only links spring games
    ROLE_SCHEDULER:P19  # Only links s5games
    
    ROLE_SCHEDULER:P18:P19:OR0160:AU16:AU19 # Region 160, U16/U19
    
## Automated testing

This is possible in theory.  
Need to be able to change the session stuff and set specific roles.
Something for later.

## Internal notes

Need to add a typedef = project_game to arrays so Voter::supportsClass works on arrays.

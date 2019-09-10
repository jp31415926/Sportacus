<?php
// this script reads in the "Team Summary Report" from eayso and
// tries to create proper team names from the info.
// The only part that is user dependent is the number at the end
// of the team name that the coordinator provides.

/*
+-------------+-------------+------+-----+---------+----------------+
| Field       | Type        | Null | Key | Default | Extra          |
+-------------+-------------+------+-----+---------+----------------+
| id          | int(11)     | NO   | PRI | NULL    | auto_increment |
| agegroup_id | int(11)     | YES  | MUL | NULL    |                |
| name        | varchar(64) | NO   |     | NULL    |                |
| colors      | varchar(64) | YES  |     | NULL    |                |
| coach       | varchar(64) | YES  |     | NULL    |                |
| region_id   | int(11)     | YES  | MUL | NULL    |                |
+-------------+-------------+------+-----+---------+----------------+

*/

if ($argc < 3)
{
    echo "Usage: {$argv[0]} <input filename> <gender>\n";
    exit;
}

$csv_file = $argv[1];
$gender = $argv[2];

function getRow($handle)
{
    global $line;
    
    while (($row = fgetcsv($handle)) !== false) {
        $line++;
        // a blank line returns array of one null. if found, skip it.
        if ((1 == count($row)) && (NULL === $row[0]))
          continue;

        return $row;
    }
    return false;
}

$csv = fopen($csv_file, 'r');
$headers = getRow($csv);
//print_r($headers);
$contains_id = in_array('id', $headers);

while ($row = getRow($csv))
{
    // combine data with headers into $a
    $a = array_combine($headers, $row);
    //print_r($a);

    // process team name.  Should be formatted like this: R0160-U10C-01,
    // but sometimes it is R160-U10C-01, or R160-U10C-1, or R160-U10-1
    $region   = $a['RegionNumber'];
    $division = str_replace('-', '', $a['DivisionName']);
    $coach = ucfirst(strtolower($a['TeamCoachLName']));
    
    // try to get the number at the end of the designation
    if (preg_match('/([0-9]+)$/', $a['TeamDesignation'], $matches))
    {
        $designation = $matches[1];
    }
    else
    {
        echo "Failed to find digit on end of TeamDesignation\n";
        $designation = 0;
    }
    $teamName = sprintf('R%04d-%s%s-%02d-%s', $region, $division, $gender, $designation, $coach);
    echo "$teamName\n";
}


<?php

include_once __DIR__ . '/parameters.php';

/*

  Uses database AYSO ID and queries national AYSO database to update fields.

  If parameter is included, assumes it is an MYXXXX string and enables refs where
  there AYSO MY matches.

  +-------------------+
  | Tables_in_symfony |
  +-------------------+
  | AgeGroup          |
  | Game              |
  | Location          |
  | Log               |
  | LogGame           |
  | Message           |
  | MobileProvider    |
  | OffAssign         |
  | OffPos            |
  | OffTeam           |
  | Project           |
  | Region            |
  | Team              |
  | Test              |
  | fos_user          |
  | msg_user          |
  | offteam_offpos    |
  +-------------------+

  User
  +--------------------------+--------------+------+-----+---------+----------------+
  | Field                    | Type         | Null | Key | Default | Extra          |
  +--------------------------+--------------+------+-----+---------+----------------+
  | id                       | int(11)      | NO   | PRI | NULL    | auto_increment |
  | username                 | varchar(180) | NO   |     | NULL    |                |
  | username_canonical       | varchar(180) | NO   | UNI | NULL    |                |
  | email                    | varchar(180) | NO   |     | NULL    |                |
  | email_canonical          | varchar(180) | NO   | UNI | NULL    |                |
  | enabled                  | tinyint(1)   | NO   |     | NULL    |                |
  | salt                     | varchar(255) | YES  |     | NULL    |                |
  | password                 | varchar(255) | NO   |     | NULL    |                |
  | last_login               | datetime     | YES  |     | NULL    |                |
  | confirmation_token       | varchar(180) | YES  | UNI | NULL    |                |
  | password_requested_at    | datetime     | YES  |     | NULL    |                |
  | roles                    | longtext     | NO   |     | NULL    |                |
  | created                  | datetime     | NO   |     | NULL    |                |
  | updated                  | datetime     | NO   |     | NULL    |                |
  | first_name               | varchar(64)  | NO   |     | NULL    |                |
  | last_name                | varchar(64)  | NO   |     | NULL    |                |
  | phone_home               | varchar(20)  | YES  |     | NULL    |                |
  | phone_mobile             | varchar(20)  | YES  | UNI | NULL    |                |
  | ayso_id                  | varchar(10)  | NO   | UNI | NULL    |                |
  | role_referee             | tinyint(1)   | YES  |     | NULL    |                |
  | role_scheduler           | tinyint(1)   | YES  |     | NULL    |                |
  | mobile_provider_id       | int(11)      | YES  | MUL | NULL    |                |
  | mobile_provider_verified | tinyint(1)   | YES  |     | NULL    |                |
  | region_id                | int(11)      | YES  | MUL | NULL    |                |
  | option_change_email      | tinyint(1)   | YES  |     | NULL    |                |
  | option_change_text       | tinyint(1)   | YES  |     | NULL    |                |
  | option_reminder_email    | tinyint(1)   | YES  |     | NULL    |                |
  | option_reminder_text     | tinyint(1)   | YES  |     | NULL    |                |
  | option_assignment_email  | tinyint(1)   | YES  |     | NULL    |                |
  | option_assignment_text   | tinyint(1)   | YES  |     | NULL    |                |
  | current_project_id       | int(11)      | YES  | MUL | NULL    |                |
  | ayso_my                  | varchar(64)  | YES  |     | NULL    |                |
  | role_referee_admin       | tinyint(1)   | YES  |     | NULL    |                |
  | is_youth                 | tinyint(1)   | YES  |     | NULL    |                |
  | role_assigner            | tinyint(1)   | YES  |     | NULL    |                |
  | role_superuser           | tinyint(1)   | YES  |     | NULL    |                |
  | badge                    | varchar(64)  | YES  |     | NULL    |                |
  +--------------------------+--------------+------+-----+---------+----------------+

  +-----------------+--------------+------+-----+---------+----------------+
  | Field           | Type         | Null | Key | Default | Extra          |
  +-----------------+--------------+------+-----+---------+----------------+
  | id              | int(11)      | NO   | PRI | NULL    | auto_increment |
  | name            | varchar(32)  | NO   |     | NULL    |                |
  | long_name       | varchar(64)  | NO   |     | NULL    |                |
  | poc_name        | varchar(255) | YES  |     | NULL    |                |
  | poc_email       | varchar(255) | YES  |     | NULL    |                |
  | ref_admin_name  | varchar(255) | YES  |     | NULL    |                |
  | ref_admin_email | varchar(255) | YES  |     | NULL    |                |
  +-----------------+--------------+------+-----+---------+----------------+

 https://national.ayso.org/Volunteers/ViewCertification?UserName=71243433

  JSON response looks like this (as of 2019-04-03):

GET https://national.ayso.org/Volunteers/SelectViewCertificationInitialData?AYSOID=71243433

{
  "ReturnStatus":0,
  "ReturnMessage":"",
  "VolunteerCertificationDetails":{
    "VolunteerCertificationsCoach":[
      {
        "RowId":1,
        "CertificationDesc":"Advanced Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1540537200000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":15,
        "CertificationDesc":"Coach Administrator Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1391414400000)\/",
        "UpdatedBy":"Stiffel, Zach"
      },
      {
        "RowId":3,
        "CertificationDesc":"Intermediate Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1462604400000)\/",
        "UpdatedBy":"King, Simon"
      },
      {
        "RowId":4,
        "CertificationDesc":"Intermediate Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1462604400000)\/",
        "UpdatedBy":"King, Simon"
      },
      {
        "RowId":5,
        "CertificationDesc":"Intermediate Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1462604400000)\/",
        "UpdatedBy":"King, Simon"
      },
      {
        "RowId":6,
        "CertificationDesc":"Intermediate Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1462604400000)\/",
        "UpdatedBy":"King, Simon"
      },
      {
        "RowId":9,
        "CertificationDesc":"U-10 Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1406962800000)\/",
        "UpdatedBy":"Steely, Chris"
      },
      {
        "RowId":10,
        "CertificationDesc":"U-12 Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1406962800000)\/",
        "UpdatedBy":"Steely, Chris"
      },
      {
        "RowId":8,
        "CertificationDesc":"VIP Buddy Training and Certification",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1452412800000)\/",
        "UpdatedBy":"Farless, Catherine"
      },
      {
        "RowId":7,
        "CertificationDesc":"VIP Volunteer Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1452412800000)\/",
        "UpdatedBy":"Farless, Catherine"
      },
      {
        "RowId":2,
        "CertificationDesc":"Z-Online Advanced Coach Pre Course",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1533020400000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":16,
        "CertificationDesc":"Z-Online U-10 Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1371193200000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":11,
        "CertificationDesc":"Z-Online U-6 Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1398322800000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":12,
        "CertificationDesc":"Z-Online U-6 Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1398322800000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":13,
        "CertificationDesc":"Z-Online U-6 Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1398322800000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":14,
        "CertificationDesc":"Z-Online U-6 Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1398322800000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":17,
        "CertificationDesc":"Z-Online U-8 Coach",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1346050800000)\/",
        "UpdatedBy":"AYSO, Training"
      }
    ],
    "VolunteerCertificationsReferee":[
      {
        "RowId":1,
        "CertificationDesc":"Z-Online Regional Referee Course",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1537772400000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":2,
        "CertificationDesc":"Referee Assessor",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1510473600000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":3,
        "CertificationDesc":"z-Online Regional Referee without Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1459148400000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":4,
        "CertificationDesc":"Webinar -  Area Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1411542000000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":5,
        "CertificationDesc":"Area Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1411542000000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":6,
        "CertificationDesc":"Area Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1411542000000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":7,
        "CertificationDesc":"Area Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1411542000000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":8,
        "CertificationDesc":"Area Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1411542000000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":9,
        "CertificationDesc":"Area Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1411542000000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":10,
        "CertificationDesc":"Area Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1411542000000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":11,
        "CertificationDesc":"Area Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1411542000000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":12,
        "CertificationDesc":"Webinar - Referee - VIP - National Games 2014",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1401346800000)\/",
        "UpdatedBy":"Dalit, Colleen"
      },
      {
        "RowId":13,
        "CertificationDesc":"Webinar - Regional Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1371020400000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":14,
        "CertificationDesc":"Intermediate Referee Course",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1357891200000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":15,
        "CertificationDesc":"Intermediate Referee",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1369897200000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":16,
        "CertificationDesc":"Intermediate Referee Course",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1357891200000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":17,
        "CertificationDesc":"Intermediate Referee Course",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1357891200000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":18,
        "CertificationDesc":"Intermediate Referee Course",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1357891200000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":19,
        "CertificationDesc":"Regional Referee",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1346050800000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":20,
        "CertificationDesc":"Webinar - Regional Referee Administrator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1349334000000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":21,
        "CertificationDesc":"z-Online Regional Referee without Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1346050800000)\/",
        "UpdatedBy":"AYSO, Training"
      }
    ],
    "VolunteerCertificationsInstructor":[
      {
        "RowId":2,
        "CertificationDesc":"Advanced Management Instructor",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1515398400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":3,
        "CertificationDesc":"Advanced Management Instructor Course",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1480752000000)\/",
        "UpdatedBy":"Mays, Michael"
      },
      {
        "RowId":1,
        "CertificationDesc":"Coach Instructor",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1536649200000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":8,
        "CertificationDesc":"Introduction to Instruction",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1386316800000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":4,
        "CertificationDesc":"Management Instructor",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1457510400000)\/",
        "UpdatedBy":"Tinder, Victoria"
      },
      {
        "RowId":6,
        "CertificationDesc":"Management Instructor Course",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1452326400000)\/",
        "UpdatedBy":"Farless, Catherine"
      },
      {
        "RowId":7,
        "CertificationDesc":"Referee Instructor",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1412665200000)\/",
        "UpdatedBy":"Dakouzlian, Debbie"
      },
      {
        "RowId":9,
        "CertificationDesc":"Referee Instructor Course",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1386403200000)\/",
        "UpdatedBy":"Fitzpatrick, Michael"
      },
      {
        "RowId":5,
        "CertificationDesc":"VIP Instructor",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1454313600000)\/",
        "UpdatedBy":"Reed, Susan"
      }
    ],
    "VolunteerCertificationsManagement":[
      {
        "RowId":19,
        "CertificationDesc":"Dispute Resolution",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1373698800000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":20,
        "CertificationDesc":"Dispute Resolution",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1373698800000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":21,
        "CertificationDesc":"Dispute Resolution",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1373698800000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":22,
        "CertificationDesc":"Dispute Resolution",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1373698800000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":16,
        "CertificationDesc":"Division Coordinator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1391846400000)\/",
        "UpdatedBy":"Tinder, Victoria"
      },
      {
        "RowId":1,
        "CertificationDesc":"Due Process",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1468566000000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":2,
        "CertificationDesc":"Due Process",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1468566000000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":3,
        "CertificationDesc":"Due Process",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1468566000000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":4,
        "CertificationDesc":"Due Process",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1468566000000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":12,
        "CertificationDesc":"Financial Auditor",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1420876800000)\/",
        "UpdatedBy":"Streeter, Patrick"
      },
      {
        "RowId":23,
        "CertificationDesc":"Introductory Management",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1373698800000)\/",
        "UpdatedBy":"Reed, Susan"
      },
      {
        "RowId":24,
        "CertificationDesc":"Introductory Management",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1358064000000)\/",
        "UpdatedBy":"Streeter, Patrick"
      },
      {
        "RowId":6,
        "CertificationDesc":"RC Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1428649200000)\/",
        "UpdatedBy":"Tinder, Victoria"
      },
      {
        "RowId":7,
        "CertificationDesc":"RC Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1428649200000)\/",
        "UpdatedBy":"Tinder, Victoria"
      },
      {
        "RowId":8,
        "CertificationDesc":"RC Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1428649200000)\/",
        "UpdatedBy":"Tinder, Victoria"
      },
      {
        "RowId":9,
        "CertificationDesc":"RC Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1428649200000)\/",
        "UpdatedBy":"Tinder, Victoria"
      },
      {
        "RowId":5,
        "CertificationDesc":"Regional Board Member Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1452240000000)\/",
        "UpdatedBy":"Farless, Catherine"
      },
      {
        "RowId":15,
        "CertificationDesc":"Registrar",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1392278400000)\/",
        "UpdatedBy":"Tinder, Victoria"
      },
      {
        "RowId":10,
        "CertificationDesc":"Tournament Management Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1423296000000)\/",
        "UpdatedBy":"Stratton, Dianne"
      },
      {
        "RowId":18,
        "CertificationDesc":"Webinar - CVPA",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1390377600000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":11,
        "CertificationDesc":"Webinar - Division Coordinator",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1426143600000)\/",
        "UpdatedBy":"Tinder, Victoria"
      },
      {
        "RowId":13,
        "CertificationDesc":"Webinar - Registrar",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1392278400000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":14,
        "CertificationDesc":"Webinar - Safety Director",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1392624000000)\/",
        "UpdatedBy":"Tinder, Victoria"
      },
      {
        "RowId":17,
        "CertificationDesc":"Webinar - Treasurer",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1392192000000)\/",
        "UpdatedBy":"Mihara, Karen"
      }
    ],
    "VolunteerCertificationsSafeHaven":[
      {
        "RowId":1,
        "CertificationDesc":"AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1502780400000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":10,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1445324400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":11,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1445324400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":12,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1445324400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":13,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1445324400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":14,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1445324400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":15,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1445324400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":16,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1445324400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":18,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1395212400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":19,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1395212400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":20,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1395212400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":21,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1395212400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":22,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1395212400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":23,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1395212400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":24,
        "CertificationDesc":"Webinar-Safe Haven Update",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1395212400000)\/",
        "UpdatedBy":"Mihara, Karen"
      },
      {
        "RowId":26,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1368774000000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":27,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1368774000000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":28,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1368774000000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":29,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1368774000000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":30,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1344582000000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":31,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1344582000000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":32,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1344582000000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":33,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1344582000000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":2,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1536822000000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":3,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1536822000000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":5,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1502694000000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":6,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1502694000000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":7,
        "CertificationDesc":"Z-Online AYSOs Safe Haven",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1502694000000)\/",
        "UpdatedBy":""
      },
      {
        "RowId":8,
        "CertificationDesc":"Z-Online CDC Concussion Awareness Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1502607600000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":9,
        "CertificationDesc":"Z-Online CDC Concussion Awareness Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1471849200000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":4,
        "CertificationDesc":"Z-Online CDC Concussion Awareness Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1536476400000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":17,
        "CertificationDesc":"Z-Online CDC Concussion Awareness Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1433746800000)\/",
        "UpdatedBy":"AYSO, Training"
      },
      {
        "RowId":25,
        "CertificationDesc":"Z-Online CDC Concussion Awareness Training",
        "CertificationDateIsNull":0,
        "CertificationDate":"\/Date(1368774000000)\/",
        "UpdatedBy":"AYSO, Training"
      }
    ],
    "VolunteerAYSOID":71243433,
    "VolunteerFullName":"Price, John",
    "Type":"Adult",
    "VolunteerSAR":"5/C",
    "VolunteerMembershipYear":"MY2018"
  }
}

 */

if ($argc < 2) {
  echo "Usage: {$argv[0]} <0=quiet,1=verbose,2=test> [<MY2015>]\n";
  exit;
}

$mode = $argv[1];
$test = ($mode == 2);
$verbose = ($mode > 0);

if ($argc > 2)
  $my = $argv[2];
else
  $my = '';
$lines = 0;

$Badges = array(
  0 => 'None',
  1 => 'U-8 Official',
  2 => 'Assistant',
  3 => 'Regional',
  4 => 'Intermediate',
  5 => 'Advanced',
  6 => 'National'
);

$Certs = array(
  'U-8 Official' => 1,
  'Assistant Referee' => 2,
  'Regional Referee' => 3,
  'Regional Referee & Safe Haven Referee' => 3,
  //'Z-Online Regional Referee Course' => 3,
  'Intermediate Referee' => 4,
  'Advanced Referee' => 5,
  'National Referee' => 6,
  'National 2 Referee' => 6,
);


function DoQuery($mysqli, $query)
{
  $a = [];
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
    printf("ERROR: query '%s' failed: %s\n", $query, $mysqli->error);
    return false;
  }

  //  echo "$query\n";
  return $a;
}


function GetVolCerts($aysoid)
{
  if ($aysoid < 10000000 || $aysoid > 999999999) {
    return false;
  }
  $url = 'https://national.ayso.org/Volunteers/SelectViewCertificationInitialData?AYSOID=' . $aysoid;
  $result = file_get_contents($url);
  // Will dump a beauty json :3
  return json_decode($result, true);
}


// returns TRUE if a change is made
function UpdateRoles(&$roles, $referee)
{
  if ($referee) {
    // if not in roles list
    if (!in_array('ROLE_REF', $roles)) {
      // add role to role list
      $roles[] = 'ROLE_REF';
      return TRUE;
    }
  } else {
    // if in role list
    $i = array_search('ROLE_REF', $roles);
    if ($i !== FALSE) {
      // delete from role list, renumbering indexes
      array_splice($roles, $i, 1);
      return TRUE;
    }
  }
  return FALSE;
}

function buildEmailListFromArray($arr)
{
  $str = '';
  foreach ($arr as $toEmail => $toName) {
    if (!empty($str)) {
      $str .= ',';
    }
    if (!empty($toName)) {
      $str .= "$toName <$toEmail>";
    } else {
      $str .= $toEmail;
    }
  }
  return $str;
}

function sendEmailMessage($emailTo, $subject, $msg, $emailCc = array())
{
  if (!array_key_exists('john.price@ayso894.net', $emailCc)) {
    $emailCc['john.price@ayso894.net'] = 'John Price'; // FIXME
  }
  $to = buildEmailListFromArray($emailTo);
  $cc = buildEmailListFromArray($emailCc);
  $headers['From'] = 'Sportac.us Scheduling System <notification@sportac.us>';
  $headers['Cc'] = $cc;
  // send an email
  mail($to, '[Sportac.us] ' . $subject, $msg, $headers);
  //echo '<pre>';
  //print_r($emailTo);
  //print_r($msg);
  //print_r($message);
  //echo '</pre>';
}


$mysqli = new mysqli($mysql_server, $mysql_user, $mysql_password, $myqsl_database);
if ($mysqli->connect_error) {
  printf("ERROR: Connect failed: %d %s\n", $mysqli->connect_errno, $mysqli->connect_error);
  exit();
}
if ($verbose) echo 'Connect success: ' . $mysqli->host_info . "\n";

$users = DoQuery($mysqli, 'SELECT * FROM fos_user');
$regionsCache = [];
// $user_id = 1;
// $user = $users[$user_id];

$count = 1;
foreach ($users as $user_id => $user) {

  if (array_key_exists($user['region_id'], $regionsCache)) {
    $region = $regionsCache[$user['region_id']];
  } else {
    $res = DoQuery($mysqli, 'SELECT * FROM Region where id=' . $user['region_id']);
    if ($res !== FALSE) {
      $region = $res[$user['region_id']];
      $regionsCache[$user['region_id']] = $region;
    }
  }

  $q = '';
  //print_r($a);
  $aysoid = $user['ayso_id'];
  $roles = unserialize($user['roles']);
  if ($verbose) echo "\n" . $aysoid . ' ' . $user['first_name'] . ' ' . $user['last_name'] . "\n";

  $ayso_info = GetVolCerts($aysoid);

  //print_r($ayso_info);

  if (empty($ayso_info)) {
    if ($verbose) echo 'FAILED to get info for AYSO ID ' . $aysoid . "\n";
    continue;
  }
  if ($ayso_info['ReturnStatus'] != 0) {
    if ($verbose) echo 'FAILED ReturnStatus != 0 for AYSO ID ' . $aysoid . ': ReturnMessage=' . $ayso_info['ReturnMessage'] . "\n";
    continue;
  }
  if (!array_key_exists('VolunteerCertificationDetails', $ayso_info)) {
    if ($verbose) echo 'FAILED VolunteerCertificationDetails not found for AYSO ID ' . $aysoid . "\n";
    continue;
  }
  $details = $ayso_info['VolunteerCertificationDetails'];

  $fullname = $details['VolunteerFullName'];
  $is_youth = ($details['Type'] != 'Adult') ? 1 : 0;
  $aysoSAR = $details['VolunteerSAR'];
  $aysoMY = $details['VolunteerMembershipYear'];
  $cert = 0;
  $safeHaven = 0;
  $concussion = 0;

  foreach ($details['VolunteerCertificationsCoach'] as $cert_info) {
    if (
      ($safeHaven == 0) &&
      array_key_exists(
        $cert_info['CertificationDesc'],
        [
          'Safe Haven Coach' => 1,
          'Z-Online Safe Haven Coach' => 2,
        ]
      )
    ) {
      $safeHaven = 1;
      if ($verbose) echo "Safe Haven (Coach) found\n";
    }
  }
  foreach ($details['VolunteerCertificationsReferee'] as $cert_info) {
    //echo '  searching for cert ' . $cert_info['CertificationDesc'] . "\n";
    if (array_key_exists($cert_info['CertificationDesc'], $Certs)) {
      $c = $Certs[$cert_info['CertificationDesc']];
      if ($verbose) echo $cert_info['CertificationDesc'] . " found\n";
      if ($cert < $c) {
        $cert = $c;
      }
    }
    if (($safeHaven == 0) && array_key_exists($cert_info['CertificationDesc'], [
      'Regional Referee & Safe Haven Referee' => 1,
      'Safe Haven Referee' => 2,
      'Z-Online Safe Haven Referee' => 3,
    ])) {
      $safeHaven = 1;
      if ($verbose) echo "Safe Haven (Referee) found\n";
    }
  }

  foreach ($details['VolunteerCertificationsSafeHaven'] as $cert_info) {
    //echo '  searching for cert ' . $cert_info['CertificationDesc'] . "\n";
    if (($safeHaven == 0) && array_key_exists($cert_info['CertificationDesc'], [
      'Z-Online AYSOs Safe Haven' => 1,
      'Webinar-AYSOs Safe Haven' => 2,
      'AYSOs Safe Haven' => 3
    ])) {
      $safeHaven = 1;
      if ($verbose) echo "Safe Haven found\n";
    }
    if (($concussion == 0) && array_key_exists($cert_info['CertificationDesc'], [
      'Z-Online CDC Concussion Awareness Training' => 1,
      'CDC Online Concussion Awareness Training' => 2
    ])) {
      $concussion = 1;
      if ($verbose) echo "Concussion found\n";
    }
  }

  if ($verbose) echo 'Badge for ' . $fullname . ' determined to be ' . $Badges[$cert] . "\n";
  if ($verbose) echo "Roles: {$user['roles']}\n";

  $valid_my = 0;
  $myMY = substr($user['ayso_my'], 0, 6);
  // if we have already marked a user as good MY, don't believe the National DB
  if (empty($aysoMY) && !empty($myMY)) {
    $aysoMY = $myMY;
  }
  if (($aysoMY != $my) && ($myMY == $my)) {
    if ($verbose) echo "National says $aysoMY but $my in Sportacus, so assume OK\n";
    $aysoMY = $myMY;
  }
  if (empty($my) || ($aysoMY == $my)) {
    $valid_my = 1;
    if ($verbose) echo "MY valid\n";
  }
  $referee = ($user['enabled'] == '1') && ($cert > 0) && $safeHaven && $concussion && $valid_my;

  // if they are already enabled, don't disable them.
  if (!$referee && $user['role_referee']) {
    if ($verbose) echo "* NOT DISABLING REFEREE THAT IS ALREADY ENABLED\n";
    $referee = TRUE;
  }

  if (!$safeHaven) {
    $aysoMY .= " SH";
  }
  if (!$concussion) {
    $aysoMY .= " C";
  }
  $rolesUpdated = UpdateRoles($roles, $referee); // make sure roles array matches referee role
  if (
    ($user['role_referee'] != $referee) || // referee role changed?
    ($user['ayso_my'] != $aysoMY) || // MY changed?
    ($user['badge'] != $Badges[$cert]) || // badge changed?
    ($user['is_youth'] != $is_youth) || // youth status changed?
    $rolesUpdated
  ) {
    if (($user['role_referee'] != $referee)) {
      if ($referee) {
        echo "* Enabling disabled referee: " . $aysoid . ' ' . $user['first_name'] . ' ' . $user['last_name'] . "\n";
        system("echo \"$aysoid $fullname\" >> ref-enabled-list.txt");

        $msg = "You now have access to request referee assignment to games on Sportacus (you may have to logout and log back in for this change to take effect).\n\n" .
          "After logging in, click \"Ref Schedule\" at top, then you will be able to click on the \"CR\", \"AR1\" or \"AR2\" of the schedule and assign yourself to games.\n\n" .
          "If you need help, please use the help menu, contact your referee admin, or use the Contact form from the Help menu.\n\n" .
          "Thanks for using Sportacus!\n";
        $to = [$user['email'] => $user['first_name'] . ' ' . $user['last_name']];
        $cc = [];
        if ($region['ref_admin_email']) {
          $cc = [$region['ref_admin_email'] => $region['ref_admin_name']];
        }
        if ($test) {
          $m = '';
          foreach ($to as $email => $name) {
            $m .= "To: $name <$email>\n";
          }
          foreach ($cc as $email => $name) {
            $m .= "Cc: $name <$email>\n";
          }
          $msg = $m . "\n" . $msg;
          $to = ['john.price@ayso894.net' => 'John Price'];
          $cc = [];
        }
        sendEmailMessage($to, "You now have referee permissions", $msg, $cc);
      } else {
        echo "* DISABLING REFEREE " . $aysoid . ' ' . $user['first_name'] . ' ' . $user['last_name'] . "\n";
        system("echo \"$aysoid $fullname\" >> ref-disabled-list.txt");
        //echo "* NOT disabling referee\n";
        //$referee = true;
      }
    }
    if ($user['ayso_my'] != $aysoMY) {
      echo "* AYSO MY updated from ${user['ayso_my']} to $aysoMY " . $aysoid . ' ' . $user['first_name'] . ' ' . $user['last_name'] . "\n";
    }
    if ($user['badge'] != $Badges[$cert]) {
      echo "* Badge updated from ${user['badge']} to ${Badges[$cert]} " . $aysoid . ' ' . $user['first_name'] . ' ' . $user['last_name'] . "\n";
    }
    if ($user['is_youth'] != $is_youth) {
      echo "* Youth flag updated from ${user['is_youth']} to $is_youth " . $aysoid . ' ' . $user['first_name'] . ' ' . $user['last_name'] . "\n";
    }
    $rolestr = serialize($roles);
    $q = "UPDATE fos_user SET ayso_my='$aysoMY',role_referee='" . ($referee ? 1 : 0) . "',roles='{$rolestr}',badge='{$Badges[$cert]}',is_youth='$is_youth',updated=NOW() WHERE id='$user_id'";
    //$q = "UPDATE fos_user SET ayso_my='$aysoMY',badge='{$Badges[$cert]}',is_youth='$is_youth',updated=NOW() WHERE id='$user_id'";

    echo $q . "\n";
    if (!$test) {
      DoQuery($mysqli, $q);
    }
  }

  //if (--$count == 0) {
  //  break;
  //}
  usleep(1000);
}

$mysqli->close();

<?php 
  $db_hostname = 'kc-sce-appdb01';
  $db_database = "jarrm5";
  $db_username = "jarrm5";
  $db_password = "4BlFIt6TvV";
  

 $connection = mysqli_connect($db_hostname, $db_username,$db_password,$db_database);
 
 if (!$connection)
    die("Unable to connect to MySQL: " . mysqli_connect_errno());
?>
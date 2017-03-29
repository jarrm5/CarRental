<?php 
  $db_hostname = "";
  $db_database = "car_rental";
  $db_username = "";
  $db_password = "";
  

 $connection = mysqli_connect($db_hostname, $db_username,$db_password,$db_database);
 
 if (!$connection)
    die("Unable to connect to MySQL: " . mysqli_connect_errno());

//Test comment
?>
<?php

// $servername = "localhost";  
// $username = "u651277261_dems"; 
// $password = "kXUHBBs[WC:6IF8]"; 
// $dbname = "u651277261_dems";



$servername = "localhost";  
$username = "root"; 
$password = ""; 
$dbname = "f_dems";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

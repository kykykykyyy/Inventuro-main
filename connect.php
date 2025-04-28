<?php
$sName="localhost";
$uName="root";
$password="root";
$db_name="Inventuro";

try{
    $conn = new PDO("mysql:host=$sName;dbname=$db_name",$uName,$password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo "Connection failed : ".$e->getMessage();
}
?>
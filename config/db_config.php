<?php //configuration de connexion à notre base de données
  $hostname = "localhost";
  $username = "root";
  $password = "";
  $dbname = "gestion_facture2";
  $port = "3306";

  $conn = mysqli_connect($hostname, $username, $password, $dbname, $port);
  if(!$conn){
    echo "Erreur de connexion à la base de données".mysqli_connect_error();
  }

?>
<?php 
	session_start();
	include_once "../config/db_config.php";

    // Vérifier si le formulaire de validation de la facture est soumis
    if(isset($_POST['valider_facture'])) {
        $id_facture = $_POST['id_facture'];
        // Mettre à jour la condition dans la table facture
        $update_query = "UPDATE facture SET `condition`='validee' , `notif`=1  WHERE id_facture = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        if($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_facture);
            if(mysqli_stmt_execute($stmt)) {
                ?>
                <script>
                    alert("La facture a été validée avec succès.");
                    window.location = "dash.php";
                </script> <?php
            } else {
                echo "Erreur lors de la mise à jour de la condition de la facture.";
            }
        } else {
            echo "Erreur lors de la préparation de la requête de mise à jour.";
        }
    }


    if(isset($_POST['submit'])){
        $idclient = $_POST['idclient'];
        $name = $_POST['name'];
        $lastname = $_POST['lastname'];
        $cin = $_POST['cin'];
        $address = $_POST['address'];
        $password = $_POST['password'];
        $id_admin = $_SESSION['id_admin'];
        // Vérifier si le client existe déjà
        $check_query = "SELECT * FROM client WHERE id_client = '$idclient'";
        $check_result = mysqli_query($conn, $check_query);   
        if(mysqli_num_rows($check_result) > 0){
            // Le client existe, donc mettre à jour ses informations
            $update_query = "UPDATE client SET nom_client = '$name', prenom_client = '$lastname', cin = '$cin', adresse = '$address', password_client = '$password' , id_admin = '$id_admin' WHERE id_client = '$idclient'";
            $update_result = mysqli_query($conn, $update_query);   
            if($update_result){
                ?>
                    <script>
                        alert("Client information updated successfully !");
                        window.location = "dash.php";
                    </script>
                <?php
            } else{
                ?>
                <script>
                    alert("Error updating client information");
                    window.location = "dash.php";
                </script>
            <?php
        }
        }else{
            // Le client n'existe pas, donc insérer un nouvel enregistrement
            $insert_query = "INSERT INTO client (id_client, nom_client, prenom_client, cin, adresse,password_client , id_admin) VALUES ('$idclient', '$name', '$lastname', '$cin', '$address', '$password', '$id_admin')";
            $insert_result = mysqli_query($conn, $insert_query);
    
            if($insert_result){
                ?>
                    <script>
                        alert("Client added successfully !");
                        window.location = "dash.php";
                    </script>
                <?php
            }else{
                    ?>
                    <script>
                        alert("Error adding client");
                        window.location = "dash.php";
                    </script>
                <?php
            }
        }
        /*// Redirection après le traitement du formulaire
        header("Location: ".$_SERVER['PHP_SELF']);
        exit(); */
    }    
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="../client_dashboard/style.css">
        <title>Fournisseur_dashboard</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style >
            .section {
                display: none;
            }
            .section.active {
                display: block;
            }
        </style>
    </head>
    <body>
        <!-- SIDEBAR -->
        <section id="sidebar">
            <a href="#" class="brand">
            <span class="text" style="margin-top:15% ; margin-left:25%; color:#0c4563; font-family: 'Namdhinggo'; font-size: 27px;">Supplier</span>
            </a>
            <ul class="side-menu top">
                <li class="active">
                    <a href="#" class="menu-item" data-target="dashboard-section">
                        <i class='bx bxs-dashboard'></i>
                        <span class="text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item" data-target="clients-section">
                        <i class='bx bxs-group'></i>
                        <span class="text">Clients management</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item" data-target="reclamations-section">
                        <i class='bx bxs-message-dots'></i>
                        <span class="text">Claims processing</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item" data-target="message-section">
                        <i class='bx bxs-dollar-circle'></i>
                        <span class="text">Invoices</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item" data-target="team-section">
                        <i class='bx bxs-doughnut-chart'></i>
                        <span class="text">Annual consumption</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item" data-target="anomalies-section">
                        <i class='bx bxs-cog'></i>
                        <span class="text">Anomalies</span>
                    </a>
                </li>
            </ul>
            <ul class="side-menu">
                <li>
                    <a href="#" class="menu-item logout" data-target="settings-section">
                        <i class='bx bxs-log-out-circle'></i>
                        <span class="text">Logout</span>
                    </a>
                </li>
            </ul>
        </section>
        <!-- SIDEBAR -->

        <!-- CONTENT -->
        <section id="content">
            <!-- NAVBAR -->
            <nav>
                <!-- HTML Form -->
                <form id="searchForm" style="margin-left: 22%;">
                    <div class="form-input">
                        <input id="searchInput" type="search" placeholder="Search...">
                        <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                    </div>
                </form>

                
                
                <a href="#" class="profile">
                    <div class="disk-container">
                        <div class="disk"></div>
                    <div class="letter-container">S</div>
                    </div>
                    <style>
                        .disk-container {
                            width: 4px;
                            height: 40px;
                            border-radius: 50%;
                            background-color: #0c4563; /* couleur du disque */
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            position: relative;
                            text-decoration: none;
                            margin-right: 20px;
                            margin-left: 20px;
                        }
                        .disk {
                            height: 40px;
                            border-radius: 50%;
                            background-color: #0c4563; /* couleur du disque intérieur */
                            position: absolute;
                            z-index: -1;
                            width: 40px;
                        }
                        .letter-container {
                            font-size: 24px;
                            color: #fff; /* couleur de la lettre */
                            font-family: 'Namdhinggo';
                        }
                    </style>
                </a>
            </nav>
            <!-- NAVBAR -->

            <!-- MAIN -->
            <main>
                <div id="dashboard-section" class="section active">
                    <div class="head-title">
                        <div class="left">
                            <h1>Dashboard</h1>
                        </div>
                    </div>

                    <ul class="box-info">
                        
                        <li>
                            <?php 
                                $id_admin = $_SESSION['id_admin'];
                                $req = "SELECT COUNT(id_reclamation) as nb_reclamation FROM reclamation inner join client on reclamation.id_client = client.id_client where id_admin = $id_admin";
                                $result = mysqli_query($conn, $req);
                                $row = mysqli_fetch_assoc($result);
                            ?>
                            <i class='bx bxs-calendar-check'></i>
                            <span class="text">
                                <h3><?php echo $row['nb_reclamation']; ?></h3>
                                <p>Client's claims</p>
                            </span>
                        </li>
                        <li>
                            <?php 
                                $id_admin = $_SESSION['id_admin'];
                                $req = "SELECT COUNT(id_client) as nb_client FROM client WHERE id_admin = $id_admin";
                                $result = mysqli_query($conn, $req);
                                $row = mysqli_fetch_assoc($result);
                            ?>
                            <i class='bx bxs-group'></i>
                            <span class="text">
                                <h3><?php echo $row['nb_client']; ?></h3>
                                <p>Number of clients </p>
                            </span>
                        </li>
                        <li>
                            <?php 
                                $id_admin = $_SESSION['id_admin'];
                                $req = "SELECT COUNT(id_facture) as nb_facture FROM facture F inner join releve_consommation_mois R on F.id_releve = R.id_releve inner join client C on R.id_client = C.id_client where C.id_admin = $id_admin";
                                $result = mysqli_query($conn, $req);
                                $row = mysqli_fetch_assoc($result);
                            ?>
                            <i class='bx bxs-dollar-circle'></i>
                            <span class="text">
                                <h3><?php echo $row['nb_facture']; ?></h3>
                                <p>Invoices number</p>
                            </span>
                        </li>
                    </ul>

                    <div class="table-data">
                        <div class="order">
                            <div class="graph-container">
                                <?php include "graphes.php"; ?>
                            </div>
                            <div class="graph-container">
                                <?php include "graphe2.php"; ?>
                            </div>
                            <style>
                                .order {
                                    display: flex;
                                    justify-content: space-between;
                                }

                                .graph-container {
                                    width: calc(50% - 80px); 
                                    margin-right: 20px; 
                                }
                            </style>
                        </div>
                    </div>
                </div>

                <!-- Autres sections de contenu -->
                <div id="clients-section" class="section">
                    <h1 style="font-size: 24px; color: #333; margin-bottom: 20px; text-align:center;">Add or Modify</h1>

                    <div class="client_manage" style="padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 80%; margin-left: auto; margin-right: auto; ">
                        <form action="dash.php" method="POST" >
                            <style>
                                label {
                                    display: block;
                                    margin-bottom: 10px;
                                    color: #050505;
                                    font-size: 16px;
                                }
                                select,input[type="text"],input[type="number"] {
                                    width: 100%;
                                    padding: 10px;
                                    margin-bottom: 15px;
                                    border: 1px solid #ccc;
                                    border-radius: 5px;
                                    box-sizing: border-box;
                                    font-size: 16px;
                                    background-color: #E8E8E8;
                                }
                                #content_reclam{
                                    width: 100%;
                                    height: 140px;
                                    padding: 10px;
                                    margin-bottom: 15px;
                                    border: 1px solid #ccc;
                                    border-radius: 5px;
                                    box-sizing: border-box;
                                    font-size: 16px;
                                    background-color: #E8E8E8;
                                }
                                input[type="file"] {
                                    margin-bottom: 15px;
                                }
                                input[type="submit"], .invoice_btn  {
                                    width: 100%;
                                    padding: 10px;
                                    border: none;
                                    border-radius: 5px;
                                    background-color: #050505;
                                    color: #fff;
                                    font-size: 16px;
                                    cursor: pointer;
                                    margin-top: 10px;
                                }
                            </style>
                            <label for="idclient">ID CLIENT</label>
                            <input type="number" name="idclient" id="idclient" placeholder="Enter the client's id">
                            <label for="name">NAME</label>
                            <input type="text" name="name" id="name" placeholder="Enter the client's name">
                            <label for="lastname">LAST NAME</label>
                            <input type="text" name="lastname" id="lastname" placeholder="Enter the client's last name">
                            <label for="cin">CIN</label>
                            <input type="text" name="cin" id="cin" placeholder="Enter the client's CIN">
                            <label for="address">ADDRESS</label>
                            <input type="text" name="address" id="address" placeholder="Enter the client's address">
                            <label for="password">PASSWORD</label>
                            <input type="text" name="password" id="password" placeholder="Enter the client's password">
                            <input type="submit" name="submit" value="Confirm">
                            <!-- Autres sections de contenu ici -->
                        </form>
                    </div>
                </div>
                
                <div id="reclamations-section" class="section">
                    <div id="content4">
                        <h1 style="font-size: 24px; color: #333; margin-bottom: 20px; text-align:center;">All clients claims</h1>

                        <div class="table-data">
                            <div class="order">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>id_client</th>
                                            <th>client's name</th>
                                            <th>date reclamation</th>
                                            <th>type</th>
                                            <th>content</th>
                                            <th>Status</th>
                                            <th>traitement</th>
                                        </tr>
                                    </thead>
                                    <?php
                                        $id_admin = $_SESSION['id_admin'];
                                        $req1 = "SELECT * FROM reclamation inner join client on reclamation.id_client = client.id_client where id_admin = $id_admin order by date_reclamation desc ";
                                        $result_reclamations = mysqli_query($conn, $req1);
                                        if(mysqli_num_rows($result_reclamations) > 0){
                                            while($row = mysqli_fetch_assoc($result_reclamations)){
                                        ?>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo $row['id_client']; ?></td>
                                                    <td><?php echo $row['nom_client'] . " " . $row['prenom_client']; ?></td>
                                                    <td><?php echo $row['date_reclamation']; ?></td>
                                                    <td><?php echo $row['type_reclamation']; ?></td>
                                                    <td><?php echo $row['contenu']; ?></td>
                                                    <td><span class="status completed"><?php echo $row['etat_reclamation']; ?></span></td>
                                                    <td><button class="trait_btn">Traiter</button></td>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>

                                        </tr>
                                        <style>
                                            .trait_btn{
                                                font-size: 10px;
                                                padding: 6px 16px;
                                                color: #ffffff;
                                                background-color: #050505;
                                                border-radius: 20px;
                                                font-weight: 700;
                                                cursor: pointer;
                                            }
                                            .trait_btn:hover{
                                                background-color: #ffffff;
                                                color: #050505;
                                            }
                                        </style>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="message-section" class="section">
                    <div id="content5">
                        <h1 style="font-size: 24px; color: #333; margin-bottom: 20px; text-align:center;">All clients invoices</h1>

                        <div class="table-data">
                            <div class="order">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>id_client</th>
                                            <th>name</th>
                                            <th>date_facture</th>
                                            <th>facture du </th>
                                            <th>amount_HT</th>
                                            <th>amount_TTC</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $id_admin = $_SESSION['id_admin'];
                                        $req2 = "SELECT * FROM facture F inner join releve_consommation_mois R on F.id_releve = R.id_releve inner join client C on R.id_client = C.id_client where C.id_admin = $id_admin order by date_facture desc ";
                                        $result_factures = mysqli_query($conn, $req2);
                                        if(mysqli_num_rows($result_factures) > 0){
                                            while($row = mysqli_fetch_assoc($result_factures)){
                                        ?>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo $row['id_client']; ?></td>
                                                    <td><?php echo $row['nom_client'] . " " . $row['prenom_client']; ?></td>
                                                    <td><?php echo $row['date_facture']; ?></td>
                                                    <td><?php echo $row['mois'] . "/" . $row['annee']; ?></td>
                                                    <td><?php echo $row['montant_HT']. " dh"; ?></td>
                                                    <td><?php echo $row['montant_TTC']. " dh"; ?></td>
                                                    <td><span class="status completed"><?php echo $row['etat_facture']; ?></span></td>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="team-section" class="section">
                    <div id="content6">
                        <h1 style="font-size: 24px; color: #333; margin-bottom: 20px; text-align:center;">All clients annual consumptions</h1>

                        <div class="table-data">
                            <div class="order">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>id_client</th>
                                            <th>name</th>
                                            <th>date</th>
                                            <th>year</th>
                                            <th>Agent's consumption</th> <!-- consommation saisie par l'agent -->
                                            <th>Client's consumption</th> <!-- consommation saisie par le client -->
                                            <th>status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $id_admin = $_SESSION['id_admin'];
                                        $req3 = "SELECT C.id_client, C.nom_client, C.prenom_client, F.date_file, F.annee AS annee_fichier, F.consommationAnnuelle,
                                        SUM(R.consommation) AS consommationClient
                                        FROM file_consommationannuelle F
                                        INNER JOIN client C ON F.id_client = C.id_client
                                        INNER JOIN releve_consommation_mois R ON C.id_client = R.id_client
                                        WHERE C.id_admin = $id_admin AND F.annee = R.annee
                                        GROUP BY C.id_client, F.annee
                                        ORDER BY F.date_file DESC";
                                        

                                        $result_consumpann = mysqli_query($conn, $req3);
                                        if(mysqli_num_rows($result_consumpann) > 0){
                                            while($row = mysqli_fetch_assoc($result_consumpann)){
                                        ?>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo $row['id_client']; ?></td>
                                                    <td><?php echo $row['nom_client'] . " " . $row['prenom_client']; ?></td>
                                                    <td><?php echo $row['date_file']; ?></td>
                                                    <td><?php echo  $row['annee_fichier']; ?></td>
                                                    <td><?php echo $row['consommationAnnuelle'] ." kwh"; ?></td>
                                                    <td><?php echo $row['consommationClient'] ." kwh"; ?></td>
                                                    <th> <?php if( abs($row['consommationAnnuelle'] - $row['consommationClient']) <= 50 ){echo "Tolérée";}else{echo "Non tolérée";} ?></th>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="blur-background" class="blur-background" style="display: none;"></div>
                <div id="anomalies-section" class="section">
                    <div id="content7">
                        <h1 style="font-size: 24px; color: #333; margin-bottom: 20px; text-align:center;">Invoice's anomalies</h1>
                        <div class="table-data">
                            <div class="order">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>id_facture</th>
                                            <th>id_client</th>
                                            <th>name</th>
                                            <th>traitement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $id_admin = $_SESSION['id_admin'];
                                            $req4 = "SELECT * FROM facture F inner join  releve_consommation_mois R on R.id_releve = F.id_releve inner join client C on R.id_client = C.id_client where C.id_admin = $id_admin and F.condition='non validee'order by F.date_facture desc ";
                                            $result_anomalies = mysqli_query($conn, $req4);
                                            if(mysqli_num_rows($result_anomalies) > 0){
                                                while($row = mysqli_fetch_assoc($result_anomalies)){
                                            ?>
                                                <tr>
                                                    <td><?php echo $row['id_facture']; ?></td>
                                                    <td><?php echo $row['id_client']; ?></td>
                                                    <td><?php echo $row['nom_client'] . " " . $row['prenom_client']; ?></td>
                                                    <td>
                                                        <button class="trait_btn" onclick="showDetails(<?php echo $row['id_facture']; ?>)">Traiter</button>
                                                        
                                                    </td>
                                                </tr>
                                            <?php
                                            }
                                            }
                                        ?> 
                                    </tbody>
                                </table> 
                            </div>
                        </div>
                        <?php
                            $id_admin = $_SESSION['id_admin'];
                            $req5 = "SELECT * FROM facture F INNER JOIN releve_consommation_mois R ON R.id_releve = F.id_releve INNER JOIN client C ON R.id_client = C.id_client WHERE C.id_admin = $id_admin AND F.condition = 'non validee' ORDER BY F.date_facture DESC";
                            $result1 = mysqli_query($conn, $req5);
                            if(mysqli_num_rows($result1) > 0) {
                                while($row = mysqli_fetch_assoc($result1)) {
                            ?>
                                <div id="details_<?php echo $row['id_facture']; ?>" style="display: none;">
                                    <div style="display: flex; background-color: #ffffff; border-radius: 8px; width: 100%; margin-left: auto; margin-right: auto; margin-top: 12px; padding: 20px;">
                                        <div style="flex: 1; border-right: 1px solid #ccc;">
                                            <!-- Section pour l'image -->
                                            <p style="color:#0c4563; text-align:center;">L'image du compteur:</p>
                                            <?php
                                            $sql = "SELECT img_compteur FROM releve_consommation_mois WHERE id_releve = ?";
                                            $stmt = mysqli_prepare($conn, $sql);

                                            if($stmt) {
                                                mysqli_stmt_bind_param($stmt, "i", $row['id_releve']);
                                                if(mysqli_stmt_execute($stmt)) {
                                                    mysqli_stmt_bind_result($stmt, $img_compteur);
                                                    if(mysqli_stmt_fetch($stmt)) {
                                                        echo '<img src="../client_dashboard/destination_path/' . $img_compteur . '" alt="Image du compteur" style="width:100%; margin-top:10px;">';
                                                    } else {
                                                        echo "Aucune image trouvée pour ce relevé.";
                                                    }
                                                    // Fermeture de la requête
                                                    mysqli_stmt_close($stmt);
                                                } else {
                                                    echo "Erreur lors de l'exécution de la requête";
                                                }
                                            } else {
                                                echo "Erreur lors de la préparation de la requête";
                                            }
                                            ?>
                                        </div>
                                        <div style="flex: 1; padding: 20px;">
                                            <!-- Section pour la valeur -->
                                            <p style="text-align: center; color:#0c4563;">Consommation saisie par le client:</p> </br>
                                            <p style="text-align: center;"><?php echo $row['consommation']; ?> kWh  </p>

                                            <div style="text-align: center; margin-top: 30px;">
                                            <form method="post">
                                                <input type="hidden" name="id_facture" value="<?php echo $row['id_facture']; ?>">
                                                <button type="submit" class="trait_btn" name="valider_facture">Valider</button>
                                            </form>
                                            <a  href="anomalies_modif.php?id_facture=<?php echo $row['id_facture']; ?>">
                                                <button class="trait_btn">Modifier</button>
                                            </a>
                                        </div>

                                        </div>
                                        
                                    </div>
                                </div>
                            <?php
                                }
                            }
                            ?>
                        </div>

                    </div>


                <div id="settings-section" class="section">
                    <div class="logout_sec" style="padding: 50px; background-color: #ffffff; border-radius: 10px;  width: 50%; margin-left: auto; margin-right: auto; margin-top:55px;  ">
                        <h1 style="text-align: center; font-family: 'Namdhinggo'; font-size: 40px;">Are you sure ?</h1>
                        <div style="text-align: center; margin-top: 25px;">
                            <a href="..\deconnexion.php"><button class="logout_btn" > Yes</button> </a>
                            <a href="..\admin_dashboard\dash.php" ><button class="logout_btn"> No </button> </a>
                            <style>
                                .logout_btn{
                                    padding: 10px;
                                    color: #0c4563;
                                    font-size: 24px;
                                    background-color: #ffffff;
                                    border-radius: 30px;
                                    width: 30%;
                                    cursor: pointer;
                                    font-family: 'Namdhinggo';
                                    border: 1px solid #050505; 
                                }
                                .logout_btn:hover{
                                    background-color: #0c4563;
                                    color: #ffffff;
                                } 
                            </style>
                        </div>
                    </div>
                </div>
            </main>
            <!-- MAIN -->
        </section>
        <!-- CONTENT -->

        <script>
            // Script JavaScript pour gérer le basculement des sections de contenu
            document.addEventListener('DOMContentLoaded', function() {
                const menuItems = document.querySelectorAll('.menu-item');
                const sections = document.querySelectorAll('.section');

                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        // Désactive toutes les sections
                        sections.forEach(section => {
                            section.classList.remove('active');
                        });
                        // Active la section correspondante
                        const target = item.getAttribute('data-target');
                        document.getElementById(target).classList.add('active');
                    });
                });
            });
        </script>

        <script>
            function showDetails(id) {
                var detailsDiv = document.getElementById('details_' + id);
                if (detailsDiv.style.display === 'none') {
                    detailsDiv.style.display = 'block';
                } else {
                    detailsDiv.style.display = 'none';
                }
            }
        </script>

        <script>
            document.getElementById('searchForm').addEventListener('submit', function(event) {
                // Empêcher le comportement par défaut du formulaire de rechargement de la page
                event.preventDefault();
                
                let searchTerm = document.getElementById('searchInput').value.trim().toLowerCase();
                
                // Table 1
                let rowsTable1 = document.querySelectorAll('#content4 table tbody tr');
                rowsTable1.forEach(function(row) {
                    if (row.textContent.toLowerCase().includes(searchTerm)) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Table 2
                let rowsTable2 = document.querySelectorAll('#content5 table tbody tr');
                rowsTable2.forEach(function(row) {
                    if (row.textContent.toLowerCase().includes(searchTerm)) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Table 3
                let rowsTable3 = document.querySelectorAll('#content6 table tbody tr');
                rowsTable3.forEach(function(row) {
                    if (row.textContent.toLowerCase().includes(searchTerm)) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Table 4
                let rowsTable4 = document.querySelectorAll('#content7 table tbody tr');
                rowsTable4.forEach(function(row) {
                    if (row.textContent.toLowerCase().includes(searchTerm)) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        </script>
    </body>
</html>

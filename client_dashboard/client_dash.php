<?php
    session_start();
    include_once "../config/db_config.php"; 


    if(isset($_POST['submit'])) {       
        $month = mysqli_real_escape_string($conn, $_POST['month']);
        $year = mysqli_real_escape_string($conn, $_POST['year']);
        $consumption = mysqli_real_escape_string($conn, $_POST['consumption']);
        // Récupérer l'ID du client depuis la session
        if(isset($_SESSION['id_client'])) {
            $id_client = $_SESSION['id_client'];
        } else {
            echo "Error: id_client not set in session";
            exit();
        } 
        // Vérifie si un fichier a été téléchargé et le déplace vers le dossier de destination
        $meter_img = "";
        if(isset($_FILES['meter_img']) && $_FILES['meter_img']['error'] == UPLOAD_ERR_OK) {
            $meter_img_name = $_FILES['meter_img']['name'];
            $meter_img_tmp = $_FILES['meter_img']['tmp_name'];
            $destination = "destination_path/" . $meter_img_name;
            if(move_uploaded_file($meter_img_tmp, $destination)) {
                $meter_img = $meter_img_name;
            } else {
                echo "Failed to move uploaded file.";
            }
        }
        // Récupérer l'ancienne valeur de consommation
        $old_month = $month - 1;
        $old_consumption_sql = "SELECT consommation FROM releve_consommation_mois WHERE id_client = '$id_client' And mois = '$old_month' And annee = '$year'";
        $old_consumption_result = mysqli_query($conn, $old_consumption_sql);
        $old_consumption_row = mysqli_fetch_assoc($old_consumption_result);
        $old_consumption = $old_consumption_row ? $old_consumption_row['consommation'] : 0;  
        // Vérifier s'il y a déjà une entrée pour le mois et l'année spécifiés
        $check_sql = "SELECT * FROM releve_consommation_mois WHERE mois = '$month' AND annee = '$year'";
        $check_result = mysqli_query($conn, $check_sql);
        if(mysqli_num_rows($check_result) > 0) {
            echo "Error: Duplicate entry";
        } else {
            // Insérer les données dans la base de données en utilisant des requêtes préparées
            $sql = "INSERT INTO releve_consommation_mois (mois, annee, consommation, old_consommation, img_compteur, id_client, id_admin) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql);
            // Stockez les valeurs dans des variables avant de les passer à mysqli_stmt_bind_param()
            $meter_img_value = $meter_img;
            $id_client_value = $id_client;
            $id_admin_value = "";
            // Récupérer l'ID de l'administrateur associé au client depuis la base de données
            $sql_get_admin = "SELECT id_admin FROM client WHERE id_client = ?";
            $stmt_get_admin = mysqli_prepare($conn, $sql_get_admin);
            mysqli_stmt_bind_param($stmt_get_admin, "i", $id_client);
            mysqli_stmt_execute($stmt_get_admin);
            $result_get_admin = mysqli_stmt_get_result($stmt_get_admin);

            if($row_get_admin = mysqli_fetch_assoc($result_get_admin)) {
                $id_admin_value = $row_get_admin['id_admin'];
            } else {
                echo "Error: Failed to retrieve admin ID from database";
                exit();
            }
            // Utilisez ces variables dans mysqli_stmt_bind_param()
            mysqli_stmt_bind_param($stmt_insert, "ssssssi", $month, $year, $consumption, $old_consumption, $meter_img_value, $id_client_value, $id_admin_value);
            if(mysqli_stmt_execute($stmt_insert)) {
                // Récupérer l'ID du relevé nouvellement inséré
                $id_releve = mysqli_insert_id($conn);
                // Calculer le prix total et le prix TTC
                $consump = $consumption - $old_consumption;
                $prix_total = calculerPrix($consump);
                $prix_TTC = calculerPrixTTC($prix_total);
                // Insérer les données dans la table facture
                $sql_facture = "INSERT INTO facture (date_facture, mois, annee, montant_HT, montant_TTC, etat_facture, id_releve) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_facture = mysqli_prepare($conn, $sql_facture);
                //$date_facture = date("$year-$month");
                $date_facture = date("Y-m-d");
                $etat = "non payee";
                mysqli_stmt_bind_param($stmt_facture, "ssssdsi", $date_facture, $month, $year, $prix_total, $prix_TTC, $etat, $id_releve);
                if(mysqli_stmt_execute($stmt_facture)) {
                    ?>
                        <script>
                            alert("Data inserted successfully !");
                            window.location = "client_dash.php";
                        </script>
                    <?php
                }else {
                    echo "Error: " . mysqli_error($conn);
                }
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
    }

    // Fonction de calcul du prix total
    function calculerPrix($consommation_kwh) {
        if ($consommation_kwh <= 100) {
            $prix_unitaire = 0.8;
        } elseif ($consommation_kwh <= 200) {
            $prix_unitaire = 0.9;
        } else {
            $prix_unitaire = 1.0;
        }
        // Calcul du prix total
        $prix_total = $consommation_kwh * $prix_unitaire;
        // Vérifier si le prix total est négatif
        if ($prix_total < 0) {
            $prix_total = 0; // Ajuster à zéro si négatif
        }
        return $prix_total;
    }

    // Fonction de calcul du prix TTC
    function calculerPrixTTC($prix_total) {
        if ($prix_total < 0) {
            $prix_total = 0; // Ajuster à zéro si négatif
        }
        $prix_TTC = $prix_total * (1 + 0.14);
        return $prix_TTC;
    }

    // Traitement de la réclamation
    if (isset($_POST['send'])) {
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $content_reclam = mysqli_real_escape_string($conn, $_POST['content_reclam']);
        $others = mysqli_real_escape_string($conn, $_POST['others']);
        if ($type == "Other") {
            $type = $others;
        }
        // Récupérer l'ID du client depuis la session
        if(isset($_SESSION['id_client'])) {
            $id_client = $_SESSION['id_client'];
        } else {
            echo "Error: id_client not set in session";
            exit();
        }
        // Insérer les données dans la base de données en utilisant des requêtes préparées
        $sql = "INSERT INTO reclamation (date_reclamation, type_reclamation, contenu, etat_reclamation, id_client) 
                VALUES (?, ?, ?, ? , ?)";
        $stmt_insert = mysqli_prepare($conn, $sql);
        // Stockez les valeurs dans des variables avant de les passer à mysqli_stmt_bind_param()
        $id_client_value = $id_client;
        $date_reclamation = date("Y-m-d");
        $etat_reclam = "en attente";
        // Utilisez ces variables dans mysqli_stmt_bind_param()
        mysqli_stmt_bind_param($stmt_insert, "ssssi",$date_reclamation, $type, $content_reclam, $etat_reclam, $id_client_value);
        if(mysqli_stmt_execute($stmt_insert)) {
            ?>
             <script>
                alert("Data inserted successfully !");
                window.location = "client_dash.php";
             </script>
            <?php
        }  else {
            echo "Error: " . mysqli_error($conn);
        }
    }   

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="style.css">
        <title>Client_dashboard</title>
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
            <span class="text" style="margin-top:15% ; margin-left:30%; color:#0c4563; font-family: 'Namdhinggo'; font-size: 27px; ">Client</span>
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
                        <i class='bx bxs-doughnut-chart'></i>
                        <span class="text">Consumption</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item" data-target="reclamations-section">
                        <i class='bx bxs-dollar-circle'></i>
                        <span class="text">Invoices</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item" data-target="message-section">
                        <i class='bx bxs-message-dots'></i>
                        <span class="text">Claims</span>
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
                <form action="#" style="margin-left: 22%;">
                    <div class="form-input">
                        <!-- <input type="search" placeholder="Search...">
                        <button type="submit" class="search-btn"><i class='bx bx-search'></i></button> -->
                    </div>
                </form>
                <a href="#" class="notification" id="notificationLink">
                    <i class='bx bxs-bell'></i>
                    <span class="num">
                        <?php
                        // Exécuter la requête SQL pour compter les factures avec notif=1
                        $id_client = $_SESSION['id_client'];
                        $query = "SELECT COUNT(*) AS num_factures FROM facture F INNER JOIN releve_consommation_mois R ON F.id_releve = R.id_releve WHERE R.id_client = $id_client AND  notif = '1' ";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);
                        echo $row['num_factures']; 
                        ?>
                    </span>
                </a>

                <?php
                // Afficher le message uniquement si notif est égal à 1
                if ($row['num_factures'] > 0) {
                    echo '<div id="notif_msg">Your invoice has been approved</div>';
                }
                ?>

                <script>
                    var notificationLink = document.getElementById('notificationLink');
                    notificationLink.addEventListener('click', function() {
                        var notificationMessage = document.getElementById('notif_msg');
                        notificationMessage.style.display = 'block';
                        return false;
                    });
                </script>

                <a href="#" class="profile">
                    <div class="disk-container">
                        <div class="disk"></div>
                        <div class="letter-container">C</div>
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
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            background-color: #0c4563; /* couleur du disque intérieur */
                            position: absolute;
                            z-index: -1;
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
                            <h1 style="text-align:center;">Welcome back to your space</h1>
                        </div>
                    </div>
                    
                    <ul class="box-info">
                        <?php 
                            $id_client = $_SESSION['id_client'];
                            $current_month = date('m');
                            $current_year = date('Y');
                            
                            $req1 = "SELECT * FROM releve_consommation_mois WHERE id_client = $id_client AND mois = $current_month AND annee = $current_year";
                            $getInf = mysqli_query($conn, $req1);
                            
                            if(mysqli_num_rows($getInf) > 0){
                                while($row = mysqli_fetch_assoc($getInf)){
                        ?>
                        <li>
                            <i class='bx bxs-calendar-check'></i>
                            <span class="text">
                                <h3><?php echo $row['consommation']; ?></h3>
                                <?php 
                                }
                            }
                                ?>
                                <p>Monthly Consumption</p>
                            </span>
                        </li>
                        <?php 
                            $id_client = $_SESSION['id_client'];
                            $current_year = date('Y');
                            
                            $req2 = "SELECT * FROM file_consommationannuelle WHERE id_client = $id_client AND  annee = $current_year";
                            $getInf = mysqli_query($conn, $req2);
                            
                            if(mysqli_num_rows($getInf) > 0){
                                while($row = mysqli_fetch_assoc($getInf)){
                        ?>
                        <li>
                            <i class='bx bxs-group'></i>
                            <span class="text">
                                <h3><?php echo $row['consommationAnnuelle']; ?></h3>
                                <?php 
                                }
                            }
                                ?>
                                <p>Annual Consumption</p>
                            </span>
                        </li>

                        <?php 
                            $id_client = $_SESSION['id_client'];
                            $current_year = date('Y');

                            $req3 = "SELECT count(*) AS total_unpaid FROM facture F INNER JOIN releve_consommation_mois R ON F.id_releve = R.id_releve WHERE R.id_client = $id_client AND F.etat_facture = 'non payee'";
                            $getInf = mysqli_query($conn, $req3);

                            if(mysqli_num_rows($getInf) > 0){
                                while($row = mysqli_fetch_assoc($getInf)){
                            ?>
                            <li>
                                <i class='bx bxs-dollar-circle'></i>
                                <span class="text">
                                    <h3><?php echo $row['total_unpaid']; ?></h3>
                                    <p>Unpaid Invoice</p>
                                </span>
                            </li>
                        <?php 
                            }
                        }
                        ?>

                    </ul>

                </div>

                <!-- Autres sections de contenu -->
                <div id="clients-section" class="section">
                    <h1 style="font-size: 24px; color: #333; margin-bottom: 20px; text-align:center;">Enter your consumption</h1>

                    <div class="consump" style="padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 80%; margin-left: auto; margin-right: auto; ">
                    <form action="client_dash.php" method="POST" enctype="multipart/form-data" >
                        <style>
                            label {
                                display: block;
                                margin-bottom: 10px;
                                color: #8f8e8e;
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
                        <label for="month">MONTH</label>
                        <select name="month" id="month" required>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                        <label for="year">YEAR</label>
                        <input type="number" name="year" id="year" placeholder="2024" required>
                        <label for="consumption">CONSUMPTION IN (KWH)</label>
                        <input type="text" name="consumption" id="consumption" required>
                        <label for="meter_img">METER_IMAGE</label>
                        <input type="file" name="meter_img" id="meter_img" accept="image/x-png,image/gif,image/jpeg,image/jpg" required>
                        <input type="submit" name="submit" value="Submit">
                    </form>
                </div>
                </div>

                
                <div id="reclamations-section" class="section">
                    <h1 style="font-size: 24px; color: #333; margin-bottom: 20px; text-align:center;">Your invoices</h1>

                    <div class="invoice" style="padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 60%; margin-left: auto; margin-right: auto; ">
                        <form action="facture.php" method="POST">
                            <label for="month_invoice">MONTH</label>
                            <input type="number" name="month_invoice" id="month_invoice" placeholder="2">
                            <label for="year_invoice">YEAR</label>
                            <input type="number" name="year_invoice" id="year_invoice" placeholder="2024">
                            <button type="submit" class="invoice_btn" name="submit"><i class='bx bxs-cloud-download'></i>Download PDF</button>
                        </form>

                    </div>
                </div>


                <div id="message-section" class="section">
                    <h1 style="font-size: 24px; color: #333; margin-bottom: 20px; text-align:center;">Write your complaint</h1>

                    <div class="complain" style="padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 60%; margin-left: auto; margin-right: auto; ">
                        <form action="client_dash.php" method="POST" id="myForm">
                            <label for="month_invoice">TYPE</label>
                            <select name="type" id="type" required onchange="toggleOthers()">
                                <option value="Fuite externe">Fuite externe</option>
                                <option value="Fuite interne">Fuite interne</option>
                                <option value="Facture">Facture</option>
                                <option value="Other">Other</option>
                            </select>
                            <label for="others" id="othersLabel" style="display:none;">OTHERS</label>
                            <input type="text" name="others" id="others" style="display:none;"></input>
                            <label for="content_reclam">CONTENT</label>
                            <input type="text" name="content_reclam" id="content_reclam" required></input>
                            <input type="submit" name="send" value="Send">                       
                            <!-- Autres sections de contenu ici -->
                        </form>
                    </div>

                    <script>
                        function toggleOthers() {
                            var typeSelect = document.getElementById("type");
                            var othersLabel = document.getElementById("othersLabel");
                            var othersInput = document.getElementById("others");

                            if (typeSelect.value === "Other") {
                                othersLabel.style.display = "block";
                                othersInput.style.display = "block";
                            } else {
                                othersLabel.style.display = "none";
                                othersInput.style.display = "none";
                            }
                        }
                    </script>

                </div>

                <div id="settings-section" class="section">
                    <div class="logout_sec" style="padding: 50px; background-color: #ffffff; border-radius: 10px;  width: 50%; margin-left: auto; margin-right: auto; margin-top:55px;  ">
                        <h1 style="text-align: center; font-family: 'Namdhinggo'; font-size: 40px;">Are you sure ?</h1>
                        <div style="text-align: center; margin-top: 25px;">
                            <a href="..\deconnexion.php"><button class="logout_btn" > Yes</button> </a>
                            <a href="..\client_dashboard\client_dash.php" ><button class="logout_btn"> No </button>
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
                                    border: 1px solid #050505; /* Couleur de bordure plus claire */
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

    </body>
</html>

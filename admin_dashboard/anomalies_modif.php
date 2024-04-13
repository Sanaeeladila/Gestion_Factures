<?php
    session_start();
    include_once "../config/db_config.php"; 

    if(isset($_POST['submit'])) {
        // Récupération de la nouvelle consommation soumise par le fournisseur
        $new_consumption = mysqli_real_escape_string($conn, $_POST['new_consumption']);
    
        // Récupération de l'ID de la facture à modifier
        if(isset($_GET['id_facture'])) {
            $id_facture = mysqli_real_escape_string($conn, $_GET['id_facture']);
    
            // Récupération de l'ID du relevé de consommation associé à la facture
            $req = "SELECT id_releve FROM facture WHERE id_facture = $id_facture";
            $result = mysqli_query($conn, $req);
            
            if($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $id_releve = $row['id_releve'];
    
                // Mise à jour de la consommation dans la table releve_consommation_mois
                $update_consumption_query = "UPDATE releve_consommation_mois SET consommation = ? WHERE id_releve = ?";
                $stmt_update_consumption = mysqli_prepare($conn, $update_consumption_query);
    
                if($stmt_update_consumption) {
                    mysqli_stmt_bind_param($stmt_update_consumption, "ii", $new_consumption,  $id_releve);
    
                    if(mysqli_stmt_execute($stmt_update_consumption)) {
                        // Succès de la mise à jour de la consommation
    
                        // Mise à jour de la colonne old_consommation dans le relevé du mois suivant
                        // Récupération de l'ID du relevé de consommation du mois suivant
                        $next_month_releve_id = $id_releve + 1;
    
                        // Mise à jour de la colonne old_consommation dans le relevé du mois suivant
                        $update_old_consumption_query = "UPDATE releve_consommation_mois SET old_consommation = ? WHERE id_releve = ?";
                        $stmt_update_old_consumption = mysqli_prepare($conn, $update_old_consumption_query);
    
                        if($stmt_update_old_consumption) {
                            mysqli_stmt_bind_param($stmt_update_old_consumption, "ii", $new_consumption, $next_month_releve_id);
    
                            if(mysqli_stmt_execute($stmt_update_old_consumption)) {
                                // Succès de la mise à jour de old_consommation dans le relevé du mois suivant
    
                                // Calculer le montant total et le montant TTC avec la nouvelle consommation
                                $prix_total = calculerPrix($new_consumption);
                                $prix_TTC = calculerPrixTTC($prix_total);
    
                                // Mise à jour des données dans la table facture avec les nouveaux montants
                                $update_invoice_query = "UPDATE facture SET montant_HT = ?, montant_TTC = ?, `condition` = 'validee', `notif` = 1 WHERE id_facture = ?";
                                $stmt_update_invoice = mysqli_prepare($conn, $update_invoice_query);
    
                                if($stmt_update_invoice) {
                                    mysqli_stmt_bind_param($stmt_update_invoice, "ddi", $prix_total, $prix_TTC, $id_facture);
    
                                    if(mysqli_stmt_execute($stmt_update_invoice)) {
                                        // Succès de la mise à jour de la facture
                                        ?>
                                        <script>
                                            alert("La consommation a été modifiée avec succès !");
                                            window.location = "dash.php";
                                        </script>
                                        <?php
                                    } else {
                                        // Erreur lors de la mise à jour de la facture
                                        echo "Erreur: " . mysqli_error($conn);
                                    }
                                } else {
                                    // Erreur de préparation de la requête de mise à jour de la facture
                                    echo "Erreur: " . mysqli_error($conn);
                                }
                            } else {
                                // Erreur lors de la mise à jour de old_consommation dans le relevé du mois suivant
                                echo "Erreur: " . mysqli_error($conn);
                            }
                        } else {
                            // Erreur de préparation de la requête de mise à jour de old_consommation dans le relevé du mois suivant
                            echo "Erreur: " . mysqli_error($conn);
                        }
                    } else {
                        // Erreur lors de la mise à jour de la consommation
                        echo "Erreur: " . mysqli_error($conn);
                    }
                } else {
                    // Erreur de préparation de la requête de mise à jour de la consommation
                    echo "Erreur: " . mysqli_error($conn);
                }
            } else {
                // Aucun résultat trouvé pour l'ID de relevé de consommation associé à la facture
                echo "Aucun relevé de consommation associé trouvé pour cette facture.";
            }
        } else {
            // L'ID de la facture n'est pas défini dans la requête GET
            echo "L'ID de la facture n'est pas défini.";
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
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifier la consommation</title>
        <style>
            h2{
                text-align: center;
                margin-bottom: 40px;
            }
            div {
                width: 40%;
                margin: 0 auto;
                padding: 20px;
                border: 2px solid #ccc;
                border-radius: 9px;
                box-sizing: border-box;
                margin-top: 10%;
            }
            label {
                display: block;
                margin-bottom: 10px;
                color: #050505;
                font-size: 16px;
            }
            select, input[type="text"] {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ccc;
                border-radius: 5px;
                box-sizing: border-box;
                font-size: 16px;
                background-color: #E8E8E8;
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
            p{
                text-align: center;
                margin-top: 20px;
                color: #9B9B9B;
                font-size: 16px;
            }
            p:hover{
                color: #050505;
                text-decoration: underline;
            }
            a{
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div>
            <h2>Modify client consumption</h2>
            <form method="post">
                <label for="new_consumption">New consumption (kWh):</label>
                <input type="text" name="new_consumption" id="new_consumption" required>
                <button type="submit" name="submit" class="invoice_btn">Submit</button>
                <a href="dash.php"><p>Cancel</p></a>
            </form>
        </div>
    </body>
</html>

<?php
    session_start();
    require_once '..\vendor\autoload.php'; 
    use Dompdf\Dompdf;

    // Fonction de génération de facture
    function generateInvoice($conn, $id_client, $month_invoice, $year_invoice) {
        $req = "SELECT * FROM releve_consommation_mois R INNER JOIN facture F ON R.id_releve = F.id_releve WHERE R.id_client = $id_client AND R.mois = $month_invoice AND R.annee = $year_invoice";
        $result = mysqli_query($conn, $req);

        if ($result) {
            // Traiter les données
            while ($row = mysqli_fetch_assoc($result)) {
                $consumption = $row['consommation'];
                $oldConsumption = $row['old_consommation'];
                $maxDifference = 500;

                // Vérifier si la différence de consommation dépasse la valeur maximale autorisée ou si la facture est validée
                if (($consumption - $oldConsumption <= $maxDifference) || ($row['condition'] == 'validee') || $oldConsumption == 0) {
                    // Générer le PDF de la facture
                    generatePDF($conn, $id_client, $month_invoice, $year_invoice);
                    // Mettre à jour la facture dans la base de données pour indiquer qu'elle a été notifiée
                    $update_invoice_query = "UPDATE facture SET `notif` = 0, `condition` = 'validee' WHERE id_facture = ?";
                    $stmt_update_invoice = mysqli_prepare($conn, $update_invoice_query);
                    mysqli_stmt_bind_param($stmt_update_invoice, "i", $row['id_facture']);
                    mysqli_stmt_execute($stmt_update_invoice);
                    
                } else {
                    // Si la facture n'est pas validée, afficher un message d'erreur
                    echo '<div class="alert" style="
                        background-color: #ffffff;
                        color: #050505;
                        padding: 15px;
                        margin: 15% auto;
                        width: 25%;
                        text-align: center;
                        border-radius: 15px;
                        border: 1px solid #050505;
                        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">';
                    echo '<h2>alert</h2>';
                    echo  '<p>The invoice is currently being processed</p>';
                    echo '<a href="client_dash.php"><button style="
                        background-color: #050505;
                        color: white;
                        padding: 10px 20px;
                        border: none;
                        cursor: pointer;
                        margin-top: 20px;
                        border-radius: 9px;
                        ">Okay</button></a>';
                    echo '</div>';
                }
            }
        } else {
            echo "Erreur: " . mysqli_error($conn);
        }
    }

    function generatePDF($conn, $id_client, $month_invoice, $year_invoice) {
        $dompdf = new Dompdf();
        $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Facture</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    bachground-color: #DBDBDB;
                }
                .amendis {
                    color: #0c4563;
                    font-family: "Namdhinggo", serif;
                    font-size: 25px;
                }
                h1 {
                    font-size: 26px;
                    text-align: center;
                    margin-top: 30px;
                    margin-bottom: 30px;
                }
                h5{
                    text-align: center;
                }
                .client-info {
                    margin-top: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                
                th, td {
                    border: 1px solid #000;
                    padding: 10px;
                    text-align: center;
                }
                
                th {
                    background-color: #f2f2f2; /* Couleur de fond pour les en-têtes */
                }
                
                td {
                    background-color: #ffffff; /* Couleur de fond pour les cellules */
                }
                
                .amendis {
                    color: #0c4563;
                    font-family: "Namdhinggo", serif;
                    font-size: 29px;
                    margin-right: 10px;
                }
            </style>
        </head>
        <body>
            <p class="amendis">Amendis</p>
            <h1>FACTURE D\'ÉLECTRICITÉ</h1>
            <p>----------------------------------------------------------------------------------------------------------------------------</p>
            <h5>INFORMATIONS DU CLIENT</h5>
            <div class="client-info">';
        
                // Ajout des informations du client à la variable $html
                $id_client = $_SESSION['id_client'];
                $req1 = "SELECT * FROM client WHERE id_client = $id_client";
                $getInf = mysqli_query($conn, $req1);
                if(mysqli_num_rows($getInf) > 0){
                    while($row = mysqli_fetch_assoc($getInf)){
                        $html .= "<p><strong>Client :</strong> " . $row['nom_client'] . " " . $row['prenom_client'] . "</p>";
                        $html .= "<p><strong>CIN :</strong> " . $row['CIN'] . "</p>";
                        $html .= "<p><strong>Adresse :</strong> " . $row['adresse'] . "</p>";
                    }
                }
            $html .= '</div><br>
            <p>----------------------------------------------------------------------------------------------------------------------------</p>
            <h5>CONSOMMATION ET FRAIS</h5>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Consommation</th>
                    <th>Montant HT</th>
                    <th>Montant TTC</th>
                </tr>';

            $req2 = "SELECT * FROM facture F INNER JOIN releve_consommation_mois R ON F.id_releve = R.id_releve WHERE R.id_client = $id_client and R.mois = $month_invoice and R.annee = $year_invoice";
            $getInf = mysqli_query($conn, $req2);
            if(mysqli_num_rows($getInf) > 0){
                while($row = mysqli_fetch_assoc($getInf)){
                    $html .= "<tr>";
                    $html .= "<td>" . $row['mois'] . "/" . $row['annee'] . "</td>";
                    $html .= "<td>" . $row['consommation'] . " kWh</td>";
                    $html .= "<td>" . $row['montant_HT'] . " DH</td>";
                    $html .= "<td>" . $row['montant_TTC'] . " DH</td>";
                    $html .= "</tr>";
                }
            }

        $html .= '</table>
        <p>----------------------------------------------------------------------------------------------------------------------------</p>
        <h5>MODES DE PAIEMENT ACCEPTÉS</h5>
        <p> - Espèces <br>
            - Carte de crédit <br>
            - Virement bancaire <br> <br> <br>
        
            Merci de régler la somme due dans les 30 jours suivant la réception de cette facture. En cas de questions ou de contestations, veuillez contacter notre service clientèle. <br>
            <br>
            Merci pour votre confiance.
        </p>
        </body></html>';

        // Charger le HTML dans Dompdf et générer le PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Nom du fichier PDF à télécharger
        $file_name = 'facture.pdf';
        
        // Télécharger le PDF avec un nom de fichier spécifique
        $dompdf->stream($file_name);
    }

    // Vérifier si le formulaire est soumis
    if(isset($_POST['submit'])) {
        include_once "..\config\db_config.php";
        $id_client = $_SESSION['id_client'];
        $month_invoice = $_POST['month_invoice'];
        $year_invoice = $_POST['year_invoice'];

        // Générer la facture
        generateInvoice($conn, $id_client, $month_invoice, $year_invoice);
    }
?>

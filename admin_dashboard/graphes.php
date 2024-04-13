<?php 

    // Récupérer les données depuis la base de données
    $id_admin = $_SESSION['id_admin'];
    $req = "SELECT SUM(consommation) AS total_consommation, mois
            FROM releve_consommation_mois
            WHERE id_admin = $id_admin
            GROUP BY mois";

    $result = mysqli_query($conn, $req);

    // Initialize arrays to store consumption data and month labels
    $consommations = [];
    $mois = [];

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            $consommations[] = $row['total_consommation'];
            $mois[] = moisEnLettres($row['mois']);
        }
    }

    function moisEnLettres($mois) {
        switch ($mois) {
            case 1:  return 'January';
            case 2:  return 'February';
            case 3:  return 'March';
            case 4:  return 'April';
            case 5:  return 'May';
            case 6:  return 'June';
            case 7:  return 'July';
            case 8:  return 'August';
            case 9:  return 'September';
            case 10: return 'October';
            case 11: return 'November';
            case 12: return 'December';
            default: return '';
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Graphique de consommation</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <canvas id="myChart" width="400" height="300"></canvas>

        <script>
            // Utiliser les données de consommation et les étiquettes des mois récupérées depuis PHP
            const consommations = <?php echo json_encode($consommations); ?>;
            const mois = <?php echo json_encode($mois); ?>;

            // Créer un graphique à barres
            const ctx = document.getElementById('myChart').getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: mois,
                    datasets: [{
                        label: 'Consumption',
                        data: consommations,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    responsive: false,
                    maintainAspectRatio: false
                }
            });
        </script>
    </body>
</html>

<?php

    $id_admin = $_SESSION['id_admin'];
    $req = "SELECT SUM(consommationAnnuelle) AS total_consommation, annee
            FROM file_consommationannuelle
            WHERE id_admin = $id_admin
            GROUP BY annee";
    $result = mysqli_query($conn, $req);

    // Initialize arrays to store consumption data and year labels
    $consommations = [];
    $year = [];

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            $consommations2[] = $row['total_consommation'];
            $year[] = $row['annee'];
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Consumption Graph</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <canvas id="myChart2" width="370" height="270"></canvas>

        <script>
            // Retrieve data from PHP
            const consommations2 = <?php echo json_encode($consommations2); ?>;
            const year = <?php echo json_encode($year); ?>;

            // Create a line chart
            const ctx2 = document.getElementById('myChart2').getContext('2d');
            const myChart2 = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: year,
                    datasets: [{
                        label: 'Annuel consumption',
                        data: consommations2,
                        backgroundColor: '#FFCE26',
                        borderColor: '#050505',
                        borderWidth: 1,
                        pointRadius: 5
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

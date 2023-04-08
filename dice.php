<!DOCTYPE html>
<html>
<head>
  <title>Dice Throw</title>
  <!-- Include Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
  <!-- Include DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="style.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
<h1 class="text-center mt-4">Dice Throw</h1>
<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-4 text-center">
      <?php
      if(isset($_POST['throw_dice'])) {
        // Generate random dice values
        $dice1 = rand(1, 6);
        $dice2 = rand(1, 6);

        $total = $dice1+$dice2;
        
        // Connect to database (Replace with your own database credentials)
        $conn = mysqli_connect("localhost", "root", "", "dice_game");
        if(!$conn) {
          die("Connection failed: " . mysqli_connect_error());
        }

        // Create table if it doesn't exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS dice_results (
                                                                      id INT AUTO_INCREMENT PRIMARY KEY,
                                                                      dice1 INT NOT NULL,
                                                                      dice2 INT NOT NULL,
                                                                      total INT NOT NULL,
                                                                      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                                                    )";
        mysqli_query($conn, $createTableQuery);

        // Insert dice values into database
        $sql = "INSERT INTO dice_results (dice1, dice2, total) VALUES ('$dice1', '$dice2', '$total')";
        if(mysqli_query($conn, $sql)) {

          echo "<div class='mt-12'>";
          echo "<p><img src='dice".$dice1.".png' alt='Dice 1' width='80'></p>";
          echo "<p><img src='dice".$dice2.".png' alt='Dice 2' width='80'></p>";
          echo "<p>Total: <strong>" . $total . "</strong></p>";
          echo "</div>";
          

        } else {
          echo "<p class='text-danger mt-4'>Error: " . $sql . "<br>" . mysqli_error($conn) . "</p>";
        }

        mysqli_close($conn);
      }
      ?>
      <form method="post" action="" class="text-center">
        <input type="submit" name="throw_dice" value="Throw Dice" class="btn btn-primary mt-3">
      </form>
      <br>
      <br>
    </div>
  </div>
</div>
<div style="width: 100%; height: 20%;">
    <canvas id="chart"></canvas>
  </div>
  <!-- Display results using DataTables -->
  <h2>Results</h2>
  <table id="diceTable" class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Dice 1</th>
        <th>Dice 2</th>
        <th>total</th>
        <th>created at</th>

      </tr>
    </thead>
    <tbody>
      <?php
      // Retrieve data from the database
      $conn = mysqli_connect("localhost", "root", "", "dice_game");
      if(!$conn) {
        die("Connection failed: " . mysqli_connect_error());
      }

      $sql = "SELECT * FROM dice_results ORDER BY created_at DESC";
      $result = mysqli_query($conn, $sql);

      while($row = mysqli_fetch_assoc($result)) {
        
        echo "<td>" . $row['dice1'] . "</td>";
        echo "<td>" . $row['dice2'] . "</td>";
        echo "<td>" . $row['total'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
      }
      

      $sql = "SELECT * FROM dice_results";
        $result = mysqli_query($conn, $sql);

        // Calculate and prepare data for the chart
        $sql_occurrences = "SELECT total, COUNT(*) as total_occurrences FROM dice_results GROUP BY total";
        $result_occurrences = mysqli_query($conn, $sql_occurrences);
        $chart_labels = [];
        $chart_data = [];
        while($row_occurrences = mysqli_fetch_assoc($result_occurrences)) {
          $chart_labels[] = $row_occurrences['total'];
          $chart_data[] = $row_occurrences['total_occurrences'];
        }


        

      mysqli_close($conn);
      ?>
    </tbody>
  </table>
</div>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <form method="post" action="" class="text-center">
        <input type="submit" name="delete_all" value="Delete All" class="btn btn-danger mt-3">
      </form>
      <?php
      if(isset($_POST['delete_all'])) {
        $conn = mysqli_connect("localhost", "root", "", "dice_game");
        if(!$conn) {
          die("Connection failed: " . mysqli_connect_error());
          }
          $sql_delete = "DELETE FROM dice_results";
        $result_delete = mysqli_query($conn, $sql_delete);
        if ($result_delete) {
          echo "<p class='row justify-content-center'>Table emptied successfully!</p>";
        } else {
          echo "<p>Error emptying table: " . mysqli_error($conn) . "</p>";
        }
        mysqli_close($conn);
      }
      ?>
    </div>
  </div>
</div>


  <script>
    // Chart.js configuration
    var ctx = document.getElementById('chart').getContext('2d');
    var chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
          label: 'Occurrences',
          data: <?php echo json_encode($chart_data); ?>,
          backgroundColor: 'rgba(255, 159, 64, 0.2)',
          borderColor: 'rgba(255, 159, 64, 1)',
          borderWidth: 2
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            stepSize: 1
          }
        }
      }
    });
  </script>

<!-- Include jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
  // Initialize DataTables
  $('#diceTable').DataTable({
    order: [[3, 'desc']],
  });
});
</script>

</body>
</html>


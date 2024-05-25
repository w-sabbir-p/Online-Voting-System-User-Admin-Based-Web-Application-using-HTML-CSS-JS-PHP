<?php 
    include('config.php'); // Include your database connection file

    $election_id = isset($_GET['viewResult']) ? $_GET['viewResult'] : null;

    if ($election_id) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results</title>
    <link rel="stylesheet" href="path/to/your/css/file.css"> <!-- Adjust the path to your CSS file -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container">
    <div class="row my-3">
        <div class="col-12">
            <h3> Election Results </h3>

            <?php 
                $fetchingActiveElections = mysqli_query($db, "SELECT * FROM elections WHERE id = '". $election_id ."'") or die(mysqli_error($db));
                $totalActiveElections = mysqli_num_rows($fetchingActiveElections);

                if($totalActiveElections > 0) {
                    while($data = mysqli_fetch_assoc($fetchingActiveElections)) {
                        $election_id = $data['id'];
                        $election_topic = $data['election_topic'];    
            ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th colspan="4" class="bg-green text-white"><h5> ELECTION TOPIC: <?php echo strtoupper($election_topic); ?></h5></th>
                                </tr>
                                <tr>
                                    <th> Photo </th>
                                    <th> Candidate Details </th>
                                    <th> # of Votes </th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                                $candidateNames = [];
                                $candidateVotes = [];
                                $fetchingCandidates = mysqli_query($db, "SELECT * FROM candidate_details WHERE election_id = '". $election_id ."'") or die(mysqli_error($db));
                                $maxVotes = 0;
                                $winnerCandidate = '';

                                while($candidateData = mysqli_fetch_assoc($fetchingCandidates)) {
                                    $candidate_id = $candidateData['id'];
                                    $candidate_photo = $candidateData['candidate_photo'];

                                    // Fetching Candidate Votes 
                                    $fetchingVotes = mysqli_query($db, "SELECT * FROM votings WHERE candidate_id = '". $candidate_id . "'") or die(mysqli_error($db));
                                    $totalVotes = mysqli_num_rows($fetchingVotes);

                                    $candidateNames[] = $candidateData['candidate_name'] . ' (' . $totalVotes . ' votes)';
                                    $candidateVotes[] = $totalVotes;

                                    if ($totalVotes > $maxVotes) {
                                        $maxVotes = $totalVotes;
                                        $winnerCandidate = $candidateData['candidate_name'];
                                    }
                            ?>
                                    <tr>
                                        <td> <img src="<?php echo $candidate_photo; ?>" class="candidate_photo"> </td>
                                        <td><?php echo "<b>" . $candidateData['candidate_name'] . "</b><br />" . $candidateData['candidate_details']; ?></td>
                                        <td><?php echo $totalVotes; ?></td>
                                    </tr>
                            <?php
                                }
                            ?>
                            </tbody>
                        </table>

                        <!-- Winner Box -->
                        <div class="alert alert-success my-3">
                            <h4>Winner: <?php echo $winnerCandidate; ?> with <?php echo $maxVotes; ?> votes!</h4>
                        </div>

                        <!-- Pie Chart Canvas -->
                        <canvas id="voteChart"></canvas>
                        <script>
                            var ctx = document.getElementById('voteChart').getContext('2d');
                            var voteChart = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: <?php echo json_encode($candidateNames); ?>,
                                    datasets: [{
                                        data: <?php echo json_encode($candidateVotes); ?>,
                                        backgroundColor: [
                                            'rgba(255, 99, 132, 0.2)',
                                            'rgba(54, 162, 235, 0.2)',
                                            'rgba(255, 206, 86, 0.2)',
                                            'rgba(75, 192, 192, 0.2)',
                                            'rgba(153, 102, 255, 0.2)',
                                            'rgba(255, 159, 64, 0.2)'
                                        ],
                                        borderColor: [
                                            'rgba(255, 99, 132, 1)',
                                            'rgba(54, 162, 235, 1)',
                                            'rgba(255, 206, 86, 1)',
                                            'rgba(75, 192, 192, 1)',
                                            'rgba(153, 102, 255, 1)',
                                            'rgba(255, 159, 64, 1)'
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: false,
                                    plugins: {
                                        legend: {
                                            position: 'top',
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(tooltipItem) {
                                                    var label = tooltipItem.label || '';
                                                    if (label) {
                                                        label += ': ';
                                                    }
                                                    label += tooltipItem.raw + ' votes';
                                                    return label;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        </script>
            <?php
                    }
                } else {
                    echo "No active election found.";
                }
            ?>

            <hr>
            <h3>Voting Details</h3>
            <?php 
                $fetchingVoteDetails = mysqli_query($db, "SELECT * FROM votings WHERE election_id = '". $election_id ."'");
                $number_of_votes = mysqli_num_rows($fetchingVoteDetails);

                if($number_of_votes > 0) {
                    $sno = 1;
            ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Voter Name</th>
                                <th>Contact No</th>
                                <th>Voted To</th>
                                <th>Date</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
            <?php
                    while($data = mysqli_fetch_assoc($fetchingVoteDetails)) {
                        $voters_id = $data['voters_id'];
                        $candidate_id = $data['candidate_id'];
                        $fetchingUsername = mysqli_query($db, "SELECT * FROM users WHERE id = '". $voters_id ."'") or die(mysqli_error($db));
                        $isDataAvailable = mysqli_num_rows($fetchingUsername);
                        $userData = mysqli_fetch_assoc($fetchingUsername);
                        if($isDataAvailable > 0) {
                            $username = $userData['username'];
                            $contact_no = $userData['contact_no'];
                        } else {
                            $username = "No_Data";
                            $contact_no = "No_Data";
                        }

                        $fetchingCandidateName = mysqli_query($db, "SELECT * FROM candidate_details WHERE id = '". $candidate_id ."'") or die(mysqli_error($db));
                        $isDataAvailable = mysqli_num_rows($fetchingCandidateName);
                        $candidateData = mysqli_fetch_assoc($fetchingCandidateName);
                        if($isDataAvailable > 0) {
                            $candidate_name = $candidateData['candidate_name'];
                        } else {
                            $candidate_name = "No_Data";
                        }
            ?>
                            <tr>
                                <td><?php echo $sno++; ?></td>
                                <td><?php echo $username; ?></td>
                                <td><?php echo $contact_no; ?></td>
                                <td><?php echo $candidate_name; ?></td>
                                <td><?php echo $data['vote_date']; ?></td>
                                <td><?php echo $data['vote_time']; ?></td>
                            </tr>
            <?php
                    }
                    echo "</tbody></table>";
                } else {
                    echo "No vote details available!";
                }
            ?>
        </div>
    </div>
</div>
</body>
</html>
<?php 
    } else {
        echo "No election ID provided.";
    }
?>

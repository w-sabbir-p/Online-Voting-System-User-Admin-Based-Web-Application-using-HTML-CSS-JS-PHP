<?php 
    include_once 'config.php'; // Include database connection

    if(isset($_GET['added'])) {
?>
        <div class="alert alert-success my-3" role="alert">
            Candidate has been added successfully.
        </div>
<?php 
    } else if(isset($_GET['largeFile'])) {
?>
        <div class="alert alert-danger my-3" role="alert">
            Candidate image is too large, please upload a smaller file (you can upload any image up to 2 MB).
        </div>
<?php
    } else if(isset($_GET['invalidFile'])) {
?>
        <div class="alert alert-danger my-3" role="alert">
            Invalid image type (Only .jpg, .png files are allowed).
        </div>
<?php
    } else if(isset($_GET['failed'])) {
?>
        <div class="alert alert-danger my-3" role="alert">
            Image uploading failed, please try again.
        </div>
<?php
    } else if(isset($_GET['delete_id'])) {
        $d_id = $_GET['delete_id'];
        mysqli_query($db, "DELETE FROM candidate_details WHERE id = '". $d_id ."'") OR die(mysqli_error($db));
?>
        <div class="alert alert-danger my-3" role="alert">
            Candidate has been deleted successfully!
        </div>
<?php
    } else if(isset($_GET['edit_id'])) {
        $e_id = $_GET['edit_id'];
        $fetchCandidate = mysqli_query($db, "SELECT * FROM candidate_details WHERE id = '". $e_id ."'") or die(mysqli_error($db));
        $candidateData = mysqli_fetch_assoc($fetchCandidate);
    }

    if(isset($_POST['addCandidateBtn'])) {
        $election_id = $_POST['election_id'];
        $candidate_name = $_POST['candidate_name'];
        $candidate_details = $_POST['candidate_details'];
        $inserted_by = 'Admin'; // You can replace 'Admin' with the actual user's name or ID
        $inserted_on = date('Y-m-d');

        // Handle file upload
        if ($_FILES['candidate_photo']['size'] > 0) {
            $allowed_types = ['image/jpeg', 'image/png'];
            if (in_array($_FILES['candidate_photo']['type'], $allowed_types)) {
                $target_dir = "../assets/images/candidate_photos/";
                $target_file = $target_dir . basename($_FILES["candidate_photo"]["name"]);

                if ($_FILES["candidate_photo"]["size"] <= 2000000) {
                    if (move_uploaded_file($_FILES["candidate_photo"]["tmp_name"], $target_file)) {
                        $candidate_photo = $target_file;
                        mysqli_query($db, "INSERT INTO candidate_details (election_id, candidate_name, candidate_details, candidate_photo, inserted_by, inserted_on) VALUES ('$election_id', '$candidate_name', '$candidate_details', '$candidate_photo', '$inserted_by', '$inserted_on')") or die(mysqli_error($db));
                        header('Location: index.php?addCandidatePage=1&added=1');
                        exit();
                    } else {
                        header('Location: index.php?addCandidatePage=1&failed=1');
                        exit();
                    }
                } else {
                    header('Location: index.php?addCandidatePage=1&largeFile=1');
                    exit();
                }
            } else {
                header('Location: index.php?addCandidatePage=1&invalidFile=1');
                exit();
            }
        }
    }

    if(isset($_POST['editCandidateBtn'])) {
        $election_id = $_POST['election_id'];
        $candidate_name = $_POST['candidate_name'];
        $candidate_details = $_POST['candidate_details'];
        $e_id = $_POST['edit_id'];
        $updated_by = 'Admin'; // You can replace 'Admin' with the actual user's name or ID
        $updated_on = date('Y-m-d');

        if ($_FILES['candidate_photo']['size'] > 0) {
            $allowed_types = ['image/jpeg', 'image/png'];
            if (in_array($_FILES['candidate_photo']['type'], $allowed_types)) {
                $target_dir = "../assets/images/candidate_photos/";
                $target_file = $target_dir . basename($_FILES["candidate_photo"]["name"]);

                if ($_FILES["candidate_photo"]["size"] <= 2000000) {
                    if (move_uploaded_file($_FILES["candidate_photo"]["tmp_name"], $target_file)) {
                        $candidate_photo = $target_file;
                        mysqli_query($db, "UPDATE candidate_details SET election_id='$election_id', candidate_name='$candidate_name', candidate_details='$candidate_details', candidate_photo='$candidate_photo', inserted_by='$updated_by', inserted_on='$updated_on' WHERE id='$e_id'") or die(mysqli_error($db));
                        header('Location: index.php?addCandidatePage=1&updated=1');
                        exit();
                    } else {
                        header('Location: index.php?addCandidatePage=1&failed=1');
                        exit();
                    }
                } else {
                    header('Location: index.php?addCandidatePage=1&largeFile=1');
                    exit();
                }
            } else {
                header('Location: index.php?addCandidatePage=1&invalidFile=1');
                exit();
            }
        } else {
            mysqli_query($db, "UPDATE candidate_details SET election_id='$election_id', candidate_name='$candidate_name', candidate_details='$candidate_details', inserted_by='$updated_by', inserted_on='$updated_on' WHERE id='$e_id'") or die(mysqli_error($db));
            header('Location: index.php?addCandidatePage=1&updated=1');
            exit();
        }
    }
?>

<div class="row my-3">
    <div class="col-4">
        <h3><?php echo isset($candidateData) ? 'Edit Candidate' : 'Add New Candidates'; ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <select class="form-control" name="election_id" required> 
                    <option value=""> Select Election </option>
                    <?php 
                        $fetchingElections = mysqli_query($db, "SELECT * FROM elections") OR die(mysqli_error($db));
                        $isAnyElectionAdded = mysqli_num_rows($fetchingElections);
                        if($isAnyElectionAdded > 0)
                        {
                            while($row = mysqli_fetch_assoc($fetchingElections))
                            {
                                $election_id = $row['id'];
                                $election_name = $row['election_topic'];
                                $allowed_candidates = $row['no_of_candidates'];

                                // Now checking how many candidates are added in this election 
                                $fetchingCandidate = mysqli_query($db, "SELECT * FROM candidate_details WHERE election_id = '". $election_id ."'") or die(mysqli_error($db));
                                $added_candidates = mysqli_num_rows($fetchingCandidate);

                                if($added_candidates < $allowed_candidates || isset($candidateData))
                                {
                    ?>
                                <option value="<?php echo $election_id; ?>" <?php echo (isset($candidateData) && $candidateData['election_id'] == $election_id) ? 'selected' : ''; ?>><?php echo $election_name; ?></option>
                    <?php
                                }
                            }
                        } else {
                    ?>
                            <option value=""> Please add election first </option>
                    <?php
                        }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="candidate_name" placeholder="Candidate Name" class="form-control" value="<?php echo isset($candidateData) ? $candidateData['candidate_name'] : ''; ?>" required />
            </div>
            <div class="form-group">
                <input type="file" name="candidate_photo" class="form-control" <?php echo isset($candidateData) ? '' : 'required'; ?> />
                <?php if(isset($candidateData)) { ?>
                    <img src="<?php echo $candidateData['candidate_photo']; ?>" class="candidate_photo" />
                <?php } ?>
            </div>
            <div class="form-group">
                <input type="text" name="candidate_details" placeholder="Candidate Details" class="form-control" value="<?php echo isset($candidateData) ? $candidateData['candidate_details'] : ''; ?>" required />
            </div>
            <input type="hidden" name="edit_id" value="<?php echo isset($candidateData) ? $candidateData['id'] : ''; ?>" />
            <input type="submit" value="<?php echo isset($candidateData) ? 'Update Candidate' : 'Add Candidate'; ?>" name="<?php echo isset($candidateData) ? 'editCandidateBtn' : 'addCandidateBtn'; ?>" class="btn btn-success" />
        </form>
    </div>   

    <div class="col-8">
        <h3>Candidate Details</h3>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">S.No</th>
                    <th scope="col">Photo</th>
                    <th scope="col">Name</th>
                    <th scope="col">Details</th>
                    <th scope="col">Election</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $fetchingData = mysqli_query($db, "SELECT * FROM candidate_details") or die(mysqli_error($db)); 
                    $isAnyCandidateAdded = mysqli_num_rows($fetchingData);

                    if($isAnyCandidateAdded > 0)
                    {
                        $sno = 1;
                        while($row = mysqli_fetch_assoc($fetchingData))
                        {
                            $election_id = $row['election_id'];
                            $fetchingElectionName = mysqli_query($db, "SELECT * FROM elections WHERE id = '". $election_id ."'") or die(mysqli_error($db));
                            $execFetchingElectionNameQuery = mysqli_fetch_assoc($fetchingElectionName);
                            $election_name = $execFetchingElectionNameQuery['election_topic'];

                            $candidate_photo = $row['candidate_photo'];

                ?>
                            <tr>
                                <td><?php echo $sno++; ?></td>
                                <td> <img src="<?php echo $candidate_photo; ?>" class="candidate_photo" />    </td>
                                <td><?php echo $row['candidate_name']; ?></td>
                                <td><?php echo $row['candidate_details']; ?></td>
                                <td><?php echo $election_name; ?></td>
                                <td> 
                                    <a href="#" class="btn btn-sm btn-warning" onclick="EditData(<?php echo $row['id']; ?>)"> Edit </a>
                                    <button class="btn btn-sm btn-danger" onclick="DeleteData(<?php echo $row['id']; ?>)"> Delete </button>
                                </td>
                            </tr>   
                <?php
                        }
                    } else {
                ?>
                        <tr> 
                            <td colspan="6"> No any candidate is added yet. </td>
                        </tr>
                <?php
                    }
                ?>
            </tbody>    
        </table>
    </div>
</div>

<script>
    const DeleteData = (c_id) => 
    {
        let c = confirm("Are you sure you want to delete this candidate?");

        if(c == true)
        {
            location.assign("index.php?addCandidatePage=1&delete_id=" + c_id);
        }
    }

    const EditData = (c_id) => 
    {
        location.assign("index.php?addCandidatePage=1&edit_id=" + c_id);
    }
</script>


               

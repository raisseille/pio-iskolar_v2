<?php 
    include_once('../functions/general.php'); 
    include('../functions/scholar_view.php');
    include ('../functions/password_fx.php');
    
    $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : null);

    if ($user_role == "2") {
    } elseif ($user_role == "1") {
        header("Location: ad_dashboard.php");
    } elseif ($user_role == "3") {
        header("Location: eval_dashboard.php");
    } else {
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pio Iskolar</title>
    <link rel="icon" type="image/x-icon" href="images/pio-logo.png">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="css/ad_skoDetail.css">
    <link rel="stylesheet" href="css/profile.css"> 
    <link rel="stylesheet" href="css/confirm.css">
    <style>
        .required-error {
            border: 2px solid red !important;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR - navbar.php -->
    <?php include 'navbar.php';?>
    

    <!-- TOP BAR -->
    <div class="main1">
        <div class="topBar">
            <div class="headerName">
                <h1>My Profile</h1>
            </div>

            <div class="headerRight">
                <div class="notifs">
                    <ion-icon name="notifications-outline" onclick="openOverlay()"></ion-icon>
                </div>
            </div>
        </div>

        <div class="line"></div>


        <!-- MY PROFILE-->
        <div class="details"><center> 
            <?php scholarFull(); ?>
        </center></div>

        <?php scholarView();?>

        <a href="#" onclick="openPass()"> Change Password </a>
    </div>


    <!-- CHANGE PASS -->
    <div id="passOverlay" class="passOverlay">
        <div class="pass-content">
            <div class="infos">
                <h2>Change Password</h2>
                <span class="closePass" onclick="closePass()">&times;</span>
            </div>
            <br><br>
            
            <form id="passForm" method="POST" novalidate>
                <div class="inner-content">
                    <label class="passText" for="oldPassword">Enter Current Password:</label> <br>
                    <input class="input" type="password" id="oldPassword" name="oldPassword" placeholder="Current Password" required>
                </div>

                <div class="inner-content">
                    <label class="passText" for="newPassword">Enter New Password:</label> <br>
                    <input class="input" type="password" id="newPassword" name="newPassword" placeholder="New Password" required>
                </div>

                <div class="inner-content">
                    <label class="passText" for="confirmPassword">Confirm Password:</label> <br>
                    <input class="input" type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                </div> <br>

                <div class="enter-button-container">
                    <button class="enter-button" type="submit" name="change"> Enter </button>
                </div>
            </form> <br>
        </div> 
    </div>

    <?php include 'notif.php'; ?>
    <?php include 'toast.php'; ?>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
        //CHANGE PASS
        function openPass() {
            document.getElementById("passOverlay").style.display = "block";
        }
        function closePass() {
            document.getElementById("passOverlay").style.display = "none";
        }
        document.getElementById('passForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            const form = e.target;
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;

            // Validate required fields (empty fields are already accounted for)
            requiredFields.forEach(field => {
                if (!field.value) {
                    field.classList.add('required-error');
                    valid = false;
                } else {
                    field.classList.remove('required-error');
                }
                field.addEventListener('input', function() {
                    if (field.value) {
                        field.classList.remove('required-error');
                    }
                });
            });

            if (!valid) {
                e.preventDefault(); // Prevent form submission if required fields are empty
            } else {
                e.preventDefault();

                const formData = new FormData();
                const data = new URLSearchParams();

                formData.append('change', 'true');
                formData.append('oldPassword', document.querySelector('[name="oldPassword"]').value);
                formData.append('newPassword', document.querySelector('[name="newPassword"]').value);
                formData.append('confirmPassword', document.querySelector('[name="confirmPassword"]').value);

                fetch('../functions/password_fx.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.text())
                .then(response => {
                    if (response === 'success') {
                        // Store success message in sessionStorage before reload
                        sessionStorage.setItem('toastMessage', 'Password changed successfully.');
                        sessionStorage.setItem('toastTitle', 'Success');
                        
                        // Reload the page to apply changes
                        window.location.href = '<?php echo $_SERVER["PHP_SELF"]; ?>';
                    } else if (response === 'incorrect') {
                        showToast('The old password is incorrect.', 'Error');
                    } else if (response === 'mismatch') {
                        showToast('New passwords do not match.', 'Error');
                    } else if (response === 'issing') {
                        showToast('User session not found.', 'Error');
                    } else {
                        showToast('An error occurred while changing the password.', 'Error');
                    }
                })
                .catch(() => {
                    showToast('A server error occurred.', 'Error');
                });
            }
        });
    </script>
</body>
</html>
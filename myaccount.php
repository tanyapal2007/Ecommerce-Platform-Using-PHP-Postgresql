<?php

session_start();

include "config/database.php";


// =========================================
// CHECK LOGIN
// =========================================

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


// =========================================
// GET USER DATA
// =========================================

$sql = "SELECT *
        FROM users
        WHERE user_id = :user_id
        LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ':user_id' => $user_id
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {

    session_destroy();

    header("Location: login.php");
    exit;
}


// =========================================
// GET USER PROFILE DATA
// =========================================

$profile_sql = "SELECT *
                FROM user_profile
                WHERE user_id = :user_id
                LIMIT 1";

$profile_stmt = $conn->prepare($profile_sql);

$profile_stmt->execute([
    ':user_id' => $user_id
]);

$profile = $profile_stmt->fetch(PDO::FETCH_ASSOC);


if (!$profile) {

    $profile = [];
}


// =========================================
// PROFILE VALUES
// =========================================

$profile_image = !empty($profile['profile_image'])
    ? $profile['profile_image']
    : "assets/images/default-user.png";


$first_name = $profile['first_name'] ?? $user['name'];

$last_name = $profile['last_name'] ?? "";

$gender = $profile['gender'] ?? "Not Added";

$date_of_birth = $profile['date_of_birth'] ?? "Not Added";

$phone = $user['phone'] ?? "Not Added";

$role = $user['role'] ?? "customer";

$status = ($user['status'] ?? 1) == 1
    ? "Active"
    : "Inactive";

$created_at = $user['created_at'] ?? "";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>My Account</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <style>
        body {
            background: #f5f6f8;
            font-family: Arial, sans-serif;
        }

        .account-wrapper {
            padding: 50px 0;
        }

        .account-sidebar {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f1f1f1;
        }

        .profile-name {
            font-size: 20px;
            font-weight: 600;
            margin-top: 15px;
        }

        .profile-role {
            color: #777;
            font-size: 14px;
        }

        .account-menu {
            margin-top: 25px;
        }

        .account-menu a {
            display: block;
            padding: 12px 15px;
            margin-bottom: 6px;
            text-decoration: none;
            color: #333;
            border-radius: 8px;
        }

        .account-menu a:hover,
        .account-menu a.active {
            background: #212529;
            color: white;
        }

        .account-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        }

        .account-title {
            font-size: 28px;
            font-weight: 600;
        }

        .info-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            height: 100%;
        }

        .info-label {
            font-size: 13px;
            color: #777;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 500;
        }

        .status-active {
            color: #198754;
            font-weight: 600;
        }

        .account-box {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
        }

        .account-box h5 {
            font-weight: 600;
        }

        @media (max-width: 991px) {

            .account-sidebar {
                margin-bottom: 25px;
            }

        }
    </style>

</head>


<body>


    <div class="container account-wrapper">

        <div class="row g-4">


            <!-- =========================================
             SIDEBAR
        ========================================== -->

            <div class="col-lg-3">

                <div class="account-sidebar text-center">


                    <!-- Profile Image -->

                    <img
                        src="<?php echo htmlspecialchars($profile_image); ?>"
                        class="profile-image"
                        alt="Profile">


                    <!-- Name -->

                    <div class="profile-name">

                        <?php
                        echo htmlspecialchars(
                            $first_name . " " . $last_name
                        );
                        ?>

                    </div>


                    <!-- Role -->

                    <div class="profile-role">

                        <?php
                        echo ucfirst(
                            htmlspecialchars($role)
                        );
                        ?>

                    </div>


                    <!-- Menu -->

                    <div class="account-menu text-start">


                        <a href="my_account.php"
                            class="active">

                            My Account

                        </a>


                        <a href="edit_profile.php">

                            Edit Profile

                        </a>


                        <a href="orders.php">

                            My Orders

                        </a>


                        <a href="wishlist.php">

                            My Wishlist

                        </a>


                        <a href="cart.php">

                            My Cart

                        </a>


                        <a href="addresses.php">

                            My Addresses

                        </a>


                        <a href="logout.php"
                            class="text-danger">

                            Logout

                        </a>


                    </div>

                </div>

            </div>



            <!-- =========================================
             MAIN CONTENT
        ========================================== -->

            <div class="col-lg-9">


                <div class="account-content">


                    <!-- Header -->

                    <div class="d-flex
                            justify-content-between
                            align-items-center
                            flex-wrap
                            gap-2
                            mb-4">

                        <div>

                            <h2 class="account-title mb-1">

                                My Account

                            </h2>

                            <p class="text-muted mb-0">

                                Manage your account and personal information.

                            </p>

                        </div>


                        <a href="edit_profile.php"
                            class="btn btn-dark">

                            Edit Profile

                        </a>

                    </div>



                    <!-- =================================
                     PERSONAL INFORMATION
                ================================== -->

                    <h5 class="mb-3">

                        Personal Information

                    </h5>


                    <div class="row g-3 mb-4">


                        <!-- First Name -->

                        <div class="col-md-6">

                            <div class="info-card">

                                <div class="info-label">

                                    First Name

                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars(
                                        $first_name
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>



                        <!-- Last Name -->

                        <div class="col-md-6">

                            <div class="info-card">

                                <div class="info-label">

                                    Last Name

                                </div>

                                <div class="info-value">

                                    <?php

                                    echo !empty($last_name)
                                        ? htmlspecialchars($last_name)
                                        : "Not Added";

                                    ?>

                                </div>

                            </div>

                        </div>



                        <!-- Gender -->

                        <div class="col-md-6">

                            <div class="info-card">

                                <div class="info-label">

                                    Gender

                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars($gender);
                                    ?>

                                </div>

                            </div>

                        </div>



                        <!-- Date of Birth -->

                        <div class="col-md-6">

                            <div class="info-card">

                                <div class="info-label">

                                    Date of Birth

                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars(
                                        $date_of_birth
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>


                    </div>



                    <!-- =================================
                     CONTACT INFORMATION
                ================================== -->

                    <h5 class="mb-3">

                        Contact Information

                    </h5>


                    <div class="row g-3 mb-4">


                        <!-- Username -->

                        <div class="col-md-6">

                            <div class="info-card">

                                <div class="info-label">

                                    Username

                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars(
                                        $user['name']
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>



                        <!-- Phone -->

                        <div class="col-md-6">

                            <div class="info-card">

                                <div class="info-label">

                                    Contact Number

                                </div>

                                <div class="info-value">

                                    <?php
                                    echo htmlspecialchars($phone);
                                    ?>

                                </div>

                            </div>

                        </div>


                    </div>



                    <!-- =================================
                     ACCOUNT INFORMATION
                ================================== -->

                    <h5 class="mb-3">

                        Account Information

                    </h5>


                    <div class="row g-3">


                        <!-- Role -->

                        <div class="col-md-4">

                            <div class="info-card">

                                <div class="info-label">

                                    Account Type

                                </div>

                                <div class="info-value">

                                    <?php
                                    echo ucfirst(
                                        htmlspecialchars($role)
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>



                        <!-- Status -->

                        <div class="col-md-4">

                            <div class="info-card">

                                <div class="info-label">

                                    Account Status

                                </div>

                                <div class="info-value status-active">

                                    <?php
                                    echo $status;
                                    ?>

                                </div>

                            </div>

                        </div>



                        <!-- Created -->

                        <div class="col-md-4">

                            <div class="info-card">

                                <div class="info-label">

                                    Member Since

                                </div>

                                <div class="info-value">

                                    <?php

                                    if (!empty($created_at)) {

                                        echo date(
                                            "d M Y",
                                            strtotime($created_at)
                                        );
                                    } else {

                                        echo "Not Available";
                                    }

                                    ?>

                                </div>

                            </div>

                        </div>


                    </div>


                </div>


            </div>

        </div>

    </div>


</body>

</html>
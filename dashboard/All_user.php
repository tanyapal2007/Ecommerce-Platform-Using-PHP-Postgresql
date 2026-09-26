<?php

session_start();

include "../config/database.php";


// ======================================================
// 1. CHECK LOGIN
// ======================================================

if (!isset($_SESSION['user_id'])) {

    die("SESSION USER ID NOT FOUND<br><br>
         Available Session Data:<br><pre>" .
        htmlspecialchars(print_r($_SESSION, true)) .
        "</pre>");
}

$login_user_id = $_SESSION['user_id'];

echo "Session User ID = " . htmlspecialchars($login_user_id);

// ======================================================
// 2. CHECK ADMIN
// ======================================================
$admin_check_sql = "SELECT
                        user_id,
                        name,
                        phone,
                        role,
                        status,
                        created_at
                    FROM users
                    WHERE user_id = :user_id
                    LIMIT 1";

$admin_check_stmt = $conn->prepare($admin_check_sql);

$admin_check_stmt->execute([
    'user_id' => $login_user_id
]);

$admin = $admin_check_stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || $admin['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}


// ======================================================
// 3. GET ADMIN PROFILE
// ======================================================

$admin_profile_sql = "SELECT
                        profile_image,
                        gender,
                        date_of_birth,
                        bio
                      FROM user_profile
                      WHERE user_id = :user_id
                      LIMIT 1";

$admin_profile_stmt = $conn->prepare($admin_profile_sql);

$admin_profile_stmt->execute([
    'user_id' => $login_user_id
]);

$admin_profile = $admin_profile_stmt->fetch(PDO::FETCH_ASSOC);


// If no profile found
if (!$admin_profile) {

    $admin_profile = null;
}


// ======================================================
// 4. LOGIN DATE & TIME
// ======================================================

// Login time is stored in session after OTP verification

if (isset($_SESSION['login_time'])) {

    $login_date_time = $_SESSION['login_time'];
} else {

    $login_date_time = date("Y-m-d H:i:s");
}


// ======================================================
// 5. TOTAL CUSTOMERS
// ======================================================

$count_sql = "SELECT COUNT(*) AS total_users
              FROM users
              WHERE role != 'admin'";

$count_stmt = $conn->prepare($count_sql);

$count_stmt->execute();

$count_data = $count_stmt->fetch(PDO::FETCH_ASSOC);

$total_users = $count_data['total_users'] ?? 0;


// ======================================================
// 6. GET ALL USERS EXCEPT ADMIN
// ======================================================
$users_sql = "
    SELECT
        u.user_id,
        u.name,
        u.phone,
        u.role,
        u.status,
        u.created_at,
        up.profile_image,
        up.gender,
        up.date_of_birth
    FROM users u
    LEFT JOIN user_profile up
        ON u.user_id = up.user_id
    WHERE u.role != 'admin'
    ORDER BY u.user_id DESC
";

$users_stmt = $conn->prepare($users_sql);

$users_stmt->execute();

$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// ======================================================
// 7. ADMIN PROFILE IMAGE
// ======================================================

if (
    $admin_profile &&
    !empty($admin_profile['profile_image'])
) {

    $admin_image =
        "../" .
        $admin_profile['profile_image'];
} else {

    $admin_image =
        "../assets/images/default-user.png";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Gentelella Alela! | </title>

    <!-- Bootstrap -->
    <link href="assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="assets/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="assets/vendors/iCheck/skins/flat/green.css" rel="stylesheet">

    <!-- bootstrap-progressbar -->
    <link href="assets/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="assets/vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet" />
    <!-- bootstrap-daterangepicker -->
    <link href="assets/vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="assets/css/custom.min.css" rel="stylesheet">
    <link href="assets/css/custom-popup.css" rel="stylesheet">
    <!-- All User Page CSS -->
    <link href="assets/css/all_user.css" rel="stylesheet">
</head>

<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <?php include 'sidebar.php'; ?>

            <div class="right_col" role="main">

                <div class="content">


                    <!-- PAGE TITLE -->

                    <div class="page-title-section">

                        <h2>
                            All Users
                        </h2>

                        <p>
                            Manage all registered customers.
                        </p>

                    </div>



                    <!-- =================================================
             ADMIN INFORMATION
        ================================================== -->

                    <div class="admin-card">


                        <div class="row align-items-center">


                            <!-- PROFILE -->

                            <div class="col-lg-5 col-md-6 mb-3 mb-md-0">


                                <div
                                    class="d-flex
                               align-items-center
                               gap-3">


                                    <img
                                        src="<?php
                                                echo htmlspecialchars(
                                                    $admin_image
                                                );
                                                ?>"
                                        alt="Admin">


                                    <div>


                                        <div class="admin-name">

                                            <?php

                                            echo htmlspecialchars(
                                                $admin['name']
                                            );

                                            ?>

                                        </div>


                                        <div class="admin-detail">

                                            <i
                                                class="fa-solid
                                           fa-phone">
                                            </i>

                                            <?php

                                            echo htmlspecialchars(
                                                $admin['phone']
                                            );

                                            ?>

                                        </div>


                                        <div class="mt-2">


                                            <span
                                                class="badge bg-dark">

                                                ADMIN

                                            </span>


                                        </div>


                                    </div>


                                </div>


                            </div>



                            <!-- LOGIN INFORMATION -->

                            <div class="col-lg-7 col-md-6">


                                <div class="row">


                                    <div class="col-sm-6 mb-3 mb-sm-0">


                                        <div
                                            class="admin-detail">

                                            Login Date

                                        </div>


                                        <strong>

                                            <?php

                                            echo date(
                                                "d-m-Y",
                                                strtotime(
                                                    $login_date_time
                                                )
                                            );

                                            ?>

                                        </strong>


                                    </div>


                                    <div class="col-sm-6">


                                        <div
                                            class="admin-detail">

                                            Login Time

                                        </div>


                                        <strong>

                                            <?php

                                            echo date(
                                                "h:i A",
                                                strtotime(
                                                    $login_date_time
                                                )
                                            );

                                            ?>

                                        </strong>


                                    </div>


                                </div>


                            </div>


                        </div>


                    </div>



                    <!-- =================================================
             SUMMARY
        ================================================== -->

                    <div class="row">


                        <!-- TOTAL USERS -->

                        <div class="col-lg-4 col-md-6 mb-3">


                            <div class="summary-card">


                                <div class="summary-icon">

                                    <i
                                        class="fa-solid
                                   fa-users">
                                    </i>

                                </div>


                                <div class="summary-number">

                                    <?php

                                    echo $total_users;

                                    ?>

                                </div>


                                <div class="summary-title">

                                    Total Customers

                                </div>


                            </div>


                        </div>



                        <!-- CURRENT ADMIN -->

                        <div class="col-lg-4 col-md-6 mb-3">


                            <div class="summary-card">


                                <div class="summary-icon">

                                    <i
                                        class="fa-solid
                                   fa-user-shield">
                                    </i>

                                </div>


                                <div class="summary-number">

                                    <?php

                                    echo htmlspecialchars(
                                        $admin['name']
                                    );

                                    ?>

                                </div>


                                <div class="summary-title">

                                    Logged In Admin

                                </div>


                            </div>


                        </div>



                        <!-- LOGIN TIME -->

                        <div class="col-lg-4 col-md-6 mb-3">


                            <div class="summary-card">


                                <div class="summary-icon">

                                    <i
                                        class="fa-solid
                                   fa-clock">
                                    </i>

                                </div>


                                <div class="summary-number">

                                    <?php

                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $login_date_time
                                        )
                                    );

                                    ?>

                                </div>


                                <div class="summary-title">

                                    Current Login Time

                                </div>


                            </div>


                        </div>


                    </div>



                    <!-- =================================================
             USERS TABLE
        ================================================== -->

                    <div class="table-card">


                        <!-- HEADER -->

                        <div class="table-card-header">


                            <h3>

                                <i
                                    class="fa-solid
                               fa-users">
                                </i>

                                All Customers

                            </h3>


                            <span
                                class="badge bg-primary">

                                <?php

                                echo $total_users;

                                ?>

                                Users

                            </span>


                        </div>



                        <!-- BODY -->

                        <div class="table-card-body">




                            <tr>

                                <td colspan="9" class="text-center">

                                    No Users Found

                                </td>

                            </tr>




                            <div
                                class="table-responsive">


                                <table
                                    class="table
                                   table-bordered
                                   table-hover
                                   user-table">


                                    <thead>


                                        <tr>


                                            <th>
                                                #
                                            </th>


                                            <th>
                                                User
                                            </th>


                                            <th>
                                                Contact
                                            </th>


                                            <th>
                                                Gender
                                            </th>


                                            <th>
                                                Date of Birth
                                            </th>


                                            <th>
                                                Role
                                            </th>


                                            <th>
                                                Status
                                            </th>


                                            <th>
                                                Registered
                                            </th>


                                            <th>
                                                Action
                                            </th>


                                        </tr>


                                    </thead>


                                    <tbody>


                                        <?php


                                        $count = 1;

                                        foreach ($users as $row) {


                                        ?>


                                            <tr>


                                                <!-- NUMBER -->

                                                <td>

                                                    <?php

                                                    echo $count;

                                                    ?>

                                                </td>



                                                <!-- USER -->

                                                <td>


                                                    <div
                                                        class="d-flex
                                                   align-items-center
                                                   gap-2">


                                                        <?php

                                                        if (
                                                            !empty($row['profile_image'])
                                                        ) {

                                                            $user_image =
                                                                "../" .
                                                                $row['profile_image'];
                                                        } else {

                                                            $user_image =
                                                                "../assets/images/default-user.png";
                                                        }

                                                        ?>


                                                        <img
                                                            src="<?php
                                                                    echo htmlspecialchars(
                                                                        $user_image
                                                                    );
                                                                    ?>"
                                                            class="user-image"
                                                            alt="User">


                                                        <div>


                                                            <div
                                                                class="user-name">

                                                                <?php

                                                                echo htmlspecialchars(
                                                                    $row['name']
                                                                );

                                                                ?>

                                                            </div>


                                                            <div
                                                                class="user-id">

                                                                ID:

                                                                <?php

                                                                echo $row['user_id'];

                                                                ?>

                                                            </div>


                                                        </div>


                                                    </div>


                                                </td>



                                                <!-- PHONE -->

                                                <td>

                                                    <?php

                                                    if (
                                                        !empty($row['phone'])
                                                    ) {

                                                        echo htmlspecialchars(
                                                            $row['phone']
                                                        );
                                                    } else {

                                                        echo "-";
                                                    }

                                                    ?>

                                                </td>



                                                <!-- GENDER -->

                                                <td>

                                                    <?php

                                                    if (
                                                        !empty($row['gender'])
                                                    ) {

                                                        echo ucfirst(
                                                            htmlspecialchars(
                                                                $row['gender']
                                                            )
                                                        );
                                                    } else {

                                                        echo "-";
                                                    }

                                                    ?>

                                                </td>



                                                <!-- DOB -->

                                                <td>

                                                    <?php

                                                    if (
                                                        !empty($row['date_of_birth'])
                                                    ) {

                                                        echo date(
                                                            "d-m-Y",
                                                            strtotime(
                                                                $row['date_of_birth']
                                                            )
                                                        );
                                                    } else {

                                                        echo "-";
                                                    }

                                                    ?>

                                                </td>



                                                <!-- ROLE -->

                                                <td>

                                                    <span
                                                        class="badge bg-primary">

                                                        <?php

                                                        echo ucfirst(
                                                            htmlspecialchars(
                                                                $row['role']
                                                            )
                                                        );

                                                        ?>

                                                    </span>

                                                </td>



                                                <!-- STATUS -->

                                                <td>

                                                    <?php

                                                    if (
                                                        $row['status'] == 1
                                                    ) {

                                                    ?>

                                                        <span
                                                            class="badge bg-success">

                                                            Active

                                                        </span>

                                                    <?php

                                                    } else {

                                                    ?>

                                                        <span
                                                            class="badge bg-danger">

                                                            Inactive

                                                        </span>

                                                    <?php

                                                    }

                                                    ?>

                                                </td>



                                                <!-- REGISTERED -->

                                                <td>

                                                    <?php

                                                    echo date(
                                                        "d-m-Y",
                                                        strtotime(
                                                            $row['created_at']
                                                        )
                                                    );

                                                    ?>

                                                    <br>

                                                    <small
                                                        class="text-muted">

                                                        <?php

                                                        echo date(
                                                            "h:i A",
                                                            strtotime(
                                                                $row['created_at']
                                                            )
                                                        );

                                                        ?>

                                                    </small>

                                                </td>



                                                <!-- ACTION -->

                                                <td>


                                                    <div
                                                        class="d-flex
                                                   gap-1">


                                                        <a
                                                            href="edit_user.php?id=<?php
                                                                                    echo $row['user_id'];
                                                                                    ?>"
                                                            class="btn
                                                       btn-sm
                                                       btn-primary">


                                                            <i
                                                                class="fa-solid
                                                           fa-pen">
                                                            </i>

                                                            Edit


                                                        </a>


                                                        <a
                                                            href="delete_user.php?id=<?php
                                                                                        echo $row['user_id'];
                                                                                        ?>"
                                                            class="btn
                                                       btn-sm
                                                       btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this user?');">


                                                            <i
                                                                class="fa-solid
                                                           fa-trash">
                                                            </i>

                                                            Delete


                                                        </a>


                                                    </div>


                                                </td>


                                            </tr>


                                        <?php


                                            $count++;
                                        }


                                        ?>


                                    </tbody>


                                </table>


                            </div>


                            <?php


                            ?>


                        </div>


                    </div>


                </div>


            </div>

        </div>

        <?php include 'footer.php'; ?>
    </div>
    </div>

    <!-- jQuery -->
    <script src="assets/vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="assets/vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="assets/vendors/nprogress/nprogress.js"></script>
    <!-- Chart.js -->
    <script src="assets/vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- gauge.js -->
    <script src="assets/vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="assets/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- iCheck -->
    <script src="assets/vendors/iCheck/icheck.min.js"></script>
    <!-- Skycons -->
    <script src="assets/vendors/skycons/skycons.js"></script>
    <!-- Flot -->
    <script src="assets/vendors/Flot/jquery.flot.js"></script>
    <script src="assets/vendors/Flot/jquery.flot.pie.js"></script>
    <script src="assets/vendors/Flot/jquery.flot.time.js"></script>
    <script src="assets/vendors/Flot/jquery.flot.stack.js"></script>
    <script src="assets/vendors/Flot/jquery.flot.resize.js"></script>
    <!-- Flot plugins -->
    <script src="assets/vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
    <script src="assets/vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
    <script src="assets/vendors/flot.curvedlines/curvedLines.js"></script>
    <!-- DateJS -->
    <script src="assets/vendors/DateJS/build/date.js"></script>
    <!-- JQVMap -->
    <script src="assets/vendors/jqvmap/dist/jquery.vmap.js"></script>
    <script src="assets/vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
    <script src="assets/vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="assets/vendors/moment/min/moment.min.js"></script>
    <script src="assets/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="assets/js/custom.min.js"></script>
    <script src="assets/js/custom-popup.js"></script>
</body>

</html>
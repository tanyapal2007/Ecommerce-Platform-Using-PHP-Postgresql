<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/../config/database.php";


/* =========================================
   CHECK ADMIN LOGIN
========================================= */

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];


/* =========================================
   GET ADMIN + PROFILE DATA
========================================= */

$sql = "
    SELECT
        u.user_id,
        u.name,
        u.phone,
        u.role,
        u.status,
        u.created_at,

        up.profile_id,
        up.first_name,
        up.last_name,
        up.profile_image,
        up.gender,
        up.date_of_birth,
        up.bio

    FROM users u

    LEFT JOIN user_profile up
        ON u.user_id = up.user_id

    WHERE u.user_id = :user_id
    AND u.role = 'admin'

    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ':user_id' => $user_id
]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);


/* =========================================
   ADMIN NOT FOUND
========================================= */

if (!$admin) {
    header("Location: ../login.php");
    exit;
}


/* =========================================
   ASSIGN VARIABLES
========================================= */

$name = $admin['name'] ?? '';

$phone = $admin['phone'] ?? '';

$first_name = $admin['first_name'] ?? '';

$last_name = $admin['last_name'] ?? '';

$profile_image = $admin['profile_image'] ?? '';

$gender = $admin['gender'] ?? '';

$date_of_birth = $admin['date_of_birth'] ?? '';

$bio = $admin['bio'] ?? '';

$role = $admin['role'] ?? '';

$status = $admin['status'] ?? 0;

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

                <!-- =================================================
             TOP NAVIGATION
        ================================================== -->

                <div class="top_nav">

                    <div class="nav_menu">

                        <nav>

                            <div class="nav toggle">

                                <a id="menu_toggle">

                                    <i class="fa fa-bars"></i>

                                </a>

                            </div>


                            <ul class="nav navbar-nav navbar-right">

                                <li>

                                    <a href="javascript:;"
                                        class="user-profile dropdown-toggle"
                                        data-toggle="dropdown">

                                        <img
                                            src="assets/images/img.jpg"
                                            alt="">

                                        John Doe

                                        <span class="fa fa-angle-down"></span>

                                    </a>


                                    <ul class="dropdown-menu dropdown-usermenu pull-right">

                                        <li>
                                            <a href="javascript:;">
                                                Profile
                                            </a>
                                        </li>

                                        <li>
                                            <a href="javascript:;">
                                                Settings
                                            </a>
                                        </li>

                                        <li>
                                            <a href="javascript:;">
                                                Help
                                            </a>
                                        </li>

                                        <li>

                                            <a href="login.php">

                                                <i class="fa fa-sign-out pull-right"></i>

                                                Log Out

                                            </a>

                                        </li>

                                    </ul>

                                </li>

                            </ul>

                        </nav>

                    </div>

                </div>

                <div class="content">
                    <!-- =========================================
     ADMIN PROFILE SECTION
========================================= -->

                    <div class="row">

                        <div class="col-md-12 col-sm-12 col-xs-12">

                            <div class="x_panel">

                                <!-- PAGE TITLE -->

                                <div class="x_title">

                                    <h2>
                                        Admin Profile
                                        <small>Account Information</small>
                                    </h2>

                                    <div class="clearfix"></div>

                                </div>


                                <div class="x_content">

                                    <div class="row">


                                        <!-- =====================================
                         LEFT SIDE - PROFILE
                    ====================================== -->

                                        <div class="col-md-4 col-sm-4 col-xs-12">

                                            <div class="profile_left">

                                                <div class="profile_img">

                                                    <div
                                                        class="avatar-view"
                                                        style="
                                        width:150px;
                                        height:150px;
                                        margin:auto;
                                        border-radius:50%;
                                        overflow:hidden;
                                        background:#f5f5f5;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                    ">

                                                        <?php if (!empty($profile_image)) { ?>

                                                            <img
                                                                src="<?php echo htmlspecialchars($profile_image); ?>"
                                                                alt="Admin Profile"
                                                                style="
                                                width:100%;
                                                height:100%;
                                                object-fit:cover;
                                            ">

                                                        <?php } else { ?>

                                                            <i
                                                                class="fa fa-user"
                                                                style="
                                                font-size:70px;
                                                color:#73879C;
                                            "></i>

                                                        <?php } ?>

                                                    </div>

                                                </div>


                                                <h3 class="text-center">
                                                    <?php
                                                    echo htmlspecialchars($name);
                                                    ?>
                                                </h3>


                                                <p class="text-center">

                                                    <span class="label label-primary">

                                                        <i class="fa fa-user"></i>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            ucfirst($role)
                                                        );
                                                        ?>

                                                    </span>

                                                </p>


                                                <br>


                                                <!-- PROFILE MENU -->

                                                <ul class="list-unstyled user_data">

                                                    <li>
                                                        <i class="fa fa-phone user-profile-icon"></i>

                                                        <?php
                                                        echo htmlspecialchars($phone);
                                                        ?>

                                                    </li>

                                                    <li>
                                                        <i class="fa fa-user user-profile-icon"></i>

                                                        <?php

                                                        if (!empty($gender)) {

                                                            echo htmlspecialchars(
                                                                ucfirst($gender)
                                                            );
                                                        } else {

                                                            echo "Gender not added";
                                                        }

                                                        ?>

                                                    </li>

                                                    <li>
                                                        <i class="fa fa-calendar user-profile-icon"></i>

                                                        <?php

                                                        if (!empty($date_of_birth)) {

                                                            echo date(
                                                                "d M Y",
                                                                strtotime($date_of_birth)
                                                            );
                                                        } else {

                                                            echo "Date of birth not added";
                                                        }

                                                        ?>

                                                    </li>

                                                </ul>


                                                <br>


                                                <!-- EDIT BUTTON -->

                                                <a
                                                    href="edit-admin-profile.php"
                                                    class="btn btn-success btn-block">

                                                    <i class="fa fa-edit"></i>

                                                    Edit Profile

                                                </a>

                                            </div>

                                        </div>


                                        <!-- =====================================
                         RIGHT SIDE - PROFILE INFORMATION
                    ====================================== -->

                                        <div class="col-md-8 col-sm-8 col-xs-12">

                                            <div class="profile_title">

                                                <div class="col-md-6">

                                                    <h2>
                                                        Profile Information
                                                    </h2>

                                                </div>

                                            </div>


                                            <br>


                                            <!-- NAME -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    Name
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <?php
                                                        echo htmlspecialchars($name);
                                                        ?>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- FIRST NAME -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    First Name
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <?php

                                                        echo !empty($first_name)
                                                            ? htmlspecialchars($first_name)
                                                            : "Not Added";

                                                        ?>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- LAST NAME -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    Last Name
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <?php

                                                        echo !empty($last_name)
                                                            ? htmlspecialchars($last_name)
                                                            : "Not Added";

                                                        ?>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- PHONE -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    Phone
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <?php
                                                        echo htmlspecialchars($phone);
                                                        ?>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- GENDER -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    Gender
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <?php

                                                        echo !empty($gender)
                                                            ? htmlspecialchars(
                                                                ucfirst($gender)
                                                            )
                                                            : "Not Added";

                                                        ?>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- DATE OF BIRTH -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    Date of Birth
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <?php

                                                        if (!empty($date_of_birth)) {

                                                            echo date(
                                                                "d M Y",
                                                                strtotime($date_of_birth)
                                                            );
                                                        } else {

                                                            echo "Not Added";
                                                        }

                                                        ?>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- ROLE -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    Role
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <span class="label label-primary">

                                                            <?php

                                                            echo htmlspecialchars(
                                                                ucfirst($role)
                                                            );

                                                            ?>

                                                        </span>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- STATUS -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    Status
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <?php if ($status == 1) { ?>

                                                            <span class="label label-success">
                                                                Active
                                                            </span>

                                                        <?php } else { ?>

                                                            <span class="label label-danger">
                                                                Inactive
                                                            </span>

                                                        <?php } ?>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- CREATED DATE -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    Created At
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <?php

                                                        if (!empty($admin['created_at'])) {

                                                            echo date(
                                                                "d M Y, h:i A",
                                                                strtotime(
                                                                    $admin['created_at']
                                                                )
                                                            );
                                                        } else {

                                                            echo "Not Available";
                                                        }

                                                        ?>

                                                    </p>

                                                </div>

                                            </div>


                                            <!-- BIO -->

                                            <div class="form-group">

                                                <label class="control-label col-md-3">
                                                    Bio
                                                </label>

                                                <div class="col-md-9">

                                                    <p class="form-control-static">

                                                        <?php

                                                        if (!empty($bio)) {

                                                            echo nl2br(
                                                                htmlspecialchars($bio)
                                                            );
                                                        } else {

                                                            echo "No bio added";
                                                        }

                                                        ?>

                                                    </p>

                                                </div>

                                            </div>

                                        </div>

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
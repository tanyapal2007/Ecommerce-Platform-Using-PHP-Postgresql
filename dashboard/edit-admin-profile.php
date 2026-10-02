<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";


/* =========================================================
   CHECK ADMIN LOGIN
========================================================= */

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];


/* =========================================================
   FETCH ADMIN DATA
========================================================= */

$sql = "
    SELECT
        u.user_id,
        u.name,
        u.phone,
        u.role,
        u.status,

        up.profile_id,
        up.first_name,
        up.last_name,
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


if (!$admin) {
    header("Location: ../login.php");
    exit;
}


/* =========================================================
   DEFAULT VALUES
========================================================= */

$name = $admin['name'] ?? '';
$phone = $admin['phone'] ?? '';

$first_name = $admin['first_name'] ?? '';
$last_name = $admin['last_name'] ?? '';
$gender = $admin['gender'] ?? '';
$date_of_birth = $admin['date_of_birth'] ?? '';
$bio = $admin['bio'] ?? '';

$message = '';
$error = '';


/* =========================================================
   UPDATE PROFILE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $bio = trim($_POST['bio'] ?? '');


    /* =========================================
       VALIDATION
    ========================================= */

    if ($name === '') {

        $error = "Name is required.";
    } elseif ($phone === '') {

        $error = "Phone number is required.";
    } else {

        try {

            $conn->beginTransaction();


            /* =========================================
               UPDATE USERS TABLE
            ========================================= */

            $update_user = "
                UPDATE users
                SET
                    name = :name,
                    phone = :phone
                WHERE user_id = :user_id
                AND role = 'admin'
            ";

            $stmt = $conn->prepare($update_user);

            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':user_id' => $user_id
            ]);


            /* =========================================
               CHECK USER PROFILE
            ========================================= */

            $check_profile = "
                SELECT profile_id
                FROM user_profile
                WHERE user_id = :user_id
                LIMIT 1
            ";

            $stmt = $conn->prepare($check_profile);

            $stmt->execute([
                ':user_id' => $user_id
            ]);

            $profile = $stmt->fetch(PDO::FETCH_ASSOC);


            /* =========================================
               UPDATE PROFILE
            ========================================= */

            if ($profile) {

                $update_profile = "
                    UPDATE user_profile
                    SET
                        first_name = :first_name,
                        last_name = :last_name,
                        gender = :gender,
                        date_of_birth = :date_of_birth,
                        bio = :bio,
                        updated_at = CURRENT_TIMESTAMP

                    WHERE user_id = :user_id
                ";

                $stmt = $conn->prepare($update_profile);

                $stmt->execute([
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':gender' => $gender,
                    ':date_of_birth' => ($date_of_birth !== '' ? $date_of_birth : null),
                    ':bio' => $bio,
                    ':user_id' => $user_id
                ]);
            } else {

                /* =========================================
                   CREATE PROFILE IF NOT EXISTS
                ========================================= */

                $insert_profile = "
                    INSERT INTO user_profile
                    (
                        user_id,
                        first_name,
                        last_name,
                        gender,
                        date_of_birth,
                        bio
                    )

                    VALUES
                    (
                        :user_id,
                        :first_name,
                        :last_name,
                        :gender,
                        :date_of_birth,
                        :bio
                    )
                ";

                $stmt = $conn->prepare($insert_profile);

                $stmt->execute([
                    ':user_id' => $user_id,
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':gender' => $gender,
                    ':date_of_birth' => ($date_of_birth !== '' ? $date_of_birth : null),
                    ':bio' => $bio
                ]);
            }

            $conn->commit();

            header("Location: admin-profile.php?updated=1");
            exit;
        } catch (Exception $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            $error = "Something went wrong. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Edit Admin Profile</title>


    <!-- Bootstrap -->

    <link
        href="assets/vendors/bootstrap/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Font Awesome -->

    <link
        href="assets/vendors/font-awesome/css/font-awesome.min.css"
        rel="stylesheet">


    <!-- NProgress -->

    <link
        href="assets/vendors/nprogress/nprogress.css"
        rel="stylesheet">


    <!-- Custom Theme -->

    <link
        href="assets/css/custom.min.css"
        rel="stylesheet">


    <style>
        .profile-edit-panel {
            margin-top: 20px;
        }

        .form-group label {
            font-weight: 600;
        }

        .form-control {
            height: 40px;
        }

        textarea.form-control {
            height: 100px;
            resize: vertical;
        }

        .btn-save {
            min-width: 130px;
        }
    </style>

</head>


<body class="nav-md">


    <div class="container body">

        <div class="main_container">


            <!-- SIDEBAR -->

            <?php include 'sidebar.php'; ?>


            <!-- RIGHT CONTENT -->

            <div class="right_col" role="main">


                <!-- TOP NAVIGATION -->

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
                                            alt="Admin">

                                        Admin

                                        <span class="fa fa-angle-down"></span>

                                    </a>


                                    <ul class="dropdown-menu dropdown-usermenu pull-right">

                                        <li>

                                            <a href="admin-profile.php">

                                                <i class="fa fa-user pull-right"></i>

                                                Profile

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


                <!-- PAGE CONTENT -->

                <div class="">

                    <div class="page-title">

                        <div class="title_left">

                            <h3>

                                <i class="fa fa-edit"></i>

                                Edit Admin Profile

                            </h3>

                        </div>

                    </div>


                    <div class="clearfix"></div>


                    <!-- SUCCESS MESSAGE -->

                    <?php if ($message !== ''): ?>

                        <div class="alert alert-success">

                            <i class="fa fa-check-circle"></i>

                            <?php echo htmlspecialchars($message); ?>

                        </div>

                    <?php endif; ?>


                    <!-- ERROR MESSAGE -->

                    <?php if ($error !== ''): ?>

                        <div class="alert alert-danger">

                            <i class="fa fa-exclamation-circle"></i>

                            <?php echo htmlspecialchars($error); ?>

                        </div>

                    <?php endif; ?>


                    <!-- EDIT FORM -->

                    <div class="row">

                        <div class="col-md-10 col-sm-12 col-xs-12">

                            <div class="x_panel profile-edit-panel">


                                <div class="x_title">

                                    <h2>

                                        <i class="fa fa-user"></i>

                                        Personal Information

                                    </h2>

                                    <div class="clearfix"></div>

                                </div>


                                <div class="x_content">


                                    <form
                                        method="POST"
                                        action=""
                                        class="form-horizontal form-label-left">


                                        <!-- NAME -->

                                        <div class="form-group">

                                            <label class="control-label col-md-3">

                                                Name

                                                <span class="required">*</span>

                                            </label>

                                            <div class="col-md-7">

                                                <input
                                                    type="text"
                                                    name="name"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($name); ?>"
                                                    required>

                                            </div>

                                        </div>


                                        <!-- PHONE -->

                                        <div class="form-group">

                                            <label class="control-label col-md-3">

                                                Phone

                                                <span class="required">*</span>

                                            </label>

                                            <div class="col-md-7">

                                                <input
                                                    type="text"
                                                    name="phone"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($phone); ?>"
                                                    required>

                                            </div>

                                        </div>


                                        <!-- FIRST NAME -->

                                        <div class="form-group">

                                            <label class="control-label col-md-3">

                                                First Name

                                            </label>

                                            <div class="col-md-7">

                                                <input
                                                    type="text"
                                                    name="first_name"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($first_name); ?>">

                                            </div>

                                        </div>


                                        <!-- LAST NAME -->

                                        <div class="form-group">

                                            <label class="control-label col-md-3">

                                                Last Name

                                            </label>

                                            <div class="col-md-7">

                                                <input
                                                    type="text"
                                                    name="last_name"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($last_name); ?>">

                                            </div>

                                        </div>


                                        <!-- GENDER -->

                                        <div class="form-group">

                                            <label class="control-label col-md-3">

                                                Gender

                                            </label>

                                            <div class="col-md-7">

                                                <select
                                                    name="gender"
                                                    class="form-control">

                                                    <option value="">
                                                        Select Gender
                                                    </option>

                                                    <option
                                                        value="Male"
                                                        <?php echo ($gender === 'Male') ? 'selected' : ''; ?>>

                                                        Male

                                                    </option>

                                                    <option
                                                        value="Female"
                                                        <?php echo ($gender === 'Female') ? 'selected' : ''; ?>>

                                                        Female

                                                    </option>

                                                    <option
                                                        value="Other"
                                                        <?php echo ($gender === 'Other') ? 'selected' : ''; ?>>

                                                        Other

                                                    </option>

                                                </select>

                                            </div>

                                        </div>


                                        <!-- DATE OF BIRTH -->

                                        <div class="form-group">

                                            <label class="control-label col-md-3">

                                                Date of Birth

                                            </label>

                                            <div class="col-md-7">

                                                <input
                                                    type="date"
                                                    name="date_of_birth"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($date_of_birth); ?>">

                                            </div>

                                        </div>


                                        <!-- BIO -->

                                        <div class="form-group">

                                            <label class="control-label col-md-3">

                                                Bio

                                            </label>

                                            <div class="col-md-7">

                                                <textarea
                                                    name="bio"
                                                    class="form-control"><?php echo htmlspecialchars($bio); ?></textarea>

                                            </div>

                                        </div>


                                        <!-- BUTTONS -->

                                        <div class="form-group">

                                            <div class="col-md-7 col-md-offset-3">

                                                <button
                                                    type="submit"
                                                    class="btn btn-success btn-save">

                                                    <i class="fa fa-save"></i>

                                                    Save Changes

                                                </button>


                                                <a
                                                    href="admin-profile.php"
                                                    class="btn btn-default">

                                                    Cancel

                                                </a>

                                            </div>

                                        </div>


                                    </form>


                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- FOOTER -->

                <footer>

                    <div class="pull-right">

                        Gentelella -
                        Bootstrap Admin Template by

                        <a href="https://colorlib.com">

                            Colorlib

                        </a>

                    </div>

                    <div class="clearfix"></div>

                </footer>


            </div>

        </div>

    </div>


    <!-- JAVASCRIPT -->

    <script
        src="assets/vendors/jquery/dist/jquery.min.js">
    </script>

    <script
        src="assets/vendors/bootstrap/dist/js/bootstrap.min.js">
    </script>

    <script
        src="assets/vendors/fastclick/lib/fastclick.js">
    </script>

    <script
        src="assets/vendors/nprogress/nprogress.js">
    </script>

    <script
        src="assets/js/custom.min.js">
    </script>


</body>

</html>
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";


/* =========================================================
   CHECK LOGIN
========================================================= */

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$message = "";
$error = "";


/* =========================================================
   DELETE ADDRESS
========================================================= */

if (isset($_GET['delete_id'])) {

    $delete_id = (int) $_GET['delete_id'];

    if ($delete_id > 0) {

        try {

            /* Get address before deleting */

            $get_address = $conn->prepare("
                SELECT
                    address_id,
                    user_id,
                    is_default
                FROM user_address
                WHERE address_id = :address_id
                LIMIT 1
            ");

            $get_address->execute([
                ':address_id' => $delete_id
            ]);

            $address_data = $get_address->fetch(PDO::FETCH_ASSOC);


            if ($address_data) {

                $address_user_id = (int) $address_data['user_id'];
                $was_default = (int) $address_data['is_default'];


                /* Delete address */

                $delete_stmt = $conn->prepare("
                    DELETE FROM user_address
                    WHERE address_id = :address_id
                ");

                $delete_stmt->execute([
                    ':address_id' => $delete_id
                ]);


                /* If deleted address was default */

                if ($was_default === 1) {

                    $new_default_stmt = $conn->prepare("
                        SELECT address_id
                        FROM user_address
                        WHERE user_id = :user_id
                        ORDER BY address_id DESC
                        LIMIT 1
                    ");

                    $new_default_stmt->execute([
                        ':user_id' => $address_user_id
                    ]);

                    $new_default_id = $new_default_stmt->fetchColumn();


                    if ($new_default_id) {

                        $set_default_stmt = $conn->prepare("
                            UPDATE user_address
                            SET is_default = 1
                            WHERE address_id = :address_id
                            AND user_id = :user_id
                        ");

                        $set_default_stmt->execute([
                            ':address_id' => $new_default_id,
                            ':user_id' => $address_user_id
                        ]);
                    }
                }

                $message = "Address deleted successfully.";
            } else {

                $error = "Address not found.";
            }
        } catch (PDOException $e) {

            $error = "Database Error: " . $e->getMessage();
        }
    }
}


/* =========================================================
   GET ONLY CUSTOMER / USER ADDRESSES
========================================================= */

try {

    $address_stmt = $conn->prepare("
        SELECT
            ua.address_id,
            ua.user_id,
            ua.address_type,
            ua.full_name,
            ua.phone,
            ua.address_line1,
            ua.address_line2,
            ua.city,
            ua.state,
            ua.pincode,
            ua.is_default,

            u.name AS user_name,
            u.phone AS user_phone,
            u.role AS user_role,
            u.status AS user_status

        FROM user_address ua

        INNER JOIN users u
            ON u.user_id = ua.user_id

        WHERE LOWER(u.role) = 'user'

        ORDER BY
            ua.address_id DESC
    ");

    $address_stmt->execute();

    $addresses = $address_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {

    $addresses = [];

    $error = "Database Error: " . $e->getMessage();
}


/* =========================================================
   TOTAL CUSTOMERS WITH ADDRESS
========================================================= */

try {

    $user_count_stmt = $conn->prepare("
        SELECT COUNT(DISTINCT ua.user_id)

        FROM user_address ua

        INNER JOIN users u
            ON u.user_id = ua.user_id

        WHERE LOWER(u.role) = 'user'
    ");

    $user_count_stmt->execute();

    $total_users = (int) $user_count_stmt->fetchColumn();
} catch (PDOException $e) {

    $total_users = 0;
}


/* =========================================================
   TOTAL CUSTOMER ADDRESSES
========================================================= */

$total_addresses = count($addresses);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Customer Addresses</title>


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


    <!-- iCheck -->

    <link
        href="assets/vendors/iCheck/skins/flat/green.css"
        rel="stylesheet">


    <!-- Custom Theme -->

    <link
        href="assets/css/custom.min.css"
        rel="stylesheet">


    <style>
        /* =====================================================
           PAGE
        ===================================================== */

        .address-page {
            padding: 25px 0;
        }


        /* =====================================================
           TITLE
        ===================================================== */

        .page-title-box {

            background: #ffffff;

            padding: 20px;

            border: 1px solid #e5e5e5;

            border-radius: 6px;

            margin-bottom: 20px;

        }


        .page-title-box h3 {

            margin: 0;

            font-size: 22px;

            font-weight: 600;

        }


        .page-title-box p {

            margin: 7px 0 0;

            color: #888;

        }


        /* =====================================================
           SUMMARY
        ===================================================== */

        .summary-box {

            background: #ffffff;

            border: 1px solid #e5e5e5;

            border-radius: 6px;

            padding: 20px;

            margin-bottom: 20px;

            min-height: 105px;

        }


        .summary-number {

            font-size: 26px;

            font-weight: 600;

            margin-bottom: 5px;

        }


        .summary-label {

            color: #777;

            font-size: 13px;

        }


        .summary-icon {

            float: right;

            font-size: 35px;

            color: #337ab7;

        }


        /* =====================================================
           TABLE PANEL
        ===================================================== */

        .table-panel {

            background: #ffffff;

            border: 1px solid #e5e5e5;

            border-radius: 6px;

            padding: 20px;

        }


        .table-panel-title {

            margin-top: 0;

            margin-bottom: 20px;

            padding-bottom: 15px;

            border-bottom: 1px solid #eeeeee;

            font-size: 20px;

            font-weight: 600;

        }


        /* =====================================================
           TABLE
        ===================================================== */

        .address-table {

            width: 100%;

            margin-bottom: 0;

        }


        .address-table th {

            background: #f7f7f7;

            color: #333333;

            font-weight: 600;

            white-space: nowrap;

            vertical-align: middle !important;

        }


        .address-table td {

            vertical-align: middle !important;

            font-size: 13px;

        }


        .address-table tbody tr:hover {

            background: #fafafa;

        }


        /* =====================================================
           USER
        ===================================================== */

        .user-name {

            font-weight: 600;

            color: #333333;

        }


        .user-id {

            color: #999999;

            font-size: 12px;

        }


        .user-phone {

            white-space: nowrap;

        }


        /* =====================================================
           ADDRESS
        ===================================================== */

        .address-text {

            min-width: 230px;

            line-height: 1.6;

        }


        .address-line {

            display: block;

        }


        /* =====================================================
           ADDRESS TYPE
        ===================================================== */

        .address-type {

            display: inline-block;

            padding: 5px 9px;

            border-radius: 4px;

            font-size: 11px;

            font-weight: 600;

            text-transform: uppercase;

            white-space: nowrap;

        }


        .address-type.home {

            background: #eaf2ff;

            color: #337ab7;

        }


        .address-type.office {

            background: #e8f7f8;

            color: #31708f;

        }


        .address-type.other {

            background: #f2f2f2;

            color: #666666;

        }


        /* =====================================================
           DEFAULT
        ===================================================== */

        .default-badge {

            display: inline-block;

            background: #dff0d8;

            color: #3c763d;

            padding: 5px 9px;

            border-radius: 4px;

            font-size: 11px;

            font-weight: 600;

            white-space: nowrap;

        }


        .not-default {

            color: #999999;

            font-size: 12px;

        }


        /* =====================================================
           STATUS
        ===================================================== */

        .status-active {

            color: #3c763d;

            font-weight: 600;

        }


        .status-inactive {

            color: #a94442;

            font-weight: 600;

        }


        /* =====================================================
           EMPTY
        ===================================================== */

        .no-address {

            text-align: center;

            padding: 60px 20px;

            color: #999999;

        }


        .no-address i {

            font-size: 50px;

            color: #cccccc;

            margin-bottom: 15px;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        .table-responsive {

            border: 0;

        }
    </style>

</head>


<body class="nav-md">


    <div class="container body">

        <div class="main_container">


            <!-- =====================================================
             SIDEBAR
        ====================================================== -->

            <?php include 'sidebar.php'; ?>


            <!-- =====================================================
             RIGHT CONTENT
        ====================================================== -->

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
                                            alt="User">

                                        <?php

                                        echo htmlspecialchars(
                                            $_SESSION['username'] ?? 'Admin'
                                        );

                                        ?>

                                        <span class="fa fa-angle-down"></span>

                                    </a>


                                    <ul class="dropdown-menu dropdown-usermenu pull-right">

                                        <li>

                                            <a href="profile.php">

                                                <i class="fa fa-user pull-right"></i>

                                                Profile

                                            </a>

                                        </li>


                                        <li>

                                            <a href="user-address.php">

                                                <i class="fa fa-map-marker pull-right"></i>

                                                Customer Addresses

                                            </a>

                                        </li>


                                        <li>

                                            <a href="javascript:;">

                                                <i class="fa fa-cog pull-right"></i>

                                                Settings

                                            </a>

                                        </li>


                                        <li>

                                            <a href="../logout.php">

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


                <!-- =================================================
                 PAGE CONTENT
            ================================================== -->

                <div class="address-page">


                    <!-- =================================================
                     TITLE
                ================================================== -->

                    <div class="page-title-box">

                        <h3>

                            <i class="fa fa-map-marker"></i>

                            Customer Addresses

                        </h3>

                        <p>

                            All delivery addresses added by website customers.

                        </p>

                    </div>


                    <!-- =================================================
                     SUCCESS MESSAGE
                ================================================== -->

                    <?php if (!empty($message)) { ?>

                        <div class="alert alert-success">

                            <i class="fa fa-check-circle"></i>

                            <?php echo htmlspecialchars($message); ?>

                        </div>

                    <?php } ?>


                    <!-- =================================================
                     ERROR MESSAGE
                ================================================== -->

                    <?php if (!empty($error)) { ?>

                        <div class="alert alert-danger">

                            <i class="fa fa-exclamation-circle"></i>

                            <?php echo htmlspecialchars($error); ?>

                        </div>

                    <?php } ?>


                    <!-- =================================================
                     SUMMARY
                ================================================== -->

                    <div class="row">


                        <!-- CUSTOMERS -->

                        <div class="col-md-6">

                            <div class="summary-box">

                                <i class="fa fa-users summary-icon"></i>

                                <div class="summary-number">

                                    <?php echo $total_users; ?>

                                </div>

                                <div class="summary-label">

                                    Customers With Address

                                </div>

                            </div>

                        </div>


                        <!-- ADDRESSES -->

                        <div class="col-md-6">

                            <div class="summary-box">

                                <i class="fa fa-map-marker summary-icon"></i>

                                <div class="summary-number">

                                    <?php echo $total_addresses; ?>

                                </div>

                                <div class="summary-label">

                                    Total Customer Addresses

                                </div>

                            </div>

                        </div>


                    </div>


                    <!-- =================================================
                     TABLE
                ================================================== -->

                    <div class="table-panel">


                        <h3 class="table-panel-title">

                            <i class="fa fa-list"></i>

                            All Customer Address Details

                        </h3>


                        <?php if (!empty($addresses)) { ?>


                            <div class="table-responsive">


                                <table class="table table-bordered table-striped address-table">


                                    <thead>

                                        <tr>

                                            <th>#</th>

                                            <th>User ID</th>

                                            <th>Customer Name</th>

                                            <th>Phone</th>

                                            <th>Address Type</th>

                                            <th>Address Name</th>

                                            <th>Address</th>

                                            <th>City</th>

                                            <th>State</th>

                                            <th>Pincode</th>

                                            <th>Default</th>

                                            <th>Status</th>

                                            <th>Action</th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                        <?php

                                        $serial = 1;

                                        foreach ($addresses as $address) {

                                        ?>

                                            <tr>


                                                <!-- # -->

                                                <td>

                                                    <?php echo $serial++; ?>

                                                </td>


                                                <!-- USER ID -->

                                                <td>

                                                    <span class="user-id">

                                                        #<?php
                                                            echo (int)$address['user_id'];
                                                            ?>

                                                    </span>

                                                </td>


                                                <!-- CUSTOMER NAME -->

                                                <td>

                                                    <span class="user-name">

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $address['user_name'] ?? ''
                                                        );

                                                        ?>

                                                    </span>

                                                </td>


                                                <!-- PHONE -->

                                                <td>

                                                    <span class="user-phone">

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $address['user_phone']
                                                                ?? $address['phone']
                                                                ?? ''
                                                        );

                                                        ?>

                                                    </span>

                                                </td>


                                                <!-- ADDRESS TYPE -->

                                                <td>

                                                    <?php

                                                    $type = strtolower(
                                                        $address['address_type']
                                                            ?? 'other'
                                                    );

                                                    ?>


                                                    <?php if ($type === 'home') { ?>

                                                        <span class="address-type home">

                                                            <i class="fa fa-home"></i>

                                                            Home

                                                        </span>

                                                    <?php } elseif ($type === 'office') { ?>

                                                        <span class="address-type office">

                                                            <i class="fa fa-building"></i>

                                                            Office

                                                        </span>

                                                    <?php } else { ?>

                                                        <span class="address-type other">

                                                            <i class="fa fa-map-marker"></i>

                                                            Other

                                                        </span>

                                                    <?php } ?>

                                                </td>


                                                <!-- FULL NAME -->

                                                <td>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $address['full_name'] ?? ''
                                                    );

                                                    ?>

                                                </td>


                                                <!-- ADDRESS -->

                                                <td>

                                                    <div class="address-text">

                                                        <span class="address-line">

                                                            <?php

                                                            echo htmlspecialchars(
                                                                $address['address_line1']
                                                                    ?? ''
                                                            );

                                                            ?>

                                                        </span>


                                                        <?php if (
                                                            !empty($address['address_line2'])
                                                        ) { ?>

                                                            <span class="address-line">

                                                                <?php

                                                                echo htmlspecialchars(
                                                                    $address['address_line2']
                                                                );

                                                                ?>

                                                            </span>

                                                        <?php } ?>

                                                    </div>

                                                </td>


                                                <!-- CITY -->

                                                <td>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $address['city'] ?? ''
                                                    );

                                                    ?>

                                                </td>


                                                <!-- STATE -->

                                                <td>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $address['state'] ?? ''
                                                    );

                                                    ?>

                                                </td>


                                                <!-- PINCODE -->

                                                <td>

                                                    <strong>

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $address['pincode'] ?? ''
                                                        );

                                                        ?>

                                                    </strong>

                                                </td>


                                                <!-- DEFAULT -->

                                                <td>

                                                    <?php if (
                                                        (int)$address['is_default'] === 1
                                                    ) { ?>

                                                        <span class="default-badge">

                                                            <i class="fa fa-check"></i>

                                                            Default

                                                        </span>

                                                    <?php } else { ?>

                                                        <span class="not-default">

                                                            No

                                                        </span>

                                                    <?php } ?>

                                                </td>


                                                <!-- STATUS -->

                                                <td>

                                                    <?php

                                                    $status = strtolower(
                                                        trim(
                                                            $address['user_status']
                                                                ?? ''
                                                        )
                                                    );

                                                    ?>


                                                    <?php if ($status === 'active') { ?>

                                                        <span class="status-active">

                                                            Active

                                                        </span>

                                                    <?php } else { ?>

                                                        <span class="status-inactive">

                                                            <?php

                                                            echo htmlspecialchars(
                                                                $address['user_status']
                                                                    ?? 'Inactive'
                                                            );

                                                            ?>

                                                        </span>

                                                    <?php } ?>

                                                </td>


                                                <!-- DELETE -->

                                                <td>

                                                    <a
                                                        href="user-address.php?delete_id=<?php
                                                                                            echo (int)$address['address_id'];
                                                                                            ?>"
                                                        class="btn btn-danger btn-xs"
                                                        onclick="return confirm('Are you sure you want to delete this customer address?');">

                                                        <i class="fa fa-trash"></i>

                                                        Delete

                                                    </a>

                                                </td>


                                            </tr>


                                        <?php } ?>


                                    </tbody>


                                </table>

                            </div>


                        <?php } else { ?>


                            <!-- =================================================
                             EMPTY
                        ================================================== -->

                            <div class="no-address">

                                <i class="fa fa-map-marker"></i>

                                <h4>

                                    No Customer Addresses Found

                                </h4>

                                <p>

                                    No customer has added a delivery address yet.

                                </p>

                            </div>


                        <?php } ?>


                    </div>


                </div>


                <!-- =================================================
                 FOOTER
            ================================================== -->

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


    <!-- =========================================================
     JAVASCRIPT
========================================================= -->

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
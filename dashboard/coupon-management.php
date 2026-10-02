<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";


// ==========================================
// ADD COUPON
// ==========================================

if (isset($_POST['save_coupon'])) {

    $coupon_code = trim($_POST['coupon_code']);
    $discount_type = $_POST['discount_type'];
    $discount_value = (float) $_POST['discount_value'];

    $minimum_order_amount = !empty($_POST['minimum_order_amount'])
        ? (float) $_POST['minimum_order_amount']
        : 0;

    $maximum_discount = !empty($_POST['maximum_discount'])
        ? (float) $_POST['maximum_discount']
        : null;

    $usage_limit = !empty($_POST['usage_limit'])
        ? (int) $_POST['usage_limit']
        : 0;

    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $status = isset($_POST['status'])
        ? (int) $_POST['status']
        : 1;


    // Check duplicate coupon code
    $check = $conn->prepare("
        SELECT coupon_id
        FROM coupons
        WHERE UPPER(coupon_code) = UPPER(:coupon_code)
        LIMIT 1
    ");

    $check->execute([
        ':coupon_code' => $coupon_code
    ]);

    if ($check->fetch()) {

        $_SESSION['coupon_error'] = "Coupon code already exists.";
    } else {

        // Insert coupon
        $stmt = $conn->prepare("
            INSERT INTO coupons
            (
                coupon_code,
                discount_type,
                discount_value,
                minimum_order_amount,
                maximum_discount,
                usage_limit,
                used_count,
                start_date,
                end_date,
                status
            )
            VALUES
            (
                :coupon_code,
                :discount_type,
                :discount_value,
                :minimum_order_amount,
                :maximum_discount,
                :usage_limit,
                0,
                :start_date,
                :end_date,
                :status
            )
        ");

        $stmt->execute([
            ':coupon_code' => strtoupper($coupon_code),
            ':discount_type' => $discount_type,
            ':discount_value' => $discount_value,
            ':minimum_order_amount' => $minimum_order_amount,
            ':maximum_discount' => $maximum_discount,
            ':usage_limit' => $usage_limit,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':status' => $status
        ]);

        $_SESSION['coupon_success'] = "Coupon added successfully.";
    }

    // Redirect to avoid duplicate form submission
    header("Location: coupon-management.php");
    exit;
}


// ==========================================
// GET COUPONS
// ==========================================

$stmt = $conn->query("
    SELECT
        coupon_id,
        coupon_code,
        discount_type,
        discount_value,
        minimum_order_amount,
        maximum_discount,
        usage_limit,
        used_count,
        start_date,
        end_date,
        status,
        created_at
    FROM coupons
    ORDER BY coupon_id DESC
");

$coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// SUMMARY
// ==========================================

$total_coupons = count($coupons);

$stmt = $conn->query("
    SELECT COUNT(*)
    FROM coupons
    WHERE status = 1
    AND CURRENT_DATE BETWEEN start_date AND end_date
");

$active_coupons = (int) $stmt->fetchColumn();


$stmt = $conn->query("
    SELECT COUNT(*)
    FROM coupons
    WHERE end_date < CURRENT_DATE
");

$expired_coupons = (int) $stmt->fetchColumn();


$stmt = $conn->query("
    SELECT COALESCE(SUM(used_count), 0)
    FROM coupons
");

$total_used = (int) $stmt->fetchColumn();

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Order Charts</title>


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


    <!-- DataTables -->

    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap.min.css">


    <style>
        .summary-card {
            min-height: 180px;
        }

        .summary-card .x_content {
            padding-top: 25px;
        }

        .summary-number {
            font-size: 38px;
            font-weight: 600;
            margin: 10px 0;
        }

        .summary-icon {
            font-size: 30px;
            margin-bottom: 5px;
        }

        .order-table th {
            white-space: nowrap;
        }

        .order-table td {
            vertical-align: middle !important;
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

                                            <a href="javascript:;">

                                                <i class="fa fa-cog pull-right"></i>

                                                Settings

                                            </a>

                                        </li>


                                        <li>

                                            <a href="javascript:;">

                                                <i class="fa fa-question-circle pull-right"></i>

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




                <!-- ================= COUPON MANAGEMENT ================= -->

              

                    <!-- PAGE HEADER -->
                    <div class="page-title">
                        <div class="title_left">
                            <h3>Coupon Management</h3>
                        </div>

                        <div class="title_right">
                            <button type="button"
                                class="btn btn-primary pull-right"
                                data-toggle="modal"
                                data-target="#addCouponModal">
                                <i class="fa fa-plus"></i> Add New Coupon
                            </button>
                        </div>
                    </div>

                    <div class="clearfix"></div>

                    <!-- ================= SUMMARY CARDS ================= -->

                    <div class="row">

                        <!-- Total Coupons -->
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="x_panel tile fixed_height_160">
                                <div class="x_title">
                                    <h2>Total Coupons</h2>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="x_content text-center">
                                    <i class="fa fa-ticket fa-3x"></i>
                                    <h3>10</h3>
                                    <p>Total Coupons</p>
                                </div>
                            </div>
                        </div>


                        <!-- Active Coupons -->
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="x_panel tile fixed_height_160">
                                <div class="x_title">
                                    <h2>Active Coupons</h2>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="x_content text-center">
                                    <i class="fa fa-check-circle fa-3x"></i>
                                    <h3>7</h3>
                                    <p>Currently Active</p>
                                </div>
                            </div>
                        </div>


                        <!-- Expired Coupons -->
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="x_panel tile fixed_height_160">
                                <div class="x_title">
                                    <h2>Expired Coupons</h2>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="x_content text-center">
                                    <i class="fa fa-calendar-times-o fa-3x"></i>
                                    <h3>3</h3>
                                    <p>Expired Coupons</p>
                                </div>
                            </div>
                        </div>


                        <!-- Total Used -->
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="x_panel tile fixed_height_160">
                                <div class="x_title">
                                    <h2>Total Used</h2>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="x_content text-center">
                                    <i class="fa fa-users fa-3x"></i>
                                    <h3>35</h3>
                                    <p>Coupon Usage</p>
                                </div>
                            </div>
                        </div>

                    </div>


                    <!-- ================= ALL COUPONS ================= -->

                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">

                            <div class="x_panel">

                                <div class="x_title">

                                    <h2>All Coupons</h2>

                                    <div class="clearfix"></div>

                                </div>

                                <div class="x_content">

                                    <table id="couponTable"
                                        class="table table-striped table-bordered">

                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Coupon Code</th>
                                                <th>Discount</th>
                                                <th>Min. Order</th>
                                                <th>Max. Discount</th>
                                                <th>Validity</th>
                                                <th>Usage</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            <?php if (!empty($coupons)): ?>

                                                <?php $i = 1; ?>

                                                <?php foreach ($coupons as $coupon): ?>

                                                    <tr>

                                                        <!-- # -->
                                                        <td>
                                                            <?= $i++ ?>
                                                        </td>


                                                        <!-- Coupon Code -->
                                                        <td>
                                                            <strong>
                                                                <?= htmlspecialchars($coupon['coupon_code']) ?>
                                                            </strong>
                                                        </td>


                                                        <!-- Discount -->
                                                        <td>

                                                            <?php if ($coupon['discount_type'] === 'percentage'): ?>

                                                                <?= number_format($coupon['discount_value'], 2) ?>%

                                                            <?php else: ?>

                                                                ₹<?= number_format($coupon['discount_value'], 2) ?>

                                                            <?php endif; ?>

                                                        </td>


                                                        <!-- Minimum Order -->
                                                        <td>
                                                            ₹<?= number_format(
                                                                    $coupon['minimum_order_amount'],
                                                                    2
                                                                ) ?>
                                                        </td>


                                                        <!-- Maximum Discount -->
                                                        <td>

                                                            <?php if ($coupon['maximum_discount'] !== null): ?>

                                                                ₹<?= number_format(
                                                                        $coupon['maximum_discount'],
                                                                        2
                                                                    ) ?>

                                                            <?php else: ?>

                                                                -

                                                            <?php endif; ?>

                                                        </td>


                                                        <!-- Validity -->
                                                        <td>

                                                            <?= date(
                                                                'd-m-Y',
                                                                strtotime($coupon['start_date'])
                                                            ) ?>

                                                            <br>

                                                            <small>
                                                                to
                                                                <?= date(
                                                                    'd-m-Y',
                                                                    strtotime($coupon['end_date'])
                                                                ) ?>
                                                            </small>

                                                        </td>


                                                        <!-- Usage -->
                                                        <td>

                                                            <?php if ((int)$coupon['usage_limit'] > 0): ?>

                                                                <?= (int)$coupon['used_count'] ?>
                                                                /
                                                                <?= (int)$coupon['usage_limit'] ?>

                                                            <?php else: ?>

                                                                <?= (int)$coupon['used_count'] ?>
                                                                / Unlimited

                                                            <?php endif; ?>

                                                        </td>


                                                        <!-- Status -->
                                                        <td>

                                                            <?php
                                                            $today = date('Y-m-d');

                                                            if (
                                                                $coupon['status'] == 1 &&
                                                                $today >= $coupon['start_date'] &&
                                                                $today <= $coupon['end_date']
                                                            ):
                                                            ?>

                                                                <span class="label label-success">
                                                                    Active
                                                                </span>

                                                            <?php elseif ($coupon['end_date'] < $today): ?>

                                                                <span class="label label-danger">
                                                                    Expired
                                                                </span>

                                                            <?php else: ?>

                                                                <span class="label label-default">
                                                                    Inactive
                                                                </span>

                                                            <?php endif; ?>

                                                        </td>


                                                        <!-- Action -->
                                                        <td>

                                                            <button type="button"
                                                                class="btn btn-info btn-xs">
                                                                <i class="fa fa-eye"></i>
                                                            </button>

                                                            <button type="button"
                                                                class="btn btn-warning btn-xs">
                                                                <i class="fa fa-edit"></i>
                                                            </button>

                                                            <button type="button"
                                                                class="btn btn-danger btn-xs">
                                                                <i class="fa fa-trash"></i>
                                                            </button>

                                                        </td>

                                                    </tr>

                                                <?php endforeach; ?>

                                            <?php else: ?>

                                                <tr>
                                                    <td colspan="9" class="text-center">
                                                        No coupons found.
                                                    </td>
                                                </tr>

                                            <?php endif; ?>

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>
                    </div>


                    <!-- ================= ADD COUPON MODAL ================= -->

                    <div class="modal fade"
                        id="addCouponModal"
                        tabindex="-1"
                        role="dialog">

                        <div class="modal-dialog modal-lg"
                            role="document">

                            <div class="modal-content">

                                <div class="modal-header">

                                    <button type="button"
                                        class="close"
                                        data-dismiss="modal">
                                        &times;
                                    </button>

                                    <h4 class="modal-title">
                                        <i class="fa fa-ticket"></i>
                                        Add New Coupon
                                    </h4>

                                </div>


                                <form method="POST"
                                    action="">

                                    <div class="modal-body">

                                        <div class="row">

                                            <!-- Coupon Code -->
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>
                                                        Coupon Code
                                                    </label>

                                                    <input type="text"
                                                        name="coupon_code"
                                                        class="form-control"
                                                        placeholder="Example: WELCOME10"
                                                        required>

                                                </div>

                                            </div>


                                            <!-- Discount Type -->
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>
                                                        Discount Type
                                                    </label>

                                                    <select name="discount_type"
                                                        class="form-control"
                                                        required>

                                                        <option value="">
                                                            Select Discount Type
                                                        </option>

                                                        <option value="percentage">
                                                            Percentage
                                                        </option>

                                                        <option value="fixed">
                                                            Fixed Amount
                                                        </option>

                                                    </select>

                                                </div>

                                            </div>

                                        </div>


                                        <div class="row">

                                            <!-- Discount Value -->
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>
                                                        Discount Value
                                                    </label>

                                                    <input type="number"
                                                        name="discount_value"
                                                        class="form-control"
                                                        placeholder="Example: 10"
                                                        min="0"
                                                        step="0.01"
                                                        required>

                                                </div>

                                            </div>


                                            <!-- Minimum Order -->
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>
                                                        Minimum Order Amount
                                                    </label>

                                                    <input type="number"
                                                        name="minimum_order_amount"
                                                        class="form-control"
                                                        placeholder="Example: 500"
                                                        min="0"
                                                        step="0.01">

                                                </div>

                                            </div>

                                        </div>


                                        <div class="row">

                                            <!-- Maximum Discount -->
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>
                                                        Maximum Discount
                                                    </label>

                                                    <input type="number"
                                                        name="maximum_discount"
                                                        class="form-control"
                                                        placeholder="Example: 200"
                                                        min="0"
                                                        step="0.01">

                                                </div>

                                            </div>


                                            <!-- Usage Limit -->
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>
                                                        Usage Limit
                                                    </label>

                                                    <input type="number"
                                                        name="usage_limit"
                                                        class="form-control"
                                                        placeholder="Example: 100"
                                                        min="0">

                                                </div>

                                            </div>

                                        </div>


                                        <div class="row">

                                            <!-- Start Date -->
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>
                                                        Start Date
                                                    </label>

                                                    <input type="date"
                                                        name="start_date"
                                                        class="form-control"
                                                        required>

                                                </div>

                                            </div>


                                            <!-- End Date -->
                                            <div class="col-md-6">

                                                <div class="form-group">

                                                    <label>
                                                        End Date
                                                    </label>

                                                    <input type="date"
                                                        name="end_date"
                                                        class="form-control"
                                                        required>

                                                </div>

                                            </div>

                                        </div>


                                        <!-- Status -->

                                        <div class="form-group">

                                            <label>
                                                Status
                                            </label>

                                            <select name="status"
                                                class="form-control">

                                                <option value="1">
                                                    Active
                                                </option>

                                                <option value="0">
                                                    Inactive
                                                </option>

                                            </select>

                                        </div>

                                    </div>


                                    <div class="modal-footer">

                                        <button type="button"
                                            class="btn btn-default"
                                            data-dismiss="modal">
                                            Close
                                        </button>

                                        <button type="submit"
                                            name="save_coupon"
                                            class="btn btn-primary">
                                            <i class="fa fa-save"></i>
                                            Save Coupon
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- ================= END COUPON MANAGEMENT ================= -->
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


    <!-- jQuery -->

    <script
        src="assets/vendors/jquery/dist/jquery.min.js">
    </script>


    <!-- Bootstrap -->

    <script
        src="assets/vendors/bootstrap/dist/js/bootstrap.min.js">
    </script>


    <!-- FastClick -->

    <script
        src="assets/vendors/fastclick/lib/fastclick.js">
    </script>


    <!-- NProgress -->

    <script
        src="assets/vendors/nprogress/nprogress.js">
    </script>


    <!-- Custom -->

    <script
        src="assets/js/custom.min.js">
    </script>


    <!-- DataTables -->

    <script
        src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js">
    </script>


    <script
        src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap.min.js">
    </script>

</body>

</html>
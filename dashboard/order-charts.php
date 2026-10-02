```php
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";


/* =========================================================
   ADMIN LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    empty($_SESSION['user_id']) ||
    strtolower($_SESSION['role'] ?? '') !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}


/* =========================================================
   SUMMARY DATA
========================================================= */


/* =========================================================
   TOTAL USERS WHO HAVE CART ITEMS
========================================================= */

$stmt = $conn->query("
    SELECT COUNT(DISTINCT c.user_id)
    FROM cart c

    INNER JOIN users u
        ON u.user_id = c.user_id

    WHERE LOWER(u.role) = 'user'
");

$total_users = (int) $stmt->fetchColumn();


/* =========================================================
   TOTAL CART ITEMS
========================================================= */

$stmt = $conn->query("
    SELECT COUNT(*)
    FROM cart c

    INNER JOIN users u
        ON u.user_id = c.user_id

    WHERE LOWER(u.role) = 'user'
");

$total_cart_items = (int) $stmt->fetchColumn();


/* =========================================================
   TOTAL QUANTITY
========================================================= */

$stmt = $conn->query("
    SELECT COALESCE(SUM(c.quantity), 0)
    FROM cart c

    INNER JOIN users u
        ON u.user_id = c.user_id

    WHERE LOWER(u.role) = 'user'
");

$total_quantity = (int) $stmt->fetchColumn();


/* =========================================================
   TOTAL CART AMOUNT
========================================================= */

$stmt = $conn->query("
    SELECT COALESCE(
        SUM(c.price * c.quantity),
        0
    )
    FROM cart c

    INNER JOIN users u
        ON u.user_id = c.user_id

    WHERE LOWER(u.role) = 'user'
");

$total_cart_amount = (float) $stmt->fetchColumn();


/* =========================================================
   CART DATA
   CART ID WISE DESCENDING
========================================================= */

$cart_query = "
    SELECT

        c.cart_id,
        c.user_id,
        c.product_id,
        c.quantity,
        c.price,
        c.created_at,

        u.name,
        u.phone,

        p.product_name

    FROM cart c

    INNER JOIN users u
        ON u.user_id = c.user_id

    INNER JOIN products p
        ON p.product_id = c.product_id

    WHERE LOWER(u.role) = 'user'

    ORDER BY c.cart_id DESC
";

$cart_stmt = $conn->query($cart_query);

$cart_data = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Cart Charts</title>


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

        .cart-table th {
            white-space: nowrap;
        }

        .cart-table td {
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

                                    <a
                                        href="javascript:;"
                                        class="user-profile dropdown-toggle"
                                        data-toggle="dropdown">

                                        <img
                                            src="assets/images/img.jpg"
                                            alt="Admin">

                                        Admin

                                        <span class="fa fa-angle-down"></span>

                                    </a>


                                    <ul
                                        class="dropdown-menu dropdown-usermenu pull-right">


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


                <!-- =================================================
                 PAGE CONTENT
            ================================================== -->

                <div>


                    <!-- =================================================
                     PAGE TITLE
                ================================================== -->

                    <div class="page-title">

                        <div class="title_left">

                            <h3>

                                <i class="fa fa-shopping-cart"></i>

                                Cart Charts

                            </h3>

                        </div>

                    </div>


                    <div class="clearfix"></div>


                    <!-- =================================================
                     SUMMARY CARDS
                ================================================== -->

                    <div class="row">


                        <!-- TOTAL USERS -->

                        <div class="col-md-3 col-sm-6 col-xs-12">

                            <div class="x_panel summary-card">

                                <div class="x_title">

                                    <h2>Total Users</h2>

                                    <div class="clearfix"></div>

                                </div>


                                <div class="x_content text-center">

                                    <i class="fa fa-users summary-icon"></i>

                                    <h1 class="summary-number">

                                        <?php echo $total_users; ?>

                                    </h1>

                                    <p>

                                        Users With Cart Items

                                    </p>

                                </div>

                            </div>

                        </div>


                        <!-- TOTAL CART ITEMS -->

                        <div class="col-md-3 col-sm-6 col-xs-12">

                            <div class="x_panel summary-card">

                                <div class="x_title">

                                    <h2>Cart Items</h2>

                                    <div class="clearfix"></div>

                                </div>


                                <div class="x_content text-center">

                                    <i class="fa fa-shopping-cart summary-icon"></i>

                                    <h1 class="summary-number">

                                        <?php echo $total_cart_items; ?>

                                    </h1>

                                    <p>

                                        Products In Carts

                                    </p>

                                </div>

                            </div>

                        </div>


                        <!-- TOTAL QUANTITY -->

                        <div class="col-md-3 col-sm-6 col-xs-12">

                            <div class="x_panel summary-card">

                                <div class="x_title">

                                    <h2>Total Quantity</h2>

                                    <div class="clearfix"></div>

                                </div>


                                <div class="x_content text-center">

                                    <i class="fa fa-cubes summary-icon"></i>

                                    <h1 class="summary-number">

                                        <?php echo $total_quantity; ?>

                                    </h1>

                                    <p>

                                        Total Product Quantity

                                    </p>

                                </div>

                            </div>

                        </div>


                        <!-- TOTAL AMOUNT -->

                        <div class="col-md-3 col-sm-6 col-xs-12">

                            <div class="x_panel summary-card">

                                <div class="x_title">

                                    <h2>Cart Amount</h2>

                                    <div class="clearfix"></div>

                                </div>


                                <div class="x_content text-center">

                                    <i class="fa fa-inr summary-icon"></i>

                                    <h1 class="summary-number">

                                        ₹<?php

                                            echo number_format(
                                                $total_cart_amount,
                                                2
                                            );

                                            ?>

                                    </h1>

                                    <p>

                                        Total Cart Value

                                    </p>

                                </div>

                            </div>

                        </div>


                    </div>


                    <!-- =================================================
                     CART TABLE
                ================================================== -->

                    <div class="row">

                        <div class="col-md-12 col-sm-12 col-xs-12">

                            <div class="x_panel">


                                <div class="x_title">

                                    <h2>

                                        <i class="fa fa-shopping-cart"></i>

                                        Cart Details

                                    </h2>

                                    <div class="clearfix"></div>

                                </div>


                                <div class="x_content">


                                    <div class="table-responsive">


                                        <table
                                            id="cartTable"
                                            class="table table-striped table-bordered cart-table">


                                            <thead>

                                                <tr>

                                                    <th>#</th>

                                                    <th>Cart ID</th>

                                                    <th>User ID</th>

                                                    <th>User Name</th>

                                                    <th>Phone</th>

                                                    <th>Product ID</th>

                                                    <th>Product</th>

                                                    <th>Quantity</th>

                                                    <th>Price</th>

                                                    <th>Total</th>

                                                    <th>Cart Date</th>

                                                </tr>

                                            </thead>


                                            <tbody>


                                                <?php

                                                $count = 1;

                                                foreach ($cart_data as $cart):

                                                ?>


                                                    <tr>


                                                        <!-- SERIAL NUMBER -->

                                                        <td>

                                                            <?php echo $count++; ?>

                                                        </td>


                                                        <!-- CART ID -->

                                                        <td>

                                                            <strong>

                                                                <?php

                                                                echo (int)
                                                                $cart['cart_id'];

                                                                ?>

                                                            </strong>

                                                        </td>


                                                        <!-- USER ID -->

                                                        <td>

                                                            <?php

                                                            echo (int)
                                                            $cart['user_id'];

                                                            ?>

                                                        </td>


                                                        <!-- USER NAME -->

                                                        <td>

                                                            <strong>

                                                                <?php

                                                                echo htmlspecialchars(
                                                                    $cart['name'] ?? '',
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                );

                                                                ?>

                                                            </strong>

                                                        </td>


                                                        <!-- PHONE -->

                                                        <td>

                                                            <?php

                                                            echo htmlspecialchars(
                                                                $cart['phone'] ?? '',
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            );

                                                            ?>

                                                        </td>


                                                        <!-- PRODUCT ID -->

                                                        <td>

                                                            <?php

                                                            echo (int)
                                                            $cart['product_id'];

                                                            ?>

                                                        </td>


                                                        <!-- PRODUCT -->

                                                        <td>

                                                            <?php

                                                            echo htmlspecialchars(
                                                                $cart['product_name'] ?? '',
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            );

                                                            ?>

                                                        </td>


                                                        <!-- QUANTITY -->

                                                        <td>

                                                            <span class="label label-info">

                                                                <?php

                                                                echo (int)
                                                                $cart['quantity'];

                                                                ?>

                                                            </span>

                                                        </td>


                                                        <!-- PRICE -->

                                                        <td>

                                                            ₹<?php

                                                                echo number_format(
                                                                    (float)$cart['price'],
                                                                    2
                                                                );

                                                                ?>

                                                        </td>


                                                        <!-- TOTAL -->

                                                        <td>

                                                            <strong>

                                                                ₹<?php

                                                                    $row_total =
                                                                        (float)$cart['price']
                                                                        *
                                                                        (int)$cart['quantity'];

                                                                    echo number_format(
                                                                        $row_total,
                                                                        2
                                                                    );

                                                                    ?>

                                                            </strong>

                                                        </td>


                                                        <!-- CART DATE -->

                                                        <td>

                                                            <?php

                                                            if (!empty($cart['created_at'])) {

                                                                echo date(
                                                                    'd-m-Y H:i',
                                                                    strtotime(
                                                                        $cart['created_at']
                                                                    )
                                                                );
                                                            } else {

                                                                echo '-';
                                                            }

                                                            ?>

                                                        </td>


                                                    </tr>


                                                <?php endforeach; ?>


                                                <?php if (empty($cart_data)): ?>


                                                    <tr>

                                                        <td
                                                            colspan="11"
                                                            class="text-center">

                                                            No cart data available.

                                                        </td>

                                                    </tr>


                                                <?php endif; ?>


                                            </tbody>

                                        </table>


                                    </div>

                                </div>

                            </div>

                        </div>

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


    <script>
        $(document).ready(function() {

            $('#cartTable').DataTable({

                pageLength: 10,

                order: [
                    [1, 'desc']
                ]

            });

        });
    </script>


</body>

</html>
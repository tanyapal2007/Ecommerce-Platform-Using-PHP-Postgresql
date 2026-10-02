<?php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/database.php";


/* =========================================================
   CHECK LOGIN
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    empty($_SESSION['user_id'])
) {

    header("Location: login.php");
    exit;
}


$user_id = (int) $_SESSION['user_id'];


/* =========================================================
   GET ORDER ID
========================================================= */

$order_id = isset($_GET['order_id'])
    ? (int) $_GET['order_id']
    : 0;


if ($order_id <= 0) {

    header("Location: shop.php");
    exit;
}


/* =========================================================
   GET ORDER
========================================================= */

$order_stmt = $conn->prepare("
    SELECT
        order_id,
        user_id,
        product_id,
        quantity,
        total_amount,
        order_status,
        order_date,
        order_details
    FROM orders
    WHERE order_id = :order_id
    AND user_id = :user_id
    LIMIT 1
");

$order_stmt->execute([
    ':order_id' => $order_id,
    ':user_id'  => $user_id
]);

$order = $order_stmt->fetch(PDO::FETCH_ASSOC);


if (!$order) {

    header("Location: shop.php");
    exit;
}


/* =========================================================
   DECODE ORDER DETAILS
========================================================= */

$order_details = [];

if (!empty($order['order_details'])) {

    $decoded = json_decode(
        $order['order_details'],
        true
    );

    if (is_array($decoded)) {
        $order_details = $decoded;
    }
}


/* =========================================================
   GET ORDER ITEMS
========================================================= */

$item_stmt = $conn->prepare("
    SELECT
        oi.order_item_id,
        oi.product_id,
        oi.quantity,
        oi.price,
        oi.total,
        p.product_name,
        p.product_code
    FROM order_items oi

    LEFT JOIN products p
        ON p.product_id = oi.product_id

    WHERE oi.order_id = :order_id

    ORDER BY oi.order_item_id ASC
");

$item_stmt->execute([
    ':order_id' => $order_id
]);

$order_items =
    $item_stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   TOTALS
========================================================= */

$subtotal = isset($order_details['subtotal'])
    ? (float) $order_details['subtotal']
    : ((float) $order['total_amount'] - 3);

$shipping = isset($order_details['shipping'])
    ? (float) $order_details['shipping']
    : 3;

$grand_total =
    (float) $order['total_amount'];


/* =========================================================
   PAYMENT METHOD
========================================================= */

$payment_method =
    $order_details['payment_method'] ?? 'cod';


$payment_labels = [

    'cod' =>
    'Cash on Delivery',

    'card' =>
    'Credit / Debit Card',

    'upi' =>
    'UPI',

    'netbanking' =>
    'Net Banking'

];


$payment_name =
    $payment_labels[$payment_method]
    ?? ucfirst($payment_method);


/* =========================================================
   ADDRESS
========================================================= */

$full_name =
    $order_details['full_name']
    ?? '';

$phone =
    $order_details['phone']
    ?? '';

$address_line1 =
    $order_details['address_line1']
    ?? '';

$address_line2 =
    $order_details['address_line2']
    ?? '';

$city =
    $order_details['city']
    ?? '';

$state =
    $order_details['state']
    ?? '';

$pincode =
    $order_details['pincode']
    ?? '';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Order Confirmed</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">


    <style>
        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f5f7f6;

        }


        .success-header {

            background: #198754;

            color: white;

            padding: 60px 20px 70px;

            text-align: center;

        }


        .success-icon {

            width: 90px;

            height: 90px;

            border-radius: 50%;

            background: white;

            color: #198754;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 25px;

            font-size: 45px;

        }


        .success-header h1 {

            font-size: 34px;

            font-weight: 700;

            margin-bottom: 10px;

        }


        .success-header p {

            font-size: 17px;

            margin-bottom: 5px;

        }


        .order-number {

            font-weight: 700;

        }


        .success-container {

            max-width: 1000px;

            margin: -35px auto 50px;

            position: relative;

        }


        .card-box {

            background: white;

            border-radius: 12px;

            padding: 25px;

            margin-bottom: 20px;

            box-shadow:
                0 5px 20px rgba(0, 0, 0, 0.08);

        }


        .section-title {

            font-size: 20px;

            font-weight: 700;

            margin-bottom: 20px;

        }


        .item-row {

            border-bottom: 1px solid #eee;

            padding: 15px 0;

        }


        .item-row:last-child {

            border-bottom: none;

        }


        .item-name {

            font-weight: 600;

        }


        .total-row {

            display: flex;

            justify-content: space-between;

            margin-bottom: 12px;

        }


        .grand-total {

            font-size: 22px;

            font-weight: 700;

            color: #198754;

        }


        .address-box {

            background: #f8f9fa;

            border-radius: 8px;

            padding: 18px;

            line-height: 1.7;

        }


        .status-badge {

            display: inline-block;

            background: #d1e7dd;

            color: #0f5132;

            padding: 7px 15px;

            border-radius: 20px;

            font-weight: 600;

        }


        .btn-green {

            background: #198754;

            border-color: #198754;

            color: white;

            padding: 12px 25px;

            border-radius: 25px;

        }


        .btn-green:hover {

            background: #157347;

            color: white;

        }


        .btn-outline-green {

            border: 1px solid #198754;

            color: #198754;

            padding: 12px 25px;

            border-radius: 25px;

        }


        .btn-outline-green:hover {

            background: #198754;

            color: white;

        }
    </style>

</head>


<body>


    <!-- =====================================================
     SUCCESS HEADER
===================================================== -->

    <div class="success-header">

        <div class="success-icon">

            <i class="fa fa-check"></i>

        </div>


        <h1>
            Order Placed Successfully!
        </h1>


        <p>
            Thank you for shopping with us.
        </p>


        <p>

            Your order

            <span class="order-number">
                #<?php echo $order_id; ?>
            </span>

            has been confirmed.

        </p>

    </div>



    <!-- =====================================================
     MAIN CONTAINER
===================================================== -->

    <div class="container success-container">


        <!-- =================================================
         ORDER STATUS
    ================================================== -->

        <div class="card-box text-center">

            <div class="status-badge">

                <i class="fa fa-check-circle me-2"></i>

                Order Confirmed

            </div>

            <p class="text-muted mt-3 mb-0">

                Order Date:

                <?php
                echo date(
                    "d M Y, h:i A",
                    strtotime($order['order_date'])
                );
                ?>

            </p>

        </div>



        <div class="row">


            <!-- =============================================
             LEFT
        ============================================== -->

            <div class="col-lg-7">


                <!-- =========================================
                 ORDER ITEMS
            ========================================== -->

                <div class="card-box">

                    <div class="section-title">

                        <i class="fa fa-shopping-bag me-2 text-success"></i>

                        Order Items

                    </div>


                    <?php if (!empty($order_items)) { ?>

                        <?php foreach ($order_items as $item) { ?>

                            <div class="item-row">

                                <div class="d-flex justify-content-between">

                                    <div>

                                        <div class="item-name">

                                            <?php
                                            echo htmlspecialchars(
                                                $item['product_name']
                                                    ?? 'Product'
                                            );
                                            ?>

                                        </div>


                                        <?php if (!empty($item['product_code'])) { ?>

                                            <small class="text-muted">

                                                Product Code:
                                                <?php
                                                echo htmlspecialchars(
                                                    $item['product_code']
                                                );
                                                ?>

                                            </small>

                                            <br>

                                        <?php } ?>


                                        <small class="text-muted">

                                            Qty:
                                            <?php
                                            echo (int) $item['quantity'];
                                            ?>

                                            ×

                                            ₹<?php
                                                echo number_format(
                                                    (float) $item['price'],
                                                    2
                                                );
                                                ?>

                                        </small>

                                    </div>


                                    <strong>

                                        ₹<?php
                                            echo number_format(
                                                (float) $item['total'],
                                                2
                                            );
                                            ?>

                                    </strong>

                                </div>

                            </div>

                        <?php } ?>

                    <?php } else { ?>

                        <p class="text-muted">
                            Order items not found.
                        </p>

                    <?php } ?>

                </div>



                <!-- =========================================
                 DELIVERY ADDRESS
            ========================================== -->

                <div class="card-box">

                    <div class="section-title">

                        <i class="fa fa-map-marker-alt me-2 text-success"></i>

                        Delivery Address

                    </div>


                    <div class="address-box">

                        <strong>

                            <?php
                            echo htmlspecialchars($full_name);
                            ?>

                        </strong>


                        <br>


                        <?php
                        echo htmlspecialchars($phone);
                        ?>


                        <br>


                        <?php
                        echo htmlspecialchars($address_line1);
                        ?>


                        <?php if (!empty($address_line2)) { ?>

                            <br>

                            <?php
                            echo htmlspecialchars($address_line2);
                            ?>

                        <?php } ?>


                        <br>


                        <?php
                        echo htmlspecialchars($city);
                        ?>,


                        <?php
                        echo htmlspecialchars($state);
                        ?>


                        -

                        <?php
                        echo htmlspecialchars($pincode);
                        ?>

                    </div>

                </div>

            </div>



            <!-- =============================================
             RIGHT
        ============================================== -->

            <div class="col-lg-5">


                <!-- =========================================
                 PAYMENT
            ========================================== -->

                <div class="card-box">

                    <div class="section-title">

                        <i class="fa fa-credit-card me-2 text-success"></i>

                        Payment Details

                    </div>


                    <div class="total-row">

                        <span>
                            Payment Method
                        </span>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $payment_name
                            );
                            ?>
                        </strong>

                    </div>


                    <hr>


                    <div class="total-row">

                        <span>
                            Subtotal
                        </span>

                        <span>
                            ₹<?php
                                echo number_format(
                                    $subtotal,
                                    2
                                );
                                ?>
                        </span>

                    </div>


                    <div class="total-row">

                        <span>
                            Shipping
                        </span>

                        <span>
                            ₹<?php
                                echo number_format(
                                    $shipping,
                                    2
                                );
                                ?>
                        </span>

                    </div>


                    <hr>


                    <div class="total-row">

                        <span>
                            <strong>
                                Total Amount
                            </strong>
                        </span>

                        <span class="grand-total">

                            ₹<?php
                                echo number_format(
                                    $grand_total,
                                    2
                                );
                                ?>

                        </span>

                    </div>

                </div>



                <!-- =========================================
                 ORDER INFORMATION
            ========================================== -->

                <div class="card-box">

                    <div class="section-title">

                        <i class="fa fa-info-circle me-2 text-success"></i>

                        Order Information

                    </div>


                    <p class="mb-2">

                        <strong>
                            Order ID:
                        </strong>

                        #<?php
                            echo $order_id;
                            ?>

                    </p>


                    <p class="mb-2">

                        <strong>
                            Status:
                        </strong>

                        <span class="text-success">

                            <?php
                            echo htmlspecialchars(
                                ucfirst(
                                    $order['order_status']
                                )
                            );
                            ?>

                        </span>

                    </p>


                    <p class="mb-0">

                        <strong>
                            Total Quantity:
                        </strong>

                        <?php
                        echo (int) $order['quantity'];
                        ?>

                    </p>

                </div>

            </div>

        </div>



        <!-- =================================================
         BUTTONS
    ================================================== -->

        <div class="text-center mt-3">

            <a
                href="shop.php"
                class="btn btn-green me-2">

                <i class="fa fa-shopping-cart me-2"></i>

                Continue Shopping

            </a>


            <a
                href="orders.php"
                class="btn btn-outline-green">

                <i class="fa fa-list me-2"></i>

                My Orders

            </a>

        </div>


    </div>


</body>

</html>
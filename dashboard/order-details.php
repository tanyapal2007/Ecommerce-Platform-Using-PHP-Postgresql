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
   GET ORDER ID
========================================================= */

$order_id = isset($_GET['order_id'])
    ? (int) $_GET['order_id']
    : 0;


if ($order_id <= 0) {

    header("Location: orders.php");
    exit;
}


/* =========================================================
   GET ORDER
========================================================= */

$order_stmt = $conn->prepare("
    SELECT
        o.order_id,
        o.user_id,
        o.product_id,
        o.quantity,
        o.total_amount,
        o.order_status,
        o.order_date,
        o.order_details,

        u.name AS customer_name,
        u.phone AS customer_phone

    FROM orders o

    LEFT JOIN users u
        ON u.user_id = o.user_id

    WHERE o.order_id = :order_id

    LIMIT 1
");


$order_stmt->execute([
    ':order_id' => $order_id
]);


$order =
    $order_stmt->fetch(PDO::FETCH_ASSOC);


if (!$order) {

    header("Location: orders.php");
    exit;
}


/* =========================================================
   ORDER DETAILS JSON
========================================================= */

$order_details = [];

if (!empty($order['order_details'])) {

    $decoded =
        json_decode(
            $order['order_details'],
            true
        );

    if (is_array($decoded)) {

        $order_details =
            $decoded;
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
   PAYMENT
========================================================= */

$payment_method =
    $order_details['payment_method']
    ?? 'cod';


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
    ?? $order['customer_name']
    ?? '';

$phone =
    $order_details['phone']
    ?? $order['customer_phone']
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


$subtotal =
    isset($order_details['subtotal'])
    ? (float) $order_details['subtotal']
    : ((float) $order['total_amount'] - 3);


$shipping =
    isset($order_details['shipping'])
    ? (float) $order_details['shipping']
    : 3;


$grand_total =
    (float) $order['total_amount'];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Order Details
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <link
        rel="stylesheet"
        href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">


    <style>
        body {
            background: #f5f6f8;
        }

        .page-title {
            font-weight: 700;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        }

        .status {
            padding: 7px 14px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-delivered {
            background: #cff4fc;
            color: #055160;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .table th {
            background: #f8f9fa;
        }

        .total-box {
            font-size: 20px;
            font-weight: 700;
        }
    </style>

</head>


<body>


    <div class="container-fluid py-4">


        <!-- =================================================
         HEADER
    ================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="page-title">
                    Order Details
                </h2>

                <p class="text-muted mb-0">

                    Order #<?php echo $order_id; ?>

                </p>

            </div>


            <a
                href="orders.php"
                class="btn btn-secondary">

                <i class="fa fa-arrow-left me-2"></i>

                Back to Orders

            </a>

        </div>



        <div class="row g-4">


            <!-- =================================================
             ORDER INFORMATION
        ================================================== -->

            <div class="col-lg-8">

                <div class="card">

                    <div class="card-body">

                        <h5 class="mb-4">

                            <i class="fa fa-shopping-bag me-2"></i>

                            Ordered Products

                        </h5>


                        <div class="table-responsive">

                            <table class="table align-middle">

                                <thead>

                                    <tr>

                                        <th>
                                            Product
                                        </th>

                                        <th>
                                            Quantity
                                        </th>

                                        <th>
                                            Price
                                        </th>

                                        <th>
                                            Total
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php foreach ($order_items as $item) { ?>

                                        <tr>

                                            <td>

                                                <strong>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $item['product_name']
                                                            ?? 'Product'
                                                    );
                                                    ?>

                                                </strong>


                                                <?php if (!empty($item['product_code'])) { ?>

                                                    <br>

                                                    <small class="text-muted">

                                                        Code:
                                                        <?php
                                                        echo htmlspecialchars(
                                                            $item['product_code']
                                                        );
                                                        ?>

                                                    </small>

                                                <?php } ?>

                                            </td>


                                            <td>

                                                <?php
                                                echo (int)
                                                $item['quantity'];
                                                ?>

                                            </td>


                                            <td>

                                                ₹<?php
                                                    echo number_format(
                                                        (float) $item['price'],
                                                        2
                                                    );
                                                    ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    ₹<?php
                                                        echo number_format(
                                                            (float) $item['total'],
                                                            2
                                                        );
                                                        ?>

                                                </strong>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                 CUSTOMER
            ================================================== -->

                <div class="card mt-4">

                    <div class="card-body">

                        <h5 class="mb-4">

                            <i class="fa fa-user me-2"></i>

                            Customer Details

                        </h5>


                        <p>

                            <strong>
                                Name:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $full_name
                            );
                            ?>

                        </p>


                        <p>

                            <strong>
                                Phone:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $phone
                            );
                            ?>

                        </p>


                        <p class="mb-0">

                            <strong>
                                User ID:
                            </strong>

                            <?php
                            echo (int)
                            $order['user_id'];
                            ?>

                        </p>

                    </div>

                </div>



                <!-- =================================================
                 DELIVERY ADDRESS
            ================================================== -->

                <div class="card mt-4">

                    <div class="card-body">

                        <h5 class="mb-4">

                            <i class="fa fa-map-marker-alt me-2"></i>

                            Delivery Address

                        </h5>


                        <p class="mb-1">

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $full_name
                                );
                                ?>

                            </strong>

                        </p>


                        <p class="mb-1">

                            <?php
                            echo htmlspecialchars(
                                $address_line1
                            );
                            ?>

                        </p>


                        <?php if (!empty($address_line2)) { ?>

                            <p class="mb-1">

                                <?php
                                echo htmlspecialchars(
                                    $address_line2
                                );
                                ?>

                            </p>

                        <?php } ?>


                        <p class="mb-1">

                            <?php
                            echo htmlspecialchars(
                                $city
                            );
                            ?>,

                            <?php
                            echo htmlspecialchars(
                                $state
                            );
                            ?>

                            -

                            <?php
                            echo htmlspecialchars(
                                $pincode
                            );
                            ?>

                        </p>


                        <p class="mb-0">

                            <strong>
                                Phone:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $phone
                            );
                            ?>

                        </p>

                    </div>

                </div>

            </div>



            <!-- =================================================
             RIGHT SIDE
        ================================================== -->

            <div class="col-lg-4">


                <!-- ORDER STATUS -->

                <div class="card">

                    <div class="card-body">

                        <h5 class="mb-4">
                            Order Information
                        </h5>


                        <p>

                            <strong>
                                Order ID:
                            </strong>

                            #<?php
                                echo $order_id;
                                ?>

                        </p>


                        <p>

                            <strong>
                                Date:
                            </strong>

                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime(
                                    $order['order_date']
                                )
                            );
                            ?>

                        </p>


                        <p>

                            <strong>
                                Status:
                            </strong>

                            <br>


                            <span
                                class="status status-<?php echo htmlspecialchars(strtolower($order['order_status'])); ?>">

                                <?php
                                echo htmlspecialchars(
                                    ucfirst(
                                        $order['order_status']
                                    )
                                );
                                ?>

                            </span>

                        </p>


                        <p>

                            <strong>
                                Payment:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $payment_name
                            );
                            ?>

                        </p>

                    </div>

                </div>



                <!-- TOTAL -->

                <div class="card mt-4">

                    <div class="card-body">

                        <h5 class="mb-4">
                            Payment Summary
                        </h5>


                        <div class="d-flex justify-content-between mb-3">

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


                        <div class="d-flex justify-content-between mb-3">

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


                        <div class="d-flex justify-content-between total-box">

                            <span>
                                Total
                            </span>

                            <span class="text-success">

                                ₹<?php
                                    echo number_format(
                                        $grand_total,
                                        2
                                    );
                                    ?>

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


</body>

</html>
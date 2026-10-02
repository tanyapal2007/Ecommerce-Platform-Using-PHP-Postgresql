<?php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/database.php";


/* =========================================
   CHECK LOGIN
========================================= */

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];


/* =========================================
   GET USER DETAILS
========================================= */

$user_sql = "
    SELECT
        user_id,
        name,
        phone
    FROM users
    WHERE user_id = :user_id
    LIMIT 1
";

$user_stmt = $conn->prepare($user_sql);

$user_stmt->execute([
    ':user_id' => $user_id
]);

$customer = $user_stmt->fetch(PDO::FETCH_ASSOC);


if (!$customer) {
    header("Location: login.php");
    exit;
}


/* =========================================
   GET DEFAULT ADDRESS
========================================= */

$address_sql = "
    SELECT
        address_id,
        address_type,
        full_name,
        phone,
        address_line1,
        address_line2,
        city,
        state,
        pincode,
        is_default
    FROM user_address
    WHERE user_id = :user_id
    ORDER BY is_default DESC, address_id DESC
";

$address_stmt = $conn->prepare($address_sql);

$address_stmt->execute([
    ':user_id' => $user_id
]);

$addresses = $address_stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================
   GET CART PRODUCTS
========================================= */

$cart_sql = "
    SELECT
        c.cart_id,
        c.product_id,
        c.quantity,

        p.product_name,
        p.product_code,
        p.stock_quantity

    FROM cart c

    INNER JOIN products p
        ON p.product_id = c.product_id

    WHERE c.user_id = :user_id

    ORDER BY c.cart_id DESC
";

$cart_stmt = $conn->prepare($cart_sql);

$cart_stmt->execute([
    ':user_id' => $user_id
]);

$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================
   CHECK CART
========================================= */

if (empty($cart_items)) {
    header("Location: cart.php");
    exit;
}


/* =========================================
   CALCULATE CART TOTAL
========================================= */

$subtotal = 0;

foreach ($cart_items as &$item) {

    $price_sql = "
        SELECT selling_price
        FROM product_prices
        WHERE product_id = :product_id
        AND status = 1
        ORDER BY price_id DESC
        LIMIT 1
    ";

    $price_stmt = $conn->prepare($price_sql);

    $price_stmt->execute([
        ':product_id' => $item['product_id']
    ]);

    $price_row = $price_stmt->fetch(PDO::FETCH_ASSOC);

    $item['price'] = $price_row
        ? (float) $price_row['selling_price']
        : 0;

    $item['item_total'] =
        $item['price'] * (int) $item['quantity'];

    $subtotal += $item['item_total'];
}

unset($item);


$shipping = ($subtotal > 0) ? 3 : 0;

$grand_total = $subtotal + $shipping;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Electro - Electronics Website Template</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="assets/lib/animate/animate.min.css" rel="stylesheet">
    <link href="assets/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">


    <!-- Customized Bootstrap Stylesheet -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <?php include 'header.php'; ?>

    <!-- Single Page Header start -->
    <div class="container-fluid page-header py-5">
        <h1 class="text-center text-white display-6 wow fadeInUp" data-wow-delay="0.1s">Cart Page</h1>
        <ol class="breadcrumb justify-content-center mb-0 wow fadeInUp" data-wow-delay="0.3s">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Pages</a></li>
            <li class="breadcrumb-item active text-white">Cart Page</li>
        </ol>
    </div>
    <!-- Single Page Header End -->


    <!-- =========================================
     CHECKOUT PAGE START
========================================= -->

    <div class="container-fluid py-5">

        <div class="container py-5">

            <div class="row g-5">


                <!-- =====================================
                 LEFT SIDE
            ====================================== -->

                <div class="col-md-12 col-lg-7 col-xl-7">


                    <!-- =====================================
                     DELIVERY ADDRESS
                ====================================== -->

                    <div class="bg-light rounded p-4 mb-4">

                        <h4 class="mb-4">

                            <i class="fa fa-map-marker-alt text-primary me-2"></i>

                            Delivery Address

                        </h4>


                        <?php if (!empty($addresses)) { ?>

                            <?php foreach ($addresses as $index => $address) { ?>

                                <div class="border rounded p-3 mb-3 bg-white">

                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="address_id"
                                            value="<?php echo $address['address_id']; ?>"
                                            id="address_<?php echo $address['address_id']; ?>"
                                            <?php echo ($index === 0) ? 'checked' : ''; ?>>


                                        <label
                                            class="form-check-label w-100"
                                            for="address_<?php echo $address['address_id']; ?>">

                                            <div class="d-flex justify-content-between">

                                                <strong>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $customer['name']
                                                    );
                                                    ?>

                                                </strong>


                                                <span class="badge bg-primary">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        ucfirst(
                                                            $address['address_type']
                                                        )
                                                    );
                                                    ?>

                                                </span>

                                            </div>


                                            <div class="mt-2">

                                                <?php
                                                echo htmlspecialchars(
                                                    $customer['phone']
                                                );
                                                ?>

                                            </div>


                                            <div>

                                                <?php
                                                echo htmlspecialchars(
                                                    $address['address_line1']
                                                );
                                                ?>

                                                <?php if (!empty($address['address_line2'])) { ?>

                                                    <br>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $address['address_line2']
                                                    );
                                                    ?>

                                                <?php } ?>

                                            </div>


                                            <div>

                                                <?php
                                                echo htmlspecialchars(
                                                    $address['city']
                                                );
                                                ?>,

                                                <?php
                                                echo htmlspecialchars(
                                                    $address['state']
                                                ); ?>

                                                -

                                                <?php
                                                echo htmlspecialchars(
                                                    $address['pincode']
                                                );
                                                ?>

                                            </div>

                                        </label>

                                    </div>

                                </div>

                            <?php } ?>


                            <a
                                href="add-address.php"
                                class="btn btn-outline-primary">

                                <i class="fa fa-plus me-2"></i>

                                Add New Address

                            </a>


                        <?php } else { ?>


                            <!-- NO ADDRESS -->

                            <div class="text-center py-4">

                                <i
                                    class="fa fa-map-marker-alt"
                                    style="
                                    font-size:45px;
                                    color:#999;
                                "></i>

                                <h5 class="mt-3">
                                    No Address Found
                                </h5>

                                <p class="text-muted">
                                    Please add a delivery address.
                                </p>


                                <a
                                    href="add-address.php"
                                    class="btn btn-primary rounded-pill px-4">

                                    <i class="fa fa-plus me-2"></i>

                                    Add Address

                                </a>

                            </div>

                        <?php } ?>

                    </div>


                    <!-- =====================================
                     PAYMENT METHOD
                ====================================== -->

                    <div class="bg-light rounded p-4">

                        <h4 class="mb-4">

                            <i class="fa fa-credit-card text-primary me-2"></i>

                            Payment Method

                        </h4>


                        <!-- COD -->

                        <div class="border rounded p-3 mb-3 bg-white">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    id="payment_cod"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="payment_cod">

                                    <strong>
                                        Cash on Delivery
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        Pay when your order is delivered.
                                    </small>

                                </label>

                            </div>

                        </div>


                        <!-- CARD -->

                        <div class="border rounded p-3 mb-3 bg-white">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="payment_method"
                                    value="card"
                                    id="payment_card">

                                <label
                                    class="form-check-label"
                                    for="payment_card">

                                    <strong>
                                        Credit / Debit Card
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        Pay securely using your card.
                                    </small>

                                </label>

                            </div>

                        </div>


                        <!-- UPI -->

                        <div class="border rounded p-3 mb-3 bg-white">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="payment_method"
                                    value="upi"
                                    id="payment_upi">

                                <label
                                    class="form-check-label"
                                    for="payment_upi">

                                    <strong>
                                        UPI
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        Pay using UPI.
                                    </small>

                                </label>

                            </div>

                        </div>


                        <!-- NET BANKING -->

                        <div class="border rounded p-3 bg-white">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="payment_method"
                                    value="netbanking"
                                    id="payment_netbanking">

                                <label
                                    class="form-check-label"
                                    for="payment_netbanking">

                                    <strong>
                                        Net Banking
                                    </strong>

                                    <br>

                                    <small class="text-muted">
                                        Pay using Internet Banking.
                                    </small>

                                </label>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =====================================
                 RIGHT SIDE - ORDER SUMMARY
            ====================================== -->

                <div class="col-md-12 col-lg-5 col-xl-5">

                    <div class="bg-light rounded p-4">

                        <h4 class="mb-4">

                            <i class="fa fa-shopping-cart text-primary me-2"></i>

                            Order Summary

                        </h4>


                        <!-- PRODUCTS -->

                        <?php foreach ($cart_items as $item) {

                            if (!is_array($item)) {
                                continue;
                            } ?>

                            <div
                                class="d-flex justify-content-between
                                   align-items-center
                                   border-bottom pb-3 mb-3">

                                <div>

                                    <h6 class="mb-1">

                                        <?php
                                        echo htmlspecialchars(
                                            $item['product_name']
                                        );
                                        ?>

                                    </h6>


                                    <small class="text-muted">

                                        Qty:
                                        <?php
                                        echo (int) $item['quantity'];
                                        ?>

                                        ×

                                        ₹<?php
                                            echo number_format(
                                                $item['price'],
                                                2
                                            );
                                            ?>

                                    </small>

                                </div>


                                <strong>

                                    ₹<?php
                                        echo number_format(
                                            $item['item_total'],
                                            2
                                        );
                                        ?>

                                </strong>

                            </div>

                        <?php } ?>


                        <!-- SUBTOTAL -->

                        <div class="d-flex justify-content-between mb-3">

                            <h6 class="mb-0">
                                Subtotal
                            </h6>

                            <span>
                                ₹<?php
                                    echo number_format(
                                        $subtotal,
                                        2
                                    );
                                    ?>
                            </span>

                        </div>


                        <!-- SHIPPING -->

                        <div class="d-flex justify-content-between mb-3">

                            <h6 class="mb-0">
                                Shipping
                            </h6>

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


                        <!-- GRAND TOTAL -->

                        <div class="d-flex justify-content-between mb-4">

                            <h5 class="mb-0">
                                Total
                            </h5>

                            <h5 class="text-primary mb-0">

                                ₹<?php
                                    echo number_format(
                                        $grand_total,
                                        2
                                    );
                                    ?>

                            </h5>

                        </div>


                        <!-- CUSTOMER -->

                        <!-- CUSTOMER -->

                        <div class="border rounded p-3 bg-white mb-4">

                            <h6>
                                Customer
                            </h6>

                            <p class="mb-1">

                                <i class="fa fa-user me-2"></i>

                                <?php
                                echo htmlspecialchars(
                                    $customer['name']
                                );
                                ?>

                            </p>


                            <p class="mb-0">

                                <i class="fa fa-phone me-2"></i>

                                <?php
                                echo htmlspecialchars(
                                    $customer['phone']
                                );
                                ?>

                            </p>

                        </div>

                        <!-- PLACE ORDER -->
                        <button type="button" id="placeOrderBtn" class="btn btn-primary rounded-pill w-100 py-3">
                            <i class="fa fa-check me-2"></i>
                            Place Order
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- =========================================
     CHECKOUT PAGE END
========================================= -->





    <?php include 'footer.php'; ?>


    <!-- Copyright Start -->
    <div class="container-fluid copyright py-4">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-md-6 text-center text-md-start mb-md-0">
                    <span class="text-white"><a href="#" class="border-bottom text-white"><i
                                class="fas fa-copyright text-light me-2"></i>Your Site Name</a>, All right
                        reserved.</span>
                </div>
                <div class="col-md-6 text-center text-md-end text-white">

                    <!--/*** This template is free as long as you keep the below author’s credit link/attribution link/backlink. ***/-->
                    <!--/*** If you'd like to use the template without the below author’s credit link/attribution link/backlink, ***/-->
                    <!--/*** you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". ***/-->
                    Designed By <a class="border-bottom text-white" href="https://htmlcodex.com">HTML Codex</a>.
                    Distributed By <a class="border-bottom text-white" href="https://themewagon.com">ThemeWagon</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Copyright End -->

    <!-- ================= LOGIN POPUP ================= -->

    <div id="loginPopup" class="login-popup-overlay">

        <div class="login-popup-box">

            <button type="button"
                class="login-popup-close"
                onclick="closeLoginPopup()">
                &times;
            </button>

            <div class="login-popup-icon">
                <i class="fa fa-user"></i>
            </div>

            <h3>Login Required</h3>

            <p>
                Please login to your account before adding products to your cart.
            </p>

            <button type="button"
                class="login-popup-login-btn"
                onclick="goToLogin()">
                <i class="fa fa-sign-in-alt me-2"></i>
                Login Now
            </button>

            <button type="button"
                class="login-popup-cancel-btn"
                onclick="closeLoginPopup()">
                Continue Shopping
            </button>

        </div>

    </div>
    <!-- Back to Top -->
    <a href="#" class="btn btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/lib/wow/wow.min.js"></script>
    <script src="assets/lib/owlcarousel/owl.carousel.min.js"></script>


    <!-- Template Javascript -->
    <script src="assets/js/main.js"></script>


    ```html
    <script>
        document.getElementById("placeOrderBtn").addEventListener("click", function() {

            const button = this;


            const selectedAddress =
                document.querySelector(
                    'input[name="address_id"]:checked'
                );


            if (!selectedAddress) {

                alert("Please select a delivery address.");

                return;
            }


            const selectedPayment =
                document.querySelector(
                    'input[name="payment_method"]:checked'
                );


            if (!selectedPayment) {

                alert("Please select a payment method.");

                return;
            }


            const formData = new FormData();

            formData.append(
                "address_id",
                selectedAddress.value
            );

            formData.append(
                "payment_method",
                selectedPayment.value
            );


            button.disabled = true;

            button.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Placing Order...
    `;


            fetch("place-order.php", {

                    method: "POST",

                    body: formData

                })

                .then(response => response.text())

                .then(responseText => {

                    console.log(
                        "SERVER RESPONSE:",
                        responseText
                    );


                    let data;


                    try {

                        data =
                            JSON.parse(responseText);

                    } catch (error) {

                        console.error(
                            "JSON ERROR:",
                            error
                        );

                        console.error(
                            "SERVER RESPONSE:",
                            responseText
                        );


                        alert(
                            "Server response is not valid JSON.\n\n" +
                            responseText.substring(0, 500)
                        );


                        button.disabled = false;

                        button.innerHTML = `
                <i class="fa fa-check me-2"></i>
                Place Order
            `;

                        return;
                    }


                    console.log(
                        "ORDER DATA:",
                        data
                    );


                    /* =====================================
                       SUCCESS
                    ===================================== */

                    if (data.success === true) {

                        window.location.href =
                            "order-success.php?order_id=" +
                            encodeURIComponent(
                                data.order_id
                            );

                        return;
                    }


                    /* =====================================
                       ACTUAL ERROR
                    ===================================== */

                    alert(
                        "Order failed:\n\n" +
                        (
                            data.message ||
                            "Unknown error"
                        )
                    );


                    button.disabled = false;

                    button.innerHTML = `
            <i class="fa fa-check me-2"></i>
            Place Order
        `;

                })

                .catch(error => {

                    console.error(
                        "FETCH ERROR:",
                        error
                    );


                    alert(
                        "Request failed:\n\n" +
                        error.message
                    );


                    button.disabled = false;

        //             button.innerHTML = `
        //     <i class="fa fa-check me-2"></i>
        //     Place Order
        // `;

                });

        });
    </script>
    

</body>

</html>
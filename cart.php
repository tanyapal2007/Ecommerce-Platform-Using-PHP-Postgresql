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
   AJAX ACTIONS
========================================= */

$action = $_GET['action'] ?? '';
$product_id = isset($_GET['product_id'])
    ? (int) $_GET['product_id']
    : 0;


/* =========================================
   INCREASE QUANTITY
========================================= */
/* =========================================
   INCREASE QUANTITY
========================================= */

if ($action === 'increase' && $product_id > 0) {

    /* Get current quantity */

    $sql = "
        SELECT quantity
        FROM cart
        WHERE user_id = :user_id
          AND product_id = :product_id
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $product_id
    ]);

    $item = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$item) {

        echo json_encode([
            'success' => false,
            'message' => 'Product not found in cart'
        ]);

        exit;
    }


    /* Increase quantity */

    $new_quantity = (int)$item['quantity'] + 1;


    $update_sql = "
        UPDATE cart
        SET
            quantity = :quantity,
            updated_at = CURRENT_TIMESTAMP
        WHERE user_id = :user_id
          AND product_id = :product_id
    ";

    $update_stmt = $conn->prepare($update_sql);

    $update_stmt->execute([
        ':quantity' => $new_quantity,
        ':user_id' => $user_id,
        ':product_id' => $product_id
    ]);


    echo json_encode([
        'success' => true,
        'quantity' => $new_quantity
    ]);

    exit;
}

/* =========================================
   DECREASE QUANTITY
========================================= */

/* =========================================
   DECREASE QUANTITY
========================================= */

if ($action === 'decrease' && $product_id > 0) {

    /* Get current quantity */

    $sql = "
        SELECT quantity
        FROM cart
        WHERE user_id = :user_id
          AND product_id = :product_id
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $product_id
    ]);

    $item = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$item) {

        echo json_encode([
            'success' => false,
            'message' => 'Product not found in cart'
        ]);

        exit;
    }


    $current_quantity = (int)$item['quantity'];


    /* If quantity is 1, remove product */

    if ($current_quantity <= 1) {

        $delete_sql = "
            DELETE FROM cart
            WHERE user_id = :user_id
              AND product_id = :product_id
        ";

        $delete_stmt = $conn->prepare($delete_sql);

        $delete_stmt->execute([
            ':user_id' => $user_id,
            ':product_id' => $product_id
        ]);


        echo json_encode([
            'success' => true,
            'removed' => true,
            'quantity' => 0
        ]);

        exit;
    }


    /* Decrease quantity */

    $new_quantity = $current_quantity - 1;


    $update_sql = "
        UPDATE cart
        SET
            quantity = :quantity,
            updated_at = CURRENT_TIMESTAMP
        WHERE user_id = :user_id
          AND product_id = :product_id
    ";

    $update_stmt = $conn->prepare($update_sql);

    $update_stmt->execute([
        ':quantity' => $new_quantity,
        ':user_id' => $user_id,
        ':product_id' => $product_id
    ]);


    echo json_encode([
        'success' => true,
        'quantity' => $new_quantity
    ]);

    exit;
}
/* =========================================
   REMOVE PRODUCT
========================================= */

if ($action === 'remove' && $product_id > 0) {

    $sql = "
        DELETE FROM cart
        WHERE user_id = :user_id
          AND product_id = :product_id
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $product_id
    ]);


    echo json_encode([
        'success' => true,
        'removed' => true,
        'product_id' => $product_id
    ]);

    exit;
}


/* =========================================
   GET CART PRODUCTS FROM DATABASE
========================================= */

$cart_sql = "
    SELECT
        c.cart_id,
        c.product_id,
        c.quantity,
        c.price,

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
   CALCULATE TOTAL
========================================= */

$subtotal = 0;

foreach ($cart_items as $item) {

    $quantity = (int)$item['quantity'];

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

    $unit_price = $price_row
        ? (float)$price_row['selling_price']
        : 0;

    $subtotal += $unit_price * $quantity;
}

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

    <!-- Cart Page Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Model</th>
                            <th scope="col">Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Total</th>
                            <th scope="col">Handle</th>
                        </tr>
                    </thead>


                    <?php if (!empty($cart_items)) { ?>

                        <?php foreach ($cart_items as $item) { ?>

                            <?php
                            $quantity = (int)$item['quantity'];

                            $unit_price_sql = "
    SELECT selling_price
    FROM product_prices
    WHERE product_id = :product_id
      AND status = 1
    ORDER BY price_id DESC
    LIMIT 1
";

                            $unit_price_stmt = $conn->prepare($unit_price_sql);

                            $unit_price_stmt->execute([
                                ':product_id' => $item['product_id']
                            ]);

                            $unit_price_row = $unit_price_stmt->fetch(PDO::FETCH_ASSOC);

                            $price = $unit_price_row
                                ? (float)$unit_price_row['selling_price']
                                : 0;

                            $item_total = $price * $quantity;
                            ?>

                            <tr class="cart-row" data-product-id="<?php echo $item['product_id']; ?>">

                                <th scope="row">
                                    <p class="mb-0 py-4">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </p>
                                </th>

                                <td>
                                    <p class="mb-0 py-4">
                                        <?php echo htmlspecialchars($item['product_code']); ?>
                                    </p>
                                </td>

                                <td>
                                    <p
                                        class="mb-0 py-4 product-price"
                                        data-price="<?php echo $price; ?>">
                                        ₹<?php echo number_format($price, 2); ?>
                                    </p>
                                </td>

                                <td>
                                    <div class="input-group quantity py-4" style="width: 120px;">

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-minus rounded-circle bg-light border quantity-btn"
                                            data-action="decrease"
                                            data-product-id="<?php echo $item['product_id']; ?>">
                                            <i class="fa fa-minus"></i>
                                        </button>

                                        <input type="text"
                                            class="form-control form-control-sm text-center border-0 quantity-input"
                                            value="<?php echo $quantity; ?>"
                                            readonly>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-plus rounded-circle bg-light border quantity-btn"
                                            data-action="increase"
                                            data-product-id="<?php echo $item['product_id']; ?>">
                                            <i class="fa fa-plus"></i>
                                        </button>

                                    </div>
                                </td>

                                <td>
                                    <p class="mb-0 py-4 item-total">
                                        ₹<?php echo number_format($item_total, 2); ?>
                                    </p>
                                </td>

                                <td class="py-4">
                                    <button
                                        type="button"
                                        class="btn btn-md rounded-circle bg-light border remove-cart"
                                        data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fa fa-times text-danger"></i>
                                    </button>
                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <h4>Your cart is empty.</h4>

                                <a href="shop.php"
                                    class="btn btn-primary rounded-pill px-4 py-2 mt-3">
                                    Continue Shopping
                                </a>
                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>
                </table>
            </div>
            <div class="mt-5">
                <input type="text" class="border-0 border-bottom rounded me-5 py-3 mb-4" placeholder="Coupon Code">
                <button class="btn btn-primary rounded-pill px-4 py-3" type="button">Apply Coupon</button>
            </div>
            <div class="row g-4 justify-content-end">
                <div class="col-8"></div>
                <div class="col-sm-8 col-md-7 col-lg-6 col-xl-4">
                    <div class="bg-light rounded">
                        <div class="p-4">
                            <h1 class="display-6 mb-4">Cart <span class="fw-normal">Total</span></h1>
                            <div class="d-flex justify-content-between mb-4">
                                <h5 class="mb-0 me-4">Subtotal:</h5>
                                <p class="mb-0" id="subtotal">
                                    ₹<?php echo number_format($subtotal, 2); ?>
                                </p>
                            </div>
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-0 me-4">Shipping</h5>
                                <div>
                                    <p class="mb-0" id="shipping">
                                        Flat rate: ₹<?php echo number_format($shipping, 2); ?>
                                    </p>
                                </div>
                            </div>
                            <p class="mb-0 text-end">Shipping to Ukraine.</p>
                        </div>
                        <div class="py-4 mb-4 border-top border-bottom d-flex justify-content-between">
                            <h5 class="mb-0 ps-4 me-4">Total</h5>
                            <p class="mb-0 pe-4" id="grand-total">
                                ₹<?php echo number_format($grand_total, 2); ?>
                            </p>
                        </div>
                        <a class="btn btn-primary rounded-pill px-4 py-3 text-uppercase mb-4 ms-4"
                            href="checkout.php">Proceed Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cart Page End -->

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
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            /* =====================================================
               QUANTITY + / -
            ===================================================== */

            document.querySelectorAll(".quantity-btn").forEach(function(button) {

                button.addEventListener("click", function() {

                    const buttonElement = this;

                    const action = buttonElement.dataset.action;
                    const productId = buttonElement.dataset.productId;

                    const row = buttonElement.closest(".cart-row");

                    const quantityInput =
                        row.querySelector(".quantity-input");

                    const priceElement =
                        row.querySelector(".product-price");

                    const itemTotalElement =
                        row.querySelector(".item-total");


                    /* Prevent double click */

                    buttonElement.disabled = true;


                    /* Call backend */

                    fetch(
                            "config/backend_cart.php?action=" +
                            action +
                            "&product_id=" +
                            productId
                        )
                        .then(response => response.json())
                        .then(data => {

                            console.log("Cart Response:", data);


                            buttonElement.disabled = false;


                            /* Error */

                            if (!data.success) {

                                alert(
                                    data.message ||
                                    "Unable to update cart."
                                );

                                return;
                            }


                            /* =================================================
                               PRODUCT REMOVED
                            ================================================= */

                            if (data.removed) {

                                row.remove();

                                updateCartTotals();

                                checkEmptyCart();

                                return;
                            }


                            /* =================================================
                               UPDATE QUANTITY
                            ================================================= */

                            const quantity =
                                parseInt(data.quantity);

                            quantityInput.value = quantity;


                            /* =================================================
                               GET UNIT PRICE
                            ================================================= */

                            const unitPrice =
                                parseFloat(
                                    priceElement.dataset.price
                                );


                            /* =================================================
                               UPDATE ITEM TOTAL
                            ================================================= */

                            const itemTotal =
                                unitPrice * quantity;

                            itemTotalElement.innerHTML =
                                "₹" + itemTotal.toFixed(2);


                            /* =================================================
                               UPDATE CART TOTALS
                            ================================================= */

                            updateCartTotals();

                        })
                        .catch(error => {

                            buttonElement.disabled = false;

                            console.error(
                                "Cart Quantity Error:",
                                error
                            );

                            alert(
                                "Unable to update quantity."
                            );

                        });

                });

            });


            /* =====================================================
               REMOVE PRODUCT
            ===================================================== */

            document.querySelectorAll(".remove-cart").forEach(function(button) {

                button.addEventListener("click", function() {

                    const buttonElement = this;

                    const productId =
                        buttonElement.dataset.productId;

                    const row =
                        buttonElement.closest(".cart-row");


                    /* Prevent double click */

                    buttonElement.disabled = true;


                    /* Call backend remove */

                    fetch(
                            "config/backend_cart.php?action=remove&product_id=" +
                            productId
                        )
                        .then(response => response.json())
                        .then(data => {

                            console.log(
                                "Remove Cart Response:",
                                data
                            );


                            buttonElement.disabled = false;


                            /* Error */

                            if (!data.success) {

                                alert(
                                    data.message ||
                                    "Unable to remove product."
                                );

                                return;
                            }


                            /* =================================================
                               PRODUCT REMOVED SUCCESSFULLY
                            ================================================= */

                            if (data.removed) {

                                /* Remove row from page */

                                row.remove();


                                /* Update totals */

                                updateCartTotals();


                                /* Check empty cart */

                                checkEmptyCart();

                            }

                        })
                        .catch(error => {

                            buttonElement.disabled = false;

                            console.error(
                                "Remove Cart Error:",
                                error
                            );

                            alert(
                                "Unable to remove product."
                            );

                        });

                });

            });


            /* =====================================================
               UPDATE CART TOTALS
            ===================================================== */

            function updateCartTotals() {

                let subtotal = 0;


                document.querySelectorAll(".cart-row")
                    .forEach(function(cartRow) {

                        const quantityInput =
                            cartRow.querySelector(".quantity-input");

                        const priceElement =
                            cartRow.querySelector(".product-price");


                        if (!quantityInput || !priceElement) {
                            return;
                        }


                        const quantity =
                            parseInt(quantityInput.value) || 0;


                        const price =
                            parseFloat(
                                priceElement.dataset.price
                            ) || 0;


                        subtotal +=
                            price * quantity;

                    });


                /* =================================================
                   SUBTOTAL
                ================================================= */

                document.getElementById("subtotal")
                    .innerHTML =
                    "₹" + subtotal.toFixed(2);


                /* =================================================
                   SHIPPING
                ================================================= */

                const shipping =
                    subtotal > 0 ? 3 : 0;


                document.getElementById("shipping")
                    .innerHTML =
                    "Flat rate: ₹" +
                    shipping.toFixed(2);


                /* =================================================
                   GRAND TOTAL
                ================================================= */

                const grandTotal =
                    subtotal + shipping;


                document.getElementById("grand-total")
                    .innerHTML =
                    "₹" + grandTotal.toFixed(2);

            }


            /* =====================================================
               CHECK EMPTY CART
            ===================================================== */

            function checkEmptyCart() {

                const rows =
                    document.querySelectorAll(".cart-row");


                if (rows.length === 0) {

                    location.reload();

                }

            }

        });


        /* =========================================================
           SHOW LOGIN POPUP
        ========================================================= */

        function showLoginPopup() {

            const popup =
                document.getElementById("loginPopup");

            if (popup) {

                popup.style.display = "flex";

                document.body.style.overflow = "hidden";

            }

        }


        /* =========================================================
           CLOSE LOGIN POPUP
        ========================================================= */

        function closeLoginPopup() {

            const popup =
                document.getElementById("loginPopup");

            if (popup) {

                popup.style.display = "none";

                document.body.style.overflow = "";

            }

        }


        /* =========================================================
           GO TO LOGIN PAGE
        ========================================================= */

        function goToLogin() {

            window.location.href = "login.php";

        }


        /* =========================================================
           CLICK OUTSIDE LOGIN POPUP
        ========================================================= */

        document
            .getElementById("loginPopup")
            ?.addEventListener("click", function(event) {

                if (event.target === this) {

                    closeLoginPopup();

                }

            });
    </script>
</body>

</html>
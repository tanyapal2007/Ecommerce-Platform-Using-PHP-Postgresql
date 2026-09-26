<?php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/config/database.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


/* =========================================
   ADD PRODUCT TO CART - AJAX
========================================= */

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'add' &&
    isset($_GET['product_id'])
) {

    $product_id = (int) $_GET['product_id'];

    if ($product_id > 0) {

        /* GET PRODUCT */

        $sql = "
            SELECT
                p.product_id,
                p.product_name,
                p.product_code,
                p.stock_quantity
            FROM products p
            WHERE p.product_id = :product_id
              AND p.status = 1
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':product_id' => $product_id
        ]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($product) {

            /* GET SELLING PRICE */

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
                ':product_id' => $product_id
            ]);

            $price_row = $price_stmt->fetch(PDO::FETCH_ASSOC);

            $price = 0;

            if ($price_row) {
                $price = (float) $price_row['selling_price'];
            }

            //         /* INSERT PRODUCT INTO CART TABLE */

            //         $cart_sql = "
            //             INSERT INTO cart
            //             (
            //                 product_id,
            //                 product_name,
            //                 product_code,
            //                 price,
            //                 quantity
            //             )
            //             VALUES
            //             (
            //                 :product_id,
            //                 :product_name,
            //                 :product_code,
            //                 :price,
            //                 :quantity
            //             )
            //         ";

            //         $cart_stmt = $conn->prepare($cart_sql);

            //         $cart_stmt->execute([
            //             ':product_id'   => $product['product_id'],
            //             ':product_name' => $product['product_name'],
            //             ':product_code' => $product['product_code'],
            //             ':price'        => $price,
            //             ':quantity'     => 1
            //         ]);
            //         /* IF PRODUCT ALREADY EXISTS */

            //         if (isset($_SESSION['cart'][$product_id])) {

            //             $_SESSION['cart'][$product_id]['quantity']++;
            //         } else {

            //             /* ADD NEW PRODUCT */

            //             $_SESSION['cart'][$product_id] = [
            //                 'product_id'   => $product['product_id'],
            //                 'product_name' => $product['product_name'],
            //                 'product_code' => $product['product_code'],
            //                 'price'        => $price,
            //                 'quantity'     => 1
            //             ];
            //         }
        }
    }

    ob_clean();

    /* AJAX RESPONSE */

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
        'product_id' => $product_id,
        'cart_count' => array_sum(
            array_column($_SESSION['cart'], 'quantity')
        )
    ]);

    exit;
}


/* =========================================
   INCREASE QUANTITY - AJAX
========================================= */

/* =========================================
   INCREASE QUANTITY - AJAX
========================================= */

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'increase' &&
    isset($_GET['product_id'])
) {

    $product_id = (int) $_GET['product_id'];

    if (isset($_SESSION['cart'][$product_id])) {

        $_SESSION['cart'][$product_id]['quantity']++;

        $quantity = $_SESSION['cart'][$product_id]['quantity'];
        $price = (float) $_SESSION['cart'][$product_id]['price'];

        $item_total = $price * $quantity;
    }

    /* Recalculate subtotal */

    $subtotal = 0;

    foreach ($_SESSION['cart'] as $item) {

        $subtotal +=
            (float)$item['price'] *
            (int)$item['quantity'];
    }

    $shipping = ($subtotal > 0) ? 3 : 0;
    $grand_total = $subtotal + $shipping;

    ob_clean();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'quantity' => $quantity ?? 0,
        'item_total' => $item_total ?? 0,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'grand_total' => $grand_total
    ]);

    exit;
}

/* =========================================
   DECREASE QUANTITY - AJAX
========================================= */

/* =========================================
   DECREASE QUANTITY - AJAX
========================================= */

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'decrease' &&

    isset($_GET['product_id'])
) {

    $product_id = (int) $_GET['product_id'];

    if (isset($_SESSION['cart'][$product_id])) {

        $_SESSION['cart'][$product_id]['quantity']--;

        if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {

            unset($_SESSION['cart'][$product_id]);

            ob_clean();

            header('Content-Type: application/json; charset=utf-8');

            echo json_encode([
                'success' => true,
                'removed' => true
            ]);

            exit;
        }

        $quantity = $_SESSION['cart'][$product_id]['quantity'];
        $price = (float) $_SESSION['cart'][$product_id]['price'];

        $item_total = $price * $quantity;
    }

    /* Recalculate subtotal */

    $subtotal = 0;

    foreach ($_SESSION['cart'] as $item) {

        $subtotal +=
            (float)$item['price'] *
            (int)$item['quantity'];
    }

    $shipping = ($subtotal > 0) ? 3 : 0;
    $grand_total = $subtotal + $shipping;

    ob_clean();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'quantity' => $quantity ?? 0,
        'item_total' => $item_total ?? 0,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'grand_total' => $grand_total
    ]);

    exit;
}


/* =========================================
   REMOVE PRODUCT - AJAX
========================================= */

/* =========================================
   REMOVE PRODUCT - AJAX
========================================= */

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'remove' &&
    isset($_GET['product_id'])
) {

    $product_id = (int) $_GET['product_id'];

    if (isset($_SESSION['cart'][$product_id])) {

        unset($_SESSION['cart'][$product_id]);
    }

    // Remove any unwanted output
    ob_clean();

    // Send JSON response
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'removed' => true,
        'product_id' => $product_id
    ]);

    exit;
}

/* =========================================
   CALCULATE TOTAL
========================================= */

$subtotal = 0;

foreach ($_SESSION['cart'] as $item) {

    $price = (float) $item['price'];
    $quantity = (int) $item['quantity'];

    $subtotal += $price * $quantity;
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


                    <?php if (!empty($_SESSION['cart'])) { ?>

                        <?php foreach ($_SESSION['cart'] as $item) { ?>

                            <?php
                            $price = (float)$item['price'];
                            $quantity = (int)$item['quantity'];
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
                        <button class="btn btn-primary rounded-pill px-4 py-3 text-uppercase mb-4 ms-4"
                            type="button">Proceed Checkout</button>
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

            document.querySelectorAll(".add-to-cart").forEach(function(button) {

                button.addEventListener("click", function() {

                    const productId = this.dataset.productId;
                    const currentButton = this;

                    // Prevent multiple clicks while request is running
                    currentButton.disabled = true;

                    fetch("config/backend_cart.php?action=add&product_id=" + productId)
                        .then(response => response.json())
                        .then(data => {

                            console.log("Cart Response:", data);

                            /* ==========================
                               LOGIN REQUIRED
                            ========================== */

                            if (data.login_required) {

                                currentButton.disabled = false;

                                showLoginPopup();

                                return;
                            }


                            /* ==========================
                               PRODUCT ADDED
                            ========================== */

                            if (data.success) {

                                if (data.quantity > 1) {

                                    currentButton.innerHTML =
                                        '<i class="fa fa-check me-2"></i> Added (' +
                                        data.quantity +
                                        ')';

                                } else {

                                    currentButton.innerHTML =
                                        '<i class="fa fa-check me-2"></i> Added';

                                }

                                currentButton.classList.add("added-cart");

                                // Keep button disabled
                                currentButton.disabled = false;
                            }


                            /* ==========================
                               ERROR
                            ========================== */
                            else {

                                currentButton.disabled = false;

                                alert(data.message || "Something went wrong.");
                            }

                        })
                        .catch(error => {

                            console.error("Cart Error:", error);

                            currentButton.disabled = false;

                            alert("Something went wrong. Please try again.");
                        });

                });

            });

        });
        /* ==========================
   SHOW LOGIN POPUP
========================== */

        function showLoginPopup() {

            const popup = document.getElementById("loginPopup");

            if (popup) {

                popup.style.display = "flex";

                document.body.style.overflow = "hidden";
            }
        }


        /* ==========================
           CLOSE LOGIN POPUP
        ========================== */

        function closeLoginPopup() {

            const popup = document.getElementById("loginPopup");

            if (popup) {

                popup.style.display = "none";

                document.body.style.overflow = "";
            }
        }


        /* ==========================
           GO TO LOGIN PAGE
        ========================== */

        function goToLogin() {

            window.location.href = "login.php";
        }


        /* ==========================
           CLICK OUTSIDE POPUP
        ========================== */

        document.getElementById("loginPopup")?.addEventListener("click", function(event) {

            if (event.target === this) {

                closeLoginPopup();

            }

        });
    </script>
</body>

</html>
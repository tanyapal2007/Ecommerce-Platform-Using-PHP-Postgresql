<?php

ob_start();

/* =========================================
   START SESSION
========================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================
   JSON HEADER
========================================= */
header('Content-Type: application/json; charset=utf-8');

/* =========================================
   DATABASE CONNECTION
========================================= */
try {

    require_once __DIR__ . "/database.php";
} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Database connection error',
        'error' => $e->getMessage()
    ]);

    exit;
}


/* =========================================
   JSON RESPONSE FUNCTION
========================================= */
function jsonResponse($success, $message = '', $data = [])
{
    // Remove unwanted output before JSON
    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));

    exit;
}


/* =========================================
   CHECK LOGIN
========================================= */
if (
    !isset($_SESSION['user_id']) ||
    empty($_SESSION['user_id'])
) {

    jsonResponse(
        false,
        'Login required',
        [
            'login_required' => true
        ]
    );
}


/* =========================================
   GET USER ID
========================================= */
$user_id = (int) $_SESSION['user_id'];


/* =========================================
   GET ACTION
========================================= */
$action = $_GET['action'] ?? '';


/* =========================================
   GET PRODUCT ID
========================================= */
$product_id = isset($_GET['product_id'])
    ? (int) $_GET['product_id']
    : 0;


/* =========================================
   VALIDATE PRODUCT ID
========================================= */
if ($product_id <= 0) {

    jsonResponse(
        false,
        'Invalid product ID'
    );
}


/* =========================================
   TRY CART OPERATION
========================================= */
try {

    /* =========================================
       CHECK PRODUCT EXISTS
    ========================================= */

    $product_sql = "
        SELECT
            product_id,
            product_name,
            product_code,
            stock_quantity
        FROM products
        WHERE product_id = :product_id
          AND status = 1
        LIMIT 1
    ";

    $product_stmt = $conn->prepare($product_sql);

    $product_stmt->execute([
        'product_id' => $product_id
    ]);

    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);


    if (!$product) {

        jsonResponse(
            false,
            'Product not found'
        );
    }


    /* =========================================
       ADD PRODUCT
    ========================================= */

    if ($action === 'add') {

        /* =====================================
           GET PRODUCT PRICE
        ===================================== */

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
            'product_id' => $product_id
        ]);

        $price_row = $price_stmt->fetch(PDO::FETCH_ASSOC);


        if (!$price_row) {

            jsonResponse(
                false,
                'Product price not found'
            );
        }


        $product_price = (float) $price_row['selling_price'];


        /* =====================================
           CHECK USER CART
        ===================================== */

        $check_sql = "
            SELECT
                cart_id,
                quantity
            FROM cart
            WHERE user_id = :user_id
              AND product_id = :product_id
            LIMIT 1
        ";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);

        $cart_item = $check_stmt->fetch(PDO::FETCH_ASSOC);


        /* =====================================
           ALREADY IN CART
        ===================================== */

        if ($cart_item) {

            $new_quantity =
                (int) $cart_item['quantity'] + 1;

            $new_price =
                $product_price * $new_quantity;


            $update_sql = "
                UPDATE cart
                SET
                    quantity = :quantity,
                    price = :price,
                    updated_at = CURRENT_TIMESTAMP
                WHERE cart_id = :cart_id
                  AND user_id = :user_id
            ";

            $update_stmt = $conn->prepare($update_sql);

            $update_stmt->execute([
                'quantity' => $new_quantity,
                'price' => $new_price,
                'cart_id' => $cart_item['cart_id'],
                'user_id' => $user_id
            ]);


            jsonResponse(
                true,
                'Product quantity increased',
                [
                    'product_id' => $product_id,
                    'quantity' => $new_quantity,
                    'price' => $new_price,
                    'added' => true,
                    'already_added' => true
                ]
            );
        }


        /* =====================================
           FIRST TIME ADD
        ===================================== */

        $insert_sql = "
            INSERT INTO cart
            (
                user_id,
                product_id,
                quantity,
                price,
                created_at,
                updated_at
            )
            VALUES
            (
                :user_id,
                :product_id,
                :quantity,
                :price,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )
        ";

        $insert_stmt = $conn->prepare($insert_sql);

        $insert_stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id,
            'quantity' => 1,
            'price' => $product_price
        ]);


        jsonResponse(
            true,
            'Product added to cart',
            [
                'product_id' => $product_id,
                'quantity' => 1,
                'price' => $product_price,
                'added' => true,
                'already_added' => false
            ]
        );
    }


    /* =========================================
       GET CART QUANTITY
    ========================================= */

    if ($action === 'get') {

        $sql = "
            SELECT quantity
            FROM cart
            WHERE user_id = :user_id
              AND product_id = :product_id
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);

        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($cart_item) {

            jsonResponse(
                true,
                'Product is in cart',
                [
                    'product_id' => $product_id,
                    'quantity' => (int) $cart_item['quantity'],
                    'in_cart' => true
                ]
            );
        }


        jsonResponse(
            true,
            'Product is not in cart',
            [
                'product_id' => $product_id,
                'quantity' => 0,
                'in_cart' => false
            ]
        );
    }


    /* =========================================
       REMOVE PRODUCT
    ========================================= */

    if ($action === 'remove') {

        $delete_sql = "
            DELETE FROM cart
            WHERE user_id = :user_id
              AND product_id = :product_id
        ";

        $delete_stmt = $conn->prepare($delete_sql);

        $delete_stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);


        jsonResponse(
            true,
            'Product removed from cart',
            [
                'product_id' => $product_id,
                'quantity' => 0,
                'removed' => true
            ]
        );
    }


    /* =========================================
       INCREASE QUANTITY
    ========================================= */

    if ($action === 'increase') {

        $sql = "
            UPDATE cart
            SET
                quantity = quantity + 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id
              AND product_id = :product_id
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);


        $get_sql = "
            SELECT quantity
            FROM cart
            WHERE user_id = :user_id
              AND product_id = :product_id
            LIMIT 1
        ";

        $get_stmt = $conn->prepare($get_sql);

        $get_stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);

        $item = $get_stmt->fetch(PDO::FETCH_ASSOC);


        jsonResponse(
            true,
            'Quantity increased',
            [
                'product_id' => $product_id,
                'quantity' => $item
                    ? (int) $item['quantity']
                    : 0
            ]
        );
    }


    /* =========================================
       DECREASE QUANTITY
    ========================================= */

    if ($action === 'decrease') {

        $sql = "
            UPDATE cart
            SET
                quantity = quantity - 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id
              AND product_id = :product_id
              AND quantity > 1
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);


        /* =====================================
           CHECK CURRENT QUANTITY
        ===================================== */

        $check_sql = "
            SELECT quantity
            FROM cart
            WHERE user_id = :user_id
              AND product_id = :product_id
            LIMIT 1
        ";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id
        ]);

        $item = $check_stmt->fetch(PDO::FETCH_ASSOC);


        /* =====================================
           REMOVE IF NOT FOUND
        ===================================== */

        if (!$item) {

            jsonResponse(
                true,
                'Product removed',
                [
                    'product_id' => $product_id,
                    'quantity' => 0,
                    'removed' => true
                ]
            );
        }


        /* =====================================
           QUANTITY DECREASED
        ===================================== */

        if ((int) $item['quantity'] <= 1) {

            $delete_sql = "
                DELETE FROM cart
                WHERE user_id = :user_id
                  AND product_id = :product_id
            ";

            $delete_stmt = $conn->prepare($delete_sql);

            $delete_stmt->execute([
                'user_id' => $user_id,
                'product_id' => $product_id
            ]);

            jsonResponse(
                true,
                'Product removed',
                [
                    'product_id' => $product_id,
                    'quantity' => 0,
                    'removed' => true
                ]
            );
        }


        jsonResponse(
            true,
            'Quantity decreased',
            [
                'product_id' => $product_id,
                'quantity' => (int) $item['quantity'],
                'removed' => false
            ]
        );
    }

    /* =========================================
       INVALID ACTION
    ========================================= */

    jsonResponse(
        false,
        'Invalid cart action'
    );
} catch (Throwable $e) {

    /* =========================================
       DATABASE / PHP ERROR
       STILL RETURN JSON
    ========================================= */

    jsonResponse(
        false,
        'Cart operation failed',
        [
            'error' => $e->getMessage()
        ]
    );
}

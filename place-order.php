
<?php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/database.php";


/* =========================================================
   PLACE ORDER - POST
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Remove any previous output */
    ob_clean();

    header('Content-Type: application/json; charset=utf-8');

    try {

        /* =====================================================
           CHECK DATABASE
        ===================================================== */

        if (!isset($conn) || !($conn instanceof PDO)) {

            throw new Exception(
                "Database connection not available."
            );
        }


        /* =====================================================
           CHECK LOGIN
        ===================================================== */

        if (
            !isset($_SESSION['user_id']) ||
            empty($_SESSION['user_id'])
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'Please login first.'
            ]);

            exit;
        }


        $user_id = (int) $_SESSION['user_id'];


        /* =====================================================
           GET POST DATA
        ===================================================== */

        $address_id = isset($_POST['address_id'])
            ? (int) $_POST['address_id']
            : 0;

        $payment_method = trim(
            $_POST['payment_method'] ?? ''
        );


        /* =====================================================
           VALIDATE ADDRESS
        ===================================================== */

        if ($address_id <= 0) {

            echo json_encode([
                'success' => false,
                'message' => 'Please select a delivery address.'
            ]);

            exit;
        }


        /* =====================================================
           VALIDATE PAYMENT
        ===================================================== */

        $allowed_payment_methods = [
            'cod',
            'card',
            'upi',
            'netbanking'
        ];

        if (
            !in_array(
                $payment_method,
                $allowed_payment_methods,
                true
            )
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid payment method.'
            ]);

            exit;
        }


        /* =====================================================
           GET ADDRESS
        ===================================================== */

        $address_stmt = $conn->prepare("
            SELECT
                address_id,
                address_type,
                full_name,
                phone,
                address_line1,
                address_line2,
                city,
                state,
                pincode
            FROM user_address
            WHERE address_id = :address_id
            AND user_id = :user_id
            LIMIT 1
        ");

        $address_stmt->execute([
            ':address_id' => $address_id,
            ':user_id' => $user_id
        ]);

        $address =
            $address_stmt->fetch(PDO::FETCH_ASSOC);


        if (!$address) {

            echo json_encode([
                'success' => false,
                'message' => 'Selected address not found.'
            ]);

            exit;
        }


        /* =====================================================
           GET CART
        ===================================================== */

        $cart_stmt = $conn->prepare("
            SELECT
                c.cart_id,
                c.product_id,
                c.quantity,
                p.product_name,
                p.product_code
            FROM cart c
            LEFT JOIN products p
                ON p.product_id = c.product_id
            WHERE c.user_id = :user_id
            ORDER BY c.cart_id ASC
        ");

        $cart_stmt->execute([
            ':user_id' => $user_id
        ]);

        $cart_items =
            $cart_stmt->fetchAll(PDO::FETCH_ASSOC);


        if (empty($cart_items)) {

            echo json_encode([
                'success' => false,
                'message' => 'Your cart is empty for this logged-in user.'
            ]);

            exit;
        }


        /* =====================================================
           CALCULATE TOTAL
        ===================================================== */

        $subtotal = 0;
        $total_quantity = 0;


        foreach ($cart_items as &$item) {

            $product_id =
                (int) $item['product_id'];

            $quantity =
                (int) $item['quantity'];


            if ($product_id <= 0) {

                throw new Exception(
                    "Invalid product ID in cart."
                );
            }


            if ($quantity <= 0) {

                throw new Exception(
                    "Invalid quantity for product ID " .
                        $product_id
                );
            }


            /* =============================================
               GET CURRENT PRODUCT PRICE
            ============================================= */

            $price_stmt = $conn->prepare("
                SELECT
                    price_id,
                    selling_price
                FROM product_prices
                WHERE product_id = :product_id
                AND status = 1
                ORDER BY price_id DESC
                LIMIT 1
            ");


            $price_stmt->execute([
                ':product_id' => $product_id
            ]);


            $price_row =
                $price_stmt->fetch(PDO::FETCH_ASSOC);


            if (!$price_row) {

                throw new Exception(
                    "Active price not found for product ID " .
                        $product_id
                );
            }


            $price =
                (float) $price_row['selling_price'];


            $item_total =
                $price * $quantity;


            $item['price'] =
                $price;

            $item['quantity'] =
                $quantity;

            $item['item_total'] =
                $item_total;


            $subtotal +=
                $item_total;


            $total_quantity +=
                $quantity;
        }

        unset($item);


        /* =====================================================
           SHIPPING
        ===================================================== */

        $shipping =
            ($subtotal > 0) ? 3 : 0;


        /* =====================================================
           GRAND TOTAL
        ===================================================== */

        $grand_total =
            $subtotal + $shipping;


        /* =====================================================
           FIRST PRODUCT
        ===================================================== */

        $first_product_id =
            (int) $cart_items[0]['product_id'];


        /* =====================================================
           ORDER DETAILS JSON
        ===================================================== */

        $order_details = json_encode([

            'address_id' =>
            $address_id,

            'address_type' =>
            $address['address_type'] ?? '',

            'full_name' =>
            $address['full_name'] ?? '',

            'phone' =>
            $address['phone'] ?? '',

            'address_line1' =>
            $address['address_line1'] ?? '',

            'address_line2' =>
            $address['address_line2'] ?? '',

            'city' =>
            $address['city'] ?? '',

            'state' =>
            $address['state'] ?? '',

            'pincode' =>
            $address['pincode'] ?? '',

            'payment_method' =>
            $payment_method,

            'subtotal' =>
            $subtotal,

            'shipping' =>
            $shipping,

            'grand_total' =>
            $grand_total

        ], JSON_UNESCAPED_UNICODE);


        if ($order_details === false) {

            throw new Exception(
                "Unable to create order details."
            );
        }


        /* =====================================================
           START TRANSACTION
        ===================================================== */

        $conn->beginTransaction();


        /* =====================================================
           INSERT INTO ORDERS
        ===================================================== */

        $order_stmt = $conn->prepare("
            INSERT INTO orders
            (
                product_id,
                user_id,
                order_details,
                quantity,
                total_amount,
                order_status,
                order_date
            )
            VALUES
            (
                :product_id,
                :user_id,
                :order_details,
                :quantity,
                :total_amount,
                :order_status,
                CURRENT_TIMESTAMP
            )
            RETURNING order_id
        ");


        $order_stmt->execute([

            ':product_id' =>
            $first_product_id,

            ':user_id' =>
            $user_id,

            ':order_details' =>
            $order_details,

            ':quantity' =>
            $total_quantity,

            ':total_amount' =>
            $grand_total,

            ':order_status' =>
            'pending'

        ]);


        $order_id =
            $order_stmt->fetchColumn();


        if (!$order_id) {

            throw new Exception(
                "Order ID was not generated."
            );
        }


        $order_id =
            (int) $order_id;


        /* =====================================================
           INSERT ORDER ITEMS
        ===================================================== */

        $item_stmt = $conn->prepare("
            INSERT INTO order_items
            (
                order_id,
                product_id,
                quantity,
                price,
                total
            )
            VALUES
            (
                :order_id,
                :product_id,
                :quantity,
                :price,
                :total
            )
        ");


        foreach ($cart_items as $item) {

            $item_stmt->execute([

                ':order_id' =>
                $order_id,

                ':product_id' =>
                (int) $item['product_id'],

                ':quantity' =>
                (int) $item['quantity'],

                ':price' =>
                (float) $item['price'],

                ':total' =>
                (float) $item['item_total']

            ]);
        }


        /* =====================================================
           DELETE CART
        ===================================================== */

        $delete_cart = $conn->prepare("
            DELETE FROM cart
            WHERE user_id = :user_id
        ");

        $delete_cart->execute([
            ':user_id' => $user_id
        ]);


        /* =====================================================
           COMMIT
        ===================================================== */

        $conn->commit();


        /* =====================================================
           SUCCESS
        ===================================================== */

        ob_clean();

        echo json_encode([

            'success' => true,

            'message' =>
            'Order placed successfully.',

            'order_id' =>
            $order_id

        ]);

        exit;
    } catch (Throwable $e) {

        /* =====================================================
           ROLLBACK
        ===================================================== */

        if (
            isset($conn) &&
            $conn instanceof PDO &&
            $conn->inTransaction()
        ) {

            $conn->rollBack();
        }


        ob_clean();


        /*
         * IMPORTANT:
         * Return actual error for debugging.
         */

        echo json_encode([

            'success' => false,

            'message' =>
            $e->getMessage(),

            'error_type' =>
            get_class($e)

        ]);

        exit;
    }
}


/* =========================================================
   BELOW THIS KEEP YOUR EXISTING GET CHECKOUT CODE
========================================================= */

```php
<?php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/database.php";


/* =========================================================
   CHECK LOGIN
========================================================= */

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$message = "";
$error = "";


/* =========================================================
   GET USER DETAILS
========================================================= */

$user_stmt = $conn->prepare("
    SELECT user_id, name, phone
    FROM users
    WHERE user_id = :user_id
    LIMIT 1
");

$user_stmt->execute([
    ':user_id' => $user_id
]);

$user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    header("Location: login.php");
    exit;
}


/* =========================================================
   SAVE ADDRESS
========================================================= */

if (isset($_POST['save_address'])) {

    $address_type  = trim($_POST['address_type'] ?? 'home');
    $full_name     = trim($_POST['full_name'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city          = trim($_POST['city'] ?? '');
    $state         = trim($_POST['state'] ?? '');
    $pincode       = trim($_POST['pincode'] ?? '');

    /* SMALLINT: 0 = no, 1 = yes */
    $is_default = isset($_POST['is_default']) ? 1 : 0;


    /* =====================================================
       VALIDATION
    ===================================================== */

    if (
        empty($full_name) ||
        empty($phone) ||
        empty($address_line1) ||
        empty($city) ||
        empty($state) ||
        empty($pincode)
    ) {

        $error = "Please fill all required fields.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {

        $error = "Please enter a valid 10 digit phone number.";
    } elseif (!preg_match('/^[0-9]{6}$/', $pincode)) {

        $error = "Please enter a valid 6 digit pincode.";
    } else {

        try {

            /* =================================================
               CHECK EXISTING ADDRESSES
            ================================================= */

            $count_stmt = $conn->prepare("
                SELECT COUNT(*)
                FROM user_address
                WHERE user_id = :user_id
            ");

            $count_stmt->execute([
                ':user_id' => $user_id
            ]);

            $address_count = (int) $count_stmt->fetchColumn();


            /* =================================================
               FIRST ADDRESS AUTOMATICALLY DEFAULT
            ================================================= */

            if ($address_count === 0) {
                $is_default = 1;
            }


            /* =================================================
               REMOVE OLD DEFAULT
            ================================================= */

            if ($is_default === 1) {

                $update_stmt = $conn->prepare("
                    UPDATE user_address
                    SET is_default = 0
                    WHERE user_id = :user_id
                ");

                $update_stmt->execute([
                    ':user_id' => $user_id
                ]);
            }


            /* =================================================
               INSERT ADDRESS
            ================================================= */

            $insert_stmt = $conn->prepare("
                INSERT INTO user_address
                (
                    user_id,
                    address_type,
                    full_name,
                    phone,
                    address_line1,
                    address_line2,
                    city,
                    state,
                    pincode,
                    is_default
                )
                VALUES
                (
                    :user_id,
                    :address_type,
                    :full_name,
                    :phone,
                    :address_line1,
                    :address_line2,
                    :city,
                    :state,
                    :pincode,
                    :is_default
                )
            ");

            $insert_stmt->execute([
                ':user_id'       => $user_id,
                ':address_type'  => $address_type,
                ':full_name'     => $full_name,
                ':phone'         => $phone,
                ':address_line1' => $address_line1,
                ':address_line2' => $address_line2,
                ':city'          => $city,
                ':state'         => $state,
                ':pincode'       => $pincode,
                ':is_default'    => (int) $is_default
            ]);


            /* =================================================
               AFTER SAVE → CHECKOUT
            ================================================= */

            header("Location: checkout.php");
            exit;
        } catch (PDOException $e) {

            $error = "Database Error: " . $e->getMessage();
        }
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <title>Add New Address</title>

    <meta
        content="width=device-width, initial-scale=1.0"
        name="viewport">


    <!-- Google Fonts -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">


    <!-- Bootstrap Icons -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css"
        rel="stylesheet">


    <!-- Bootstrap -->

    <link
        href="assets/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Main CSS -->

    <link
        href="assets/css/style.css"
        rel="stylesheet">


    <style>
        .add-address-page {
            padding: 50px 0;
        }


        .address-box {
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 35px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }


        .address-title {
            margin-bottom: 30px;
        }


        .address-title h3 {
            font-weight: 600;
            margin-bottom: 8px;
        }


        .address-title p {
            color: #777;
            margin-bottom: 0;
        }


        .form-label {
            font-weight: 600;
            margin-bottom: 7px;
        }


        .form-control,
        .form-select {
            min-height: 45px;
            border-radius: 5px;
        }


        .save-btn {
            min-height: 48px;
            font-weight: 600;
        }


        .back-btn {
            min-height: 48px;
        }


        .user-info {
            background: #f7f7f7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
        }


        .user-info i {
            margin-right: 7px;
        }
    </style>

</head>


<body>


    <!-- =========================================================
     HEADER
========================================================= -->

    <?php include 'header.php'; ?>


    <!-- =========================================================
     PAGE HEADER
========================================================= -->

    <div class="container-fluid page-header py-5">

        <h1 class="text-center text-white display-6">

            Add New Address

        </h1>

        <ol class="breadcrumb justify-content-center mb-0">

            <li class="breadcrumb-item">

                <a href="index.php">
                    Home
                </a>

            </li>

            <li class="breadcrumb-item">

                <a href="checkout.php">
                    Checkout
                </a>

            </li>

            <li class="breadcrumb-item active text-white">

                Add Address

            </li>

        </ol>

    </div>


    <!-- =========================================================
     ADD ADDRESS SECTION
========================================================= -->

    <div class="container add-address-page">

        <div class="address-box">


            <!-- TITLE -->

            <div class="address-title">

                <h3>

                    <i class="fa fa-map-marker-alt text-primary me-2"></i>

                    Add New Delivery Address

                </h3>

                <p>
                    Enter your delivery address details below.
                </p>

            </div>


            <!-- =================================================
             LOGGED USER
        ================================================== -->

            <div class="user-info">

                <strong>
                    <i class="fa fa-user"></i>
                    Address for:
                </strong>

                <?php
                echo htmlspecialchars($user_data['name'] ?? '');
                ?>

                <br>

                <small>
                    <i class="fa fa-phone"></i>

                    <?php
                    echo htmlspecialchars($user_data['phone'] ?? '');
                    ?>
                </small>

            </div>


            <!-- =================================================
             SUCCESS MESSAGE
        ================================================== -->

            <?php if (!empty($message)) { ?>

                <div class="alert alert-success">

                    <i class="fa fa-check-circle me-2"></i>

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php } ?>


            <!-- =================================================
             ERROR MESSAGE
        ================================================== -->

            <?php if (!empty($error)) { ?>

                <div class="alert alert-danger">

                    <i class="fa fa-exclamation-circle me-2"></i>

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php } ?>


            <!-- =================================================
             ADDRESS FORM
        ================================================== -->

            <form method="POST">


                <!-- ADDRESS TYPE -->

                <div class="mb-3">

                    <label class="form-label">
                        Address Type
                    </label>

                    <select
                        name="address_type"
                        class="form-select">

                        <option value="home">
                            Home
                        </option>

                        <option value="office">
                            Office
                        </option>

                        <option value="other">
                            Other
                        </option>

                    </select>

                </div>


                <div class="row">


                    <!-- FULL NAME -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Full Name
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control"
                            value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>"

                            placeholder="Enter full name"
                            required>

                    </div>


                    <!-- PHONE -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Phone Number
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>"
                            maxlength="10"
                            placeholder="Enter 10 digit phone number"
                            required>

                    </div>


                </div>


                <!-- ADDRESS LINE 1 -->

                <div class="mb-3">

                    <label class="form-label">

                        Address
                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="text"
                        name="address_line1"
                        class="form-control"
                        placeholder="House No, Street, Area"
                        required>

                </div>


                <!-- ADDRESS LINE 2 -->

                <div class="mb-3">

                    <label class="form-label">

                        Address Line 2

                    </label>

                    <input
                        type="text"
                        name="address_line2"
                        class="form-control"
                        placeholder="Landmark, Near...">

                </div>


                <div class="row">


                    <!-- CITY -->

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            City
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="city"
                            class="form-control"
                            placeholder="Enter city"
                            required>

                    </div>


                    <!-- STATE -->

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            State
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="state"
                            class="form-control"
                            placeholder="Enter state"
                            required>

                    </div>


                    <!-- PINCODE -->

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Pincode
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="pincode"
                            class="form-control"
                            maxlength="6"
                            placeholder="6 digit pincode"
                            required>

                    </div>


                </div>


                <!-- DEFAULT ADDRESS -->

                <div class="form-check mb-4">

                    <input
                        type="checkbox"
                        name="is_default"
                        value="1"
                        class="form-check-input"
                        id="is_default">

                    <label
                        class="form-check-label"
                        for="is_default">

                        Make this my default address

                    </label>

                </div>


                <!-- BUTTONS -->

                <div class="row">


                    <div class="col-md-6 mb-2">

                        <a
                            href="checkout.php"
                            class="btn btn-outline-secondary w-100 back-btn">

                            <i class="fa fa-arrow-left me-2"></i>

                            Back to Checkout

                        </a>

                    </div>


                    <div class="col-md-6 mb-2">

                        <button
                            type="submit"
                            name="save_address"
                            class="btn btn-primary w-100 save-btn">

                            <i class="fa fa-save me-2"></i>

                            Save Address

                        </button>

                    </div>


                </div>


            </form>

        </div>

    </div>


    <!-- =========================================================
     FOOTER
========================================================= -->

    <?php include 'footer.php'; ?>


    <!-- =========================================================
     JAVASCRIPT
========================================================= -->

    <script
        src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js">
    </script>

    <script src="assets/js/main.js"></script>


</body>

</html>
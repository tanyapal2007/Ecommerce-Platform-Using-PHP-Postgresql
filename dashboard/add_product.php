<?php

include "../config/database.php";

$error_message = "";


/* =========================================================
   ADD PRODUCT
========================================================= */

if (isset($_POST['add_product'])) {

    $subcategory_id = (int) ($_POST['subcategory_id'] ?? 0);

    $product_name = trim(
        $_POST['product_name'] ?? ''
    );

    $product_description = trim(
        $_POST['product_description'] ?? ''
    );

    $product_code = trim(
        $_POST['product_code'] ?? ''
    );

    $stock_quantity = (int) (
        $_POST['stock_quantity'] ?? 0
    );

    $status = (int) (
        $_POST['status'] ?? 1
    );

    $brand_name = trim(
        $_POST['brand_name'] ?? ''
    );

    $color = trim(
        $_POST['color'] ?? ''
    );

    $size = trim(
        $_POST['size'] ?? ''
    );

    $material = trim(
        $_POST['material'] ?? ''
    );


    /* =====================================================
       VALIDATION
    ====================================================== */

    if ($subcategory_id <= 0) {

        $error_message =
            "Please select a subcategory.";
    } elseif ($product_name == '') {

        $error_message =
            "Please enter product name.";
    } else {


        /* =================================================
           CHECK PRODUCT CODE
        ================================================= */

        if ($product_code != '') {

            $check_sql = "
                SELECT product_id
                FROM products
                WHERE product_code = :product_code
                LIMIT 1
            ";

            $check_stmt =
                $conn->prepare($check_sql);

            $check_stmt->execute([
                ':product_code' => $product_code
            ]);

            $existing_product =
                $check_stmt->fetch(
                    PDO::FETCH_ASSOC
                );
        } else {

            $existing_product = false;
        }


        if ($existing_product) {

            $error_message =
                "This product code already exists. Please enter a different product code.";
        } else {


            /* =============================================
               INSERT PRODUCT
            ============================================== */

            $sql = "
                INSERT INTO products
                (
                    subcategory_id,
                    product_name,
                    product_description,
                    product_code,
                    stock_quantity,
                    status,
                    brand_name,
                    color,
                    size,
                    material
                )
                VALUES
                (
                    :subcategory_id,
                    :product_name,
                    :product_description,
                    :product_code,
                    :stock_quantity,
                    :status,
                    :brand_name,
                    :color,
                    :size,
                    :material
                )
                RETURNING product_id
            ";


            try {

                $stmt =
                    $conn->prepare($sql);


                $stmt->execute([

                    ':subcategory_id' =>
                    $subcategory_id,

                    ':product_name' =>
                    $product_name,

                    ':product_description' =>
                    $product_description,

                    ':product_code' =>
                    $product_code,

                    ':stock_quantity' =>
                    $stock_quantity,

                    ':status' =>
                    $status,

                    ':brand_name' =>
                    $brand_name,

                    ':color' =>
                    $color,

                    ':size' =>
                    $size,

                    ':material' =>
                    $material
                ]);


                /* =========================================
                   GET NEW PRODUCT ID
                ========================================== */

                $product_id =
                    $stmt->fetchColumn();


                /* =========================================
                   GO TO PRICE PAGE
                ========================================== */

                header(
                    "Location: add_price.php?product_id=" .
                        $product_id
                );

                exit;
            } catch (PDOException $e) {

                $error_message =
                    "Product Add Failed: " .
                    $e->getMessage();
            }
        }
    }
}


/* =========================================================
   GET CATEGORIES
========================================================= */

$category_sql = "
    SELECT
        category_id,
        category_name
    FROM product_category
    WHERE status = 1
    ORDER BY category_name ASC
";


try {

    $category_stmt =
        $conn->prepare($category_sql);

    $category_stmt->execute();

    $categories =
        $category_stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
} catch (PDOException $e) {

    die("Category Fetch Error: " .
        $e->getMessage());
}


/* =========================================================
   GET SUBCATEGORIES
========================================================= */

$subcategory_sql = "
    SELECT
        subcategory_id,
        category_id,
        subcategory_name
    FROM product_subcategory
    WHERE status = 1
    ORDER BY subcategory_name ASC
";


try {

    $subcategory_stmt =
        $conn->prepare($subcategory_sql);

    $subcategory_stmt->execute();

    $subcategories =
        $subcategory_stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
} catch (PDOException $e) {

    die("Subcategory Fetch Error: " .
        $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta charset="utf-8">

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Add Product</title>


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


    <!-- PNotify -->

    <!-- <link
        href="assets/vendors/pnotify/dist/pnotify.css"
        rel="stylesheet">

    <link
        href="assets/vendors/pnotify/dist/pnotify.buttons.css"
        rel="stylesheet">

    <link
        href="assets/vendors/pnotify/dist/pnotify.nonblock.css"
        rel="stylesheet"> -->


    <!-- Custom Theme -->

    <link
        href="assets/css/custom.min.css"
        rel="stylesheet">

    <link
        href="assets/css/custom-popup.css"
        rel="stylesheet">


    <style>
        .product-card {
            background: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 25px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: #777;
            margin-bottom: 0;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 7px;
        }

        .required {
            color: red;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .form-control,
        .form-select {
            min-height: 40px;
        }

        .description-box {
            resize: vertical;
        }
    </style>

</head>


<body class="nav-md">

    <div class="container body">

        <div class="main_container">


            <!-- =====================================================
             SIDEBAR
        ====================================================== -->

            <?php include "sidebar.php"; ?>


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
                                        data-toggle="dropdown"
                                        aria-expanded="false">

                                        <img
                                            src="assets/images/img.jpg"
                                            alt="">

                                        John Doe

                                        <span class="fa fa-angle-down"></span>

                                    </a>


                                    <ul
                                        class="dropdown-menu dropdown-usermenu pull-right">

                                        <li>

                                            <a href="javascript:;">
                                                Profile
                                            </a>

                                        </li>

                                        <li>

                                            <a href="javascript:;">

                                                <span class="badge bg-red pull-right">
                                                    50%
                                                </span>

                                                <span>
                                                    Settings
                                                </span>

                                            </a>

                                        </li>

                                        <li>

                                            <a href="javascript:;">
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

                <div class="container-fluid">


                    <!-- PAGE HEADER -->

                    <div
                        class="row"
                        style="margin-top:20px; margin-bottom:20px;">

                        <div class="col-md-8">

                            <h3 class="page-title">
                                Add Product
                            </h3>

                            <p class="page-subtitle">
                                Create a new product
                            </p>

                        </div>


                        <div class="col-md-4 text-right">

                            <a
                                href="products.php"
                                class="btn btn-default">

                                <i class="fa fa-arrow-left"></i>

                                Back to Products

                            </a>

                        </div>

                    </div>


                    <!-- ERROR MESSAGE -->

                    <?php if (!empty($error_message)) { ?>

                        <div class="alert alert-danger">

                            <i class="fa fa-times-circle"></i>

                            <?php
                            echo htmlspecialchars(
                                $error_message
                            );
                            ?>

                        </div>

                    <?php } ?>


                    <!-- =================================================
                     PRODUCT FORM CARD
                ================================================== -->

                    <div class="product-card">


                        <div class="section-title">

                            <i class="fa fa-cube"></i>

                            Product Information

                        </div>


                        <form
                            method="POST"
                            action=""
                            autocomplete="off">


                            <div class="row">


                                <!-- =================================================
                                 CATEGORY
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Category

                                            <span class="required">
                                                *
                                            </span>

                                        </label>


                                        <select
                                            id="category_id"
                                            class="form-control"
                                            onchange="loadSubcategories(this.value)"
                                            required>

                                            <option value="">
                                                Select Category
                                            </option>

                                            <?php foreach ($categories as $category) { ?>

                                                <option
                                                    value="<?php echo $category['category_id']; ?>">

                                                    <?php echo htmlspecialchars($category['category_name']); ?>

                                                </option>

                                            <?php } ?>

                                        </select>

                                    </div>

                                </div>


                                <!-- =================================================
                                 SUBCATEGORY
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="subcategory_id">Subcategory</label>

                                        <select
                                            name="subcategory_id"
                                            id="subcategory_id"
                                            class="form-control"
                                            required>

                                            <option value="">Select Subcategory</option>

                                            <?php foreach ($subcategories as $subcategory) { ?>

                                                <option
                                                    value="<?php echo $subcategory['subcategory_id']; ?>"
                                                    data-category="<?php echo $subcategory['category_id']; ?>">

                                                    <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>

                                                </option>

                                            <?php } ?>

                                        </select>
                                    </div>

                                </div>


                                <!-- =================================================
                                 PRODUCT NAME
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Product Name

                                            <span class="required">
                                                *
                                            </span>

                                        </label>


                                        <input
                                            type="text"
                                            name="product_name"
                                            class="form-control"
                                            placeholder="Enter product name"
                                            required>

                                    </div>

                                </div>


                                <!-- =================================================
                                 PRODUCT CODE
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Product Code

                                        </label>


                                        <input
                                            type="text"
                                            name="product_code"
                                            class="form-control"
                                            placeholder="Enter product code">

                                    </div>

                                </div>


                                <!-- =================================================
                                 BRAND NAME
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Brand Name

                                        </label>


                                        <input
                                            type="text"
                                            name="brand_name"
                                            class="form-control"
                                            placeholder="Enter brand name">

                                    </div>

                                </div>


                                <!-- =================================================
                                 COLOR
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Color

                                        </label>


                                        <input
                                            type="text"
                                            name="color"
                                            class="form-control"
                                            placeholder="Enter color">

                                    </div>

                                </div>


                                <!-- =================================================
                                 SIZE
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Size

                                        </label>


                                        <input
                                            type="text"
                                            name="size"
                                            class="form-control"
                                            placeholder="Enter size">

                                    </div>

                                </div>


                                <!-- =================================================
                                 MATERIAL
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Material

                                        </label>


                                        <input
                                            type="text"
                                            name="material"
                                            class="form-control"
                                            placeholder="Enter material">

                                    </div>

                                </div>


                                <!-- =================================================
                                 STOCK
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Stock Quantity

                                        </label>


                                        <input
                                            type="number"
                                            name="stock_quantity"
                                            class="form-control"
                                            min="0"
                                            value="0"
                                            placeholder="Enter stock quantity">

                                    </div>

                                </div>


                                <!-- =================================================
                                 STATUS
                            ================================================== -->

                                <div class="col-md-6">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Status

                                        </label>


                                        <select
                                            name="status"
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


                                <!-- =================================================
                                 DESCRIPTION
                            ================================================== -->

                                <div class="col-md-12">

                                    <div class="form-group">

                                        <label class="form-label">

                                            Product Description

                                        </label>


                                        <textarea
                                            name="product_description"
                                            class="form-control description-box"
                                            rows="5"
                                            placeholder="Enter product description"></textarea>

                                    </div>

                                </div>


                            </div>


                            <!-- =================================================
                             BUTTONS
                        ================================================== -->

                            <div
                                style="
                                border-top:1px solid #eee;
                                margin-top:20px;
                                padding-top:20px;
                            ">


                                <a
                                    href="products.php"
                                    class="btn btn-default">

                                    <i class="fa fa-times"></i>

                                    Cancel

                                </a>


                                <button
                                    type="submit"
                                    name="add_product"
                                    value="1"
                                    class="btn btn-primary">

                                    <i class="fa fa-save"></i>

                                    Save Product

                                </button>


                            </div>


                        </form>

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


    <script
        src="assets/vendors/jquery/dist/jquery.min.js">
    </script>


    <script
        src="assets/vendors/bootstrap/dist/js/bootstrap.min.js">
    </script>


    <script
        src="assets/vendors/fastclick/lib/fastclick.js">
    </script>


    <script
        src="assets/vendors/nprogress/nprogress.js">
    </script>


    <script
        src="assets/vendors/iCheck/icheck.min.js">
    </script>

    <!-- 
    <script
        src="assets/vendors/pnotify/dist/pnotify.js">
    </script>


    <script
        src="assets/vendors/pnotify/dist/pnotify.buttons.js">
    </script>


    <script
        src="assets/vendors/pnotify/dist/pnotify.nonblock.js">
    </script> -->


    <script
        src="assets/js/custom.min.js">
    </script>


    <script
        src="assets/js/custom-popup.js">
    </script>


    <script>
        function loadSubcategories(categoryId) {
            const subcategory =
                document.getElementById("subcategory_id");

            const options =
                subcategory.querySelectorAll("option");


            subcategory.value = "";


            options.forEach(function(option) {

                if (option.value === "") {
                    option.style.display = "block";
                    return;
                }


                if (
                    option.getAttribute("data-category") ==
                    categoryId
                ) {
                    option.style.display = "block";
                } else {
                    option.style.display = "none";
                }

            });

        }
    </script>


</body>

</html>
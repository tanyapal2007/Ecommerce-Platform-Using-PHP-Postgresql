<?php

include "../config/database.php";

$error = "";


/* =========================================================
   ADD SUBCATEGORY
========================================================= */

if (isset($_POST['add_subcategory'])) {

    $category_id = (int) ($_POST['category_id'] ?? 0);

    $subcategory_name = trim(
        $_POST['subcategory_name'] ?? ''
    );

    $subcategory_description = trim(
        $_POST['subcategory_description'] ?? ''
    );

    $status = (int) ($_POST['status'] ?? 1);

    $subcategory_image = "";


    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($category_id <= 0) {

        $error = "Please select a category.";
    } elseif ($subcategory_name == "") {

        $error = "Subcategory name is required.";
    }


    /* =====================================================
       IMAGE UPLOAD
    ===================================================== */

    if (
        $error == '' &&
        isset($_FILES['subcategory_image']) &&
        $_FILES['subcategory_image']['error'] == 0
    ) {

        $upload_dir = "../uploads/subcategories/";


        if (!is_dir($upload_dir)) {

            mkdir(
                $upload_dir,
                0777,
                true
            );
        }


        $file_name =
            $_FILES['subcategory_image']['name'];

        $tmp_name =
            $_FILES['subcategory_image']['tmp_name'];


        $extension = strtolower(
            pathinfo(
                $file_name,
                PATHINFO_EXTENSION
            )
        );


        $allowed = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];


        if (!in_array($extension, $allowed)) {

            $error =
                "Only JPG, JPEG, PNG and WEBP images are allowed.";
        } else {

            $new_name =
                "subcategory_" .
                time() .
                "_" .
                rand(1000, 9999) .
                "." .
                $extension;


            if (
                move_uploaded_file(
                    $tmp_name,
                    $upload_dir . $new_name
                )
            ) {

                $subcategory_image = $new_name;
            } else {

                $error =
                    "Image upload failed.";
            }
        }
    }


    /* =====================================================
       INSERT INTO DATABASE
    ===================================================== */

    if ($error == '') {

        $sql = "
            INSERT INTO product_subcategory
            (
                category_id,
                subcategory_name,
                subcategory_description,
                subcategory_image,
                status
            )
            VALUES
            (
                :category_id,
                :subcategory_name,
                :subcategory_description,
                :subcategory_image,
                :status
            )
        ";


        try {

            $stmt = $conn->prepare($sql);


            $stmt->execute([

                ':category_id' =>
                $category_id,

                ':subcategory_name' =>
                $subcategory_name,

                ':subcategory_description' =>
                $subcategory_description,

                ':subcategory_image' =>
                $subcategory_image,

                ':status' =>
                $status
            ]);


            /* DIRECT REDIRECT */

            header(
                "Location: product_subcategory.php"
            );

            exit;
        } catch (PDOException $e) {

            $error =
                "Database Error: " .
                $e->getMessage();
        }
    }
}


/* =========================================================
   FETCH CATEGORIES
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

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Add Product Subcategory</title>

    <link
        href="assets/vendors/bootstrap/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="assets/vendors/font-awesome/css/font-awesome.min.css"
        rel="stylesheet">

    <link
        href="assets/vendors/nprogress/nprogress.css"
        rel="stylesheet">

    <link
        href="assets/css/custom.min.css"
        rel="stylesheet">

</head>


<body class="nav-md">

    <div class="container body">

        <div class="main_container">

            <?php include 'sidebar.php'; ?>


            <div class="right_col" role="main">


                <!-- TOP NAV -->

                <div class="top_nav">

                    <div class="nav_menu">

                        <nav>

                            <div class="nav toggle">

                                <a id="menu_toggle">

                                    <i class="fa fa-bars"></i>

                                </a>

                            </div>

                        </nav>

                    </div>

                </div>



                <!-- PAGE -->

                <div class="container-fluid py-4">


                    <!-- HEADER -->

                    <div class="row mb-4">

                        <div class="col-md-8">

                            <h3 class="fw-bold">

                                Add Product Subcategory

                            </h3>

                            <p class="text-muted">

                                Create a new product subcategory

                            </p>

                        </div>


                        <div class="col-md-4 text-right">

                            <a
                                href="product_subcategory.php"
                                class="btn btn-secondary">

                                <i class="fa fa-arrow-left"></i>

                                Back to Subcategories

                            </a>

                        </div>

                    </div>


                    <!-- FORM CARD -->

                    <div class="card">

                        <div class="card-header">

                            <h4 class="mb-0">

                                Subcategory Information

                            </h4>

                        </div>


                        <div class="card-body">

                            <form
                                method="POST"
                                enctype="multipart/form-data">


                                <div class="row">


                                    <!-- CATEGORY -->

                                    <div class="col-md-6">

                                        <div class="form-group">

                                            <label>

                                                Category

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>


                                            <select name="category_id" class="form-control" required>

                                                <option value="">Select Category</option>

                                                <?php foreach ($categories as $category) { ?>

                                                    <option value="<?php echo $category['category_id']; ?>">
                                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                                    </option>

                                                <?php } ?>

                                            </select>

                                        </div>

                                    </div>


                                    <!-- SUBCATEGORY NAME -->

                                    <div class="col-md-6">

                                        <div class="form-group">

                                            <label>

                                                Subcategory Name

                                                <span class="text-danger">
                                                    *
                                                </span>

                                            </label>


                                            <input
                                                type="text"
                                                name="subcategory_name"
                                                class="form-control"
                                                placeholder="Enter subcategory name"
                                                required>

                                        </div>

                                    </div>


                                    <!-- IMAGE -->

                                    <div class="col-md-6">

                                        <div class="form-group">

                                            <label>

                                                Subcategory Image

                                            </label>


                                            <input
                                                type="file"
                                                name="subcategory_image"
                                                class="form-control"
                                                accept=".jpg,.jpeg,.png,.webp">


                                            <small class="text-muted">

                                                Allowed: JPG, JPEG, PNG, WEBP

                                            </small>

                                        </div>

                                    </div>


                                    <!-- STATUS -->

                                    <div class="col-md-6">

                                        <div class="form-group">

                                            <label>

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


                                    <!-- DESCRIPTION -->

                                    <div class="col-md-12">

                                        <div class="form-group">

                                            <label>

                                                Subcategory Description

                                            </label>


                                            <textarea
                                                name="subcategory_description"
                                                class="form-control"
                                                rows="5"
                                                placeholder="Enter subcategory description"></textarea>

                                        </div>

                                    </div>


                                </div>


                                <!-- BUTTONS -->

                                <div class="border-top pt-4 mt-3">

                                    <a
                                        href="product_subcategory.php"
                                        class="btn btn-secondary">

                                        <i class="fa fa-times"></i>

                                        Cancel

                                    </a>


                                    <button
                                        type="submit"
                                        name="add_subcategory"
                                        value="1"
                                        class="btn btn-primary">

                                        <i class="fa fa-save"></i>

                                        Save Subcategory

                                    </button>

                                </div>


                            </form>

                        </div>

                    </div>

                </div>


                <!-- FOOTER -->

                <footer>

                    <div class="pull-right">

                        Gentelella - Bootstrap Admin Template

                    </div>

                    <div class="clearfix"></div>

                </footer>


            </div>
        </div>
    </div>


    <script src="assets/vendors/jquery/dist/jquery.min.js"></script>

    <script src="assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>

    <script src="assets/vendors/fastclick/lib/fastclick.js"></script>

    <script src="assets/vendors/nprogress/nprogress.js"></script>

    <script src="assets/js/custom.min.js"></script>

</body>

</html>
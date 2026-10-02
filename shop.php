<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/config/database.php";


/* =========================================
   GET CATEGORY ID
========================================= */

$category_id = isset($_GET['category_id'])
    ? (int) $_GET['category_id']
    : 0;


/* =========================================
   GET SUBCATEGORY ID
========================================= */

$subcategory_id = isset($_GET['subcategory_id'])
    ? (int) $_GET['subcategory_id']
    : 0;


/* =========================================
   GET PRODUCTS
========================================= */

$sql = "
    SELECT
        p.product_id,
        p.product_name,
        p.product_code,
        p.stock_quantity,
        p.product_description,
        p.status,

        ps.subcategory_id,
        ps.subcategory_name,

        pc.category_id,
        pc.category_name

    FROM products p

    INNER JOIN product_subcategory ps
        ON p.subcategory_id = ps.subcategory_id

    INNER JOIN product_category pc
        ON ps.category_id = pc.category_id

    WHERE p.status = 1
      AND ps.status = 1
      AND pc.status = 1
";


/* =========================================
   CATEGORY FILTER
========================================= */

if ($category_id > 0) {

    $sql .= "
        AND ps.category_id = :category_id
    ";
}


/* =========================================
   SUBCATEGORY FILTER
========================================= */

if ($subcategory_id > 0) {

    $sql .= "
        AND ps.subcategory_id = :subcategory_id
    ";
}


/* =========================================
   ORDER
========================================= */

$sql .= "
    ORDER BY p.product_id DESC
";


/* =========================================
   PREPARE
========================================= */

$stmt = $conn->prepare($sql);


/* =========================================
   PARAMETERS
========================================= */

$params = [];


if ($category_id > 0) {

    $params[':category_id'] = $category_id;
}


if ($subcategory_id > 0) {

    $params[':subcategory_id'] = $subcategory_id;
}


/* =========================================
   EXECUTE
========================================= */

$stmt->execute($params);


/* =========================================
   FETCH PRODUCTS
========================================= */

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   GET CATEGORY NAME
========================================= */

$category_name = "Shop";

if (!empty($products)) {
    $category_name = $products[0]['category_name'];
}

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
        <h1 class="text-center text-white display-6">
            <?php echo htmlspecialchars($category_name); ?>
        </h1>
        <ol class="breadcrumb justify-content-center mb-0 wow fadeInUp" data-wow-delay="0.3s">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Pages</a></li>
            <li class="breadcrumb-item active text-white">Shop</li>
        </ol>
    </div>
    <!-- Single Page Header End -->

    <!-- Searvices Start -->
    <div class="container-fluid px-0">
        <div class="row g-0">
            <div class="col-6 col-md-4 col-lg-2 border-start border-end wow fadeInUp" data-wow-delay="0.1s">
                <div class="p-4">
                    <div class="d-inline-flex align-items-center">
                        <i class="fa fa-sync-alt fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Free Return</h6>
                            <p class="mb-0">30 days money back guarantee!</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.2s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fab fa-telegram-plane fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Free Shipping</h6>
                            <p class="mb-0">Free shipping on all order</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.3s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-life-ring fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Support 24/7</h6>
                            <p class="mb-0">We support online 24 hrs a day</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.4s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-credit-card fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Receive Gift Card</h6>
                            <p class="mb-0">Recieve gift all over oder $50</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.5s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-lock fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Secure Payment</h6>
                            <p class="mb-0">We Value Your Security</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.6s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-blog fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Online Service</h6>
                            <p class="mb-0">Free return products in 30 days</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Searvices End -->


    <!-- Products Offer Start -->
    <div id="tab-5" class="tab-pane fade show p-0 active">
        <div class="container">
            <div class="row g-4">

                <?php if (!empty($products)) { ?>

                    <?php foreach ($products as $product) { ?>

                        <?php
                        $product_id = (int) $product['product_id'];

                        // Default image
                        $product_image = "assets/img/footwear1.jpg";

                        // Get product image
                        try {

                            $image_sql = "
                    SELECT image_path
                    FROM product_images
                    WHERE product_id = :product_id
                    ORDER BY is_primary DESC
                    LIMIT 1
                ";

                            $image_stmt = $conn->prepare($image_sql);

                            $image_stmt->execute([
                                ':product_id' => $product_id
                            ]);

                            $image_row = $image_stmt->fetch(PDO::FETCH_ASSOC);

                            if ($image_row && !empty($image_row['image_path'])) {
                                $product_image = $image_row['image_path'];
                            }
                        } catch (PDOException $e) {

                            $product_image = "assets/img/footwear1.jpg";
                        }
                        ?>

                        <!-- ONE PRODUCT -->
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">

                            <div class="product-item rounded">

                                <div class="product-item-inner border rounded">

                                    <div class="product-item-inner-item">

                                        <img
                                            src="<?php echo htmlspecialchars($product_image); ?>"
                                            class="img-fluid w-100 rounded-top"
                                            style="height:250px; object-fit:cover;"
                                            alt="<?php echo htmlspecialchars($product['product_name']); ?>">

                                    </div>

                                    <div class="text-center rounded-bottom p-4">

                                        <a
                                            href="single.php?id=<?php echo $product_id; ?>"
                                            class="d-block h4 text-decoration-none">

                                            <?php echo htmlspecialchars($product['product_name']); ?>

                                        </a>

                                        <p class="text-muted mb-2">

                                            Product Code:
                                            <?php echo htmlspecialchars($product['product_code'] ?? ''); ?>

                                        </p>

                                        <p class="mb-3">

                                            Stock:
                                            <?php echo (int) $product['stock_quantity']; ?>

                                        </p>

                                        <button
                                            type="button"
                                            class="btn btn-primary border-secondary rounded-pill py-2 px-4 add-to-cart"
                                            data-product-id="<?php echo $product_id; ?>">

                                            <i class="fas fa-shopping-cart me-2"></i>
                                            Add To Cart

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="col-12">

                        <div class="alert alert-warning text-center">

                            No products found in this category.

                        </div>

                    </div>

                <?php } ?>

            </div>
        </div>
    </div>
    <!-- Products Offer End -->


    <!-- Shop Page Start -->
    <div class="container-fluid shop py-5">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="product-categories mb-4">
                        <h4>Products Categories</h4>
                        <ul class="list-unstyled">
                            <li>
                                <div class="categories-item">
                                    <a href="#" class="text-dark"><i class="fas fa-apple-alt text-secondary me-2"></i>
                                        Accessories</a>
                                    <span>(3)</span>
                                </div>
                            </li>
                            <li>
                                <div class="categories-item">
                                    <a href="#" class="text-dark"><i class="fas fa-apple-alt text-secondary me-2"></i>
                                        Electronics & Computer</a>
                                    <span>(5)</span>
                                </div>
                            </li>
                            <li>
                                <div class="categories-item">
                                    <a href="#" class="text-dark"><i
                                            class="fas fa-apple-alt text-secondary me-2"></i>Laptops & Desktops</a>
                                    <span>(2)</span>
                                </div>
                            </li>
                            <li>
                                <div class="categories-item">
                                    <a href="#" class="text-dark"><i
                                            class="fas fa-apple-alt text-secondary me-2"></i>Mobiles & Tablets</a>
                                    <span>(8)</span>
                                </div>
                            </li>
                            <li>
                                <div class="categories-item">
                                    <a href="#" class="text-dark"><i
                                            class="fas fa-apple-alt text-secondary me-2"></i>SmartPhone & Smart TV</a>
                                    <span>(5)</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="price mb-4">
                        <h4 class="mb-2">Price</h4>
                        <input type="range" class="form-range w-100" id="rangeInput" name="rangeInput" min="0" max="500"
                            value="0" oninput="amount.value=rangeInput.value">
                        <output id="amount" name="amount" min-velue="0" max-value="500" for="rangeInput">0</output>
                        <div class=""></div>
                    </div>
                    <div class="product-color mb-3">
                        <h4>Select By Color</h4>
                        <ul class="list-unstyled">
                            <li>
                                <div class="product-color-item">
                                    <a href="#" class="text-dark"><i class="fas fa-apple-alt text-secondary me-2"></i>
                                        Gold</a>
                                    <span>(1)</span>
                                </div>
                            </li>
                            <li>
                                <div class="product-color-item">
                                    <a href="#" class="text-dark"><i class="fas fa-apple-alt text-secondary me-2"></i>
                                        Green</a>
                                    <span>(1)</span>
                                </div>
                            </li>
                            <li>
                                <div class="product-color-item">
                                    <a href="#" class="text-dark"><i class="fas fa-apple-alt text-secondary me-2"></i>
                                        White</a>
                                    <span>(1)</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="additional-product mb-4">
                        <h4>Additional Products</h4>
                        <div class="additional-product-item">
                            <input type="radio" class="me-2" id="Categories-1" name="Categories-1" value="Beverages">
                            <label for="Categories-1" class="text-dark"> Accessories</label>
                        </div>
                        <div class="additional-product-item">
                            <input type="radio" class="me-2" id="Categories-2" name="Categories-1" value="Beverages">
                            <label for="Categories-2" class="text-dark"> Electronics & Computer</label>
                        </div>
                        <div class="additional-product-item">
                            <input type="radio" class="me-2" id="Categories-3" name="Categories-1" value="Beverages">
                            <label for="Categories-3" class="text-dark"> Laptops & Desktops</label>
                        </div>
                        <div class="additional-product-item">
                            <input type="radio" class="me-2" id="Categories-4" name="Categories-1" value="Beverages">
                            <label for="Categories-4" class="text-dark"> Mobiles & Tablets</label>
                        </div>
                        <div class="additional-product-item">
                            <input type="radio" class="me-2" id="Categories-5" name="Categories-1" value="Beverages">
                            <label for="Categories-5" class="text-dark"> SmartPhone & Smart TV</label>
                        </div>
                    </div>
                    <div class="featured-product mb-4">
                        <h4 class="mb-3">Featured products</h4>
                        <div class="featured-product-item">
                            <div class="rounded me-4" style="width: 100px; height: 100px;">
                                <img src="assets/img/product-3.png" class="img-fluid rounded" alt="Image">
                            </div>
                            <div>
                                <h6 class="mb-2">SmartPhone</h6>
                                <div class="d-flex mb-2">
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star"></i>
                                </div>
                                <div class="d-flex mb-2">
                                    <h5 class="fw-bold me-2">2.99 $</h5>
                                    <h5 class="text-danger text-decoration-line-through">4.11 $</h5>
                                </div>
                            </div>
                        </div>
                        <div class="featured-product-item">
                            <div class="rounded me-4" style="width: 100px; height: 100px;">
                                <img src="assets/img/product-4.png" class="img-fluid rounded" alt="Image">
                            </div>
                            <div>
                                <h6 class="mb-2">Smart Camera</h6>
                                <div class="d-flex mb-2">
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star"></i>
                                </div>
                                <div class="d-flex mb-2">
                                    <h5 class="fw-bold me-2">2.99 $</h5>
                                    <h5 class="text-danger text-decoration-line-through">4.11 $</h5>
                                </div>
                            </div>
                        </div>
                        <div class="featured-product-item">
                            <div class="rounded me-4" style="width: 100px; height: 100px;">
                                <img src="assets/img/product-5.png" class="img-fluid rounded" alt="Image">
                            </div>
                            <div>
                                <h6 class="mb-2">Camera Leance</h6>
                                <div class="d-flex mb-2">
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star text-secondary"></i>
                                    <i class="fa fa-star"></i>
                                </div>
                                <div class="d-flex mb-2">
                                    <h5 class="fw-bold me-2">2.99 $</h5>
                                    <h5 class="text-danger text-decoration-line-through">4.11 $</h5>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center my-4">
                            <a href="#" class="btn btn-primary px-4 py-3 rounded-pill w-100">Vew More</a>
                        </div>
                    </div>
                    <a href="#">
                        <div class="position-relative">
                            <img src="assets/img/product-banner-2.jpg" class="img-fluid w-100 rounded" alt="Image">
                            <div class="text-center position-absolute d-flex flex-column align-items-center justify-content-center rounded p-4"
                                style="width: 100%; height: 100%; top: 0; right: 0; background: rgba(242, 139, 0, 0.3);">
                                <h5 class="display-6 text-primary">SALE</h5>
                                <h4 class="text-secondary">Get UP To 50% Off</h4>
                                <a href="#" class="btn btn-primary rounded-pill px-4">Shop Now</a>
                            </div>
                        </div>
                    </a>
                    <div class="product-tags py-4">
                        <h4 class="mb-3">PRODUCT TAGS</h4>
                        <div class="product-tags-items bg-light rounded p-3">
                            <a href="#" class="border rounded py-1 px-2 mb-2">New</a>
                            <a href="#" class="border rounded py-1 px-2 mb-2">brand</a>
                            <a href="#" class="border rounded py-1 px-2 mb-2">black</a>
                            <a href="#" class="border rounded py-1 px-2 mb-2">white</a>
                            <a href="#" class="border rounded py-1 px-2 mb-2">tablats</a>
                            <a href="#" class="border rounded py-1 px-2 mb-2">phone</a>
                            <a href="#" class="border rounded py-1 px-2 mb-2">camera</a>
                            <a href="#" class="border rounded py-1 px-2 mb-2">drone</a>
                            <a href="#" class="border rounded py-1 px-2 mb-2">talevision</a>
                            <a href="#" class="border rounded py-1 px-2 mb-2">slaes</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="rounded mb-4 position-relative">
                        <img src="assets/img/product-banner-3.jpg" class="img-fluid rounded w-100" style="height: 250px;"
                            alt="Image">
                        <div class="position-absolute rounded d-flex flex-column align-items-center justify-content-center text-center"
                            style="width: 100%; height: 250px; top: 0; left: 0; background: rgba(242, 139, 0, 0.3);">
                            <h4 class="display-5 text-primary">SALE</h4>
                            <h3 class="display-4 text-white mb-4">Get UP To 50% Off</h3>
                            <a href="#" class="btn btn-primary rounded-pill">Shop Now</a>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-xl-7">
                            <div class="input-group w-100 mx-auto d-flex">
                                <input type="search" class="form-control p-3" placeholder="keywords"
                                    aria-describedby="search-icon-1">
                                <span id="search-icon-1" class="input-group-text p-3"><i
                                        class="fa fa-search"></i></span>
                            </div>
                        </div>
                        <div class="col-xl-3 text-end">
                            <div class="bg-light ps-3 py-3 rounded d-flex justify-content-between">
                                <label for="electronics">Sort By:</label>
                                <select id="electronics" name="electronicslist"
                                    class="border-0 form-select-sm bg-light me-3" form="electronicsform">
                                    <option value="volvo">Default Sorting</option>
                                    <option value="volv">Nothing</option>
                                    <option value="sab">Popularity</option>
                                    <option value="saab">Newness</option>
                                    <option value="opel">Average Rating</option>
                                    <option value="audio">Low to high</option>
                                    <option value="audi">High to low</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-xl-2">
                            <ul class="nav nav-pills d-inline-flex text-center py-2 px-2 rounded bg-light mb-4">
                                <li class="nav-item me-4">
                                    <a class="bg-light" data-bs-toggle="pill" href="#tab-5">
                                        <i class="fas fa-th fa-3x text-primary"></i>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="bg-light" data-bs-toggle="pill" href="#tab-6">
                                        <i class="fas fa-bars fa-3x text-primary"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div id="tab-5" class="tab-pane fade show p-0 active">

                            <div class="row g-4">
                                <?php if (!empty($products)) { ?>

                                    <?php foreach ($products as $product) { ?>

                                        <?php
                                        $product_id = (int)$product['product_id'];

                                        /* =========================
           PRODUCT IMAGE
        ========================= */

                                        $product_image = "assets/img/footwear1.jpg";

                                        $image_sql = "
            SELECT image_path
            FROM product_images
            WHERE product_id = :product_id
            ORDER BY is_primary DESC
            LIMIT 1
        ";

                                        $image_stmt = $conn->prepare($image_sql);

                                        $image_stmt->execute([
                                            ':product_id' => $product_id
                                        ]);

                                        $image_row = $image_stmt->fetch(PDO::FETCH_ASSOC);

                                        if ($image_row && !empty($image_row['image_path'])) {
                                            $product_image = $image_row['image_path'];
                                        }
                                        ?>

                                        <!-- ONE PRODUCT -->
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">

                                            <div class="product-item rounded">

                                                <div class="product-item-inner border rounded">

                                                    <div class="product-item-inner-item">

                                                        <img
                                                            src="<?php echo htmlspecialchars($product_image); ?>"
                                                            class="img-fluid w-100 rounded-top"
                                                            style="height:250px; object-fit:cover;"
                                                            alt="<?php echo htmlspecialchars($product['product_name']); ?>">

                                                    </div>

                                                    <div class="text-center rounded-bottom p-4">

                                                        <a
                                                            href="single.php?id=<?php echo $product_id; ?>"
                                                            class="d-block h4 text-decoration-none">

                                                            <?php echo htmlspecialchars($product['product_name']); ?>

                                                        </a>

                                                        <p class="text-muted mb-2">

                                                            Product Code:
                                                            <?php echo htmlspecialchars($product['product_code']); ?>

                                                        </p>

                                                        <p class="mb-3">

                                                            Stock:
                                                            <?php echo (int)$product['stock_quantity']; ?>

                                                        </p>

                                                        <button
                                                            type="button"
                                                            class="btn btn-primary border-secondary rounded-pill py-2 px-4 add-to-cart"
                                                            data-product-id="<?php echo $product_id; ?>">

                                                            <i class="fas fa-shopping-cart me-2"></i>
                                                            Add To Cart

                                                        </button>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    <?php } ?>

                                <?php } else { ?>

                                    <div class="col-12">

                                        <div class="alert alert-warning text-center">

                                            No products found in this category.

                                        </div>

                                    </div>

                                <?php } ?>

                            </div>

                        </div>
                        <div id="tab-6" class="products tab-pane fade show p-0">
                            <div class="row g-4 products-mini">
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-3.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-4.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-5.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-6.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-7.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-8.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-9.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-12.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-13.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-14.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-15.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="products-mini-item border">
                                        <div class="row g-0">
                                            <div class="col-5">
                                                <div class="products-mini-img border-end h-100">
                                                    <img src="assets/img/product-16.png" class="img-fluid w-100 h-100"
                                                        alt="Image">
                                                    <div class="products-mini-icon rounded-circle bg-primary">
                                                        <a href="#"><i class="fa fa-eye fa-1x text-white"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-7">
                                                <div class="products-mini-content p-3">
                                                    <a href="#" class="d-block mb-2">SmartPhone</a>
                                                    <a href="#" class="d-block h4">Apple iPad Mini <br> G2356</a>
                                                    <del class="me-2 fs-5">$1,250.00</del>
                                                    <span class="text-primary fs-5">$1,050.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="products-mini-add border p-3">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4"><i
                                                    class="fas fa-shopping-cart me-2"></i> Add To Cart</a>
                                            <div class="d-flex">
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-3"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-random"></i></i></a>
                                                <a href="#"
                                                    class="text-primary d-flex align-items-center justify-content-center me-0"><span
                                                        class="rounded-circle btn-sm-square border"><i
                                                            class="fas fa-heart"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 wow fadeInUp" data-wow-delay="0.1s">
                                    <div class="pagination d-flex justify-content-center mt-5">
                                        <a href="#" class="rounded">&laquo;</a>
                                        <a href="#" class="active rounded">1</a>
                                        <a href="#" class="rounded">2</a>
                                        <a href="#" class="rounded">3</a>
                                        <a href="#" class="rounded">4</a>
                                        <a href="#" class="rounded">5</a>
                                        <a href="#" class="rounded">6</a>
                                        <a href="#" class="rounded">&raquo;</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Shop Page End -->

    <!-- Product Banner Start -->
    <div class="container-fluid py-5">
        <div class="container pb-5">
            <div class="row g-4">
                <div class="col-lg-6 wow fadeInLeft" data-wow-delay="0.1s">
                    <a href="#">
                        <div class="bg-primary rounded position-relative">
                            <img src="assets/img/product-banner.jpg" class="img-fluid w-100 rounded" alt="">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center rounded p-4"
                                style="background: rgba(255, 255, 255, 0.5);">
                                <h3 class="display-5 text-primary">EOS Rebel <br> <span>T7i Kit</span></h3>
                                <p class="fs-4 text-muted">$899.99</p>
                                <a href="#" class="btn btn-primary rounded-pill align-self-start py-2 px-4">Shop Now</a>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.2s">
                    <a href="#">
                        <div class="text-center bg-primary rounded position-relative">
                            <img src="assets/img/product-banner-2.jpg" class="img-fluid w-100" alt="">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center rounded p-4"
                                style="background: rgba(242, 139, 0, 0.5);">
                                <h2 class="display-2 text-secondary">SALE</h2>
                                <h4 class="display-5 text-white mb-4">Get UP To 50% Off</h4>
                                <a href="#" class="btn btn-secondary rounded-pill align-self-center py-2 px-4">Shop
                                    Now</a>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Product Banner End -->

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

                    if (!productId) {
                        console.error("Product ID is missing");
                        return;
                    }

                    currentButton.disabled = true;

                    fetch("config/backend_cart.php?action=add&product_id=" + productId, {
                            method: "GET",
                            headers: {
                                "Accept": "application/json"
                            }
                        })
                        .then(async function(response) {

                            const text = await response.text();

                            console.log("Raw Backend Response:", text);

                            let data;

                            try {
                                data = JSON.parse(text);
                            } catch (error) {
                                console.error("Invalid JSON:", text);
                                throw new Error("Backend returned invalid JSON");
                            }

                            return data;
                        })
                        .then(function(data) {

                            console.log("Cart Response:", data);

                            /*
                             * LOGIN REQUIRED
                             */
                            if (data.login_required === true) {

                                // Remember the product that user clicked
                                sessionStorage.setItem(
                                    "pending_cart_product",
                                    productId
                                );

                                // Open Login Popup
                                const loginModalElement = document.getElementById("loginModal");

                                if (loginModalElement) {

                                    const loginModal = new bootstrap.Modal(loginModalElement);

                                    loginModal.show();

                                } else {

                                    console.error("Login popup not found!");

                                }

                                currentButton.disabled = false;

                                return;
                            }
                            /*
                             * SUCCESS
                             */
                            if (data.success === true) {

                                if (data.quantity > 1) {

                                    currentButton.innerHTML =
                                        '<i class="fa fa-check me-2"></i> Added (' +
                                        data.quantity +
                                        ')';

                                } else {

                                    currentButton.innerHTML =
                                        '<i class="fa fa-check me-2"></i> Added';
                                }

                                currentButton.classList.add("added-to-cart");
                                currentButton.disabled = false;

                                return;
                            }

                            currentButton.disabled = false;

                            alert(
                                data.message ||
                                "Unable to add product to cart."
                            );

                        })
                        .catch(function(error) {

                            console.error("ADD TO CART ERROR:", error);

                            currentButton.disabled = false;

                            alert(
                                error.message ||
                                "Something went wrong. Please try again."
                            );
                        });

                });

            });

        });
    </script>

    <!-- LOGIN POPUP START -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <!-- LOGIN POPUP START -->
        <div id="loginPopup"
            style="
        display:none;
        position:fixed;
        top:80px;
        right:30px;
        width:350px;
        background:#fff;
        padding:25px;
        border-radius:10px;
        box-shadow:0 5px 25px rgba(0,0,0,0.25);
        z-index:99999;
    ">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">

                <h5 style="margin:0;">
                    Login Required
                </h5>

                <button type="button"
                    onclick="closeLoginPopup()"
                    style="
                border:none;
                background:none;
                font-size:24px;
                cursor:pointer;
            ">
                    &times;
                </button>

            </div>

            <p style="margin-bottom:20px;">
                Please login to add this product to your cart.
            </p>

            <form action="login.php" method="POST">

                <div class="mb-3">
                    <label>Username</label>

                    <input type="text"
                        name="username"
                        class="form-control"
                        required>
                </div>

                <div class="mb-3">
                    <label>Password</label>

                    <input type="password"
                        name="password"
                        class="form-control"
                        required>
                </div>

                <button type="submit"
                    class="btn btn-primary w-100">
                    Login
                </button>

            </form>

        </div>
        <!-- LOGIN POPUP END -->
    </div>
    <!-- LOGIN POPUP END -->
</body>

</html>
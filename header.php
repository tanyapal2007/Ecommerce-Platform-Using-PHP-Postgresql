<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . "/config/database.php";


/* ================================
   GET ALL ACTIVE CATEGORIES
================================ */
$category_sql = "
    SELECT
        pc.category_id,
        pc.category_name,
        pc.category_description,
        pc.category_image,
        COUNT(DISTINCT p.product_id) AS product_count
    FROM product_category pc
    INNER JOIN product_subcategory ps
        ON pc.category_id = ps.category_id
    INNER JOIN products p
        ON ps.subcategory_id = p.subcategory_id
    WHERE pc.status = 1
      AND ps.status = 1
      AND p.status = 1
    GROUP BY
        pc.category_id,
        pc.category_name,
        pc.category_description,
        pc.category_image
    ORDER BY pc.category_name ASC
";

$category_result = $conn->query($category_sql);

$categories = [];

if ($category_result) {
    while ($row = $category_result->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }
}


/* ================================
   GET ALL ACTIVE SUBCATEGORIES
================================ */
$subcategory_sql = "
    SELECT
        subcategory_id,
        category_id,
        subcategory_name
    FROM product_subcategory
    WHERE status = 1
    ORDER BY subcategory_name ASC
";

$subcategory_result = $conn->query($subcategory_sql);

$subcategories = [];

if ($subcategory_result) {
    while ($row = $subcategory_result->fetch(PDO::FETCH_ASSOC)) {
        $subcategories[] = $row;
    }
}

?>
<!-- =========================================
     TOP BAR
========================================= -->

<div class="container-fluid px-5 d-none border-bottom d-lg-block">

    <div class="row gx-0 align-items-center">

        <div class="col-lg-4 text-center text-lg-start mb-lg-0">

            <div class="d-inline-flex align-items-center" style="height: 45px;">

                <a href="#" class="text-muted me-2">Help</a>
                <small>/</small>

                <a href="#" class="text-muted mx-2">Support</a>
                <small>/</small>

                <a href="#" class="text-muted ms-2">Contact</a>

            </div>

        </div>


        <div class="col-lg-4 text-center d-flex align-items-center justify-content-center">

            <small class="text-dark">Call Us:</small>

            <a href="#" class="text-muted">
                (+012) 1234 567890
            </a>

        </div>


        <div class="col-lg-4 text-center text-lg-end">

            <div class="d-inline-flex align-items-center" style="height: 45px;">

                <?php if (
                    isset($_SESSION['login']) &&
                    $_SESSION['login'] === true &&
                    isset($_SESSION['role'])
                ) { ?>

                    <?php if ($_SESSION['role'] === 'admin') { ?>

                        <!-- =========================
                 ADMIN : USD + ENGLISH
            ========================== -->

                        <div class="dropdown">

                            <a href="#"
                                class="dropdown-toggle text-muted me-2"
                                data-bs-toggle="dropdown">

                                <small>USD</small>

                            </a>

                            <div class="dropdown-menu rounded">

                                <a href="#" class="dropdown-item">Euro</a>
                                <a href="#" class="dropdown-item">Dollar</a>

                            </div>

                        </div>


                        <div class="dropdown">

                            <a href="#"
                                class="dropdown-toggle text-muted mx-2"
                                data-bs-toggle="dropdown">

                                <small>English</small>

                            </a>

                            <div class="dropdown-menu rounded">

                                <a href="#" class="dropdown-item">English</a>
                                <a href="#" class="dropdown-item">Turkish</a>
                                <a href="#" class="dropdown-item">Spanish</a>
                                <a href="#" class="dropdown-item">Italiano</a>

                            </div>

                        </div>

                    <?php } elseif ($_SESSION['role'] === 'user') { ?>

                        <!-- =========================
                 USER : WISHLIST + CART
            ========================== -->

                        <a href="wishlist.php"
                            class="text-muted mx-2 text-decoration-none">

                            <small>
                                <i class="fa fa-heart me-1"></i>
                                Wishlist
                            </small>

                        </a>


                        <a href="cart.php"
                            class="text-muted mx-2 text-decoration-none">

                            <small>
                                <i class="fa fa-shopping-cart me-1"></i>
                                My Cart
                            </small>

                        </a>

                    <?php } ?>

                <?php } ?>

            </div>


            <!-- Profile -->

            <div class="dropdown">

                <a href="#"
                    class="dropdown-toggle text-muted ms-2"
                    data-bs-toggle="dropdown">

                    <small>

                        <i class="fa fa-home me-2"></i>

                        My Profile

                    </small>

                </a>


                <div class="dropdown-menu rounded">

                    <?php

                    if (
                        isset($_SESSION['login']) &&
                        $_SESSION['login'] === true &&
                        isset($_SESSION['user_id']) &&
                        !empty($_SESSION['user_id'])
                    ) {

                    ?>

                        <!-- LOGOUT -->

                        <a href="logout.php" class="dropdown-item">
                            Log Out
                        </a>


                        <!-- WISHLIST -->

                        <a href="#" class="dropdown-item">
                            Wishlist
                        </a>


                        <!-- MY CART -->

                        <a href="myaccount.php" class="dropdown-item">
                            My Cart
                        </a>


                        <!-- NOTIFICATIONS -->

                        <a href="#" class="dropdown-item">
                            Notifications
                        </a>


                        <!-- ACCOUNT SETTINGS -->

                        <a href="#" class="dropdown-item">
                            Account Settings
                        </a>


                        <?php

                        /* ===========================
               ADMIN ONLY
            =========================== */

                        if (
                            isset($_SESSION['role']) &&
                            $_SESSION['role'] === 'admin'
                        ) {

                        ?>

                            <a href="dashboard/index3.php" class="dropdown-item">
                                Dashboard
                            </a>

                        <?php

                        }

                        ?>


                    <?php

                    } else {

                    ?>

                        <!-- NOT LOGGED IN -->

                        <a href="login.php" class="dropdown-item">
                            Login
                        </a>

                    <?php

                    }

                    ?>

                </div>

            </div>

        </div>

    </div>

</div>

</div>



<!-- =========================================
     LOGO + SEARCH
========================================= -->

<div class="container-fluid px-5 py-4 d-none d-lg-block">

    <div class="row gx-0 align-items-center text-center">

        <div class="col-md-4 col-lg-3 text-center text-lg-start">

            <div class="d-inline-flex align-items-center">

                <a href="" class="navbar-brand p-0">

                    <h1 class="display-5 text-primary m-0">

                        <i class="fas fa-shopping-bag text-secondary me-2"></i>

                        Electro

                    </h1>

                    <img src="assets/img/logo.jpg" alt="Logo">

                </a>

            </div>

        </div>





        <div class="col-md-4 col-lg-3 text-center text-lg-end">

            <div class="d-inline-flex align-items-center">

                <a href="#"
                    class="text-muted d-flex align-items-center justify-content-center me-3">

                    <span class="rounded-circle btn-md-square border">

                        <i class="fas fa-random"></i>

                    </span>

                </a>


                <a href="#"
                    class="text-muted d-flex align-items-center justify-content-center me-3">

                    <span class="rounded-circle btn-md-square border">

                        <i class="fas fa-heart"></i>

                    </span>

                </a>


                <a href="#"
                    class="text-muted d-flex align-items-center justify-content-center">

                    <span class="rounded-circle btn-md-square border">

                        <i class="fas fa-shopping-cart"></i>

                    </span>

                    <span class="text-dark ms-2">
                        $0.00
                    </span>

                </a>

            </div>

        </div>

    </div>

</div>



<!-- =========================================
     NAVBAR
========================================= -->

<div class="container-fluid nav-bar p-0">

    <div class="row gx-0 bg-primary px-5 align-items-center">


        <!-- =========================================
             DESKTOP ALL CATEGORIES
        ========================================= -->

        <!-- =========================================
     DESKTOP ALL CATEGORIES
========================================= -->

        <div class="col-lg-3 d-none d-lg-block">

            <nav class="navbar navbar-light position-relative p-0"
                style="width: 250px;">

                <!-- ALL CATEGORIES BUTTON -->

                <button
                    class="navbar-toggler border-0 fs-4 w-100 px-0 text-start"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#allCat"
                    aria-controls="allCat"
                    aria-expanded="false"
                    aria-label="Toggle Categories">

                    <h4 class="m-0">

                        <i class="fa fa-bars me-2"></i>

                        All Categories

                    </h4>

                </button>


                <!-- CATEGORY DROPDOWN -->

                <div
                    class="collapse position-absolute bg-white shadow rounded-bottom w-100"
                    id="allCat"
                    style="z-index: 9999; top: 100%; left: 0;">

                    <div class="navbar-nav py-2">

                        <ul class="list-unstyled categories-bars mb-0">

                            <?php if (!empty($categories)) { ?>

                                <?php foreach ($categories as $category) { ?>

                                    <li>

                                        <div class="categories-bars-item">
                                            <a
                                                href="shop.php?category_id=<?php echo (int)$category['category_id']; ?>"
                                                class="text-dark text-decoration-none d-flex justify-content-between align-items-center px-3 py-2">

                                                <span>
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                </span>

                                                <span class="badge bg-primary rounded-pill">
                                                    <?php echo (int)$category['product_count']; ?>
                                                </span>

                                            </a>

                                        </div>

                                    </li>

                                <?php } ?>

                            <?php } else { ?>

                                <li>

                                    <div class="categories-bars-item px-3 py-2">

                                        <span>
                                            No Categories Found
                                        </span>

                                    </div>

                                </li>

                            <?php } ?>

                        </ul>

                    </div>

                </div>

            </nav>

        </div>



        <!-- =========================================
             MAIN NAVIGATION
        ========================================= -->

        <div class="col-12 col-lg-9">

            <nav class="navbar navbar-expand-lg navbar-light bg-primary">


                <!-- MOBILE LOGO -->

                <a href=""
                    class="navbar-brand d-block d-lg-none">

                    <h1 class="display-5 text-secondary m-0">

                        <i class="fas fa-shopping-bag text-white me-2"></i>

                        Electro

                    </h1>

                    <img src="assets/img/logo.jpg" alt="Logo">

                </a>


                <button class="navbar-toggler ms-auto"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarCollapse">

                    <span class="fa fa-bars fa-1x"></span>

                </button>


                <div class="collapse navbar-collapse"
                    id="navbarCollapse">


                    <div class="navbar-nav ms-auto py-0">


                        <a href="index.php"
                            class="nav-item nav-link active">

                            Home

                        </a>


                        <a href="shop.php"
                            class="nav-item nav-link">

                            Shop

                        </a>


                        <a href="single.php"
                            class="nav-item nav-link">

                            Single Page

                        </a>



                        <!-- =========================================
     PAGES DROPDOWN
========================================= -->

                        <div class="nav-item dropdown pages-category-dropdown">

                            <a
                                href="#"
                                class="nav-link dropdown-toggle"
                                data-bs-toggle="dropdown">
                                Pages
                            </a>


                            <div class="dropdown-menu pages-menu">

                                <?php if (!empty($categories)) { ?>

                                    <?php foreach ($categories as $category) { ?>

                                        <div class="pages-category-item">

                                            <!-- CATEGORY -->
                                            <a href="shop.php?category_id=<?php echo (int)$category['category_id']; ?>"
                                                class="dropdown-item pages-category-link">

                                                <?php echo htmlspecialchars($category['category_name']); ?>

                                                <span class="category-arrow">›</span>

                                            </a>


                                            <!-- SUBCATEGORIES -->
                                            <div class="subcategory-menu">

                                                <?php foreach ($subcategories as $subcategory) { ?>

                                                    <?php if (
                                                        (int)$subcategory['category_id']
                                                        ===
                                                        (int)$category['category_id']
                                                    ) { ?>

                                                        <a href="shop.php?category_id=<?php echo (int)$category['category_id']; ?>&subcategory_id=<?php echo (int)$subcategory['subcategory_id']; ?>"
                                                            class="dropdown-item">

                                                            <?php echo htmlspecialchars($subcategory['subcategory_name']); ?>

                                                        </a>

                                                    <?php } ?>

                                                <?php } ?>

                                            </div>

                                        </div>

                                    <?php } ?>

                                <?php } ?>

                                <div class="dropdown-divider"></div>

                                <a href="cart.php" class="dropdown-item">
                                    Cart Page
                                </a>

                                <a href="cheackout.php" class="dropdown-item">
                                    Cheackout
                                </a>

                                <a href="404.php" class="dropdown-item">
                                    404 Page
                                </a>

                            </div>

                        </div>



                        <a href="contact.php"
                            class="nav-item nav-link me-2">

                            Contact

                        </a>


                    </div>


                    <a href=""
                        class="btn btn-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0">

                        <i class="fa fa-mobile-alt me-2"></i>

                        +0123 456 7890

                    </a>


                </div>

            </nav>

        </div>

    </div>

</div>

<!-- Navbar & Hero End -->
<!-- <?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "my_database";

$conn = mysqli_connect($host, $user, $password);
$sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";

if (mysqli_query($conn, $sql)) {
    // echo "Database exists or created successfully.<br>";
} else {
    die("Database Creation Failed: " . mysqli_error($conn));
}


if (!$conn) {
    die("Connection Failed:" . mysqli_connect_error());
}

mysqli_select_db($conn, $dbname); -->
// echo "Databse Connected Successfully";

/* =========================================
   1. PRODUCT CATEGORY
========================================= */

// $sql = "CREATE TABLE IF NOT EXISTS product_category (
//     category_id INT AUTO_INCREMENT PRIMARY KEY,
//     category_name VARCHAR(100) NOT NULL,
//     category_description TEXT,
//     category_image VARCHAR(255),
//     status TINYINT(1) DEFAULT 1,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "Product Category Table Created Successfully.<br>";
// } else {
//     echo "Product Category Error: " . mysqli_error($conn) . "<br>";
// }


/* =========================================
   2. PRODUCT SUBCATEGORY
========================================= */

// $sql = "CREATE TABLE IF NOT EXISTS product_subcategory (
//     subcategory_id INT AUTO_INCREMENT PRIMARY KEY,
//     category_id INT NOT NULL,
//     subcategory_name VARCHAR(100) NOT NULL,
//     subcategory_description TEXT,
//     subcategory_image VARCHAR(255),
//     status TINYINT(1) DEFAULT 1,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

//     FOREIGN KEY (category_id)
//     REFERENCES product_category(category_id)
//     ON DELETE CASCADE
//     ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "Product Subcategory Table Created Successfully.<br>";
// } else {
//     echo "Product Subcategory Error: " . mysqli_error($conn) . "<br>";
// }


/* =========================================
   3. PRODUCTS
========================================= */

// $sql = "CREATE TABLE IF NOT EXISTS products (
//     product_id INT AUTO_INCREMENT PRIMARY KEY,
//     subcategory_id INT NOT NULL,
//     product_name VARCHAR(150) NOT NULL,
//     product_description TEXT,
//     product_code VARCHAR(50) UNIQUE,
//     stock_quantity INT DEFAULT 0,
//     status TINYINT(1) DEFAULT 1,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
//         ON UPDATE CURRENT_TIMESTAMP,

//     FOREIGN KEY (subcategory_id)
//     REFERENCES product_subcategory(subcategory_id)
//     ON DELETE CASCADE
//     ON UPDATE CASCADE
// )";
// if (!function_exists('addColumnIfNotExists')) {
//     function addColumnIfNotExists($conn, $table, $column, $definition)
//     {
//         $check = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");

//         if (!$check) {
//             return false;
//         }

//         if (mysqli_num_rows($check) == 0) {
//             $sql = "ALTER TABLE `$table` ADD `$column` $definition";

//             if (!mysqli_query($conn, $sql)) {
//                 return false;
//             }
//         }

//         return true;
//     }
// }

// if (mysqli_query($conn, $sql)) {

//     // echo "Products Table Created Successfully.<br>";

//     // Function to add field if it does not exist



//     // Add new fields
//     addColumnIfNotExists(
//         $conn,
//         "products",
//         "brand_name",
//         "VARCHAR(100)"
//     );

//     addColumnIfNotExists(
//         $conn,
//         "products",
//         "color",
//         "VARCHAR(50)"
//     );

//     addColumnIfNotExists(
//         $conn,
//         "products",
//         "size",
//         "VARCHAR(50)"
//     );

//     addColumnIfNotExists(
//         $conn,
//         "products",
//         "material",
//         "VARCHAR(100)"
//     );
// } else {

//     echo "Products Error: " . mysqli_error($conn) . "<br>";
// }


/* =========================================
   4. PRODUCT RATING
========================================= */

// $sql = "CREATE TABLE IF NOT EXISTS product_rating (
//     rating_id INT AUTO_INCREMENT PRIMARY KEY,
//     product_id INT NOT NULL,
//     customer_name VARCHAR(100) NOT NULL,
//     customer_email VARCHAR(150),
//     rating DECIMAL(2,1) NOT NULL,
//     review TEXT,
//     status TINYINT(1) DEFAULT 1,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

//     FOREIGN KEY (product_id)
//     REFERENCES products(product_id)
//     ON DELETE CASCADE
//     ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "Product Rating Table Created Successfully.<br>";
// } else {
//     echo "Product Rating Error: " . mysqli_error($conn) . "<br>";
// }


/* =========================================
   5. PRODUCT IMAGES
========================================= */

// $sql = "CREATE TABLE IF NOT EXISTS product_images (
//     image_id INT AUTO_INCREMENT PRIMARY KEY,
//     product_id INT NOT NULL,
//     image_name VARCHAR(255) NOT NULL,
//     image_path VARCHAR(255) NOT NULL,
//     is_primary TINYINT(1) DEFAULT 0,
//     status TINYINT(1) DEFAULT 1,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

//     FOREIGN KEY (product_id)
//     REFERENCES products(product_id)
//     ON DELETE CASCADE
//     ON UPDATE CASCADE
// )";

// if (!mysqli_query($conn, $sql)) {

//     echo "Product Images Error: "
//         . mysqli_error($conn);
// }


/* =========================================================
   IF TABLE ALREADY EXISTS
   ADD STATUS COLUMN IF IT DOES NOT EXIST
========================================================= */

// $check_status = mysqli_query(
//     $conn,
//     "SHOW COLUMNS FROM product_images LIKE 'status'"
// );

// if ($check_status && mysqli_num_rows($check_status) == 0) {

//     $alter_sql = "
//         ALTER TABLE product_images
//         ADD status TINYINT(1) DEFAULT 1
//     ";

//     if (!mysqli_query($conn, $alter_sql)) {

//         echo "Status Column Error: "
//             . mysqli_error($conn);
//     }
// }




/* =========================================
6. PRODUCT PRICES
========================================= */

// $sql = "CREATE TABLE IF NOT EXISTS product_prices (
// price_id INT AUTO_INCREMENT PRIMARY KEY,
// product_id INT NOT NULL,
// original_price DECIMAL(10,2) NOT NULL,
// discount_percentage DECIMAL(5,2) DEFAULT 0,
// selling_price DECIMAL(10,2) NOT NULL,
// start_date DATE,
// end_date DATE,
// status TINYINT(1) DEFAULT 1,
// created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

// FOREIGN KEY (product_id)
// REFERENCES products(product_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "Product Prices Table Created Successfully.<br>";
// } else {
//     echo "Product Prices Error: " . mysqli_error($conn) . "<br>";
// }


// // echo "<br><b>All 6 Tables Are Ready.</b>";

// /* =========================================
// 1.user
// ========================================= */
// $sql = "CREATE TABLE IF NOT EXISTS user (
// user_id INT AUTO_INCREMENT PRIMARY KEY,
// name VARCHAR(100) NOT NULL,
// phone VARCHAR(15) NOT NULL,
// role ENUM('admin','customer') DEFAULT 'customer',
// status TINYINT(1) DEFAULT 1,
// created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "User table created successfully.<br>";
// } else {
//     echo "User Table Error: " . mysqli_error($conn) . "<br>";
// }

// /* =========================================
// 2. user_profile
// ========================================= */

// $sql = "CREATE TABLE IF NOT EXISTS user_profile (
// profile_id INT AUTO_INCREMENT PRIMARY KEY,
// user_id INT NOT NULL,
// profile_image VARCHAR(255),
// gender VARCHAR(20),
// date_of_birth DATE,
// bio TEXT,
// created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
// updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// ON UPDATE CURRENT_TIMESTAMP,

// FOREIGN KEY (user_id)
// REFERENCES user(user_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {

//     // echo "User Profile table created successfully.<br>";

// } else {

//     echo "User Profile Error: "
//         . mysqli_error($conn)
//         . "<br>";
// }


// /* =========================================
// ADD first_name COLUMN
// ========================================= */

// // $alter_sql = "ALTER TABLE user_profile
// // ADD COLUMN first_name VARCHAR(100)
// // AFTER user_id";

// // if (mysqli_query($conn, $alter_sql)) {

// // echo "first_name column added successfully.<br>";
// // } else {

// // // Column already exists → ignore error
// // }


// /* =========================================
// ADD last_name COLUMN
// ========================================= */

// // $alter_sql = "ALTER TABLE user_profile
// // ADD COLUMN last_name VARCHAR(100)
// // AFTER first_name";

// // if (mysqli_query($conn, $alter_sql)) {

// // echo "last_name column added successfully.<br>";
// // } else {

// // // Column already exists → ignore error
// // }

// /* =========================================
// 3. user_login_history
// ========================================= */
// $sql = "CREATE TABLE IF NOT EXISTS user_login_history (
// login_id INT AUTO_INCREMENT PRIMARY KEY,
// user_id INT NOT NULL,
// login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
// ip_address VARCHAR(45),
// user_agent TEXT,
// login_status ENUM('success','failed') DEFAULT 'success',

// FOREIGN KEY (user_id)
// REFERENCES user(user_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "User Login History table created successfully.<br>";
// } else {
//     echo "User Login History Error: " . mysqli_error($conn) . "<br>";
// }


// /* =========================================
// 4. orders
// ========================================= */
// $sql = "CREATE TABLE IF NOT EXISTS orders (
// order_id INT AUTO_INCREMENT PRIMARY KEY,
// product_id INT NOT NULL,
// user_id INT NOT NULL,
// order_details TEXT,
// quantity INT DEFAULT 1,
// total_amount DECIMAL(10,2) NOT NULL,
// order_status ENUM(
// 'pending',
// 'confirmed',
// 'shipped',
// 'delivered',
// 'cancelled'
// ) DEFAULT 'pending',
// order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

// FOREIGN KEY (product_id)
// REFERENCES products(product_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE,

// FOREIGN KEY (user_id)
// REFERENCES user(user_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE
)";

// if (mysqli_query($conn, $sql)) {
//     // echo "Orders table created successfully.<br>";
// } else {
//     echo "Orders Error: " . mysqli_error($conn) . "<br>";
// }


// /* =========================================
// 5. user_address
// ========================================= */
// $sql = "CREATE TABLE IF NOT EXISTS user_address (
// address_id INT AUTO_INCREMENT PRIMARY KEY,
// user_id INT NOT NULL,
// address_type ENUM('home','office','other') DEFAULT 'home',
// full_name VARCHAR(100) NOT NULL,
// phone VARCHAR(15),
// address_line1 VARCHAR(255) NOT NULL,
// address_line2 VARCHAR(255),
// city VARCHAR(100) NOT NULL,
// state VARCHAR(100) NOT NULL,
// pincode VARCHAR(10) NOT NULL,
// is_default TINYINT(1) DEFAULT 0,
// created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

// FOREIGN KEY (user_id)
// REFERENCES user(user_id)
// ON DELETE CASCADE
//  ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "User Address table created successfully.<br>";
// } else {
//     echo "User Address Error: " . mysqli_error($conn) . "<br>";
// }


// /* =========================================
// 6. Payment
// ========================================= */
// $sql = "CREATE TABLE IF NOT EXISTS payments (
// payment_id INT AUTO_INCREMENT PRIMARY KEY,
// order_id INT NOT NULL,
// user_id INT NOT NULL,
// payment_method ENUM('cod','card','upi','netbanking') NOT NULL,
// transaction_id VARCHAR(150) UNIQUE,
// amount DECIMAL(10,2) NOT NULL,
// payment_status ENUM('pending','success','failed','refunded') DEFAULT 'pending',
// payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

// FOREIGN KEY (order_id)
// REFERENCES orders(order_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE,

// FOREIGN KEY (user_id)
// REFERENCES user(user_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "Payment table created successfully.<br>";
// } else {
//     echo "Payment Table Error: " . mysqli_error($conn) . "<br>";
// }


// /* =========================================
// 7. Wishlist
// ========================================= */
// $sql = "CREATE TABLE IF NOT EXISTS wishlist (
// wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
// user_id INT NOT NULL,
// product_id INT NOT NULL,
// added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

// UNIQUE KEY unique_wishlist (user_id, product_id),

// FOREIGN KEY (user_id)
// REFERENCES user(user_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE,

// FOREIGN KEY (product_id)
// REFERENCES products(product_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "Wishlist table created successfully.<br>";
// } else {
//     echo "Wishlist Error: " . mysqli_error($conn) . "<br>";
// }

// /* =========================================
// 8. Add to cart
// ========================================= */
// $sql = "CREATE TABLE IF NOT EXISTS cart (
// cart_id INT AUTO_INCREMENT PRIMARY KEY,
// user_id INT NOT NULL,
// product_id INT NOT NULL,
// quantity INT DEFAULT 1,
// added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
// updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// ON UPDATE CURRENT_TIMESTAMP,

// UNIQUE KEY unique_cart (user_id, product_id),

// FOREIGN KEY (user_id)
// REFERENCES user(user_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE,

// FOREIGN KEY (product_id)
// REFERENCES products(product_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "Cart table created successfully.<br>";
// } else {
//     echo "Cart Error: " . mysqli_error($conn) . "<br>";
// }


// /* =========================================
// 9. OTP Model
// ========================================= */
// $sql = "CREATE TABLE IF NOT EXISTS user_otp (
// otp_id INT AUTO_INCREMENT PRIMARY KEY,
// user_id INT NOT NULL,
// otp VARCHAR(6) NOT NULL,
// otp_type ENUM('signup','login','forgot_password') NOT NULL,
// expires_at DATETIME NOT NULL,
// is_verified TINYINT(1) DEFAULT 0,
// used TINYINT(1) DEFAULT 0,
// created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

// FOREIGN KEY (user_id)
// REFERENCES user(user_id)
// ON DELETE CASCADE
// ON UPDATE CASCADE
// )";

// if (mysqli_query($conn, $sql)) {
//     // echo "User OTP table created successfully.<br>";
// } else {
//     echo "User OTP Error: " . mysqli_error($conn) . "<br>";
// }


<?php

$host = "localhost";
$port = "5432";
$dbname = "student_management";
$user = "postgres";
$password = "1234";

try {

    // =========================================
    // STEP 1: Connect to default postgres DB
    // =========================================

    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=postgres",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // =========================================
    // STEP 2: Check database exists or not
    // =========================================

    $stmt = $pdo->prepare(
        "SELECT 1 FROM pg_database WHERE datname = ?"
    );

    $stmt->execute([$dbname]);

    $exists = $stmt->fetch();


    // =========================================
    // STEP 3: Create database if not exists
    // =========================================

    if (!$exists) {

        $pdo->exec("CREATE DATABASE \"$dbname\"");

        // echo "Database '$dbname' created successfully!<br>";
    }


    // =========================================
    // STEP 4: Connect to student_management
    // =========================================

    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "PostgreSQL Connected Successfully!<br><br>";


    // =========================================
    // 1. PRODUCT CATEGORY
    // =========================================

    $sql = "
    CREATE TABLE IF NOT EXISTS product_category (

        category_id SERIAL PRIMARY KEY,

        category_name VARCHAR(100) NOT NULL,

        category_description TEXT,

        category_image VARCHAR(255),

        status SMALLINT DEFAULT 1,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
    ";

    $conn->exec($sql);

    // echo "Product Category table created.<br>";


    // =========================================
    // 2. PRODUCT SUBCATEGORY
    // =========================================

    $sql = "
    CREATE TABLE IF NOT EXISTS product_subcategory (

        subcategory_id SERIAL PRIMARY KEY,

        category_id INT NOT NULL,

        subcategory_name VARCHAR(100) NOT NULL,

        subcategory_description TEXT,

        subcategory_image VARCHAR(255),

        status SMALLINT DEFAULT 1,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (category_id)
            REFERENCES product_category(category_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

    $conn->exec($sql);

    // echo "Product Subcategory table created.<br>";


    // =========================================
    // 3. PRODUCTS
    // =========================================

    $sql = "
    CREATE TABLE IF NOT EXISTS products (

        product_id SERIAL PRIMARY KEY,

        subcategory_id INT NOT NULL,

        product_name VARCHAR(150) NOT NULL,

        product_description TEXT,

        product_code VARCHAR(50) UNIQUE,

        stock_quantity INT DEFAULT 0,

        status SMALLINT DEFAULT 1,

        brand_name VARCHAR(100),

        color VARCHAR(50),

        size VARCHAR(50),

        material VARCHAR(100),

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (subcategory_id)
            REFERENCES product_subcategory(subcategory_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

    $conn->exec($sql);

    // echo "Products table created.<br>";


    // =========================================
    // 4. PRODUCT RATING
    // =========================================

    $sql = "
    CREATE TABLE IF NOT EXISTS product_rating (

        rating_id SERIAL PRIMARY KEY,

        product_id INT NOT NULL,

        customer_name VARCHAR(100) NOT NULL,

        customer_email VARCHAR(150),

        rating DECIMAL(2,1) NOT NULL,

        review TEXT,

        status SMALLINT DEFAULT 1,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (product_id)
            REFERENCES products(product_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

    $conn->exec($sql);

    // echo "Product Rating table created.<br>";


    // =========================================
    // 5. PRODUCT IMAGES
    // =========================================

    $sql = "
    CREATE TABLE IF NOT EXISTS product_images (

        image_id SERIAL PRIMARY KEY,

        product_id INT NOT NULL,

        image_name VARCHAR(255) NOT NULL,

        image_path VARCHAR(255) NOT NULL,

        is_primary SMALLINT DEFAULT 0,

        status SMALLINT DEFAULT 1,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (product_id)
            REFERENCES products(product_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

    $conn->exec($sql);

    // echo "Product Images table created.<br>";


    // =========================================
    // 6. PRODUCT PRICES
    // =========================================

    $sql = "
    CREATE TABLE IF NOT EXISTS product_prices (

        price_id SERIAL PRIMARY KEY,

        product_id INT NOT NULL,

        original_price DECIMAL(10,2) NOT NULL,

        discount_percentage DECIMAL(5,2) DEFAULT 0,

        selling_price DECIMAL(10,2) NOT NULL,

        start_date DATE,

        end_date DATE,

        status SMALLINT DEFAULT 1,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (product_id)
            REFERENCES products(product_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

    $conn->exec($sql);

    // echo "Product Prices table created.<br>";


    // =========================================
    // FINAL MESSAGE
    // =========================================

    echo "<br>";
    // echo "<b>All 6 tables created successfully!</b>";
} catch (PDOException $e) {

    die("PostgreSQL Connection Failed: "
        . $e->getMessage());
}
// =========================================
// 7. USERS
// =========================================

$sql = "
    CREATE TABLE IF NOT EXISTS users (

        user_id SERIAL PRIMARY KEY,

        name VARCHAR(100) NOT NULL,

        phone VARCHAR(15) NOT NULL,

        role VARCHAR(20) DEFAULT 'customer'
            CHECK (role IN ('admin', 'customer')),

        status SMALLINT DEFAULT 1,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
    ";

$conn->exec($sql);

// echo "Users table created.<br>";


// =========================================
// 8. USER PROFILE
// =========================================

$sql = "
    CREATE TABLE IF NOT EXISTS user_profile (

        profile_id SERIAL PRIMARY KEY,

        user_id INT NOT NULL,

        first_name VARCHAR(100),

        last_name VARCHAR(100),

        profile_image VARCHAR(255),

        gender VARCHAR(20),

        date_of_birth DATE,

        bio TEXT,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (user_id)
            REFERENCES users(user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

$conn->exec($sql);

// echo "User Profile table created.<br>";


// =========================================
// 9. USER LOGIN HISTORY
// =========================================

$sql = "
    CREATE TABLE IF NOT EXISTS user_login_history (

        login_id SERIAL PRIMARY KEY,

        user_id INT NOT NULL,

        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        ip_address VARCHAR(45),

        user_agent TEXT,

        login_status VARCHAR(20) DEFAULT 'success'
            CHECK (login_status IN ('success', 'failed')),

        FOREIGN KEY (user_id)
            REFERENCES users(user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

$conn->exec($sql);

// echo "User Login History table created.<br>";


// =========================================
// 10. ORDERS
// =========================================

$sql = "
    CREATE TABLE IF NOT EXISTS orders (

        order_id SERIAL PRIMARY KEY,

        product_id INT NOT NULL,

        user_id INT NOT NULL,

        order_details TEXT,

        quantity INT DEFAULT 1,

        total_amount DECIMAL(10,2) NOT NULL,

        order_status VARCHAR(20) DEFAULT 'pending'
            CHECK (order_status IN (
                'pending',
                'confirmed',
                'shipped',
                'delivered',
                'cancelled'
            )),

        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (product_id)
            REFERENCES products(product_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,

        FOREIGN KEY (user_id)
            REFERENCES users(user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

$conn->exec($sql);

// echo "Orders table created.<br>";


// =========================================
// 11. USER ADDRESS
// =========================================

$sql = "
    CREATE TABLE IF NOT EXISTS user_address (

        address_id SERIAL PRIMARY KEY,

        user_id INT NOT NULL,

        address_type VARCHAR(20) DEFAULT 'home'
            CHECK (address_type IN ('home', 'office', 'other')),

        full_name VARCHAR(100) NOT NULL,

        phone VARCHAR(15),

        address_line1 VARCHAR(255) NOT NULL,

        address_line2 VARCHAR(255),

        city VARCHAR(100) NOT NULL,

        state VARCHAR(100) NOT NULL,

        pincode VARCHAR(10) NOT NULL,

        is_default SMALLINT DEFAULT 0,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (user_id)
            REFERENCES users(user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

$conn->exec($sql);

// echo "User Address table created.<br>";


// =========================================
// 12. PAYMENTS
// =========================================

$sql = "
    CREATE TABLE IF NOT EXISTS payments (

        payment_id SERIAL PRIMARY KEY,

        order_id INT NOT NULL,

        user_id INT NOT NULL,

        payment_method VARCHAR(20) NOT NULL
            CHECK (payment_method IN (
                'cod',
                'card',
                'upi',
                'netbanking'
            )),

        transaction_id VARCHAR(150) UNIQUE,

        amount DECIMAL(10,2) NOT NULL,

        payment_status VARCHAR(20) DEFAULT 'pending'
            CHECK (payment_status IN (
                'pending',
                'success',
                'failed',
                'refunded'
            )),

        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (order_id)
            REFERENCES orders(order_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,

        FOREIGN KEY (user_id)
            REFERENCES users(user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

$conn->exec($sql);

// echo "Payments table created.<br>";


// =========================================
// 13. WISHLIST
// =========================================

$sql = "
    CREATE TABLE IF NOT EXISTS wishlist (

        wishlist_id SERIAL PRIMARY KEY,

        user_id INT NOT NULL,

        product_id INT NOT NULL,

        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        UNIQUE (user_id, product_id),

        FOREIGN KEY (user_id)
            REFERENCES users(user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,

        FOREIGN KEY (product_id)
            REFERENCES products(product_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

$conn->exec($sql);

// echo "Wishlist table created.<br>";


// =========================================
// 14. CART
// =========================================

$sql = "
    CREATE TABLE IF NOT EXISTS cart (

        cart_id SERIAL PRIMARY KEY,

        user_id INT NOT NULL,

        product_id INT NOT NULL,

        quantity INT DEFAULT 1,

        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        UNIQUE (user_id, product_id),

        FOREIGN KEY (user_id)
            REFERENCES users(user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,

        FOREIGN KEY (product_id)
            REFERENCES products(product_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

$conn->exec($sql);

// echo "Cart table created.<br>";


// =========================================
// 15. USER OTP
// =========================================

$sql = "
    CREATE TABLE IF NOT EXISTS user_otp (

        otp_id SERIAL PRIMARY KEY,

        user_id INT NOT NULL,

        otp VARCHAR(6) NOT NULL,

        otp_type VARCHAR(30) NOT NULL
            CHECK (otp_type IN (
                'signup',
                'login',
                'forgot_password'
            )),

        expires_at TIMESTAMP NOT NULL,

        is_verified SMALLINT DEFAULT 0,

        used SMALLINT DEFAULT 0,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (user_id)
            REFERENCES users(user_id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )
    ";

$conn->exec($sql);

// echo "User OTP table created.<br>";


// =========================================
// FINAL MESSAGE
// =========================================

echo "<br>";
// echo "<b>All remaining tables created successfully!</b>";


// =========================================
    // Order-items
// =========================================

$conn->exec("
    CREATE TABLE IF NOT EXISTS order_items (
        order_item_id SERIAL PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,

        CONSTRAINT fk_order_items_order
            FOREIGN KEY (order_id)
            REFERENCES orders(order_id)
            ON DELETE CASCADE,

        CONSTRAINT fk_order_items_product
            FOREIGN KEY (product_id)
            REFERENCES products(product_id)
            ON DELETE CASCADE
    )
");


?>
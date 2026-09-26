-- ========================================
-- MySQL Database Backup
-- Database: my_database
-- Date: 2026-09-07 16:13:59
-- ========================================

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `unique_cart` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_details` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` enum('cod','card','upi','netbanking') NOT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `product_category`;
CREATE TABLE `product_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_description` text DEFAULT NULL,
  `category_image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `product_category` VALUES ('1', 'Electronics', 'Electronic products and devices', NULL, '1', '2026-09-07 18:52:21');
INSERT INTO `product_category` VALUES ('2', 'Mobiles & Tablets', 'Mobile phones, tablets and accessories', NULL, '1', '2026-09-07 18:52:21');
INSERT INTO `product_category` VALUES ('3', 'Laptops & Computers', 'Laptops, computers and accessories', NULL, '1', '2026-09-07 18:52:21');
INSERT INTO `product_category` VALUES ('4', 'Fashion', 'Clothing, shoes and fashion accessories', NULL, '1', '2026-09-07 18:52:21');
INSERT INTO `product_category` VALUES ('5', 'Beauty & Personal Care', 'Beauty and personal care products', NULL, '1', '2026-09-07 18:52:21');
INSERT INTO `product_category` VALUES ('6', 'Home & Kitchen', 'Home and kitchen products', NULL, '1', '2026-09-07 18:52:21');
INSERT INTO `product_category` VALUES ('7', 'Grocery', 'Grocery and daily essential products', NULL, '1', '2026-09-07 18:52:21');
INSERT INTO `product_category` VALUES ('8', 'Sports & Fitness', 'Sports and fitness products', NULL, '1', '2026-09-07 18:52:21');
INSERT INTO `product_category` VALUES ('9', 'Toys & Games', 'Toys and games for kids', NULL, '1', '2026-09-07 18:52:21');
INSERT INTO `product_category` VALUES ('10', 'Books & Stationery', 'Books, notebooks and stationery products', NULL, '1', '2026-09-07 18:52:21');

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`image_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `product_prices`;
CREATE TABLE `product_prices` (
  `price_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`price_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_prices_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `product_prices` VALUES ('1', '1', '123.02', '72.55', '197.53', '2009-06-22', '1970-04-16', '0', '2026-09-07 18:53:55');

DROP TABLE IF EXISTS `product_rating`;
CREATE TABLE `product_rating` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `rating` decimal(2,1) NOT NULL,
  `review` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`rating_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_rating_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `product_subcategory`;
CREATE TABLE `product_subcategory` (
  `subcategory_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `subcategory_name` varchar(100) NOT NULL,
  `subcategory_description` text DEFAULT NULL,
  `subcategory_image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`subcategory_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_subcategory_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `product_subcategory` VALUES ('1', '1', 'Televisions', 'Smart TVs and LED TVs', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('2', '1', 'Headphones & Earphones', 'Wired and wireless headphones', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('3', '1', 'Speakers', 'Bluetooth and home speakers', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('4', '1', 'Cameras', 'Digital cameras and accessories', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('5', '2', 'Smartphones', 'Latest smartphones and mobile phones', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('6', '2', 'Tablets', 'Android and other tablets', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('7', '2', 'Mobile Accessories', 'Mobile covers, chargers and cables', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('8', '2', 'Smart Watches', 'Smart watches and fitness watches', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('9', '3', 'Laptops', 'Laptops for personal and professional use', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('10', '3', 'Desktop Computers', 'Desktop computers and PCs', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('11', '3', 'Computer Accessories', 'Keyboard, mouse and other accessories', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('12', '3', 'Printers', 'Printers and printing accessories', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('13', '4', 'Men Clothing', 'Clothing for men', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('14', '4', 'Women Clothing', 'Clothing for women', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('15', '4', 'Kids Clothing', 'Clothing for kids', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('16', '4', 'Shoes & Footwear', 'Shoes and footwear', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('17', '4', 'Bags & Accessories', 'Bags and fashion accessories', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('18', '5', 'Makeup', 'Makeup and cosmetics', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('19', '5', 'Skincare', 'Skin care products', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('20', '5', 'Hair Care', 'Hair care products', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('21', '5', 'Personal Care', 'Personal care products', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('22', '5', 'Fragrances', 'Perfumes and fragrances', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('23', '6', 'Kitchen Appliances', 'Kitchen appliances', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('24', '6', 'Cookware', 'Pots, pans and cookware', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('25', '6', 'Home Decor', 'Home decoration products', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('26', '6', 'Furniture', 'Home furniture', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('27', '6', 'Storage & Organization', 'Storage products', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('28', '7', 'Fruits & Vegetables', 'Fresh fruits and vegetables', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('29', '7', 'Snacks & Beverages', 'Snacks and beverages', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('30', '7', 'Staples', 'Rice, flour and pulses', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('31', '7', 'Dairy Products', 'Milk, butter and cheese', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('32', '8', 'Fitness Equipment', 'Gym and fitness equipment', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('33', '8', 'Sports Shoes', 'Shoes for sports and fitness', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('34', '8', 'Cricket', 'Cricket equipment and accessories', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('35', '8', 'Football', 'Football equipment and accessories', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('36', '9', 'Kids Toys', 'Toys for kids', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('37', '9', 'Board Games', 'Indoor board games', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('38', '9', 'Educational Toys', 'Educational and learning toys', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('39', '9', 'Remote Control Toys', 'Remote control toys', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('40', '10', 'Fiction Books', 'Fiction and story books', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('41', '10', 'Educational Books', 'Educational and academic books', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('42', '10', 'Notebooks', 'Notebooks and writing pads', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('43', '10', 'Pens & Writing', 'Pens and writing materials', NULL, '1', '2026-09-07 18:53:02');
INSERT INTO `product_subcategory` VALUES ('44', '10', 'Art & Craft', 'Art and craft supplies', NULL, '1', '2026-09-07 18:53:02');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `subcategory_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `brand_name` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `product_code` (`product_code`),
  KEY `subcategory_id` (`subcategory_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`subcategory_id`) REFERENCES `product_subcategory` (`subcategory_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` VALUES ('1', '35', 'Vijay Dhayalan', 'Esse iste facere eum', 'Sed pariatur Odit d', '459', '0', '2026-09-07 18:53:49', '2026-09-07 18:53:49', 'Muthu Devi', 'Nobis quia architect', 'Exercitation saepe p', 'Perferendis nihil do');

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user` VALUES ('1', 'Tanya', '9979450222', 'admin', '1', '2026-09-07 18:49:56');

DROP TABLE IF EXISTS `user_address`;
CREATE TABLE `user_address` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `address_type` enum('home','office','other') DEFAULT 'home',
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`address_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user_login_history`;
CREATE TABLE `user_login_history` (
  `login_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_status` enum('success','failed') DEFAULT 'success',
  PRIMARY KEY (`login_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user_otp`;
CREATE TABLE `user_otp` (
  `otp_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `otp_type` enum('signup','login','forgot_password') NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`otp_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_otp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user_otp` VALUES ('1', '1', '555315', 'login', '2026-09-07 18:54:56', '1', '1', '2026-09-07 18:49:56');

DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE `user_profile` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`profile_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`wishlist_id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


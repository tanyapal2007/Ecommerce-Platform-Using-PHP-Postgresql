<?php

session_start();

include "config/database.php";


// =====================================================
// CHECK LOGIN
// =====================================================

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$message = "";
$message_type = "";


// =====================================================
// SAVE CHANGES
// =====================================================

if (isset($_POST['update_profile'])) {

    // =================================================
    // GET USER TABLE DATA
    // =================================================

    $username = mysqli_real_escape_string(
        $conn,
        $_POST['username']
    );

    $contact_number = mysqli_real_escape_string(
        $conn,
        $_POST['contact_number']
    );


    // =================================================
    // GET USER PROFILE DATA
    // =================================================

    $first_name = mysqli_real_escape_string(
        $conn,
        $_POST['first_name']
    );

    $last_name = mysqli_real_escape_string(
        $conn,
        $_POST['last_name']
    );

    $gender = mysqli_real_escape_string(
        $conn,
        $_POST['gender']
    );

    $date_of_birth = mysqli_real_escape_string(
        $conn,
        $_POST['date_of_birth']
    );


    // =================================================
    // CHECK CONTACT NUMBER
    // =================================================

    $check_sql = "SELECT user_id
                  FROM user
                  WHERE phone = '$contact_number'
                  AND user_id != '$user_id'
                  LIMIT 1";

    $check_result = mysqli_query(
        $conn,
        $check_sql
    );


    if (!$check_result) {

        $message =
            "Contact Check Error: " .
            mysqli_error($conn);

        $message_type = "danger";
    } elseif (mysqli_num_rows($check_result) > 0) {

        $message =
            "This contact number is already registered.";

        $message_type = "danger";
    } else {


        // =================================================
        // 1. UPDATE USER TABLE
        // =================================================

        $user_sql = "UPDATE user
                     SET name = '$username',
                         phone = '$contact_number'
                     WHERE user_id = '$user_id'";


        if (!mysqli_query($conn, $user_sql)) {

            $message =
                "User Update Error: " .
                mysqli_error($conn);

            $message_type = "danger";
        } else {


            // =================================================
            // 2. CHECK USER_PROFILE RECORD
            // =================================================

            $profile_check_sql = "SELECT profile_id
                                  FROM user_profile
                                  WHERE user_id = '$user_id'
                                  LIMIT 1";

            $profile_result = mysqli_query(
                $conn,
                $profile_check_sql
            );


            if (!$profile_result) {

                $message =
                    "Profile Check Error: " .
                    mysqli_error($conn);

                $message_type = "danger";
            } else {


                // =================================================
                // 3. IF PROFILE EXISTS → UPDATE
                // =================================================

                if (mysqli_num_rows($profile_result) > 0) {

                    $profile_sql = "UPDATE user_profile
                                    SET first_name = '$first_name',
                                        last_name = '$last_name',
                                        gender = '$gender',
                                        date_of_birth = '$date_of_birth'
                                    WHERE user_id = '$user_id'";


                    if (!mysqli_query($conn, $profile_sql)) {

                        $message =
                            "Profile Update Error: " .
                            mysqli_error($conn);

                        $message_type = "danger";
                    }
                }


                // =================================================
                // 4. IF PROFILE DOES NOT EXIST → INSERT
                // =================================================

                else {

                    $profile_sql = "INSERT INTO user_profile
                                    (
                                        user_id,
                                        first_name,
                                        last_name,
                                        gender,
                                        date_of_birth
                                    )
                                    VALUES
                                    (
                                        '$user_id',
                                        '$first_name',
                                        '$last_name',
                                        '$gender',
                                        '$date_of_birth'
                                    )";


                    if (!mysqli_query($conn, $profile_sql)) {

                        $message =
                            "Profile Insert Error: " .
                            mysqli_error($conn);

                        $message_type = "danger";
                    }
                }
            }


            // =================================================
            // 5. PROFILE IMAGE
            // =================================================

            if (
                $message_type != "danger" &&
                isset($_FILES['profile_image']) &&
                $_FILES['profile_image']['error'] == 0
            ) {


                $image_name =
                    $_FILES['profile_image']['name'];

                $tmp_name =
                    $_FILES['profile_image']['tmp_name'];


                // Get extension

                $extension = strtolower(
                    pathinfo(
                        $image_name,
                        PATHINFO_EXTENSION
                    )
                );


                // Allowed extensions

                $allowed_extensions = array(
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                );


                if (
                    in_array(
                        $extension,
                        $allowed_extensions
                    )
                ) {


                    // =================================================
                    // CREATE FOLDER
                    // =================================================

                    $upload_folder =
                        "uploads/profile/";


                    if (!is_dir($upload_folder)) {

                        mkdir(
                            $upload_folder,
                            0777,
                            true
                        );
                    }


                    // =================================================
                    // UNIQUE IMAGE NAME
                    // =================================================

                    $new_image_name =
                        "user_" .
                        $user_id .
                        "_" .
                        time() .
                        "." .
                        $extension;


                    $image_path =
                        $upload_folder .
                        $new_image_name;


                    // =================================================
                    // MOVE IMAGE
                    // =================================================

                    if (
                        move_uploaded_file(
                            $tmp_name,
                            $image_path
                        )
                    ) {


                        // Update profile image

                        $image_sql =
                            "UPDATE user_profile
                             SET profile_image = '$image_path'
                             WHERE user_id = '$user_id'";


                        if (!mysqli_query(
                            $conn,
                            $image_sql
                        )) {

                            $message =
                                "Image Update Error: " .
                                mysqli_error($conn);

                            $message_type = "danger";
                        }
                    } else {

                        $message =
                            "Profile image upload failed.";

                        $message_type = "danger";
                    }
                } else {

                    $message =
                        "Only JPG, JPEG, PNG and WEBP images are allowed.";

                    $message_type = "danger";
                }
            }


            // =================================================
            // 6. REDIRECT TO MY ACCOUNT
            // =================================================

            if ($message_type != "danger") {

                header("Location: myaccount.php");
                exit;
            }
        }
    }
}


// =====================================================
// GET USER DATA
// =====================================================

$user_sql = "SELECT *
             FROM user
             WHERE user_id = '$user_id'
             LIMIT 1";

$user_result = mysqli_query(
    $conn,
    $user_sql
);


if (
    !$user_result ||
    mysqli_num_rows($user_result) == 0
) {

    session_destroy();

    header("Location: login.php");
    exit;
}


$user = mysqli_fetch_assoc(
    $user_result
);


// =====================================================
// GET USER PROFILE DATA
// =====================================================

$profile_sql = "SELECT *
                FROM user_profile
                WHERE user_id = '$user_id'
                LIMIT 1";

$profile_result = mysqli_query(
    $conn,
    $profile_sql
);


if (
    $profile_result &&
    mysqli_num_rows($profile_result) > 0
) {

    $profile = mysqli_fetch_assoc(
        $profile_result
    );
} else {

    $profile = array();
}


// =====================================================
// SET VALUES
// =====================================================

$username =
    $user['name'] ?? "";

$phone =
    $user['phone'] ?? "";

$role =
    $user['role'] ?? "customer";


$first_name =
    $profile['first_name'] ?? "";

$last_name =
    $profile['last_name'] ?? "";

$gender =
    $profile['gender'] ?? "";

$date_of_birth =
    $profile['date_of_birth'] ?? "";


$profile_image =
    !empty($profile['profile_image'])
    ? $profile['profile_image']
    : "assets/img/default-user.png";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Edit Profile</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <style>
        body {
            background: #f5f6f8;
            font-family: Arial, sans-serif;
        }

        .account-wrapper {
            padding: 50px 0;
        }

        .account-sidebar {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #eee;
        }

        .profile-name {
            font-size: 20px;
            font-weight: 600;
            margin-top: 15px;
        }

        .profile-role {
            color: #777;
            font-size: 14px;
        }

        .account-menu {
            margin-top: 25px;
        }

        .account-menu a {
            display: block;
            padding: 12px 15px;
            margin-bottom: 6px;
            text-decoration: none;
            color: #333;
            border-radius: 8px;
        }

        .account-menu a:hover,
        .account-menu a.active {
            background: #212529;
            color: white;
        }

        .account-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        }

        .account-title {
            font-size: 28px;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            min-height: 48px;
            border-radius: 8px;
        }

        .profile-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #eee;
        }

        .btn-save {
            min-height: 48px;
            border-radius: 8px;
        }

        @media (max-width: 991px) {

            .account-sidebar {
                margin-bottom: 25px;
            }

        }
    </style>

</head>


<body>


    <div class="container account-wrapper">

        <div class="row g-4">


            <!-- =================================================
             SIDEBAR
        ================================================== -->

            <div class="col-lg-3">

                <div class="account-sidebar text-center">


                    <!-- Profile Image -->

                    <img
                        src="<?php
                                echo htmlspecialchars(
                                    $profile_image
                                );
                                ?>"
                        class="profile-image"
                        alt="Profile">


                    <!-- Username -->

                    <div class="profile-name">

                        <?php
                        echo htmlspecialchars(
                            $username
                        );
                        ?>

                    </div>


                    <!-- Role -->

                    <div class="profile-role">

                        <?php
                        echo ucfirst(
                            htmlspecialchars(
                                $role
                            )
                        );
                        ?>

                    </div>


                    <!-- Menu -->

                    <div class="account-menu text-start">


                        <a href="my_account.php">

                            My Account

                        </a>


                        <a
                            href="edit_profile.php"
                            class="active">

                            Edit Profile

                        </a>


                        <a href="orders.php">

                            My Orders

                        </a>


                        <a href="wishlist.php">

                            My Wishlist

                        </a>


                        <a href="cart.php">

                            My Cart

                        </a>


                        <a href="addresses.php">

                            My Addresses

                        </a>


                        <a
                            href="logout.php"
                            class="text-danger">

                            Logout

                        </a>

                    </div>

                </div>

            </div>



            <!-- =================================================
             MAIN CONTENT
        ================================================== -->

            <div class="col-lg-9">

                <div class="account-content">


                    <h2 class="account-title mb-1">

                        Edit Profile

                    </h2>


                    <p class="text-muted mb-4">

                        Update your personal information.

                    </p>



                    <!-- =================================================
                     ERROR MESSAGE
                ================================================== -->

                    <?php if ($message != "") { ?>

                        <div
                            class="alert alert-<?php
                                                echo $message_type;
                                                ?>">

                            <?php
                            echo htmlspecialchars(
                                $message
                            );
                            ?>

                        </div>

                    <?php } ?>



                    <!-- =================================================
                     FORM
                ================================================== -->

                    <form
                        method="POST"
                        enctype="multipart/form-data">


                        <!-- =================================================
                         PROFILE PHOTO
                    ================================================== -->

                        <h5 class="mb-3">

                            Profile Photo

                        </h5>


                        <div
                            class="d-flex
                               align-items-center
                               gap-4
                               flex-wrap
                               mb-4">


                            <img
                                src="<?php
                                        echo htmlspecialchars(
                                            $profile_image
                                        );
                                        ?>"
                                class="profile-preview"
                                alt="Profile">


                            <div>

                                <label class="form-label">

                                    Change Profile Photo

                                </label>


                                <input
                                    type="file"
                                    name="profile_image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp">


                                <small class="text-muted">

                                    JPG, JPEG, PNG or WEBP

                                </small>

                            </div>

                        </div>


                        <hr>



                        <!-- =================================================
                         USER TABLE FIELDS
                    ================================================== -->

                        <h5 class="mb-3 mt-4">

                            Account Information

                        </h5>


                        <div class="row g-3 mb-4">


                            <!-- Username -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Username

                                </label>


                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $username
                                            );
                                            ?>"
                                    required>

                            </div>



                            <!-- Contact Number -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Contact Number

                                </label>


                                <input
                                    type="text"
                                    name="contact_number"
                                    class="form-control"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $phone
                                            );
                                            ?>"
                                    maxlength="10"
                                    pattern="[0-9]{10}"
                                    required>

                            </div>

                        </div>


                        <hr>



                        <!-- =================================================
                         USER PROFILE TABLE FIELDS
                    ================================================== -->

                        <h5 class="mb-3 mt-4">

                            Personal Information

                        </h5>


                        <div class="row g-3 mb-4">


                            <!-- First Name -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    First Name

                                </label>


                                <input
                                    type="text"
                                    name="first_name"
                                    class="form-control"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $first_name
                                            );
                                            ?>"
                                    required>

                            </div>



                            <!-- Last Name -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Last Name

                                </label>


                                <input
                                    type="text"
                                    name="last_name"
                                    class="form-control"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $last_name
                                            );
                                            ?>">

                            </div>



                            <!-- Gender -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Gender

                                </label>


                                <select
                                    name="gender"
                                    class="form-select">


                                    <option value="">

                                        Select Gender

                                    </option>


                                    <option
                                        value="male"
                                        <?php
                                        if ($gender == "male") {
                                            echo "selected";
                                        }
                                        ?>>

                                        Male

                                    </option>


                                    <option
                                        value="female"
                                        <?php
                                        if ($gender == "female") {
                                            echo "selected";
                                        }
                                        ?>>

                                        Female

                                    </option>


                                    <option
                                        value="other"
                                        <?php
                                        if ($gender == "other") {
                                            echo "selected";
                                        }
                                        ?>>

                                        Other

                                    </option>


                                </select>

                            </div>



                            <!-- Date of Birth -->

                            <div class="col-md-6">

                                <label class="form-label">

                                    Date of Birth

                                </label>


                                <input
                                    type="date"
                                    name="date_of_birth"
                                    class="form-control"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $date_of_birth
                                            );
                                            ?>">

                            </div>

                        </div>



                        <!-- =================================================
                         BUTTONS
                    ================================================== -->

                        <div class="d-flex gap-2">


                            <!-- SAVE -->

                            <button
                                type="submit"
                                name="update_profile"
                                class="btn btn-dark btn-save px-4">

                                Save Changes

                            </button>


                            <!-- CANCEL -->

                            <a
                                href="my_account.php"
                                class="btn btn-outline-secondary btn-save px-4">

                                Cancel

                            </a>


                        </div>


                    </form>

                </div>

            </div>

        </div>

    </div>


</body>

</html>
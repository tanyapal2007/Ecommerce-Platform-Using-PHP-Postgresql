<?php

session_start();

if (
    isset($_SESSION['login']) &&
    $_SESSION['login'] === true &&
    isset($_SESSION['user_id']) &&
    !empty($_SESSION['user_id'])
) {
    header("Location: index.php");
    exit();
}

include "config/database.php";

$message = "";
$otp_generated = false;


/* =========================================
   GENERATE OTP
========================================= */

if (isset($_POST['generate_otp'])) {

    $username = $_POST['username'];
    $contact_number = $_POST['contact_number'];

    /* ======================================
       CHECK USER
    ====================================== */

    $sql = "SELECT user_id
            FROM users
            WHERE name = :name
            AND phone = :phone
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':name' => $username,
        ':phone' => $contact_number
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);


    /* ======================================
       USER EXISTS
    ====================================== */

    if ($row) {

        $user_id = $row['user_id'];
    }

    /* ======================================
   NEW USER
====================================== */ else {

        $sql = "INSERT INTO users
            (name, phone, role)
            VALUES
            (:name, :phone, 'user')
            RETURNING user_id";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':name' => $username,
            ':phone' => $contact_number
        ]);

        $user_id = $stmt->fetchColumn();
    }

    /* ======================================
       GENERATE OTP
    ====================================== */

    if ($user_id) {

        $otp = rand(100000, 999999);

        $_SESSION['user_id'] = $user_id;


        /* ======================================
           STORE OTP
        ====================================== */

        $sql = "INSERT INTO user_otp
                (
                    user_id,
                    otp,
                    otp_type,
                    expires_at,
                    is_verified,
                    used
                )
                VALUES
                (
                    :user_id,
                    :otp,
                    'login',
                    NOW() + INTERVAL '5 minutes',
                    0,
                    0
                )";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':user_id' => $user_id,
            ':otp' => $otp
        ]);

        $message = "OTP Generated Successfully.";

        $otp_generated = true;

        // Testing only
        $_SESSION['generated_otp'] = $otp;
    }
}


/* =========================================
   RESEND OTP
========================================= */

if (isset($_POST['resend_otp'])) {

    $user_id = $_SESSION['user_id'] ?? 0;

    if ($user_id > 0) {

        $otp = rand(100000, 999999);

        $sql = "INSERT INTO user_otp
                (
                    user_id,
                    otp,
                    otp_type,
                    expires_at,
                    is_verified,
                    used
                )
                VALUES
                (
                    :user_id,
                    :otp,
                    'login',
                    NOW() + INTERVAL '5 minutes',
                    0,
                    0
                )";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':user_id' => $user_id,
            ':otp' => $otp
        ]);

        $_SESSION['generated_otp'] = $otp;

        $message = "New OTP Generated Successfully.";

        $otp_generated = true;
    }
}


/* =========================================
   VERIFY OTP
========================================= */

/* =========================================
   VERIFY OTP
========================================= */

if (isset($_POST['verify_otp'])) {

    $entered_otp = $_POST['otp'];

    $user_id = $_SESSION['user_id'] ?? 0;


    /* ======================================
       CHECK OTP
    ====================================== */

    $sql = "SELECT *
            FROM user_otp
            WHERE user_id = :user_id
            AND otp = :otp
            AND otp_type = 'login'
            AND used = 0
            AND expires_at >= NOW()
            ORDER BY otp_id DESC
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':user_id' => $user_id,
        ':otp' => $entered_otp
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);


    /* ======================================
       OTP VALID
    ====================================== */

    if ($row) {

        $otp_id = $row['otp_id'];


        /* ======================================
           MARK OTP VERIFIED
        ====================================== */

        $update_otp = "UPDATE user_otp
                       SET is_verified = 1,
                           used = 1
                       WHERE otp_id = :otp_id";

        $stmt = $conn->prepare($update_otp);

        $stmt->execute([
            ':otp_id' => $otp_id
        ]);


        /* ======================================
           GET USER DETAILS
        ====================================== */

        $sql_user = "SELECT
                        user_id,
                        name,
                        phone,
                        role
                     FROM users
                     WHERE user_id = :user_id
                     LIMIT 1";

        $stmt = $conn->prepare($sql_user);

        $stmt->execute([
            ':user_id' => $user_id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($user) {

            /* ======================================
               SET LOGIN SESSION
            ====================================== */

            $_SESSION['login'] = true;

            // IMPORTANT: user_id, NOT id
            $_SESSION['user_id'] = $user['user_id'];

            $_SESSION['username'] = $user['name'];

            $_SESSION['phone'] = $user['phone'];

            $_SESSION['role'] = $user['role'];

            $_SESSION['login_time'] = date("Y-m-d H:i:s");


            unset($_SESSION['generated_otp']);


            /* ======================================
               REDIRECT
            ====================================== */

            if ($user['role'] === 'admin') {

                header("Location: dashboard/index3.php");
                exit();
            } else {

                header("Location: index.php");
                exit();
            }
        } else {

            $message = "User details not found.";

            $otp_generated = true;
        }
    } else {

        $message = "Invalid or expired OTP.";

        $otp_generated = true;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Login</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <style>
        body {
            background: #f5f6f8;
        }


        .login-container {
            min-height: 100vh;
        }


        .company-section {

            background: #212529;

            color: white;

            padding: 60px;

            display: flex;

            align-items: center;
        }


        .company-section h1 {

            font-size: 42px;

            font-weight: bold;
        }


        .company-section p {

            color: #ddd;

            line-height: 1.7;
        }


        .login-section {

            background: white;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 40px;
        }


        .login-box {

            width: 100%;

            max-width: 430px;
        }


        .form-control {

            height: 50px;

            border-radius: 8px;
        }


        .btn-login {

            height: 50px;

            border-radius: 8px;
        }


        @media (max-width: 767px) {

            .company-section {

                padding: 40px 25px;

                text-align: center;
            }


            .company-section h1 {

                font-size: 32px;
            }


            .login-section {

                padding: 35px 25px;
            }

        }
    </style>

</head>


<body>


    <div class="container-fluid login-container">

        <div class="row min-vh-100">


            <!-- =================================
             LEFT SIDE
        ================================= -->

            <div class="col-lg-6 company-section">

                <div>

                    <h1>
                        My Company
                    </h1>


                    <p>

                        Welcome to our website.

                        Login to access your account
                        and explore our products
                        and services.

                    </p>


                    <p>

                        <strong>Contact:</strong>

                        +91 9876543210

                    </p>


                    <p>

                        <strong>Address:</strong>

                        Ahmedabad, Gujarat

                    </p>

                </div>

            </div>



            <!-- =================================
             RIGHT SIDE
        ================================= -->

            <div class="col-lg-6 login-section">

                <div class="login-box">


                    <h2 class="mb-2">

                        Login

                    </h2>


                    <p class="text-muted mb-4">

                        Enter your details to continue

                    </p>



                    <!-- MESSAGE -->

                    <?php if ($message != "") { ?>

                        <div class="alert alert-info">

                            <?php echo $message; ?>

                        </div>

                    <?php } ?>



                    <!-- =================================
                     USERNAME + PHONE FORM
                ================================= -->

                    <?php if (!$otp_generated) { ?>


                        <form method="POST">


                            <!-- Username -->

                            <div class="mb-3">

                                <label class="form-label">

                                    Username

                                </label>


                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    placeholder="Enter username"
                                    required>

                            </div>



                            <!-- Contact Number -->

                            <div class="mb-3">

                                <label class="form-label">

                                    Contact Number

                                </label>


                                <input
                                    type="text"
                                    name="contact_number"
                                    class="form-control"
                                    placeholder="Enter contact number"
                                    maxlength="10"
                                    pattern="[0-9]{10}"
                                    required>

                            </div>



                            <!-- Generate OTP -->

                            <button
                                type="submit"
                                name="generate_otp"
                                class="btn btn-dark w-100 btn-login">

                                Generate OTP

                            </button>


                        </form>



                    <?php } else { ?>


                        <!-- =================================
                         OTP FORM
                    ================================= -->


                        <div class="alert alert-success">

                            OTP has been generated successfully.

                        </div>


                        <!-- TESTING ONLY -->

                        <div class="alert alert-warning">

                            Your OTP:

                            <strong>

                                <?php

                                echo $_SESSION['generated_otp'];

                                ?>

                            </strong>

                        </div>



                        <!-- VERIFY OTP -->

                        <form method="POST">


                            <div class="mb-3">

                                <label class="form-label">

                                    Enter OTP

                                </label>


                                <input
                                    type="text"
                                    name="otp"
                                    class="form-control"
                                    placeholder="Enter 6 digit OTP"
                                    maxlength="6"
                                    pattern="[0-9]{6}"
                                    required>

                            </div>


                            <button
                                type="submit"
                                name="verify_otp"
                                class="btn btn-dark w-100 btn-login mb-2">

                                Verify OTP

                            </button>


                        </form>



                        <!-- NEW OTP -->

                        <form method="POST">

                            <button
                                type="submit"
                                name="resend_otp"
                                class="btn btn-outline-dark w-100">

                                Generate New OTP

                            </button>

                        </form>


                    <?php } ?>


                </div>

            </div>


        </div>

    </div>


</body>

</html>
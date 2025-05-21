<?php
// Start the session to maintain user login state
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- This is the contact page of our hotel website -->
    <!-- It shows contact information and a form for sending messages -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waterpool Suites - CONTACT</title>
    <?php require('includes/links.php'); // Include CSS and JS files ?>


</head>
<body class="bg-white">
        <?php require('includes/header.php'); ?>

        <div class="my-5 px-4">
            <h2 class="fw-bold h-font text-center">CONTACT US</h2>
            <div class="h-line bg-dark"></div>
            <p class="text-center mt-3">
            You can contact Waterpool Micro Hotel through multiple channels for inquiries or reservations.
            </p>
        </div>

            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-6 mb-5 px-4">
                        <div class="bg-white rounded shadow p-4">
                        <iframe class="w-100 rounded mb-4" height="320" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1311.8142748527318!2d125.12478626379351!3d8.140556427538876!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32ffa9f609e872c5%3A0x931cdc1731925afb!2sWaterpool%20Inn%20%26%20Suites!5e1!3m2!1sen!2sph!4v1745657345160!5m2!1sen!2sph"  height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

                            <h5>Address</h5>
                            <a href="https://maps.app.goo.gl/rvu7x9h36Hf8HFsQ8" target="_blank" class="d-inline-block text-decoration-none text-dark mb-2>
                                <i class="bi bi-geo-alt-fill"></i>44RG+2CP, Sayre Hwy, Malaybalay, 8700 Bukidnon, Philippines
                            </a>

                            <h5 class="mt-4">Call us</h5>
                        <a href="tel: +9233345671"class="d-inline-block mb-2 text-decoration-none text-dark">
                            <i class="bi bi-telephone-fill"></i>
                            +63 9175551207</a>
                            <br>
                            <a href="tel: +9233345671"class="d-inline-block text-decoration-none text-dark">
                            <i class="bi bi-telephone-fill"></i>
                            +63 9175551207</a>
                            <h5 class="mt-4">Email</h5>
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=waterpoolmicro.hotelmc@gmail.com&su=Subject&body=Body%20text" target="_blank" class="d-inline-block text-decoration-none text-dark">
                            <i class="bi bi-envelope-at-fill"></i> waterpoolmicro.hotelmc@gmail.com
                            </a>

                            <h5 class="mt-4">Follow us</h5>
                        <a href="#"class="d-inline-block text-dark fs-5 me-2">
                            <i class="bi bi-twitter-x"></i>
                        </a>

                        <a href="#"class="d-inline-block text-dark fs-5 me-2">
                            <i class="bi bi-facebook"></i>
                        </a>

                        <a href="#"class="d-inline-block text-dark fs-5 ">
                            <i class="bi bi-instagram me-1"></i>
                        </a>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 px-4">
                        <div class="bg-white rounded shadow p-4">
                        <?php
                        // Show error message if there is one
                        if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Show success message if message was sent
                        if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success" role="alert">
                                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>
                            <!-- Contact form - sends data to process_contact.php -->
                            <form action="process_contact.php" method="POST" >
                                <h5>Send a Message</h5>
                                <!-- Name field -->
                                <div class="mt-3">
                                    <label class="form-label" style="font-weight: 500;">Name</label>
                                    <input type="text" class="form-control shadow-none" name="name" required>
                                </div>
                                <!-- Email field -->
                                <div class="mt-3">
                                    <label class="form-label" style="font-weight: 500;">Email</label>
                                    <input type="email" class="form-control shadow-none" name="email" required>
                                </div>
                                <!-- Subject field -->
                                <div class="mt-3">
                                    <label class="form-label" style="font-weight: 500;">Subject</label>
                                    <input type="text" class="form-control shadow-none" name="subject" required>
                                </div>
                                <!-- Message field -->
                                <div class="mt-3">
                                    <label class="form-label" style="font-weight: 500;">Message</label>
                                    <textarea class="form-control shadow-none" rows="5" style="resize: none;" name="message" required></textarea>
                                </div>
                                <!-- Submit button -->
                                <button type="submit" class="btn text-white custom-bg mt-3 shadow-none">SEND</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

         <?php require('includes/footer.php'); ?>



</body>
</html>
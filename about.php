<?php
// Start the session to maintain user login state
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- This is the about page of our hotel website -->
    <!-- It shows information about the hotel, statistics, and the management team -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waterpool Micro - ABOUT</title>
    <?php require('includes/links.php'); // Include CSS and JS files ?>

    <style>
        /* Style for the statistic boxes */
        .box{
            border-top-color: var(--teal) !important;
        }

    </style>

</head>
<body class="bg-white">
        <?php require('includes/header.php'); ?>

        <div class="my-5 px-4">
            <h2 class="fw-bold h-font text-center">ABOUT US</h2>
            <div class="h-line bg-dark"></div>

        </div>

        <div class="container">
            <div class="row justify-content-between align-items-center">
                <div class="col-lg-6 col-md-5 mb-4 order-lg-1 order-md-1 order-2">
                    <h3 class="mb-3">Waterpool Micro Hotel</h3>
                    <p style="font-size: 20px;">
                    Waterpool Micro Hotel is a budget-friendly hotel located in Malaybalay City, Bukidnon,
                    offering guests a comfortable and relaxing stay. With a focus on simplicity and convenience,
                    the hotel provides clean and well-maintained rooms, essential amenities, and a peaceful atmosphere
                    ideal for travelers, families, and business guests. Situated along Sayre Highway in Barangay Casisang,
                    the hotel is easily accessible and close to key city attractions. Whether you're visiting for leisure or work,
                    Waterpool Micro Hotel is committed to delivering warm hospitality and a restful experience at an affordable price.
                    </p>
                </div>
                <div class="col-lg-5 col-md-5 mb-4 order-lg-2 order-md-2 order-1">
                    <img src="images/about/people.png" alt="people">

                </div>
            </div>
        </div>

        <div class="container mt-5">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4 px-4">
                    <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                        <img src="images/about/room.png" alt="" width="70px">
                        <h2 class="mt-3">12 ROOMS</h2>
                    </div>

                </div>
                <div class="col-lg-3 col-md-6 mb-4 px-4">
                    <div class="bg-white rounded shadow p-3 border-top border-4 text-center box">
                        <img src="images/about/people.png" alt="" width="50px">
                        <h2 class="mt-3">100+ CUSTOMERS</h2>
                    </div>

                </div>
                <div class="col-lg-3 col-md-6 mb-4 px-4">
                    <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                        <img src="images/about/room.png" alt="" width="70px">
                        <h2 class="mt-3">100+ REVIEWS</h2>
                    </div>

                </div>
                <div class="col-lg-3 col-md-6 mb-4 px-4">
                    <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                        <img src="images/about/room.png" alt="" width="70px">
                        <h2 class="mt-3">10+ STAFF</h2>
                    </div>

                </div>

            </div>
        </div>


        <h3 class="my-5 fw-bold h-font text-center">MANAGEMENT TEAM</h3>

        <div class="container px-4">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper mb-5">
                    <div class="swiper-slide  bg-white text-center overflow-hidden rounded-pill">
                        <img src="images/about/IMG20230901200551.jpg" alt="" class="w-100">
                        <h5 class="mt-2">Kendrick Amparado</h5>
                    </div>
                    <div class="swiper-slide  bg-white text-center overflow-hidden rounded-pill">
                        <img src="images/about/95a0da5f-7b07-4536-ba20-ade3d46b25f6.jpg" alt="" class="w-100">
                        <h5 class="mt-2">Elbert Cahanap</h5>
                    </div>
                    <div class="swiper-slide  bg-white text-center overflow-hidden rounded-pill">
                        <img src="images/about/475679912_1405836873721880_7762433971019574409_n.jpg" alt="" class="w-100">
                        <h5 class="mt-2">Jeric Centillas</h5>
                    </div>
                    <div class="swiper-slide  bg-white text-center overflow-hidden rounded-pill">
                        <img src="images/about/95a0da5f-7b07-4536-ba20-ade3d46b25f6.jpg" alt="" class="w-100">
                        <h5 class="mt-2">Elbert Cahanap</h5>
                    </div>
                     <div class="swiper-slide  bg-white text-center overflow-hidden rounded-pill">
                        <img src="images/about/IMG20230901200551.jpg" alt="" class="w-100">
                        <h5 class="mt-2">Kendrick Amparado</h5>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>

         <?php require('includes/footer.php'); ?>

         <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

        <script>
            // This sets up the image slider for the management team
            var swiper = new Swiper(".mySwiper", {
                spaceBetween: 40, // Space between slides
                pagination: {
                    el: ".swiper-pagination", // Add pagination dots
                },
                // Make the slider responsive for different screen sizes
                breakpoints: {
                    320: {
                    slidesPerView: 1, // Show 1 slide on small phones
                    },
                    640: {
                    slidesPerView: 1, // Show 1 slide on phones
                    },
                    768: {
                    slidesPerView: 3, // Show 3 slides on tablets
                    },
                    1024: {
                    slidesPerView: 3, // Show 3 slides on desktops
                    },
                }
                });
        </script>

</body>
</html>
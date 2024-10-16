<?php include "db_conn.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>View</title>
    <style>
        body {
            font-family: Arial, sans-serif; /* Use a clean font for better readability */
            background-color: #f9f9f9; /* Light background color for a clean look */
            margin: 0; /* Remove default margin */
            padding: 20px; /* Add padding for better spacing */
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px; /* Space below the button */
            padding: 10px 20px; /* Padding around the text */
            background-color: #007bff; /* Bootstrap primary blue color */
            color: white; /* White text color */
            text-decoration: none; /* Remove underline */
            border-radius: 5px; /* Rounded corners */
            transition: background-color 0.3s ease; /* Smooth background color transition */
            font-size: 16px; /* Slightly larger font size */
        }
        .back-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
        .alb {
            margin: 10px;
            display: inline-block;
            text-align: center; /* Center-align the image and number */
        }
        .alb img {
            width: 300px; /* Set the desired width */
            height: 300px; /* Set a fixed height to make all images the same size */
            object-fit: cover; /* Maintain aspect ratio and cover the specified dimensions */
        }
        .image-number {
            margin-top: 5px; /* Space between image and number */
            font-size: 14px; /* Font size for image number */
            color: #555; /* Slightly darker color for contrast */
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-button">&#8592; Back</a>

    <?php
        $sql = "SELECT * FROM image ORDER BY id DESC"; // Table name should be 'image'
        $res = mysqli_query($conn, $sql);
        $totalImages = mysqli_num_rows($res); // Get total number of images

        // Display total number of images
        echo "<h2>Total Images Uploaded: " . $totalImages . "</h2>";

        if ($totalImages > 0) {
            $counter = 1; // Initialize a counter for image numbering
            while ($images = mysqli_fetch_assoc($res)) { ?>
                <div class="alb">
                    <img src="uploads/<?= htmlspecialchars($images['image_url']) ?>" alt="Image">
                    <div class="image-number"><?= $counter++; ?></div> <!-- Display image number -->
                </div>
    <?php
            } // End of while loop
        } else {
            echo "<p>No images found.</p>";
        }
    ?>

</body>
</html>

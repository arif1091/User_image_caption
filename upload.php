<?php
// Check if form is submitted and image files are uploaded
if (isset($_POST['submit']) && isset($_FILES['my_image'])) {
    include "db_conn.php"; // Include database connection

    $images = $_FILES['my_image']; // Get array of uploaded images
    $total_images = count($images['name']); // Count the number of images uploaded

    for ($i = 0; $i < $total_images; $i++) {
        $img_name = $images['name'][$i];
        $tmp_name = $images['tmp_name'][$i];
        $img_size = $images['size'][$i];
        $error = $images['error'][$i];

        if ($error === 0) {
            // Allowed file extensions
            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_lc = strtolower($img_ex);
            $allowed_exs = array("jpg", "jpeg", "png");

            if (in_array($img_ex_lc, $allowed_exs)) {
                $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
                $img_upload_path = 'uploads/' . $new_img_name;

                // If the image size is greater than 1MB, compress it
                if ($img_size > 1 * 1024 * 1024) {
                    // Compress the image
                    $quality = 75; // Compression quality for JPG
                    if ($img_ex_lc === "jpg" || $img_ex_lc === "jpeg") {
                        $image = imagecreatefromjpeg($tmp_name);
                        imagejpeg($image, $img_upload_path, $quality); // Compress JPEG
                    } elseif ($img_ex_lc === "png") {
                        $image = imagecreatefrompng($tmp_name);
                        imagepng($image, $img_upload_path, 9); // Compress PNG
                    }
                    imagedestroy($image); // Free memory
                } else {
                    // Move image directly if size is <= 1MB
                    move_uploaded_file($tmp_name, $img_upload_path);
                }

                // Insert into Database
                $sql = "INSERT INTO image (image_url, original_name) VALUES ('$new_img_name', '$img_name')";
                mysqli_query($conn, $sql);
            } else {
                // Display error for invalid file types
                echo "<h1 style='text-align: center; color: red;'>File type not allowed: $img_name</h1>";
                exit();
            }
        } else {
            // Display error for unknown issues
            echo "<h1 style='text-align: center; color: red;'>Unknown error occurred for file: $img_name</h1>";
            exit();
        }
    }

    // Redirect to view page after all images are uploaded
    header("Location: view.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        input[type="file"] {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: calc(100% - 22px);
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Your Images</h1>
        <form action="" method="post" enctype="multipart/form-data">
            <!-- Add the 'multiple' attribute to allow multiple file selection -->
            <input type="file" name="my_image[]" accept=".jpg, .jpeg, .png" multiple required>
            <input type="submit" name="submit" value="Upload">
        </form>
    </div>
</body>
</html>

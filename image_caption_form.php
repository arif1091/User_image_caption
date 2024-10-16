<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to the database
$conn = new mysqli("localhost", "root", "", "test_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Initialize variables
$images = [];
$image_names = []; // Added to store image names (original_name)
$captions = [];
$showImages = false;
$name = $student_id = $email = "";

// Sanitize inputs to prevent XSS
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_info'])) {
        // Retrieve and sanitize user information
        $name = sanitize_input($_POST['name']);
        $student_id = sanitize_input($_POST['student_id']);
        $email = sanitize_input($_POST['email']);

        // Check if the email has already completed submission
        $emailCheckQuery = $conn->prepare("SELECT submission_complete FROM user_image_data WHERE email = ? LIMIT 1");
        $emailCheckQuery->bind_param("s", $email);
        $emailCheckQuery->execute();
        $emailCheckResult = $emailCheckQuery->get_result();
        $row = $emailCheckResult->fetch_assoc();

        if ($row && $row['submission_complete'] == 1) {
            echo "<script>
                alert('This email has already been used for a full submission.');
                window.history.back();
            </script>";
        } else {
            // Get the total number of users
            $userCountResult = $conn->query("SELECT COUNT(DISTINCT email) AS total_users FROM user_image_data");
            $userCountRow = $userCountResult->fetch_assoc();
            $totalUsers = isset($userCountRow['total_users']) ? $userCountRow['total_users'] : 0;

            // Get the total number of images in the database
            $imageCountResult = $conn->query("SELECT COUNT(*) AS total_images FROM image");
            $imageCountRow = $imageCountResult->fetch_assoc();
            $totalImages = isset($imageCountRow['total_images']) ? $imageCountRow['total_images'] : 0;

            // Number of images per user (e.g., 20 images)
            $imagesPerUser = 20;

            // Calculate the offset for the next set of images
            $imageOffset = ($totalUsers * $imagesPerUser) % $totalImages;

            // Adjust the limit if there are fewer than 20 images remaining
            $remainingImages = $totalImages - $imageOffset;
            $limit = ($remainingImages < $imagesPerUser) ? $remainingImages : $imagesPerUser;
            $stmt = $conn->prepare("SELECT image_url, original_name FROM image LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $imageOffset);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $images[] = $row['image_url']; 
                $image_names[] = $row['original_name']; 
                $captions[] = ''; 
            }

            $showImages = true;
        }
    }
// Handle final form submission with captions
if (isset($_POST['submit_captions'])) {
    $name = sanitize_input($_POST['name']);
    $student_id = sanitize_input($_POST['student_id']);
    $email = sanitize_input($_POST['email']);

    foreach ($_POST['captions'] as $index => $caption) {
        $image_url = sanitize_input($_POST['image_urls'][$index]);
        $image_name = sanitize_input($_POST['image_names'][$index]); // Get the corresponding image_name
        $caption = sanitize_input($caption);

        // Insert or update the caption and include image_name
        $stmt = $conn->prepare("INSERT INTO user_image_data (name, student_id, email, image_name, image_url, caption, submission_complete) 
                                VALUES (?, ?, ?, ?, ?, ?, 0)
                                ON DUPLICATE KEY UPDATE caption = ?");
        $stmt->bind_param("sssssss", $name, $student_id, $email, $image_name, $image_url, $caption, $caption);
        $stmt->execute();
    }

    // Mark submission as complete
    $updateQuery = $conn->prepare("UPDATE user_image_data SET submission_complete = 1 WHERE email = ?");
    $updateQuery->bind_param("s", $email);
    $updateQuery->execute();

    echo "<script>
        alert('Captions submitted successfully!');
        window.location.href = window.location.href;
    </script>";
}

}

// Close the database connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Caption Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0; /* Remove padding for full-width content */
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 1200px; /* Increased form width */
            margin: 0 auto; /* Center the form */
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        /* Align button to the right */
        .button-container {
            display: flex;
            justify-content:center;  /* Align the button to the right */
            margin-top: 20px;
        }


        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }


        button:hover {
            background-color: #218838;
        }

        .image-caption {
            margin-top: 20px;
        }

        .image-container {
            width: 100%; /* Take full width of the form */
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .image-container img {
            width: 150%; /* Full width of container */
            max-width: 100%; /* Ensure the image doesn't overflow */
            height: 20%; /* Maintain aspect ratio */
            object-fit: cover;
            margin: 0; /* No margin for the image */
        }

        .image-container textarea {
            width: 100%; /* Match caption width to image */
            margin-top: 10px;
            resize: vertical;
            min-height: 60px;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            box-sizing: border-box;
        }

    </style>
</head>
<body>
    <h1>Submit Your Image Captions</h1>

    <!-- User Information Form -->
    <form action="" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="student_id">Student ID:</label>
        <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <div class="button-container">
            <button type="submit" name="submit_info">Next</button>
        </div>
    </form>

    <!-- Display Images and Captions after the Next button is clicked -->
    <?php if ($showImages): ?>
    <form action="" method="POST">
        <div class="image-caption">
            <?php foreach ($images as $index => $image_url): ?>
                <div class="image-container">
                    <img src="uploads/<?php echo $image_url; ?>" alt="Image"><br>
                    <textarea name="captions[]" placeholder="Enter caption for image <?php echo $index + 1; ?>" required><?php echo isset($captions[$index]) ? htmlspecialchars($captions[$index]) : ''; ?></textarea>
                    <input type="hidden" name="image_urls[]" value="<?php echo $image_url; ?>">
                    <input type="hidden" name="image_names[]" value="<?php echo $image_names[$index]; ?>"> <!-- Added this line -->
                </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <div class="button-container">
            <button type="submit" name="submit_captions">Submit Captions</button>
        </div>
    </form>
<?php endif; ?>

</body>
</html>


    
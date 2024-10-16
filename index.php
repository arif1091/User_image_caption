<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiple Image Upload</title>
</head>
<body>
    <h2>Upload Multiple Images</h2>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="images[]" multiple required>
        <br><br>
        <input type="submit" name="submit" value="Upload Images">
    </form>
</body>
</html>
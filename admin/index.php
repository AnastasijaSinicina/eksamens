
<?php
session_start();
if (!isset($_SESSION['lietotajvardsSIN']) || ($_SESSION['loma'] !== 'admin' && $_SESSION['loma'] !== 'moder')) {
    header("Location: ../login.php"); // Redirect to login page if not authenticated
    exit();
}
 require 'header.php'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

</body>
</html>


<?php
session_start();
if (!isset($_SESSION['user_id']) && !isset($_SESSION['employee_id'])) {

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Inventuro Login</title>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <form class="p-5 rounded shadow" 
              style="width: 30rem"
              action="auth.php"
              method="post">
            <h1 class="text-center pb-5 display-4">Login</h1>
            <?php if(isset($_GET['error'])) {?>
            <div class="alert alert-danger" role="alert">
                <?php echo$_GET['error']; ?>
            </div>
            <?php } ?>
        <div class="mb-3">
            <label for="input_employee_id" 
                    class="form-label">Employee ID
            </label>
                <input type="text" 
                    name="employee_id"
                    class="form-control" 
                    id="input_employee_id">
            </div>
            <div class="mb-3">
                <label for="input_password" 
                    class="form-label">Password
                </label>
                <input type="password" 
                    name="password"
                    class="form-control" 
                    id="input_password">
            </div>
            <button type="submit" 
                    class="btn btn-primary">Login
            </button>
        </form>
    </div>
</body>
</html>
<?php
} else {

    if($_SESSION['user_type'] === "admin") {
        header(header: "Location: admin/index.php");
    }
    else if($_SESSION['user_type'] === "engineering") {
        header(header: "Location: engineering/index.php");
    }
    else { //non-engineering
        header(header: "Location: non-engineering/index.php");
    }
}
?>
<?php
// Prevent direct access
defined('INSIDE_INCLUDE') or define('INSIDE_INCLUDE', true);

// Get error message from session if it exists
$error_message = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : null;
unset($_SESSION['error_msg']); // Clear the message after using it

// Default error message if none is set
if (!$error_message) {
    $error_message = "<div class='alert alert-danger'>
        <h4>System Error</h4>
        <p>An unexpected error has occurred. Please try again later.</p>
        <p><small>If the problem persists, contact the system administrator.</small></p>
    </div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>System Error</title>
    <style>
        .error-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .error-actions {
            margin-top: 20px;
            text-align: center;
        }
        .error-actions .btn {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <?php echo $error_message; ?>
        
        <div class="error-actions">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
            <a href="?view=Dashboard1" class="btn btn-primary">
                <i class="fas fa-home"></i> Go to Dashboard
            </a>
            <?php if (isset($_SESSION['login']) && $_SESSION['login']): ?>
                <a href="?view=configuracao" class="btn btn-info">
                    <i class="fas fa-cog"></i> System Settings
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Clear error message from URL after displaying
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname);
    }
    </script>
</body>
</html> 
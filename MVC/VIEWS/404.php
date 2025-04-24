<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Error Page</title>
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fc;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 1rem;
            position: relative;
        }
        .error-code:after {
            content: attr(data-text);
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            font-size: 7rem;
            color: rgba(78, 115, 223, 0.1);
            z-index: -1;
        }
        .error-title {
            font-size: 1.5rem;
            color: #5a5c69;
            margin-bottom: 1.5rem;
        }
        .error-message {
            color: #858796;
            margin-bottom: 2rem;
        }
        .back-link {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #4e73df;
            color: white;
            text-decoration: none;
            border-radius: 0.35rem;
            transition: background-color 0.2s;
        }
        .back-link:hover {
            background-color: #2e59d9;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <?php
            // Get error code and message from URL parameters or set defaults
            $errorCode = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : '404';
            $errorTitle = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : 'Page Not Found';
            $errorMessage = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.';
            ?>
            <div class="error-code" data-text="<?php echo $errorCode; ?>"><?php echo $errorCode; ?></div>
            <div class="error-title"><?php echo $errorTitle; ?></div>
            <p class="error-message"><?php echo $errorMessage; ?></p>
            <a href="?view=Dashboard1" class="back-link">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
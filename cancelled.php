<?php
session_start();

$orderId = isset($_GET['order']) ? (string) $_GET['order'] : '';
if (isset($_SESSION['hostedPaymentOrders'][$orderId])) {
    $_SESSION['hostedPaymentOrders'][$orderId]['status'] = 'cancelled';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Cancelled | Accept Sample App</title>
    <link href="scripts/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding-top: 50px;">
    <main class="container" style="max-width: 720px;">
        <div class="panel panel-warning">
            <div class="panel-heading"><h1 class="panel-title">Payment Cancelled</h1></div>
            <div class="panel-body">
                <p>No payment was completed.</p>
                <?php if (isset($_SESSION['hostedPaymentOrders'][$orderId])): ?>
                    <p><strong>Order reference:</strong> <?php echo htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <a class="btn btn-primary" href="payment.php">Try again</a>
                <a class="btn btn-default" href="index.php">Return to the app</a>
            </div>
        </div>
    </main>
</body>
</html>

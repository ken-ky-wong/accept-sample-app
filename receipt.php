<?php
session_start();

$orderId = isset($_GET['order']) ? (string) $_GET['order'] : '';
$order = isset($_SESSION['hostedPaymentOrders'][$orderId]) ? $_SESSION['hostedPaymentOrders'][$orderId] : null;

if ($order !== null) {
    $_SESSION['hostedPaymentOrders'][$orderId]['status'] = 'awaiting_confirmation';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Receipt | Accept Sample App</title>
    <link href="scripts/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding-top: 50px;">
    <main class="container" style="max-width: 720px;">
        <div class="panel panel-success">
            <div class="panel-heading"><h1 class="panel-title">Payment Receipt</h1></div>
            <div class="panel-body">
                <?php if ($order !== null): ?>
                    <p>We received your return from the hosted payment page.</p>
                    <dl class="dl-horizontal">
                        <dt>Order reference</dt><dd><?php echo htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8'); ?></dd>
                        <dt>Amount</dt><dd>$<?php echo htmlspecialchars($order['amount'], ENT_QUOTES, 'UTF-8'); ?></dd>
                        <dt>Status</dt><dd>Awaiting transaction confirmation</dd>
                    </dl>
                    <p class="text-muted">The redirect does not include transaction details. Confirm payment status from an Authorize.Net webhook before fulfilling the order.</p>
                <?php else: ?>
                    <p class="text-warning">This return link does not match an active order in this browser session.</p>
                <?php endif; ?>
                <a class="btn btn-primary" href="index.php">Return to the app</a>
            </div>
        </div>
    </main>
</body>
</html>

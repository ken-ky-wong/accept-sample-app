<?php
session_start();

if (!isset($_COOKIE['cpid']) && !isset($_COOKIE['temp_cpid'])) {
    header('Location: login.php');
    exit;
}

$hostedPaymentOrderId = 'AH-' . strtoupper(bin2hex(random_bytes(8)));
$_SESSION['hostedPaymentOrders'][$hostedPaymentOrderId] = array(
    'amount' => '0.50',
    'createdAt' => time(),
    'status' => 'pending'
);

include 'getHostedPaymentForm.php';

$isSuccessful = isset($hostedPaymentResponse)
    && isset($hostedPaymentResponse->messages->resultCode)
    && (string) $hostedPaymentResponse->messages->resultCode === 'Ok';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pay | Accept Sample App</title>
    <link href="scripts/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding-top: 50px;">
    <main class="container" style="max-width: 720px;">
        <div class="panel panel-primary">
            <div class="panel-heading"><h1 class="panel-title">Payment</h1></div>
            <div class="panel-body">
                <?php if ($isSuccessful): ?>
                    <p>Continue to the secure Authorize.Net hosted payment page to complete your payment.</p>
                    <p><strong>Order reference:</strong> <?php echo htmlspecialchars($hostedPaymentOrderId, ENT_QUOTES, 'UTF-8'); ?></p>
                    <form action="https://test.authorize.net/payment/payment" method="post">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars((string) $hostedPaymentResponse->token, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-primary btn-lg">Pay</button>
                        <a class="btn btn-default btn-lg" href="index.php">Back</a>
                    </form>
                <?php else: ?>
                    <p class="text-danger">Unable to create a hosted payment session. Please try again.</p>
                    <a class="btn btn-default" href="index.php">Back</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>

<?php
// Accept Hosted returns the transaction result to this URL after a successful payment.
$response = array_merge($_GET, $_POST);

function receiptValue($key, $response)
{
    return isset($response[$key]) ? htmlspecialchars((string) $response[$key], ENT_QUOTES, 'UTF-8') : 'Not provided';
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
                <p>Your payment was returned from the hosted payment page.</p>
                <dl class="dl-horizontal">
                    <dt>Transaction ID</dt><dd><?php echo receiptValue('transId', $response); ?></dd>
                    <dt>Response code</dt><dd><?php echo receiptValue('responseCode', $response); ?></dd>
                    <dt>Reason code</dt><dd><?php echo receiptValue('responseReasonCode', $response); ?></dd>
                    <dt>Response</dt><dd><?php echo receiptValue('responseReasonText', $response); ?></dd>
                </dl>
                <a class="btn btn-primary" href="index.php">Return to the app</a>
            </div>
        </div>
    </main>
</body>
</html>

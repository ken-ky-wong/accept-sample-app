<?php
session_start();

function showPaymentError(int $statusCode, string $message): void
{
    http_response_code($statusCode);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Payment | Accept Sample App</title>
        <link href="scripts/bootstrap.min.css" rel="stylesheet">
    </head>
    <body style="padding-top: 50px;">
        <main class="container" style="max-width: 720px;">
            <div class="panel panel-danger">
                <div class="panel-heading"><h1 class="panel-title">Unable to continue payment</h1></div>
                <div class="panel-body">
                    <p class="text-danger"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                    <a class="btn btn-default" href="payment.php">Back to payment options</a>
                </div>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    showPaymentError(405, 'Use the Pay (.NET) button to start this payment flow.');
}

$orderId = isset($_POST['order']) && is_string($_POST['order']) ? trim($_POST['order']) : '';
if (!preg_match('/^AH-[A-F0-9]{16}$/D', $orderId)) {
    showPaymentError(400, 'The payment order is invalid or has expired.');
}

$order = $_SESSION['hostedPaymentOrders'][$orderId] ?? null;
if (!is_array($order) || ($order['status'] ?? null) !== 'pending') {
    showPaymentError(404, 'The payment order is not available in this session.');
}

$amount = $order['amount'] ?? null;
if (!is_scalar($amount) || !is_numeric($amount) || (float) $amount <= 0) {
    showPaymentError(400, 'The payment order has an invalid amount.');
}

session_write_close();

$apiUrl = 'https://localhost:7002/api/accept-hosted/sessions';
$caInfoPath = getenv('ACCEPT_HOSTED_CAINFO');
if ($caInfoPath === false || $caInfoPath === '') {
    $userProfile = getenv('USERPROFILE');
    $caInfoPath = $userProfile !== false && $userProfile !== ''
        ? $userProfile . DIRECTORY_SEPARATOR . 'aspnet-dev-cert.pem'
        : '';
}

if ($caInfoPath === '' || !is_file($caInfoPath) || !is_readable($caInfoPath)) {
    error_log('The ASP.NET development certificate is not readable by PHP. Set ACCEPT_HOSTED_CAINFO to its PEM path.');
    showPaymentError(500, 'The .NET development certificate is unavailable to PHP. Set ACCEPT_HOSTED_CAINFO to the PEM certificate path.');
}

$requestBody = json_encode(array(
    'amount' => (float) $amount,
    'invoiceNumber' => $orderId
), JSON_THROW_ON_ERROR);

$ch = curl_init($apiUrl);
if (false === $ch) {
    error_log('Could not initialize cURL for the local Accept Hosted API.');
    showPaymentError(502, 'The .NET payment service could not be reached.');
}

try {
    curl_setopt_array($ch, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $requestBody,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json'
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CAINFO => $caInfoPath
    ));

    $responseBody = curl_exec($ch);
    if (false === $responseBody) {
        $curlError = curl_error($ch);
        error_log('Could not reach the local Accept Hosted API: ' . $curlError);
        showPaymentError(502, 'The .NET payment service could not be reached. Check that it is running and its HTTPS certificate is trusted by PHP.');
    }

    $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
} finally {
    if (PHP_VERSION_ID < 80500) {
        curl_close($ch);
    }
}

if ($httpStatus < 200 || $httpStatus >= 300) {
    error_log('The local Accept Hosted API returned HTTP status ' . $httpStatus . '.');
    showPaymentError(502, 'The .NET payment service could not create a hosted payment session.');
}

try {
    $apiResponse = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    error_log('The local Accept Hosted API returned invalid JSON: ' . $exception->getMessage());
    showPaymentError(502, 'The .NET payment service returned an invalid response.');
}

$token = is_array($apiResponse) && isset($apiResponse['token']) && is_string($apiResponse['token'])
    ? $apiResponse['token']
    : '';
if ($token === '') {
    error_log('The local Accept Hosted API response did not contain a token.');
    showPaymentError(502, 'The .NET payment service returned an invalid response.');
}

$escapedToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Continue to Payment | Accept Sample App</title>
    <link href="scripts/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding-top: 50px;">
    <main class="container" style="max-width: 720px;">
        <div class="panel panel-success">
            <div class="panel-heading"><h1 class="panel-title">Continue to secure payment</h1></div>
            <div class="panel-body">
                <p>Redirecting to the Authorize.Net hosted payment page.</p>
                <form id="hosted-payment-form" action="https://test.authorize.net/payment/payment" method="post">
                    <input type="hidden" name="token" value="<?php echo $escapedToken; ?>">
                    <button type="submit" class="btn btn-primary btn-lg">Continue to Authorize.Net</button>
                </form>
            </div>
        </div>
    </main>
    <script>
        document.getElementById('hosted-payment-form').submit();
    </script>
</body>
</html>
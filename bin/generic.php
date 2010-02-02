<?php
require_once '../ga.php';

$ga_id = 'MO-XXXXXX-XX'; // change me.

$ga = new Ga(array('account' => $ga_id));

if (isset($_GET['utmac']) && isset($_GET['utmn'])) {
    $ga->track();

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Server-side Google Analytics</title>
</head>
<body>
    <?php echo $ga->url(); ?>
</body>
</html>
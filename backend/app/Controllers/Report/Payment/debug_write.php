<?php
$target_dir = __DIR__ . '/download/';
echo "Target directory: " . $target_dir . "<br>";

if (!is_dir($target_dir)) {
    echo "Folder tidak ditemukan. Sedang membuat...<br>";
    mkdir($target_dir, 0777, true);
}

echo "Folder exists: " . (file_exists($target_dir) ? 'Yes' : 'No') . "<br>";
echo "Is writable: " . (is_writable($target_dir) ? 'Yes' : 'No') . "<br>";

$test_file = $target_dir . 'test_from_php.txt';
$result = file_put_contents($test_file, "Test write from PHP at " . date('Y-m-d H:i:s'));

if ($result !== false) {
    echo "<span style='color:green'>SUKSES! File berhasil ditulis: " . $test_file . "</span><br>";
    echo "Isi file: <br><pre>" . file_get_contents($test_file) . "</pre>";
    unlink($test_file);
    echo "File test sudah dihapus.";
} else {
    echo "<span style='color:red'>GAGAL! Tidak bisa menulis file. Error: " . error_get_last()['message'] . "</span>";
}
?>
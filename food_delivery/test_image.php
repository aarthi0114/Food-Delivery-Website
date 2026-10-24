<?php

echo "<h2>Project Location:</h2>";
echo __DIR__;

echo "<h2>Images Folder:</h2>";

if (is_dir("images")) {

    echo "Images folder FOUND";

    echo "<h3>Files inside images folder:</h3>";

    $files = scandir("images");

    echo "<pre>";

    print_r($files);

    echo "</pre>";

} else {

    echo "Images folder NOT FOUND";

}

?>
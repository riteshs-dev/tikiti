<?php
echo "Apache rewrite is working!<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "<br>";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'N/A') . "<br>";
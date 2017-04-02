<?php 
$mysqli = new mysqli('localhost', 'homestead', 'secret','mpmdb','33060');

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
;?>
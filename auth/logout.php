<?php
session_unset();
session_destroy();

header('Location: ' . BASE_URL);
exit();
?>
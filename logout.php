<?php
session_start();
session_destroy();
header("Location: connexion2.html");
exit();
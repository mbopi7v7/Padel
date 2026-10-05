<?php

include "lib/seguridad.php";

cerrarSesion();

header("Location: login.php?logout=1");
exit();

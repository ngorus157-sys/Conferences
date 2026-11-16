<?php
require 'config.php';
go(user() ? 'requests.php' : 'login.php');
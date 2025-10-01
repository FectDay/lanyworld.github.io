<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

session_start();
session_destroy();
redirect('/');
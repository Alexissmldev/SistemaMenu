<?php

session_destroy();

if (headers_sent()) {
    echo "<script> window.location.href='login';</script>";
} else {
    header("Location: login");
}

<?php

$errors = [];

$nom = "anime";
$nom = trim($nom);
if (empty($nom)) {
    $errors[] = "entre un nom ";
} 

$email = "  anime@gmail.com  ";
$email = trim($email);

if (empty($email)) {
    $errors[] = "entre un email";    
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "email no valid"; 
}

$password = "Salam152639";
$password = trim($password);


if (empty($password)) {
    $errors[] = "entre un password";
} elseif (strlen($password) < 8) {
    $errors[] = "minimum 8 characters";
} elseif (!preg_match("/[A-Z]/", $password)) {
    $errors[] = "password must contain uppercase";
} elseif (!preg_match("/[0-9]/", $password)) {
    $errors[] = "password must contain number";
}

$confirmPassword = "Salam152639";
$confirmPassword = trim($confirmPassword);

if ($password !== $confirmPassword) {
    $errors[] = "password and confirm password not match";
}

if (empty($errors)) {
    echo "Registration successful <br>";
} else {
    foreach ($errors as $error) {
        echo $error . "<br>";
    }
}


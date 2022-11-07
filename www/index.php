<?php

require __DIR__ . '/User.php';
require __DIR__ . '/ActionsWithUsers.php';

$user = new User(
    null,
    'Name4',
    'Surname4',
    '1960-03-03',
    '1',
    'Town4'
);


$user = new User(
    1
);

echo $usersAge = User::countOfAge($user->getDateOfBirth());

echo $usersGender = User::genderNumberToValue($user->getGender());

$actions = new ActionsWithUsers(
    'id',
    '>',
    '2'
);

$users = $actions->getUsers();

$actions->deleteUsers($users);


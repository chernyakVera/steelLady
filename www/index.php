<?php
/**
 * Автор: Черняк Вера
 *
 * Дата реализации: 07.11.2022 18:50
 *
 * Дата изменения: 23.01.2023 13:20
 *
 * Файл для подключения классов и работы с их объектами.
 */

require __DIR__ . '/User.php';
require __DIR__ . '/ActionsWithUsers.php';

//$user = new User(
//    null,
//    'Name5',
//    'Surname5',
//    '1987-10-02',
//    '1',
//    'Town5'
//);


//$user = new User(2);
//echo $usersAge = User::countOfAge($user->getDateOfBirth());
//echo $usersGender = User::genderNumberToValue($user->getGender());
//
//
//$user->deleteUser();


$actions = new ActionsWithUsers(
    'dateOfBirth',
    '>',
    '1960-01-02'
);
$users = $actions->getUsers();
//
//
//
//$actions->deleteUsers($users);


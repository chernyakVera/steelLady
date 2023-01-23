<?php
/**
 * Автор: Черняк Вера
 *
 * Дата реализации: 07.11.2022 18:50
 *
 * Дата изменения: 23.01.2023 13:20
 *
 * Класс для работы со списками людей, который работает при помощи класса User.
 */

/**
 * Класс ActionsWithUsers вызывается в index.php куда впоследствии и возвращает результат выполнения.
 * Класс, с помощью метода getUsers(), находит и возвращает список пользователей.
 * Условия, по которым отбираются пользователи, описаны в конструкторе класса.
 * Конструктор класса принимает название столбца, оператор сравнения и значение, с которым сравнивать.
 * Далее, если класс User существует, происходит создание объекта класса ActionsWithUsers,
 * иначе выбрасывается исключение.
 * При создании объекта, конструктор, с помощью getUsersByValue(), получает из БД массив объектов (список людей,
 * подходящих под заданное ранее условие).
 * Также есть метод deleteUsers(), который удаляет все записи из БД, соответсвующие списку людей, полученному ранее.
 */
class ActionsWithUsers
{
    /** @var string */
    private $value;

    /** @var string */
    private $comparisonOperator;

    private $comparisonValue;

    /** @var array */
    private $usersId;

    /** @var PDO */
    private $pdo;

    public function __construct(string $value, string $comparisonOperator,  $comparisonValue)
    {
        try {
            if (class_exists('User')) {
                $this->value = $this->camelCaseToUnderscore($value);
                $this->comparisonOperator = $comparisonOperator;
                $this->comparisonValue = $comparisonValue;
                $this->usersId = $this->getUsersByValue();
            }
        } catch (Exception $e) {
            throw new Exception('Класса "User" не существует!'. $e->getMessage());
        }
    }


    private function camelCaseToUnderscore(string $source): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $source));
    }


    public function getValue(): ?string
    {
        return $this->value;
    }


    public function getComparisonOperator(): ?string
    {
        return $this->comparisonOperator;
    }


    public function getComparisonValue()
    {
        return $this->comparisonValue;
    }

    public function getUsersId(): ?array
    {
        return $this->usersId;
    }


    public function getUsersByValue(): ?array
    {
        try {
            $this->pdo = new \PDO(
                'mysql:host=localhost;dbname=steel_lady',
                'root',
                'root'
            );
            $this->pdo->exec('SET NAMES UTF8');
        } catch (\PDOException $e) {
            throw new \Exception(
                'Ошибка при подключении к базе данных: ' . $e->getMessage()
            );
        }
        $sql = 'SELECT * FROM `users` WHERE ' . $this->getValue()
             . ' ' . $this->getComparisonOperator() . ' :comparisonValue';
        $users = $this->query($sql, [':comparisonValue' => $this->getComparisonValue()]);
        return $users;
    }


    /**
     * Метод вызывается в файле index.php куда и возвращает результат после выполнения.
     * Метод вызывается объектом класса ActionsWithUsers, где уже хранится список людей, подходящих под заданное
     * ранее условие. С помощью метода getUsersId() возвращается массив id людей. Далее создаются объекты класса User
     * по id из полученного ранее массива. Новые объекты теперь хранятся в массиве $arrayOfUsers.
     * Далее составляется строка со всеми данными из свойств объекта (из массива $arrayOfUsers).
     * Далее все строки с данными людей выводятся на экран, а массив объектов класса Users $arrayOfUsers возвращается
     * в переменную в файл index.php для дальнейшей работы (например, для использования метода deleteUsers()).
     */
    public function getUsers(): array
    {
        $users = $this->getUsersId();
        $arrayOfUsers = [];
        foreach ($users as $user) {
            $userUser = new User($user->id);
            $arrayOfUsers[] = $userUser;
        }
        foreach ($arrayOfUsers as $user) {
            $userInfo = $user->getId()
                . ' ' . $user->getName()
                . ' ' . $user->getSurname()
                . ' ' . $user->getDateOfBirth()
                . ' ' . $user->getGender()
                . ' ' . $user->getTownOfBirth()
                . '<br>';
            echo $userInfo;
        }
        return $arrayOfUsers;
    }


    public function deleteUsers(array $users)
    {
        foreach ($users as $user) {
            $user->deleteUser();
        }
    }


    public function query(string $sql, $params = []): ?array
    {
        $sth = $this->pdo->prepare($sql);
        $result = $sth->execute($params);


        if(false === $result) {
            return null;
        }
        return $sth->fetchAll(\PDO::FETCH_CLASS);
    }

}
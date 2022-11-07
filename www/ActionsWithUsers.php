<?php

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
        if (class_exists('User')) {
            $this->value = $value;
            $this->comparisonOperator = $comparisonOperator;
            $this->comparisonValue = $comparisonValue;
            $this->usersId = $this->getUsersById();
        }
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


    public function getUsersById()
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

        $sql = 'SELECT * FROM `users` WHERE ' . $this->getValue() . ' ' . $this->getComparisonOperator() . ' :comparisonValue';
        $users = $this->query($sql, [':comparisonValue' => $this->getComparisonValue()]);

        return $users;
    }


    public function getUsers(): array
    {
        $users = $this->getUsersId();

        $arrayOfUsers = [];
        foreach ($users as $user) {
            $userUser = new User($user->id);
            $arrayOfUsers[] = $userUser;
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
<?php
/**
 * Автор: Черняк Вера
 *
 * Дата реализации: 07.11.2022 18:50
 *
 * Дата изменения: 23.01.2023 13:20
 *
 * Класс для работы с базой данных людей.
 */

/**
 * Класс User, который отражает таблицу `users` из БД.
 * Объект класса User создается в index.php и в методе getUsers() класса ActionsWithUsers.
 * Данные, полученные с помощью класса User возвращаются в те файлы, из которых были ранее вызваны.
 * При создании объекта, конструктор класса принимает аргументы одноименные полям таблицы БД.
 * Если id объекта отсутствует, то создается новый объект и далее с помощью метода saveUser() объект сохраняется в БД.
 * Если у объекта есть id, то по нему получаем из БД уже существующую запись и с помощью метода setAllProperties()
 * назначаются свойства новому объекту класса User.
 * Методы-сеттеры предназначены для назначения новых значений свойств объекта класса User.
 * Методы-геттеры предназначены для возвращения значений свойств объекта класса User.
 * Метод deleteUser() удаляет запись из таблицы `users` по id объекта, у которого был вызван.
 * Статический метод genderNumberToValue() принимает значение гендера  объекта (число) и возвращает понятное значение
 * гендера в виде строки: муж. или жен.
 * Статический метод countOfAge() принимает значение даты рождения объекта (строка) и возвращает возраст относительно
 * даты, когда был вызван метод.
 */
class User
{
    const MALE = 'муж.';
    const FEMALE = 'жен.';

    /** @var int */
    protected $id;

    /** @var string */
    private  $name;

    /** @var string */
    private  $surname;

    /** @var string */
    private  $dateOfBirth;

    /** @var string */
    private  $gender;

    /** @var string */
    private  $townOfBirth;

    /** @var PDO */
    private $pdo;


    /**
     * Конструктор класса принимает аргументы, одноименные с полями таблицы `users` БД.
     * По-умолчанию они равны null. Далее, т.к. при создании объекта не задается id,
     * т.к. он назначается далее в БД автоматически, идет проверка на наличие значения свойства $townOfBirth.
     * Т.к. объект может уже существовать в БД и проверяется наличие id.
     * Если id нет, то назначаются свойства нового объекта и ладее с помощью метода saveUser() происходит сохранение
     * записи в таблицу `user` БД.
     * Если id есть, то запись с таким id запрашивается из БД и далее объекту назначаются пришедшие значения свойств.
     * */
    public function __construct(
        int $id = null,
        string $name = null,
        string $surname = null,
        string $dateOfBirth = null,
        string $gender = null,
        string $townOfBirth = null
    ) {
        if ($this->getTownOfBirth() === null) {
            if ($id === null) {
                $this->name = $name;
                $this->surname = $surname;
                $this->dateOfBirth = $dateOfBirth;
                $this->gender = $gender;
                $this->townOfBirth = $townOfBirth;
                $this->saveUser(
                    [
                        'name' => $this->getName(),
                        'surname' => $this->getSurname(),
                        'dateOfBirth' => $this->getDateOfBirth(),
                        'gender' => $this->getGender(),
                        'townOfBirth' => $this->getTownOfBirth(),
                    ]
                );
            } elseif ($id !== null) {
                $this->id = $id;
                $this->getUserById($this->getId());
            }
        }
    }


    public function setName($name)
    {
        $this->name = $name;
    }


    public function setSurname($surname)
    {
        $this->surname = $surname;
    }


    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }


    public function setGender($gender)
    {
        $this->gender = $gender;
    }


    public function setTownOfBirth($townOfBirth)
    {
        $this->townOfBirth = $townOfBirth;
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getName(): ?string
    {
        return $this->name;
    }


    public function getSurname(): ?string
    {
        return $this->surname;
    }


    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }


    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getTownOfBirth(): ?string
    {
        return $this->townOfBirth;
    }

    /**
     * Метод, который запрашивает запись из БД по id объекта, пришедшего в конструктор класса.
     * Далее, если запрос вернул запись (в виде массива массивов), вызывается метод setAllProperties(),
     * который как конструктор, назначает свойства объекту.
     * Если записи с переданным ранее id не существует, то метод возвращает null.
     */
    private function getUserById(int $id): ?User
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

        $sql = 'SELECT * FROM `users` WHERE id = :id';
        $user = $this->query($sql, [':id' => $id]);

        if (isset($user[0])) {
            $this->setAllProperties($user[0]);
        }

        return $user ? $user[0] : null;
    }


    /**
     * Метод, сохраняющий объект класса User в таблицу `users` БД.
     * Если подключение к БД прошло успешно, то далее, с помощью mapPropertiesToDbFormat(), происходит процесс
     * преобразования названий свойств из camelCase в snake_case, т.к. правила именования свойств в коде
     * и столбцов в БД разные.
     * Далее подготавливаются массивы с корректными для БД названиями столбцов и с значениями, которые нужно записать
     * в эти столбцы.
     * В конце, запршиваем у БД id записи, т.к. при создании нового объекта класса id в конструктор не преедаем,
     * и назначаем этот id текущему объекту.
     */
    private function saveUser(array $user): void
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

        $mappedProperties = $this->mapPropertiesToDbFormat();
        array_pop($mappedProperties);

        $filteredProperties = array_filter($mappedProperties, 'strlen');

        $columns = []; // массив для сбора названий столбцов вида [`column`]
        $paramsNames = []; // массив для сбора названий столбцов в качестве параметров
        $params2values = []; // массив для сбора зависимости параметра и значения вида [:column => value]

        foreach ($filteredProperties as $columnName => $value) {
            $columns[] = '`'. $columnName . '`'; // [`column`]
            $paramName = ':' . $columnName; // :column
            $paramsNames[] = $paramName; // [:column]
            $params2values[$paramName] = $value; // [:column => value1]
        }

        $sql = 'INSERT INTO `users` (' . implode(', ', $columns)
             . ') VALUES (' . implode(', ', $paramsNames) . ');';

        $this->query($sql, $params2values);
        $this->id = $this->getLastInsertId();
    }


    public function query(string $sql, $params = [], string $className = 'User'): ?array
    {
        $sth = $this->pdo->prepare($sql);
        $result = $sth->execute($params);

        if(false === $result) {
            return null;
        }
        return $sth->fetchAll(\PDO::FETCH_CLASS, $className);
    }


    private function mapPropertiesToDbFormat(): array
    {
        $reflector = new \ReflectionObject($this);
        $properties = $reflector->getProperties();
        $mappedProperties = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertyNameAsUnderScore = $this->camelCaseToUnderscore($propertyName);
            $mappedProperties[$propertyNameAsUnderScore] = $this->$propertyName;
        }
        return $mappedProperties;
    }


    public function getLastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }


    private function camelCaseToUnderscore(string $source): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $source));
    }


    public function __set($name, $value)
    {
        $camelCaseName = $this->underscoreToCamelCase($name);
        $this->$camelCaseName = $value;
    }


    private function underscoreToCamelCase(string $sourse): string
    {
        return lcfirst(str_replace('_', '', (ucwords($sourse, '_'))));
    }


    private function setAllProperties($user): User
    {
        $this->setName($user->name);
        $this->setSurname($user->surname);
        $this->setDateOfBirth($user->dateOfBirth);
        $this->setGender($user->gender);
        $this->setTownOfBirth($user->townOfBirth);

        return $this;
    }


    public function deleteUser(): void
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
        $currentId = $this->id;
        $sql = 'DELETE FROM `users` ' . 'WHERE id = :id';
        $this->query($sql, [':id' => $currentId]);
        $this->id = null;
    }


    public static function genderNumberToValue(int $genderNumber): string
    {
        if ($genderNumber === 1) {
            return self::FEMALE;
        } else {
            return self::MALE;
        }
    }


    public static function countOfAge(string $dateOfBirth): int
    {
        $dateOfBirth = new DateTime($dateOfBirth);
        $currentDate = new DateTime(date('Y-m-d'));
        $interval = $dateOfBirth->diff($currentDate);
        $age = $interval->y;
        return $age;
    }


}

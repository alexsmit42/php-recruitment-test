<?php

namespace Snowdog\DevTest\Model;

use Snowdog\DevTest\Core\Database;

class UserManager
{

    /**
     * @var Database|\PDO
     */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getByLogin($login)
    {
        /** @var \PDOStatement $query */
        $query = $this->database->prepare('SELECT * FROM users WHERE login = :login');
        $query->setFetchMode(\PDO::FETCH_CLASS, User::class);
        $query->bindParam(':login', $login, \PDO::PARAM_STR);
        $query->execute();
        /** @var User $user */
        $user = $query->fetch(\PDO::FETCH_CLASS);
        return $user;
    }

    public function create($login, $password, $displayName)
    {
        $salt = hash('sha512', microtime());
        $hash = $this->hashPassword($password, $salt);
        /** @var \PDOStatement $statement */
        $statement = $this->database->prepare('INSERT INTO users (login, password_hash, password_salt, display_name) VALUES (:login, :hash, :salt, :name)');
        $statement->bindParam(':login', $login, \PDO::PARAM_STR);
        $statement->bindParam(':hash', $hash, \PDO::PARAM_STR);
        $statement->bindParam(':salt', $salt, \PDO::PARAM_STR);
        $statement->bindParam(':name', $displayName, \PDO::PARAM_STR);
        $statement->execute();
        return $this->database->lastInsertId();
    }

    public function verifyPassword(User $user, $password)
    {
        $hash = $this->hashPassword($password, $user->getPasswordSalt());
        return $hash === $user->getPasswordHash();
    }

    protected function hashPassword($password, $salt)
    {
        return hash('sha512', $password . $salt);
    }

    public function getUserInfo(User $user) {
        $userId = $user->getUserId();

        $query = $this->database->prepare('
            SELECT
              w.hostname,
              p.url,
              p.last_visit,
              p.total_visits
            FROM
              pages p
              JOIN websites w USING(website_id)
            WHERE
              w.user_id = :user_id
            ORDER BY p.last_visit DESC
        ');
        $query->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $query->execute();

        $totalPages = $query->rowCount();

        $userInfo = [
            'total' => $totalPages,
            'last_visited' => '',
            'most_visited' => ''
        ];

        if ($totalPages) {
            $pages = $query->fetchAll(\PDO::FETCH_ASSOC);
            $userInfo['last_visited'] = $this->getFullUrl($pages[0]) . " ({$pages[0]['last_visit']})";

            usort($pages, function($a, $b) {
                return $a['total_visits'] < $b['total_visits'];
            });
            $userInfo['most_visited'] = $this->getFullUrl($pages[0]) . " ({$pages[0]['total_visits']})";
        }

        return $userInfo;
    }

    private function getFullUrl(array $page) {
        return "http://{$page['hostname']}/{$page['url']}";
    }
}
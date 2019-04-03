<?php

namespace App\User;

require_once(__DIR__ . '\..\Database\DatabaseConnector.php');

class UserModel
{
    const TABLE_NAME = 'user';
    const ID = 'id';
    const USER_NAME = 'user_name';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';

    private $dbConn;

    function __construct() {
        $databaseConnector = new \DatabaseConnector();
        $this->dbConn = $databaseConnector->getConnector();
    }

    private function validate($fieldValues) {
        $dateTimeFormat = 'Y-m-d H:i:s';

        foreach ($fieldValues as $field => $value) {
            Switch ($field) {
                case self::ID: 
                    if (!is_numeric($value)) {
                        throw new \Exception("$field: must be an integer");
                    }
                    break;
                case self::USER_NAME:
                case self::FIRST_NAME:
                case self::LAST_NAME:
                    // Need to check if string? Not really necessary... 
                    break;
                default:
                    throw new \Exception("Invalid parameter: $field");
                    break;
            }
        }
    }

    public function getByUserName($userName) {
        try {
            $this->validate([self::USER_NAME => $userName]);

            $stmt = $this->dbConn->prepare("SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::USER_NAME . " = :user_name");
            $stmt->execute(array(':user_name' => $userName));
            $rows = $stmt->fetchAll();

            if (!empty($rows)) {
                return $rows[0];
            }

            return false;
        }
        catch(PDOException $e) {
            echo "Database query failed: " . $e->getMessage();
        }
    }
}
?>
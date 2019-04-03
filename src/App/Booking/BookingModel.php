<?php

namespace App\Booking;

require_once(__DIR__ . '\..\Database\DatabaseConnector.php');

class BookingModel
{   
    const TABLE_NAME = 'booking';
    const ID = 'id';
    const USER_ID = 'user_id';
    const VISITATION_REASON = 'visitation_reason';
    const START_TIME = 'start_time';
    const END_TIME = 'end_time';
    const CREATE_AT = 'create_at';
    const MODIFY_AT = 'modify_at';
    const DELETED = 'deleted';

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
                case self::USER_ID:
                    if (!is_numeric($value)) {
                        throw new \Exception("$field: must be an integer");
                    }
                    break;
                case self::VISITATION_REASON:
                    // Need to check if string? Not really necessary... 
                    break;
                case self::START_TIME:
                case self::END_TIME:
                    $date = \DateTime::createFromFormat($dateTimeFormat, $value);
                    if (!$date || $date->format($dateTimeFormat) !== $value) {
                        throw new \Exception("$field: invalid dateTime format");
                    }

                    if (!empty($fieldValues[self::START_TIME]) && !empty($fieldValues[self::END_TIME]) && (strtotime($fieldValues[self::START_TIME]) >= strtotime($fieldValues[self::END_TIME]))) {
                        throw new \Exception("start_time cannot be greater than end_time");
                    }
                    break;
                default:
                    throw new \Exception("Invalid parameter: $field");
                    break;
            }
        }
    }

    public function getById($id) {
        try {
            $this->validate([self::ID => $id]);

            $sql =  "SELECT " . self::TABLE_NAME . "." . self::ID . ", " . self::TABLE_NAME . "." . self::USER_ID . ", user.user_name, user.first_name, user.last_name, " . self::TABLE_NAME . "." . self::VISITATION_REASON . ", " . self::TABLE_NAME . "." . self::START_TIME . ", " . self::TABLE_NAME . "." . self::END_TIME . ", " . self::TABLE_NAME . "." . self::CREATE_AT . ", " . self::TABLE_NAME . "." . self::MODIFY_AT . " " .
                    "FROM " . self::TABLE_NAME . " " .
                    "LEFT JOIN user on " . self::TABLE_NAME . "." . self::USER_ID . " = user.id " .
                    "WHERE " . self::TABLE_NAME . "." . self::ID . " = :id AND " . self::TABLE_NAME . "." . self::DELETED . " = 0";
            $execParams = array(':id' => $id);

            $stmt = $this->dbConn->prepare($sql);
            $stmt->execute($execParams);
            return $stmt->fetchAll();
        }
        catch(PDOException $e) {
            echo "Database query failed: " . $e->getMessage();
        }
    }

    public function getByUserName($userName) {
        try {
            // Check if user is valid
            $userModel = new \App\User\UserModel();
            $user = $userModel->getByUserName($userName);

            if ($user) {
                $sql =  "SELECT " . self::TABLE_NAME . "." . self::ID . ", " . self::TABLE_NAME . "." . self::USER_ID . ", user.user_name, user.first_name, user.last_name, " . self::TABLE_NAME . "." . self::VISITATION_REASON . ", " . self::TABLE_NAME . "." . self::START_TIME . ", " . self::TABLE_NAME . "." . self::END_TIME . ", " . self::TABLE_NAME . "." . self::CREATE_AT . ", " . self::TABLE_NAME . "." . self::MODIFY_AT . " " .
                "FROM " . self::TABLE_NAME . " " .
                "LEFT JOIN user on " . self::TABLE_NAME . "." . self::USER_ID . " = user.id " .
                "WHERE " . self::TABLE_NAME . "." . self::USER_ID . " = :user_id AND " . self::TABLE_NAME . "." . self::DELETED . " = 0";
                $execParams = array('user_id' => $user['id']);
                
                $stmt = $this->dbConn->prepare($sql);
                $stmt->execute($execParams);
                return $stmt->fetchAll();
            }
            else {
                throw new \Exception("User $userName does not exist");
            }
        }
        catch(PDOException $e) {
            echo "Database query failed: " . $e->getMessage();
        }
    }

    public function insert($userName, $visitationReason, $startTime, $endTime) {
        try {
            // Check if user is valid
            $userModel = new \App\User\UserModel();
            $user = $userModel->getByUserName($userName);

            if ($user) {
                $this->validate([self::VISITATION_REASON => $visitationReason,
                                self::START_TIME => $startTime,
                                self::END_TIME => $endTime]);

                $sql = "INSERT INTO " . self::TABLE_NAME . " (" . self::USER_ID . ", " . self::VISITATION_REASON . ", " . self::START_TIME . ", " . self::END_TIME . ") VALUES (:user_id, :visitation_reason, :start_time, :end_time)";
                $execParams = array(':user_id' => intval($user['id']),
                                    ':visitation_reason' => $visitationReason,
                                    ':start_time' => $startTime,
                                    ':end_time' => $endTime);

                $stmt = $this->dbConn->prepare($sql);              
                if ($stmt->execute($execParams)) {
                    return $this->dbConn->lastInsertId();
                }
                return false;
            }
            else {
                throw new PDOException("User $userName does not exist");
            }
        }
        catch(PDOException $e) {
            echo "Database query failed: " . $e->getMessage();
        }
    }

    public function update($id, $updateParams) {
        try {
            $bookings = $this->getById($id);

            if ($bookings) {
                // Checks to see if any values have changed and if update is necessary
                foreach ($updateParams as $field => &$updateValue) {
                    if ($bookings[0][$field] == $updateValue) {
                        unset($updateParams[$field]);
                    }
                }
                
                // If there are actual changes 
                if (!empty($updateParams)) {
                    // TO-DO: Need to check if time range is still valid
                    $prepareParams = array();
                    $execParams = array(':id' => $bookings[0]['id']);

                    foreach ($updateParams as $field => $value) {
                        $prepareParams[] = $field . ' = :' . $field;
                        $execParams[':' . $field] = $value;
                    }

                    $sql = "UPDATE " . self::TABLE_NAME . " SET " . implode(', ', $prepareParams) . " WHERE " . self::ID . " = :id";

                    $stmt = $this->dbConn->prepare($sql);
                    $stmt->execute($execParams);
                    return $stmt->rowCount();
                }
                return false;
            }
            else {
                throw new PDOException("Booking Id $id for User $userName does not exist");
            }
        }
        catch(PDOException $e) {
            echo "Database query failed: " . $e->getMessage();
        }
    }

    public function delete($id) {
        try {
            $this->validate([self::ID => $id]);

            $sql = "UPDATE " . self::TABLE_NAME . " SET deleted = 1 WHERE " . self::ID . " = :id";
            $execParams = array(':id' => $id);

            $stmt = $this->dbConn->prepare($sql);
            $stmt->execute($execParams);
            return $stmt->rowCount();
        }
        catch(PDOException $e) {
            echo "Database query failed: " . $e->getMessage();
        }
    }
}
?>
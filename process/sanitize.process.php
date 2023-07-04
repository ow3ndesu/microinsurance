<?php
class Sanitize extends Database
{
    public function sanitizeForString($data)
    {
        return filter_var(mysqli_real_escape_string($this->conn, $data), FILTER_SANITIZE_STRING);
    }

    public function sanitizeForInput($data)
    {
        return filter_var($data, FILTER_SANITIZE_STRING);
    }

    public function sanitizeForEmail($data)
    {
        return filter_var(mysqli_real_escape_string($this->conn, $data), FILTER_SANITIZE_EMAIL);
    }

    public function generateUserID()
    {
        return substr(str_shuffle('0123456789'), 0, 6);
    }
}

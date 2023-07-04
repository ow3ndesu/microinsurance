<?php 

include_once("../database/connection.php");
include_once("../database/constants.php");

class BackUp extends Database {

    public function BackupDatabase() {

        $host = (string)HOST;
        $username = (string)USERNAME;
        $password = (string)PASSWORD;
        $port = (string)PORT;
        $dbname = (string)DBNAME;
        $dateTime = date('m_d_Y_H-i');
        $backupFile = $dbname . "_" . $dateTime . ".sql";

        // Execute mysqldump command to create a backup
        $command = "mysqldump --host={$host} --user={$username} --password={$password} --port={$port} {$dbname} > databasebackups\\{$backupFile} 2>&1";
        exec($command, $output, $returnVar);

        $_SESSION['backupFile'] = $backupFile;

        if ($returnVar === 0) {
            echo "SUCCESS";
        } else {
            echo "Error Backing Up Database.";
        }
    }

    public function ExportDatabase($backupFile) {
        echo "<script> const link = document.createElement('a');
        link.href = 'databasebackups\\/{$backupFile}';
        link.download = 'databasebackups\\/{$backupFile}';
        link.click();
        window.close(); </script>";

        unset($_SESSION['backupFile']);
    }
}
?>
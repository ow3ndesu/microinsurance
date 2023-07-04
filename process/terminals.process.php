<?php 

include_once("../database/connection.php");
include_once("sanitize.process.php");

class Process extends Database {

    public function LoadTerminals() {
        
        $terminals = [];
        $terminalsloaded = "TERMINALS_LOADED";

        $query = "SELECT id, terminal, nextor FROM tbl_terminals";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $terminals[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $terminalsloaded,
                "TERMINALS" => $terminals
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function LoadTerminal($data) {
        
        $vterminal = [];
        $sanitize = new Sanitize();
        $terminal = $sanitize->sanitizeForString($data["terminal"]);
        $terminalloaded = "TERMINAL_LOADED";

        $query = "SELECT id, terminal, nextor, orfrom, orto, orleft, noofreset FROM tbl_terminals WHERE terminal = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $terminal);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {

            while($row = $result->fetch_assoc()) {
                $vterminal[] = $row;
            }

            echo json_encode(array(
                "MESSAGE" => $terminalloaded,
                "TERMINAL" => $vterminal
            ));

        } else {
            echo 'NO_DATA';
        }
    }

    public function SubmitTerminal($data) {
        $sanitize = new Sanitize();
        $terminal = $sanitize->sanitizeForString($data["terminal"]);
        $nextor = $sanitize->sanitizeForString($data["nextor"]);
        $orfrom = $sanitize->sanitizeForString($data["orfrom"]);
        $orto = $sanitize->sanitizeForString($data["orto"]);
        $orleft = $sanitize->sanitizeForString($data["orleft"]);
        $noofreset = "0";

        $sql = "SELECT terminal FROM tbl_terminals WHERE terminal = ? LIMIT 1;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $terminal);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        if ($res->num_rows == 1) {
            echo 'Terminal Number In Use.';
        } else {
            $sql = "INSERT INTO tbl_terminals (terminal, nextor, orfrom, orto, orleft, noofreset) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssss", $terminal, $nextor, $orfrom, $orto, $orleft, $noofreset);

            if (!$stmt->execute()) {
                $stmt->close();
                printf($stmt->error);
            } else {
                $stmt->close();
                echo 'SUCCESS';
            }
        }
    }

    public function SubmitUpdateTerminal($data) {
        $sanitize = new Sanitize();
        $terminal = $sanitize->sanitizeForString($data["terminal"]);
        $nextor = $sanitize->sanitizeForString($data["nextor"]);
        $orfrom = $sanitize->sanitizeForString($data["orfrom"]);
        $orto = $sanitize->sanitizeForString($data["orto"]);
        $orleft = $sanitize->sanitizeForString($data["orleft"]);
        $noofreset = $sanitize->sanitizeForString($data["noofreset"]);
        
        $sql = "UPDATE tbl_terminals SET nextor = ?, orfrom = ?, orto = ?, orleft = ?, noofreset = ? WHERE terminal = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", $nextor, $orfrom, $orto, $orleft, $noofreset, $terminal);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }

    public function ResetTerminal($data) {
        $sanitize = new Sanitize();
        $terminal = $sanitize->sanitizeForString($data["terminal"]);
        $nextor = $sanitize->sanitizeForString($data["nextor"]);
        $orfrom = $sanitize->sanitizeForString($data["orfrom"]);
        $orto = $sanitize->sanitizeForString($data["orto"]);
        $orleft = $sanitize->sanitizeForString($data["orleft"]);
        $noofreset = $sanitize->sanitizeForString($data["noofreset"]);
        
        $sql = "UPDATE tbl_terminals SET nextor = ?, orfrom = ?, orto = ?, orleft = ?, noofreset = ? WHERE terminal = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", $nextor, $orfrom, $orto, $orleft, $noofreset, $terminal);

        if (!$stmt->execute()) {
            $stmt->close();
            printf($stmt->error);
        } else {
            $stmt->close();
            echo 'SUCCESS';
        }
    }
}
?>
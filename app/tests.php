<?php
/*
 * Copyright (c) 2025.
 *
 * Code is licensed under GNU GPLv3 License and available in the Copying file.
 * Code was written by:
 * - Simone Robaldo ( simone.robaldo at itiscuneo.eu )
 * - Giulia Vadelli ( giulia.vadelli at itiscuneo.eu )
 * - Daniele Torchio ( daniele.torchio at itiscuneo.eu )
 */

use DAO\RedisDb;
use Model\Token;
use Random\RandomException;
use Utilities\Uid;

require '../vendor/autoload.php';

$localAuthToken = "";

if($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $localAuthToken = Token::generate(Uid::generate());

        RedisDb::connect();
        RedisDb::storeTestingToken($localAuthToken->getToken());
    } catch (RandomException|Exception $e) {
        echo $e->getMessage();
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];
    $token = $_POST["token"];

    $output = "";

    if($action == "testCreationAndVerification") {
        $output .= "-- CREATING ACCOUNT --";

    }

}

error_log($localAuthToken);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>API Testing page</title>
</head>
<body>
    <h1>API Testing Page</h1>
    <h2>Test result</h2>
    <p style="font-family: 'JetBrains Mono', 'Maple Mono SC NF', monospace; padding: 1rem; background-color: black; color: white" id="commandOutput">
        <?php echo $output ?? "Run a command first"; ?>
    </p>
    <form>
    <h2>Authorization</h2>
    <label>
        Token <input type="password" required name="token" id="token" placeholder="Authorization token" />
    </label>
    <h2>Tests available</h2>
    <table>
        <thead>
            <tr>
                <th>Action</th>
                <th>Exectue</th>
            </tr>
        </thead>
    </table>
    </form>
</body>
</html>


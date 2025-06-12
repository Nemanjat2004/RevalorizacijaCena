<?php

class Database
{

    private $host, $database, $username, $password, $connection;

    private $port = 3306;

    /** Put this variable to true if you want ALL queries to be debugged by default:

     */

    public $defaultDebug = false;

    /** INTERNAL: The last result ressource of a query().

     */

    public $lastResult;

    /** INTERNAL: The start time, in miliseconds.

     */

    public $mtStart;

    /** INTERNAL: The number of executed queries.

     */

    public $nbQueries;

    /**

     * Sets the connection credentials to connect to your database.

     *

     * @param string $host - the host of your database

     * @param string $username - the username of your database

     * @param string $password - the password of your database

     * @param string $database - your database name

     * @param integer $port - the port of your database

     * @param boolean $autoconnect - to auto connect to the database after settings connection credentials

     */

    public function __construct($host, $username, $password, $database, $port = 3306, $autoconnect = true)
    {

        $this->host = $host;

        $this->database = $database;

        $this->username = $username;

        $this->password = $password;

        $this->port = $port;

/////////////////////////////

        $this->mtStart = $this->getMicroTime();

        $this->nbQueries = 0;

        $this->lastResult = null;

//////////////////////////////

        if ($autoconnect) {
            $this->open();
        }

    }

    /**

     * Open the connection to your database.

     */

    public function open()
    {

        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);

    }

    /**

     * Close the connection to your database.

     */

    public function close()
    {

        $this->connection->close();

    }

    /**

     *

     * Execute your query

     *

     * @param string $query - your sql query

     * @return the result of the executed query

     */

    public function query($query, $debug = -1)
    {

        $this->nbQueries++;

        $this->lastResult = $this->connection->query($query) or $this->debugAndDie($query);

        $this->debug($debug, $query, $this->lastResult);

        return $this->lastResult;

    }

    /** Do the same as query() but do not return nor store result.\n

     * Should be used for INSERT, UPDATE, DELETE...

     * @param $query The query.

     * @param $debug If true, it output the query and the resulting table.

     */

    public function execute($query, $debug = -1)
    {

        $this->nbQueries++;

        mysql_query($query) or $this->debugAndDie($query);

        $this->debug($debug, $query);

    }

    public function fetchNextObject($result = null)
    {

        if ($result == null) {
            $result = $this->lastResult;
        }

        if ($result == null || mysqli_num_rows($result) < 1) {
            return null;
        } else {
            return mysqli_fetch_object($result);
        }

    }

    public function numRows($result = null)
    {

        if ($result == null) {
            return mysqli_num_rows($this->lastResult);
        } else {
            return mysqli_num_rows($result);
        }

    }

    /** Get the result of the query as an object. The query should return a unique row.\n

     * Note: no need to add "LIMIT 1" at the end of your query because

     * the method will add that (for optimisation purpose).

     * @param $query The query.

     * @param $debug If true, it output the query and the resulting row.

     * @return An object representing a data row (or NULL if result is empty).

     */

    /**

     * Escape your parameters to prevent SQL Injections! Usage: See documentation (link at the top of the file)

     *

     * @param string $string - your parameter to escape

     * @return the escaped string

     */

    public function escape($string)
    {

        return $this->connection->escape_string($string);

    }

//////////////////////////////////////

    public function getMicroTime()
    {

        list($msec, $sec) = explode(' ', microtime());

        return floor($sec / 1000) + $msec;

    }

    public function debugAndDie($query)
    {

        $this->debugQuery($query, "Error");

        die("<p style=\"margin: 2px;\">" . mysql_error() . "</p></div>");

    }

    /** Internal function to debug a MySQL query.\n

     * Show the query and output the resulting table if not NULL.

     * @param $debug The parameter passed to query() functions. Can be boolean or -1 (default).

     * @param $query The SQL query to debug.

     * @param $result The resulting table of the query, if available.

     */

    public function debug($debug, $query, $result = null)
    {

        if ($debug === -1 && $this->defaultDebug === false) {
            return;
        }

        if ($debug === false) {
            return;
        }

        $reason = ($debug === -1 ? "Default Debug" : "Debug");

        $this->debugQuery($query, $reason);

        if ($result == null) {
            echo "<p style=\"margin: 2px;\">Number of affected rows: " . mysql_affected_rows() . "</p></div>";
        } else {
            $this->debugResult($result);
        }

    }

    /** Internal function to output a query for debug purpose.\n

     * Should be followed by a call to debugResult() or an echo of "</div>".

     * @param $query The SQL query to debug.

     * @param $reason The reason why this function is called: "Default Debug", "Debug" or "Error".

     */

    public function debugQuery($query, $reason = "Debug")
    {

        $color = ($reason == "Error" ? "red" : "orange");

        echo "<div style=\"border: solid $color 1px; margin: 2px;\">" .

        "<p style=\"margin: 0 0 2px 0; padding: 0; background-color: #DDF;\">" .

        "<strong style=\"padding: 0 3px; background-color: $color; color: white;\">$reason:</strong> " .

        "<span style=\"font-family: monospace;\">" . htmlentities($query) . "</span></p>";

    }

    /** Internal function to output a table representing the result of a query, for debug purpose.\n

     * Should be preceded by a call to debugQuery().

     * @param $result The resulting table of the query.

     */

    public function debugResult($result)
    {

        echo "<table border=\"1\" style=\"margin: 2px;\">" .

            "<thead style=\"font-size: 80%\">";

        $numFields = mysqli_num_fields($result);

        // BEGIN HEADER

        $tables = [];

        $nbTables = -1;

        $lastTable = "";

        $fields = [];

        $nbFields = -1;

        while ($column = mysqli_fetch_field($result)) {

            if ($column->table != $lastTable) {

                $nbTables++;

                $tables[$nbTables] = ["name" => $column->table, "count" => 1];

            } else {
                $tables[$nbTables]["count"]++;
            }

            $lastTable = $column->table;

            $nbFields++;

            $fields[$nbFields] = $column->name;

        }

        for ($i = 0; $i <= $nbTables; $i++) {
            echo "<th colspan=" . $tables[$i]["count"] . ">" . $tables[$i]["name"] . "</th>";
        }

        echo "</thead>";

        echo "<thead style=\"font-size: 80%\">";

        for ($i = 0; $i <= $nbFields; $i++) {
            echo "<th>" . $fields[$i] . "</th>";
        }

        echo "</thead>";

        // END HEADER

        while ($row = mysqli_fetch_array($result)) {

            echo "<tr>";

            for ($i = 0; $i < $numFields; $i++) {
                echo "<td>" . htmlentities($row[$i]) . "</td>";
            }

            echo "</tr>";

        }

        echo "</table></div>";

        $this->resetFetch($result);

    }

    /** Get how many time the script took from the begin of this object.

     * @return The script execution time in seconds since the

     * creation of this object.

     */

    public function getExecTime()
    {

        return round(($this->getMicroTime() - $this->mtStart) * 1000) / 1000;

    }

    /** Get the number of queries executed from the begin of this object.

     * @return The number of queries executed on the database server since the

     * creation of this object.

     */

    public function getQueriesCount()
    {

        return $this->nbQueries;

    }

    /** Go back to the first element of the result line.

     * @param $result The resssource returned by a query() function.

     */

    public function resetFetch($result)
    {

        if (mysqli_num_rows($result) > 0) {
            mysqli_data_seek($result, 0);
        }

    }

    /** Get the id of the very last inserted row.

     * @return The id of the very last inserted row (in any table).

     */

    public function lastInsertedId()
    {

        return mysqli_insert_id();

    }
/* NOVE FUNKCIJE ZA NOVI DBCLASS7.php */

    public function prepareAndExecute($query, $types, ...$params)
    {
        $stmt = $this->connection->prepare($query);
        if (! $stmt) {
            die("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result();
    }

/* FUNKCIJA YA INSTERT,UPDATE,DELETE   */

    public function executeNonSelect($query, $types = "", ...$params)
    {
        $stmt = $this->connection->prepare($query);
        if (! $stmt) {
            die("Prepare failed: " . $this->connection->error);
        }

        if (! empty($types)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        return [
            'success'       => $stmt->affected_rows > 0,
            'affected_rows' => $stmt->affected_rows,
            'insert_id'     => $this->connection->insert_id,
        ];
    }

}

function DBstambena()
{

    return new Database('localhost', 'root', '', 'stambenaGithub');

}

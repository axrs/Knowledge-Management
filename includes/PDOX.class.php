<?php
/**
 * PDOX Class
 * Author: Alexander Scott
 * Date: April 2012
 * Revision:
 * 20120409 - Initial Release
 */
class PDOX extends PDO
{
    private $userTableSQL = "CREATE TABLE `user` (userID INTEGER PRIMARY KEY AUTOINCREMENT, userName TEXT NOT NULL UNIQUE, userFirstName TEXT NOT NULL, userSurname TEXT NOT NULL, userPassword TEXT NOT NULL,userEmail TEXT NOT NULL, userIsActive BOOLEAN, userIsAdmin BOOLEAN);";
    private $knowledgeTableSQL = "CREATE TABLE `knowledge` (knowledgeID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, knowledgeTitle TEXT NOT NULL, knowledgeBody VARCHAR NOT NULL, knowledgeAuthor INTEGER NOT NULL, knowledgeDate DATE NOT NULL);";
    private $tagTableSQL = "CREATE TABLE `tag` (tagID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, tagName TEXT NOT NULL UNIQUE);";
    private $tagsTableSQL = "CREATE TABLE `tags` (tagID INTEGER NOT NULL, knowledgeID INTEGER NOT NULL);";

    /**Constructs the PDO Object
     * @param string $dsn Data Source Name
     * @param string $username Data Source Username
     * @param string $password Data Source Password
     * @param array $driver_options Driver Options
     */
    public function __construct($dsn, $username = null, $password = null, $driver_options = null)
    {
        parent::__construct($dsn, $username, $password, $driver_options);

        //Check if the database was just created. If it was, create the necessary tables and users.
        if (!$this->hasTable('user')){
            $this->initialiseDatabaseStructure();
        }
        $this->sqliteCreateAggregate(
            "group_concat",
            array($this, '__sqlite_group_concat_step'),
            array($this, '__sqlite_group_concat_finalize'),
            2
        );

    }

    function __sqlite_group_concat_step($context, $idx, $string, $separator = ",") {
        return ($context) ? ($context . $separator . $string) : $string;
    }

    function __sqlite_group_concat_finalize($context) {
        return $context;
    }

    private function destroyDatabase(){
        $this->exec("DROP TABLE `user`;");
        $this->exec("DROP TABLE `knowledge`;");
        $this->exec("DROP TABLE `tag`;");
        $this->exec("DROP TABLE `tags`;");
    }
    private function initialiseDatabaseStructure(){
        //Create and populate the User Table
        $this->exec($this->userTableSQL);
        $query = "INSERT INTO `user` (`userName`,`userEmail`,`userFirstName`,`userSurname`,`userPassword`,`userIsActive`,`userIsAdmin`) VALUES(?,?,?,?,?,?,?);";
        $statement = $this->prepare($query);
        $password = md5('Halipenek3');
        $statement->execute(array('admin','admin@glynntucker.com.au','Super','Admin',$password,1,1));

        //Create the knowledge Table
        $this->exec($this->knowledgeTableSQL);
        $this->exec($this->tagTableSQL);
        $this->exec($this->tagsTableSQL);
    }

    function hasTable($table){
        $results = parent::prepare("SELECT name FROM sqlite_master WHERE type='table';");
        $results->execute();
        foreach ($results as $tables){
            if ($tables['name'] == $table) return true;
        }
        return false;
    }
    /**Prints out generic HTML view of the specified table content
     * @param string $table Table name to print out.
     */
    function printTable($table)
    {
        //Get the column names
        $columnNames = array();
        $columns = parent::prepare("PRAGMA table_info($table);");
        $columns->execute();
        foreach ($columns as $column) {
            array_push($columnNames, $column['name']);
        }

        //Get the table tuples
        $results = parent::prepare("SELECT * FROM $table;");
        $results->execute();

        $out = "";

        //If there are results in the table, print the table
        if ($results != null) {
            print "<table border=1 style='margin: 20px;border-collapse: collapse;'><tr style='padding: 4px;background: #eee;' >";

            //Print each column name as headder
            foreach ($columnNames as $column) {
                $out .= "<th style='padding: 4px;' >$column</th>";
            }
            print $out;
            print "</tr>";

            //Print out the table rows
            while ($row = $results->fetch()) {
                $out = "<tr>";
                foreach ($columnNames as $column) {
                    $out .= "<td style='padding: 4px;'>$row[$column]</td>";
                }
                print "<tr>";
                print $out;
                print "</tr>";
            }
            print "</table>";
        }
    }
}
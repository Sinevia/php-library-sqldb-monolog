<?php

namespace Sinevia;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use PDO;
use PDOStatement;
use Sinevia\SqlDb;

/**
 * This class is a handler for Monolog, which can be used
 * to write records in a MySQL table
 */
class SqlDbHandler extends AbstractProcessingHandler
{

    /**
     * @var bool defines whether the MySQL connection is been initialized
     */
    private $initialized = false;

    /**
     * @var Sinevia\SqlDb
     */
    protected $db = null;

    /**
     * @var PDO pdo object of database connection
     */
    protected $pdo;

    /**
     * @var PDOStatement statement to insert a new record
     */
    private $statement;

    /**
     * @var string the table to store the logs in
     */
    private $table = 'logs';

    private $debug = false;

    /**
     * Constructor of this class, sets the PDO and calls parent constructor
     *
     * @param PDO $pdo                  PDO Connector for the database
     * @param bool $table               Table in the database to store the logs in
     * @param bool|int $level           Debug level which this handler should store
     * @param bool $bubble
     */
    public function __construct(PDO $pdo = null, $table, $level = Logger::DEBUG, $bubble = true)
    {
        if (!is_null($pdo)) {
            $this->pdo = $pdo;
            $this->db = new SqlDb();
            $this->db->setPdo($this->pdo);
        }

        $this->table = $table;

        parent::__construct($level, $bubble);
    }

    /**
     * Initializes this handler by creating the table if it not exists
     */
    private function initialize()
    {
        $this->db->debug = $this->debug;

        if ($this->db->table($this->table)->exists() == false) {
            $this->db->table($this->table)
                ->column('Id', 'INTEGER', 'PRIMARY KEY AUTOINCREMENT')
                ->column('Channel', 'STRING')
                ->column('Level', 'STRING')
                ->column('Message', 'TEXT')
                ->column('Context', 'TEXT')
                ->column('Time', 'DATETIME')
                ->create();
        }

        $this->initialized = true;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  $record[]
     * @return void
     */
    protected function write(array $record)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $contentArray = [
            'Channel' => $record['channel'],
            'Level' => $record['level'],
            'Message' => $record['message'],
            'Context' => json_encode($record['context']),
            'Time' => $record['datetime']->format('Y-m-d H:i:s'),
        ];

        $this->db->table($this->table)->insert($contentArray);
    }
}

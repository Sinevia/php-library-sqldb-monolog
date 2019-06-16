# php-library-sqldb-monolog


```
/**
 * Logger function
 * @return \Monolog\Logger
 */
function logger($channel="default")
{
    static $logger = null;

    if (is_null($logger)) {
        //Create MysqlHandler
        $mySQLHandler = new \Sinevia\SqlDbMonologHandler($YOURPDO, $YOOURLOGTABLENAME, \Monolog\Logger::DEBUG);

        //Create logger
        $logger = new \Monolog\Logger($channel);
        $logger->pushHandler($mySQLHandler);
    }

    return $logger;
}
```

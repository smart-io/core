<?php
namespace Sinergi\Core\Test;

use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit_Extensions_Database_DataSet_CompositeDataSet;
use PHPUnit_Extensions_Database_DataSet_DefaultDataSet;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Extensions_Database_Operation_Composite;
use PHPUnit_Extensions_Database_Operation_Factory;
use PHPUnit_Extensions_Database_Operation_Truncate;
use PHPUnit_Extensions_Database_TestCase;

/**
 * Executes a mysql 5.5 safe truncate against all tables in a dataset.
 *
 * @package    DbUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2011 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_Extensions_Database_Operation_MySQL55Truncate extends PHPUnit_Extensions_Database_Operation_Truncate
{
    public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        $connection->getConnection()->query("SET @PHAKE_PREV_foreign_key_checks = @@foreign_key_checks");
        $connection->getConnection()->query("SET foreign_key_checks = 0");
        parent::execute($connection, $dataSet);
        $connection->getConnection()->query("SET foreign_key_checks = @PHAKE_PREV_foreign_key_checks");
    }
}

abstract class DatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
    use TestCaseTrait;

    /**
     * @var PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    private static $connection;

    public function __construct()
    {
        $this->initTestCaseTrait();
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_CompositeDataSet
     */
    public function import()
    {
        $tables = func_get_args();

        $dataset = [];
        foreach ($tables as $table) {
            ltrim($table, '/');
            $dataset[] = $this->createMySQLXMLDataSet($this->getTestDir() . "/_files/DataSets/{$table}.xml");
        }

        return new PHPUnit_Extensions_Database_DataSet_CompositeDataSet($dataset);
    }

    public function truncate()
    {
        $tables = func_get_args();

        $em = $this->registry->getDoctrine()->getEntityManager();
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');

        foreach ($tables as $table) {
            $connection->executeUpdate($platform->getTruncateTableSQL($table, true));
        }

        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');

        $em->clear();
    }

    public function getSetUpOperation()
    {
        return new PHPUnit_Extensions_Database_Operation_Composite([
            new PHPUnit_Extensions_Database_Operation_MySQL55Truncate(true),
            PHPUnit_Extensions_Database_Operation_Factory::INSERT()
        ]);
    }

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $em = $this->registry->getDoctrine()->createEntityManager();
        $pdo = $em->getConnection()->getWrappedConnection();
        $em->clear();

        $tool = new SchemaTool($em);
        $classes = $em->getMetaDataFactory()->getAllMetaData();

        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        self::$connection = $this->createDefaultDBConnection($pdo, ':memory:');;
        return self::$connection;
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }
}

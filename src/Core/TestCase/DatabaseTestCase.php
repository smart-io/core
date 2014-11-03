<?php
namespace Sinergi\Core\TestCase;

use PHPUnit_Extensions_Database_DataSet_CompositeDataSet;
use PHPUnit_Extensions_Database_DataSet_DefaultDataSet;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Extensions_Database_Operation_Composite;
use PHPUnit_Extensions_Database_Operation_Factory;
use PHPUnit_Extensions_Database_Operation_Truncate;
use PHPUnit_Extensions_Database_TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\ClassMetadata;

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

    /**
     * @var SchemaTool
     */
    private static $tool;

    /**
     * @var EntityManagerInterface
     */
    private static $em;

    /**
     * @var array|ClassMetadata[]
     */
    private static $classes;

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

        $em = $this->container->getDoctrine()->getEntityManager();
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $dataset = [];
        foreach ($tables as $table) {
            ltrim($table, '/');
            $this->createSchema($table);
            $dataset[] = $this->createMySQLXMLDataSet($this->getTestDir() . "/_files/DataSets/{$table}.xml");
        }

        return new PHPUnit_Extensions_Database_DataSet_CompositeDataSet($dataset);
    }

    public function createSchema($table)
    {
        foreach (self::$classes as $meta) {
            if (
                $table === $meta->getTableName() ||
                ".{$table}" === substr($meta->getTableName(), -strlen(".{$table}"))
            ) {
                self::$tool->dropSchema([$meta]);
                self::$tool->createSchema([$meta]);
            }
        }
    }

    public function truncate()
    {
        $tables = func_get_args();

        $em = $this->container->getDoctrine()->getEntityManager();
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
        $em = $this->container->getDoctrine()->createEntityManager();
        $pdo = $em->getConnection()->getWrappedConnection();
        $em->clear();

        self::$em = $em;
        self::$tool = new SchemaTool($em);
        self::$classes = self::$em->getMetaDataFactory()->getAllMetaData();

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

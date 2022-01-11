<?php

namespace Ayumila\Classes;

use Ayumila\Exceptions\AyumilaException;
use Ayumila\Traits\CreateStandard;
use Exception;
use Propel\Runtime\Collection\ObjectCollection;
use \Propel\Runtime\Propel AS PropelRunTime;

class Propel
{
    use CreateStandard;

    /**
     * @param ObjectCollection $collection
     * @return bool
     * @throws AyumilaException
     */
    public function automaticGroupDelete(ObjectCollection $collection): bool
    {
        $objectHelper = $this->getTableDataFromObjectCollection($collection);

        if(!$objectHelper->getPrimaryKeyNames())
        {
            throw new AyumilaException('automaticGroupDelete can\'t find PrimaryKeys');
        }

        $queryHeader  = 'DELETE FROM '.$objectHelper->getTableName().' ';
        $queryWhere   = '';
        $sqlDelete    = array();
        $addSqlDelete = function ($query) use(&$sqlDelete) { $sqlDelete[] = $query.';'; };

        foreach ($collection AS $item)
        {
            $queryWhere .= $queryWhere ? 'OR (' : 'WHERE (';

            foreach ($objectHelper->getPrimaryKeyNames() AS $keyName)
            {
                $queryWhere .= $keyName['name'].' = \''.$item->getByName($keyName['phpName']).'\' ';
            }
            $queryWhere .= ') ';

            if(strlen($queryHeader.$queryWhere) > 300000)
            {
                $addSqlDelete($queryHeader.$queryWhere);
                $queryWhere = '';
            }
        }

        if($queryWhere)
            $addSqlDelete($queryHeader.$queryWhere);

        $this->customQuery($sqlDelete);

        return true;
    }

    /**
     * @param array $sqlQuerys
     * @return bool
     */
    public function customQuery(array $sqlQuerys): bool
    {
        foreach ($sqlQuerys AS $query)
        {
            if(is_string($query))
            {
                $connection = PropelRunTime::getConnection();
                $stmt = $connection->prepare($query);
                try{
                    $stmt->execute();
                }catch (Exception $ex)
                {
                    return false;
                }
            }else{
                return false;
            }
        }

        return true;
    }

    /**
     * @throws AyumilaException
     */
    public function getTableDataFromObjectCollection(ObjectCollection $collection): PropelObjectHelper
    {
        $tableMap = $collection->getTableMapClass();

        if(
            class_exists($tableMap)
            && method_exists($tableMap,'getTableMap')
        ){
            $tableMap = $tableMap::getTableMap();

            $primaryKeyNames = array();

            foreach($tableMap->getPrimaryKeys() AS $key)
            {
                $primaryKeyNames[] = [
                    'name' => $key->getName(),
                    'phpName' => $key->getPhpName(),
                ];
            }

            $objectHelper = new PropelObjectHelper();
            $objectHelper->setTableName($tableMap::getTableMap()->getName());
            $objectHelper->setPrimaryKeyNames($primaryKeyNames);

            return $objectHelper;
        }

        throw new AyumilaException('getTableDataFromObjectCollection can\'t find the Propel:TableMap or Propel:TableMap Method getTableMap');
    }
}

class PropelObjectHelper
{
    private string $tableName = '';
    private array  $primaryKeyNames = array();

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return array
     */
    public function getPrimaryKeyNames(): array
    {
        return $this->primaryKeyNames;
    }

    /**
     * @param array $primaryKeyNames
     */
    public function setPrimaryKeyNames(array $primaryKeyNames): void
    {
        $this->primaryKeyNames = $primaryKeyNames;
    }
}


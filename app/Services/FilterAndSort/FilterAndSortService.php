<?php

namespace App\Services\FilterAndSort;

use App\Services\FilterAndSort\Filter\Filter;
use App\Services\FilterAndSort\Sort\Sort;
use ReflectionClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class FilterAndSortService
{
    private $tableColumns;
    private $filterAndSortOnColumns;

    public function __construct()
    {
       // 
    }

    private function getColumns(Model $model) : void
    {
        $tableName = $model->getTable();
        $this->tableColumns = Schema::getColumnListing($tableName);

        /**
         *  ? Retrieving the MODEL name with Full namespace from the Model object (i.e. 'App\User')
         *  ? Creating Reflection class object for the Model
         *  ? Getting the value of constant ENABLE_FILTER_AND_SORT_ON_COLUMNS using the Reflection class object
         **/
        $className = get_class($model);
        $reflection = new ReflectionClass($className);
        $this->filterAndSortOnColumns = $reflection->getConstant('ENABLE_FILTER_AND_SORT_ON_COLUMNS');
    }

    // Receiving the Collection and Model instance
    public function apply(Collection $collection, Model $model) : Collection
    { 
        $this->getColumns($model);

        // FILTERING
        $filter = new Filter($this->filterAndSortOnColumns, $this->tableColumns);
        $filteredCollection = $filter->apply($collection);

        // SORTING
        $sort = new Sort($this->filterAndSortOnColumns, $this->tableColumns);
        $filteredAndSortedCollection = $sort->apply($filteredCollection);
        
        return $filteredAndSortedCollection;
    }
}

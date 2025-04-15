<?php

namespace App\Services\FilterAndSort\Filter;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;

class Filter
{
    private $filterAndSortOnColumns;
    private $tableColumns;
    private $excludeQueryParamsFromFilter;
    private $filterEnabledTableColumnCollection;
    private $queryParametersForFiltering;

    public function __construct($filterAndSortOnColumns, $tableColumns)
    {
        $this->filterAndSortOnColumns       = $filterAndSortOnColumns;
        $this->tableColumns                 = $tableColumns;
        $this->excludeQueryParamsFromFilter = explode(',', config('custom.SUPPORTED_QUERY_PARAMETERS', [
            'sort_by',  // 'sort_by' is used only for Sorting 
            'desc',     // 'desc' is used for Sorting in Reverse order
            'per_page', // 'per_page' is used in Pagination
            'unique',   // 'unique' is used in the Relevant controllers to fetch the unique results
            'page'      // 'page' is being used inside the Paginated links
        ]));
        $this->filterEnabledTableColumnCollection = collect();
    }

    /**
     *  ! Ensuring that the ENABLE_FILTER_AND_SORT_ON_COLUMNS is defined
     *      ? IF the 'const ENABLE_FILTER_AND_SORT_ON_COLUMNS' is defined inside the MODEL
     *          * The query parameter must be one of the FILTER_ENABLED_COLUMNS's values  
     *      ? ELSE
     *          * The query parameter must be one of the tables's columns   
     */
    private function verifyCustomFilterColumns() : void
    {
        if (is_array($this->filterAndSortOnColumns) && sizeOf($this->filterAndSortOnColumns) > 0) {
            $this->filterEnabledTableColumnCollection = collect($this->filterAndSortOnColumns);
        } else {
            $this->filterEnabledTableColumnCollection = collect($this->tableColumns);
        }
    }

    /**
     *  ! Unsetting all the Query Parameters which are not being used to Filter the collection
     *      ? Because, $collection = $collection->where($query, $value); used in the 'Filter logic' returns nothing when encountering these Query parameters   
     */
    private function prepareQueryParamsForFilter() : void
    {
        $this->queryParametersForFiltering = Request::query();
        foreach ($this->excludeQueryParamsFromFilter as $value) {
            unset($this->queryParametersForFiltering[$value]);
        }
    }

    /**
     * ! Validating the Query parameters being used inside the filterCollection()
     * ! Throw the HttpException if the following coditions are not met: 
     *      ? 1. If 'const ENABLE_FILTER_AND_SORT_ON_COLUMNS' is defined inside the MODEL
     *          * Then the Query parameter must be one of the FILTER_ENABLED_COLUMNS's values  
     *      ? 2. Else
     *          * The query parameter must be one of the tables's columns    
     */
    private function validate() : void
    {
        foreach ($this->queryParametersForFiltering as $key => $value) { 
            $search = $this->filterEnabledTableColumnCollection->search($key);
            $exist = $search >= 0 && $search != null ? 1 : 0;
            if($exist === 0) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, "The $key field is not allowed."); // Throwing HttpException
            }
        }
    }

    private function filterCollection(Collection $collection) : Collection    {
        foreach ($this->queryParametersForFiltering as $query => $value) {
            /**
             *  ? The Query Parameter must be present and has a value.
             *  *    AND
             *  ? The query parameter must be one of the table field's or is defined inside the 'const ENABLE_FILTER_FOR_COLUMNS' in the corresponding Model.       
             *      * Need Ternary operation to return 'boolean' for Query parameter 'id':
             *          * Since $collection->search() returns 0 for the Query parameter 'id' which is mostly at the 0th index in all the database tables
             *          * And 0 inside the If condition will fail the condition resulting in Non-filtered results  
             */
            if (isset($query, $value) && ($this->filterEnabledTableColumnCollection->search($query) >= 0 ? true : false)) {
                $collection = $collection->where($query, $value);
            }
        }

        return $collection;
    }

    public function apply(Collection $collection) : Collection
    {
        $this->verifyCustomFilterColumns();
        $this->prepareQueryParamsForFilter();
        $this->validate();

        return $this->filterCollection($collection);
    }
}

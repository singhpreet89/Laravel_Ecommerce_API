<?php

namespace App\Services\Pagination;

use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginationService
{
    private $perPage;
    private $page;

    public function __construct()
    {
        $this->perPage = 20;                                        // Each page will have 20 results only BY DEFAULT
        $this->page = LengthAwarePaginator::resolveCurrentPage();   // To find which page we are on
    }

    private function validate() : void
    {
        // ? Performing the Validation here to eliminate the need of creating the Request Validation for each Controller's Index method where PAGINATION will be used 
        Request::validate([
            'per_page' => [
                'bail',         // 'bail' makes sure that the Request Stops on the First Validation Failure
                'sometimes',
                'required',
                // Rule::in(array_merge(["disabled"], range(2, 50))),
                function ($attribute, $value, $fail) {
                    if (((int) $value >= 2 && (int) $value <= 50) || $value === 'disabled') {
                        return true;
                    } else {
                        $fail("The selected " . $attribute . " must be an integer between 2 to 50 or a string 'disabled'.");
                    }
                },
            ],
        ]);
    }

    private function paginate(Collection $collection) : LengthAwarePaginator | Collection
    {
        if (Request::has('per_page')) {
            if (Request::input('per_page') === 'disabled') {
                return $collection;
            } else {
                $this->perPage = (int) Request::input('per_page');
            }
        }

        $results = $collection->slice(($this->page - 1) * $this->perPage, $this->perPage)->values();    // Because the first index of collection is 0, but LengthAwarePaginator will start from Page 1

        // ! If we use $paginated, then the other QUERY parameters coming with the GET request such as 'sort_by' will be dropped by default
        $paginated = new LengthAwarePaginator($results, $collection->count(), $this->perPage, $this->page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),   // resolveCurrentPath() will generate the path for another page in the META shown in the GET request
        ]);

        $paginated->appends(Request::all());    // ! So, Appending the other request parameters such as 'sort_by' again
        return $paginated;
    }

    public function apply(Collection $collection) : LengthAwarePaginator | Collection
    {
        $this->validate();
        $paginatedCollection = $this->paginate($collection);

        return $paginatedCollection;
    }
}

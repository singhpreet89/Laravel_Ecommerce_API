<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Pagination\PaginationFacade;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Category\CategoryCollection;
use App\Services\FilterAndSort\FilterAndSortFacade;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product, Category $category) : AnonymousResourceCollection
    {
        $categories = $product->categories;

        $filteredAndSortedCategories = FilterAndSortFacade::apply($categories, $category);
        $paginatedCategories = PaginationFacade::apply($filteredAndSortedCategories);
        
        return CategoryCollection::collection($paginatedCategories);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    // ! Allow to add a new Category for an existing product
    public function update(Request $request, Product $product, Category $category) : AnonymousResourceCollection
    {
        // For using Many to Many relationships, attach, sync, syncWithoutDetaching can be used
        // $product->categories()->attach([$category->id]);             // ? This will add the Category again if it is already attached
        // $product->categories()->sync([$category->id]);               // ! This will attach the Category only once, and if the Category is aleady attached then it will not be attached again, BUT IT REMOVES ALL THE EXISTING Categories (WORSE)
        $product->categories()->syncWithoutDetaching([$category->id]);  // * This will attach a Category only once, and if the Category already exists then it will not be attached again and it does not removes the existing Categories 

        return CategoryCollection::collection($product->categories);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, Category $category) : AnonymousResourceCollection | Response
    {
        if (!$product->categories()->find($category->id)) {
            return response([
                'message' => 'Not found.',
                'errors' => [
                    'category' => [
                        'The specified category does not belong to this product.'
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        $product->categories()->detach($category->id);

        $categories = $product->categories;

        $filteredAndSortedCategories = FilterAndSortFacade::apply($categories, $category);
        $paginatedCategories = PaginationFacade::apply($filteredAndSortedCategories);

        return CategoryCollection::collection($paginatedCategories);
    }
}

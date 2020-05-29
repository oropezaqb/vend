<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Searchable\Search;
use App\Company;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $searchResults = (new Search())
           ->registerModel(Company::class, 'name')
           ->search($request->search);
        return view('search', compact('searchResults'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubcategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.subcategories', ['subcategories' => Subcategory::with('category')->latest()->get(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(StoreSubcategoryRequest $request): RedirectResponse
    {
        Subcategory::create($request->validated());

        return redirect()->route('admin.subcategories.index')->with('success', 'Sub category added successfully.');
    }
}

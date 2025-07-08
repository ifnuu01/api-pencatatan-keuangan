<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $categories = Category::where('user_id', $this->getAuthUser()->id)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'type', 'icon', 'color']);

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        Category::create([
            'user_id' => $this->getAuthUser()->id,
            'name' => $request->name,
            'type' => $request->type,
            'icon' => $request->icon ?? "fa fa-circle",
            'color' => $request->color ?? '#000000',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Category created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::where('user_id', $this->getAuthUser()->id)->find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Category retrieved successfully',
            'data' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, string $id)
    {
        $category = Category::where('user_id', $this->getAuthUser()->id)->find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $category->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Category updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::where('user_id', $this->getAuthUser()->id)->find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}

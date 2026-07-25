<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $categories = Category::where('business_id', $business->id)->get();
        return view('categories.index', compact('categories'));
    }

    public function create() { return view('categories.create'); }

    public function store(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $role = $request->user()->getBusinessRole($business);
        abort_unless(in_array($role, ['primary_admin', 'admin']), 403, 'Regular users cannot manage categories.');

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where('business_id', $business->id),
            ],
            'type' => 'required|in:income,expense',
        ]);

        $category = Category::create($data + ['business_id' => $business->id]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'category' => $category
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    public function edit(Request $request, Category $category)
    {
        $business = $request->attributes->get('activeBusiness');
        $role = $request->user()->getBusinessRole($business);
        abort_unless(in_array($role, ['primary_admin', 'admin']), 403, 'Regular users cannot manage categories.');

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $business = $request->attributes->get('activeBusiness');
        $role = $request->user()->getBusinessRole($business);
        abort_unless(in_array($role, ['primary_admin', 'admin']), 403, 'Regular users cannot manage categories.');

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where('business_id', $business->id)->ignore($category->id),
            ],
            'type' => 'required|in:income,expense',
        ]);
        $category->update($data);
        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy(Request $request, Category $category)
    {
        $business = $request->attributes->get('activeBusiness');
        $role = $request->user()->getBusinessRole($business);
        abort_unless(in_array($role, ['primary_admin', 'admin']), 403, 'Regular users cannot manage categories.');

        // Unlink category from attached transactions before deletion
        $category->transactions()->update(['category_id' => null]);
        $category->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!'
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}

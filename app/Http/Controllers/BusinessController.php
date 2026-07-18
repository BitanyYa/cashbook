<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Auth::user()->businesses()->paginate(12);
        return view('businesses.index', compact('businesses'));
    }

    public function show(Business $business)
    {
    $this->authorize('view', $business);
    $businesses_count = Auth::user()->businesses()->count();
    return view('businesses.show', compact('business', 'businesses_count'));
    }

    public function create() { return view('businesses.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
        ]);
        $business = Business::create($data);
        $request->user()->businesses()->attach($business->id, ['role' => 'primary_admin']);
        session(['active_business_id' => $business->id]);
        return redirect()->route('businesses.index');
    }

    public function edit(Business $business)
    {
        $this->authorize('update', $business);
        return view('businesses.edit', compact('business'));
    }

    public function update(Request $request, Business $business)
    {
        $this->authorize('update', $business);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
        ]);
        $business->update($data);
        return back();
    }

    public function destroy(Business $business)
    {
        $this->authorize('delete', $business);
        $business->delete();
        return redirect()->route('businesses.index');
    }
}

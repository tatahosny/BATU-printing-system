<?php

namespace App\Http\Controllers;

use App\Models\University;
use App\Models\College;
use App\Models\Department;
use App\Models\Batch;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HierarchyController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Hierarchy', [
            'universities' => University::with('colleges.departments.batches.subjects')->get()
        ]);
    }

    public function storeUniversity(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        University::create($data);
        return back()->with('success', 'University added successfully');
    }

    public function storeCollege(Request $request, University $university)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $university->colleges()->create($data);
        return back();
    }

    // ... تكرار نفس المنطق للأقسام والدفعات والمواد
}

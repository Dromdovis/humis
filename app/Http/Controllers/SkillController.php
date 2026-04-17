<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index()
    {
        $skills = Skill::withCount(['employees', 'projects'])
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        $existingCategories = Skill::distinct()->pluck('category')->filter()->values();

        return view('skills.index', compact('skills', 'existingCategories'));
    }

    public function create()
    {
        $categories = Skill::distinct()->pluck('category')->filter()->values();

        return view('skills.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name',
            'category' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Įveskite technologijos pavadinimą.',
            'name.max' => 'Pavadinimas per ilgas (maks. 255 simboliai).',
            'name.unique' => 'Tokia technologija jau egzistuoja.',
        ]);

        Skill::create($validated);

        return redirect()->route('skills.index')
            ->with('success', 'Įgūdis sukurtas');
    }

    public function edit(Skill $skill)
    {
        $categories = Skill::distinct()->pluck('category')->filter()->values();

        return view('skills.edit', compact('skill', 'categories'));
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name,' . $skill->id,
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Įveskite technologijos pavadinimą.',
            'name.max' => 'Pavadinimas per ilgas (maks. 255 simboliai).',
            'name.unique' => 'Tokia technologija jau egzistuoja.',
        ]);

        $skill->update($validated);

        return redirect()->route('skills.index')
            ->with('success', 'Įgūdis atnaujintas');
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();

        return redirect()->route('skills.index')
            ->with('success', 'Įgūdis ištrintas');
    }
}

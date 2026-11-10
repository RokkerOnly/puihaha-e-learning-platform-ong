<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    public function index()
    {
        $levels = Level::withCount('members')
            ->orderBy('fee')
            ->get();

        return view('levels.index', compact('levels'));
    }

    public function create()
    {
        $level = new Level();

        return view('levels.form', compact('level'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Level::create($validated);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Membership level added successfully.');
    }

    public function edit(Level $level)
    {
        return view('levels.form', compact('level'));
    }

    public function update(Request $request, Level $level)
    {
        $validated = $request->validate($this->rules($level));

        $level->update($validated);

        return redirect()
            ->route('levels.index')
            ->with('success', 'Membership level updated successfully.');
    }

    private function rules(?Level $level = null): array
    {
        $uniqueName = Rule::unique('levels', 'name');

        if ($level !== null) {
            $uniqueName->ignore($level);
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueName,
            ],
            'fee' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:99999999.99',
            ],
            'validity_months' => [
                'required',
                'integer',
                'min:1',
                'max:1200',
            ],
            'benefits' => [
                'nullable',
                'string',
                'max:10000',
            ],
        ];
    }
}
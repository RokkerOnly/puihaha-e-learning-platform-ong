<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Category;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\MembershipTransaction;
use App\Models\Payment;

class MemberController extends Controller
{
    public function index(Request $request)
    {
    $validated = $request->validate([
        'search' => ['nullable', 'string', 'max:255'],
    ]);

    $search = trim($validated['search'] ?? '');

    $members = Member::with(['category', 'level'])
        ->when($search !== '', function ($query) use ($search) {
            $terms = preg_split('/\s+/', $search);

            $query->where(function ($matches) use ($search, $terms) {
                $matches->where(function ($names) use ($terms) {
                    foreach ($terms as $term) {
                        $names->where(function ($name) use ($term) {
                            $name->where('first_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%");
                        });
                    }
                })
                ->orWhereHas('category', function ($category) use ($search) {
                    $category->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('level', function ($level) use ($search) {
                    $level->where('name', 'like', "%{$search}%");
                });
            });
        })
        ->orderByDesc('id')
        ->get();

    return view('members.index', compact('members'));
    }

    public function create()
    {
        $categories = Category::all();
        $levels = Level::all();

        return view('members.create', compact('categories', 'levels'));
    }

    public function store(Request $request)
    {
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'email',
            'max:255',
            'unique:members,email',
        ],
        'category_id' => [
            'required',
            'integer',
            'exists:categories,id',
        ],
        'level_id' => [
            'required',
            'integer',
            'exists:levels,id',
        ],
    ]);

    $validated['membership_no'] = 'MEM-' . Str::uuid();
    $validated['active'] = true;

    Member::create($validated);

    return redirect()
        ->route('members.index')
        ->with('success', 'Member registered successfully.');
    }
    
    public function show(int $id)
    {
    $member = Member::with(['category', 'level'])->findOrFail($id);

    return view('members.show', compact('member'));
    }

    public function edit(int $id)
    {
    $member = Member::findOrFail($id);
    $categories = Category::all();
    $levels = Level::all();

    return view('members.edit', compact(
        'member',
        'categories',
        'levels'
    ));
    }

    public function update(Request $request, int $id)
    {
    $member = Member::findOrFail($id);

    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => [
            'required',
            'email',
            'max:255',
            Rule::unique('members', 'email')->ignore($member),
        ],
        'category_id' => [
            'required',
            'integer',
            'exists:categories,id',
        ],
        'level_id' => [
            'required',
            'integer',
            'exists:levels,id',
        ],
    ]);

    $member->update($validated);

    return redirect()
        ->route('members.index')
        ->with('success', 'Member updated successfully.');
    }

    public function deactivate(int $id)
        {
    $member = Member::findOrFail($id);
    $member->active = false;
    $member->save();

    return redirect()
        ->route('members.index')
        ->with('success', 'Member deactivated successfully.');
    }

   public function reactivate(Member $member)
    {
    if (
        $member->membership_expires_at
        && $member->membership_expires_at->isPast()
    ) {
        return redirect()
            ->route('members.index')
            ->withErrors([
                'membership' => 'This membership has expired. Process and approve a renewal to reactivate it.',
            ]);
    }

    $member->active = true;
    $member->save();

    return redirect()
        ->route('members.index')
        ->with('success', 'Member reactivated successfully.');
    }
    
    public function dashboard()
    {
    $totalMembers = Member::count();
    $activeMembers = Member::where('active', true)->count();
    $inactiveMembers = Member::where('active', false)->count();

    $totalCategories = Category::count();
    $totalLevels = Level::count();

    $pendingTransactions = MembershipTransaction::where(
        'status',
        MembershipTransaction::STATUS_PENDING
    )->count();

    $collectedPayments = Payment::sum('amount');

    return view('dashboard', compact(
        'totalMembers',
        'activeMembers',
        'inactiveMembers',
        'totalCategories',
        'totalLevels',
        'pendingTransactions',
        'collectedPayments'
    ));
    }

    public function printRecord(Member $member)
    {
    $member->load(['category', 'level']);

    return view('members.print', compact('member'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->expenses()->orderBy('date', 'desc');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'category'    => 'required|string',
            'date'        => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $expense = $request->user()->expenses()->create([
            'amount'      => $request->amount,
            'category'    => $request->category,
            'date'        => $request->date,
            'description' => $request->description,
        ]);

        return response()->json($expense, 201);
    }

    public function update(Request $request, $id)
    {
        $expense = $request->user()->expenses()->findOrFail($id);

        $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'category'    => 'required|string',
            'date'        => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $expense->update($request->only('amount', 'category', 'date', 'description'));

        return response()->json($expense);
    }

    public function destroy(Request $request, $id)
    {
        $expense = $request->user()->expenses()->findOrFail($id);
        $expense->delete();
        return response()->json(['message' => 'Expense deleted']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function dashboard(Request $request)
    {
        $user  = $request->user();
        $today = Carbon::today();
        $month = Carbon::now()->startOfMonth();

        $todayExpenses = $user->expenses()
            ->whereDate('date', $today)
            ->sum('amount');

        $monthExpenses = $user->expenses()
            ->where('date', '>=', $month)
            ->sum('amount');

        $totalTransactions = $user->expenses()->count();

        $avgExpense = $totalTransactions > 0
            ? $user->expenses()->avg('amount')
            : 0;

        $byCategory = $user->expenses()
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        $recent = $user->expenses()
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'today_expenses'    => round($todayExpenses, 2),
            'month_expenses'    => round($monthExpenses, 2),
            'total_transactions'=> $totalTransactions,
            'avg_expense'       => round($avgExpense, 2),
            'by_category'       => $byCategory,
            'recent'            => $recent,
        ]);
    }

    public function summary(Request $request)
    {
        $user  = $request->user();
        $total = $user->expenses()->sum('amount');
        $avg   = $user->expenses()->avg('amount') ?? 0;
        $cats  = $user->expenses()->distinct('category')->count('category');

        return response()->json([
            'total_expenses'        => round($total, 2),
            'avg_per_transaction'   => round($avg, 2),
            'total_categories'      => $cats,
        ]);
    }

    public function daily(Request $request)
    {
        $user = $request->user();
        $days = $request->get('days', 7);

        $data = $user->expenses()
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw('SUM(amount) as total')
            )
            ->where('date', '>=', Carbon::now()->subDays($days - 1)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return response()->json($data);
    }

    public function byCategory(Request $request)
    {
        $user = $request->user();

        $data = $user->expenses()
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        return response()->json($data);
    }

    public function monthly(Request $request)
    {
        $user = $request->user();

        $data = $user->expenses()
            ->select(
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return response()->json($data);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $request->user()->id,
        ]);

        $request->user()->update($request->only('first_name', 'last_name', 'email'));

        return response()->json(['message' => 'Profile updated', 'user' => $request->user()]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password updated']);
    }

    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'daily_summary'       => 'boolean',
            'budget_alerts'       => 'boolean',
        ]);

        $request->user()->update($request->only(
            'email_notifications',
            'daily_summary',
            'budget_alerts'
        ));

        return response()->json(['message' => 'Notifications updated']);
    }

    public function exportCsv(Request $request)
    {
        $expenses = $request->user()->expenses()->orderBy('date', 'desc')->get();

        $csv  = "Date,Description,Category,Amount\n";
        foreach ($expenses as $e) {
            $csv .= "{$e->date},{$e->description},{$e->category},{$e->amount}\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="expenses.csv"',
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->expenses()->delete();
        $user->delete();

        return response()->json(['message' => 'Account deleted']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function index()
    {
        $bonuses = Bonus::orderByDesc('id')->paginate(20);
        return view('admin.bonus', compact('bonuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'bonus_percent'  => 'required|numeric|min:0.01|max:100',
            'min_amount'     => 'required|numeric|min:100',
        ], [
            'min_amount.min' => 'Minimum order amount must be at least ₹100.',
        ]);

        Bonus::create([
            'name'           => $data['name'],
            'bonus_percent'  => $data['bonus_percent'],
            'min_amount'     => $data['min_amount'],
            'is_active'      => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Bonus rule created. Coupons will auto-generate when an order meets the minimum.');
    }

    public function update(Request $request, Bonus $bonus)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'bonus_percent'  => 'required|numeric|min:0.01|max:100',
            'min_amount'     => 'required|numeric|min:100',
        ], [
            'min_amount.min' => 'Minimum order amount must be at least ₹100.',
        ]);

        $bonus->update([
            'name'           => $data['name'],
            'bonus_percent'  => $data['bonus_percent'],
            'min_amount'     => $data['min_amount'],
            'is_active'      => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Bonus rule updated.');
    }

    public function destroy(Bonus $bonus)
    {
        $bonus->delete();
        return back()->with('success', 'Bonus deleted.');
    }
}

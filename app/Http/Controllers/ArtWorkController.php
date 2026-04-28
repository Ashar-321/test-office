<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArtWorkController extends Controller
{
    public function index(Request $request)
    {
        try {
            $request->validate([
                'input.quantity' => 'required|integer|min:1',
                'input.tiers' => 'required|array|min:1',
                'input.tiers.*.min' => 'required|integer|min:1',
                'input.tiers.*.price' => 'required|numeric|min:0'
            ]);

            $quantity = $request->input('input.quantity');
            $tiers = $request->input('input.tiers');

            $arrayTiers = array_filter($tiers, function ($tier) use ($quantity) { return $tier['min'] <= $quantity; });

            if (empty($arrayTiers)) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'error' => 'No valid pricing tier found'
                ]);
            }

            usort($arrayTiers, function ($a, $b) {
                return $b['min'] <=> $a['min'];
            });

            $selectedTier = $arrayTiers[0];

            return response()->json([
                'success' => true,
                'data' => [
                    'price' => $selectedTier['price']
                ],
                'error' => false
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'data' => null,
                'error' => true
            ]);
           
        }


    }
}
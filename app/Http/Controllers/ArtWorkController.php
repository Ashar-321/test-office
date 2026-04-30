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
                if ($b['min'] !== $a['min']) {
                    return $b['min'] <=> $a['min'];
                }

                return $a['price'] <=> $b['price'];
            });

            $selectedTier = $arrayTiers[0];

            return response()->json([
                'success' => true,
                'data' => [
                    'price' => $selectedTier['price']
                ],
                'error' => null
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'data' => null,
                'error' => null
            ]);
           
        }
    }

    public function exercise1(Request $request)
    {
        try {
            $request->validate([
                'input' => 'required|array|min:1',
                'input.*.id' => 'required|integer',
                'input.*.approved' => 'required|boolean',
                'input.*.rejected' => 'required|boolean',
                'input.*.time' => 'required|integer|min:1'
            ]);

            $versions = $request->input('input');

            $selected = null;

            foreach ($versions as $item) {

                if ($item['approved'] && $item['rejected']) {
                    continue;
                }

                if ($item['approved'] && !$item['rejected']) {

                    if (
                        $selected === null ||
                        $item['time'] > $selected['time'] ||
                        ($item['time'] == $selected['time'] && $item['id'] > $selected['id'])
                    ) {
                        $selected = $item;
                    }
                }
            }

            if (!$selected) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'error' => 'No valid version found'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $selected['id']
                ],
                'error' => null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exercise3(Request $request)
    {
        try {
           $request->validate([
                'input' => 'required|array|min:1',
                'input.*.id' => 'required',
                'input.*.required' => 'required|boolean',
                'input.*.done' => 'required|boolean'
            ]);

            $input = $request->input('input');

            $invalid_ids = [];


            $input = $request->input('input');

            $invalid_ids = [];

            foreach ($input as $item) {
                $required = isset($item['required']) ? (bool)$item['required'] : false;
                $done = isset($item['done']) ? (bool)$item['done'] : false;

                if ($required && !$done) {
                    $invalid_ids[] = $item['id'];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'valid' => empty($invalid_ids),
                    'invalid_items' => $invalid_ids
                ],
                'error' => null
            ], 200);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exercise4(Request $request)
    {
        try{

        
        $request->validate([
            'input.order_qty' => 'required|integer|min:1',
            'input.vendors' => 'required|array|min:1',
            'input.vendors.*.id' => 'required|integer',
            'input.vendors.*.stock' => 'required|integer|min:0'
        ]);

        $orderQty = $request->input('input.order_qty');
        $vendors = $request->input('input.vendors');
        }
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
    }
    }

}
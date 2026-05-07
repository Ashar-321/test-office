<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
                'error' => null
            ], 500);
        }
    }

    public function exercise4(Request $request)
    {
        try {
            $validated = $request->validate([
                'input.order_qty' => 'required|integer|min:1',
                'input.vendors' => 'required|array|min:1',
                'input.vendors.*.id' => 'required|integer',
                'input.vendors.*.stock' => 'required|integer|min:0'
            ]);

            $orderQty = $validated['input']['order_qty'];
            $vendors = $validated['input']['vendors'];

            $totalStock = array_sum(array_column($vendors, 'stock'));

            if ($totalStock < $orderQty) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'error' => 'Insufficient total stock from vendors'
                ], 400);
            }

            $remainingQty = $orderQty;
            $allocation = [];

            foreach ($vendors as $vendor) {

                if ($remainingQty <= 0) {
                    break;
                }

                if ($vendor['stock'] <= 0) {
                    continue;
                }

                $allocated = min($vendor['stock'], $remainingQty);

                $allocation[] = [
                    'vendor_id' => $vendor['id'],
                    'allocated' => $allocated
                ];

                $remainingQty -= $allocated;
            }

            if ($remainingQty > 0) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'error' => 'Allocation failed'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $allocation,
                'error' => null
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'data' => [],
                'error' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exercise5(Request $request)
    {
        try {

            $validated = $request->validate([
                'input.price' => 'required|numeric|min:0',
                'input.discounts' => 'required|array|min:1',
                'input.discounts.*.type' => 'required|in:percentage,flat',
                'input.discounts.*.value' => 'required|numeric|min:0',
            ], [
                'input.price.min' => 'Price cannot be negative.',
                'input.price.numeric' => 'Price must be a valid number.',
            ]);

            $price = (float) $validated['input']['price'];
            $discounts = $validated['input']['discounts'];

            if ($price < 0) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'error' => [
                        'message' => 'Price cannot be negative.',
                    ]
                ], 422);
            }

            $bestPrice = $price;

            foreach ($discounts as $discount) {
                $type = strtolower($discount['type']);
                $value = (float) $discount['value'];

                $finalPrice = $price;

                if ($type === 'percentage') {
                    $finalPrice = $price * (1 - $value / 100);
                } elseif ($type === 'flat') {
                    $finalPrice = $price - $value;
                }

                if ($finalPrice < $bestPrice) {
                    $bestPrice = $finalPrice;
                }
            }

            $bestPrice = max(0, round($bestPrice, 2));

            return response()->json([
                'success' => true,
                'data' => [
                    'final_price' => $bestPrice,
                ],
                'error' => null
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'message' => 'Validation failed',
                    'details' => $e->errors()
                ]
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'message' => 'Something went wrong while calculating discount.',
                    'details' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
        
    }

    // public function exercise6(Request $request)
    // {
    //     try{

    //         $validate = $request->validate([
    //             'input' => 'required|array|min:1',
    //             'input.steps' => 'required|array|min:1',
    //             'input.steps.*.id' => 'required|string',
    //             'input.steps.*.depends_on' => 'nullable|string'
    //         ]);

    //         $steps = $validate['input']['steps'];
    //         $stepMap = [];

    //         foreach ($steps as $step) {
    //             $stepMap[$step['id']] = $step['depends_on'];
    //         }
    //         $visited = [];
    //         $result = [];
    //         foreach ($stepMap as $id => $dependsOn) {
    //             if (!isset($visited[$id])) {
    //                 $stack = [];
    //                 $current = $id;

    //                 while ($current !== null) {
    //                     if (isset($visited[$current])) {
    //                         break;
    //                     }
    //                     if (in_array($current, $stack)) {
    //                         return response()->json([
    //                             'success' => false,
    //                             'data' => null,
    //                             'error' => 'Circular dependency detected'
    //                         ], 400);
    //                     }
    //                     $stack[] = $current;
    //                     $current = $stepMap[$current] ?? null;
    //                 }

    //                 foreach (array_reverse($stack) as $stepId) {
    //                     if (!isset($visited[$stepId])) {
    //                         $result[] = $stepId;
    //                         $visited[$stepId] = true;
    //                     }
    //                 }

    //                 return response()->json([
    //                     'success' => true,
    //                     'data' => json_encode(['valid' => $result, JSON_PRETTY_PRINT]),
    //                     'error' => null
    //                 ], 200);
    //             }
    //         }
    //     }
    //     catch(\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'data' => null,
    //             'error' => $e->getMessage()
    //         ], 422);
    //     }
        
    // }

    public function exercise6(Request $request)
    {
        try {
        $request->validate([
            'input' => 'required|array|min:1',
            'input.steps' => 'required|array|min:1',
            'input.steps.*.id' => 'required|string',
            'input.steps.*.depends_on' => 'nullable|string',
        ], [
            'input.steps.*.id.required' => 'Each step must have an id.',
            'input.steps.*.id.string' => 'Each step id must be a string.',
            'input.steps.*.depends_on.string' => 'Each step dependency must be a string or null.',
        ]
        );

        $steps = $request->input('input.steps');

        $result = $this->validateApprovalFlow($steps);

        return response()->json([
            'success' => true,
            'data' => [
                'valid' => $result['valid']
            ],
            'error' => $result['error']
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    private function validateApprovalFlow(array $steps): array
    {
        $stepMap = [];
        $graph = [];
        $in = [];

        foreach ($steps as $step) {
            $id = $step['id'];
            $dependsOn = $step['depends_on'] ?? null;

            $stepMap[$id] = true;
            $graph[$id] = $dependsOn;

            if (!isset($in[$id])) {
                $in[$id] = 0;
            }
        }

        foreach ($graph as $id => $dependsOn) {
            if ($dependsOn !== null) {
                if (!isset($stepMap[$dependsOn])) {
                    return [
                        'valid' => false,
                        'error' => "Missing dependency: Step '{$id}' depends on non-existent step '{$dependsOn}'"
                    ];
                }
                $in[$dependsOn] = ($in[$dependsOn] ?? 0) + 1;
            }
        }

        $queue = [];
        foreach ($in as $id => $degree) {
            if ($degree === 0) {
                $queue[] = $id;
            }
        }

        $processed = 0;
        while (!empty($queue)) {
            $current = array_shift($queue);
            $processed++;

            $parent = $graph[$current] ?? null;
            if ($parent !== null) {
                $in[$parent]--;
                if ($in[$parent] === 0) {
                    $queue[] = $parent;
                }
            }
        }

        if ($processed !== count($steps)) {
            return [
                'valid' => false,
                'error' => 'Disconnected'
            ];
        }

        return [
            'valid' => true,
            'error' => null
        ];
    }

    public function exercise7(Request $request)
    {
        try {
            $request->validate([
                'input.stock'     => 'required|integer|min:0',
                'input.requests'  => 'required|array|min:1',
                'input.requests.*' => 'integer',
            ], [
                'input.stock.required'    => 'Stock quantity is required.',
                'input.stock.integer'     => 'Stock must be a valid integer.',
                'input.stock.min'         => 'Stock cannot be negative.',
                'input.requests.required' => 'Requests array is required.',
                'input.requests.array'    => 'Requests must be an array.',
                'input.requests.min'      => 'At least one reservation request is required.',
            ]);

            $input = $request->input('input');
            $stock = (int) $input['stock'];
            $requests = $input['requests'];

            $remainingStock = $stock;
            $results = [];

            foreach ($requests as $req) {
                $amount = (int) $req;

                if ($amount > 0 && $amount <= $remainingStock) {
                    $results[] = true;
                    $remainingStock -= $amount;
                } else {
                    $results[] = false;
                }
            }

            return response()->json([
                'success' => true,
                'data'    => $results,
                'error'   => null
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'error'   => 'Validation failed',
                'messages' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'error'   => 'An unexpected error occurred.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function exercise8(Request $request)
    {
        try
        {
            $validated = $request->validate([
                'input.ordered' => 'required|integer|min:1',
                'input.shipped' => 'required|array|min:1',
                'input.shipped.*' => 'integer|min:0'
            ], [
                'input.ordered.required' => 'Ordered quantity is required.',
                'input.ordered.integer' => 'Ordered quantity must be an integer.',
                'input.ordered.min' => 'Ordered quantity must be at least 1.',
                'input.shipped.required' => 'Shipped array is required.',
                'input.shipped.array' => 'Shipped must be an array.',
                'input.shipped.min' => 'At least one shipped entry is required.',
                'input.shipped.*.integer' => 'Each shipped quantity must be an integer.',
                'input.shipped.*.min' => 'Shipped quantities cannot be negative.'
            ]);

            $ordered = $validated['input']['ordered'];
            $shipped = $validated['input']['shipped'];

            $shippedArray = array_sum($shipped);
            $totalShipped = $ordered - $shippedArray;
            $remaining = max(0, $totalShipped);

            return response()->json([
                'success' => true,
                'data'    => ['remaining' => $remaining],
                'error'   => null
            ], 200);


        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'error'   => 'Validation failed',
                'messages' => $e->errors()
            ], 422);

        }
         catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'error'   => 'An unexpected error occurred.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
        
    
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ['addresses' => $addresses],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateAddress($request);

        $address = DB::transaction(function () use ($request, $validated) {
            $address = $request->user()->addresses()->create($validated);
            $this->normalizeDefault($request, $address);

            return $address;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Address added',
            'data' => ['address' => $address->refresh()],
        ], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $validated = $this->validateAddress($request);

        DB::transaction(function () use ($request, $address, $validated) {
            $address->update($validated);
            $this->normalizeDefault($request, $address);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Address updated',
            'data' => ['address' => $address->refresh()],
        ]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $address->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Address deleted',
        ]);
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label'          => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:30'],
            'address'        => ['required', 'string', 'max:500'],
            'city'           => ['required', 'string', 'max:100'],
            'province'       => ['required', 'string', 'max:100'],
            'postal_code'    => ['required', 'string', 'max:20'],
            'is_default'     => ['nullable', 'boolean'],
        ]);
    }

    // Ensure only one default address exists per user.
    private function normalizeDefault(Request $request, Address $address): void
    {
        if ($request->boolean('is_default')) {
            $request->user()->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);
        }
    }
}

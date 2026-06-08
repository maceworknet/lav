<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    /**
     * Resolve current customer ID or guest token.
     */
    private function getIdentity()
    {
        $guestToken = session('guest_token');
        $customerId = auth('customer')->id();

        if (!$guestToken && !$customerId) {
            $guestToken = 'cart_' . bin2hex(random_bytes(16)); // Use same key as Cart
            session(['guest_token' => $guestToken]);
        }

        return [$guestToken, $customerId];
    }

    /**
     * Show favorites list page.
     */
    public function index()
    {
        [$guestToken, $customerId] = $this->getIdentity();

        $query = Favorite::with(['product.images', 'product.favorites']);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } else {
            $query->where('guest_token', $guestToken);
        }

        $favorites = $query->latest()->get();

        return view('frontend.pages.favorites', compact('favorites'));
    }

    /**
     * Toggle favorite status (AJAX).
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = (int)$request->input('product_id');
        [$guestToken, $customerId] = $this->getIdentity();

        try {
            $query = Favorite::where('product_id', $productId);

            if ($customerId) {
                $query->where('customer_id', $customerId);
            } else {
                $query->where('guest_token', $guestToken);
            }

            $favorite = $query->first();

            if ($favorite) {
                $favorite->delete();
                $status = 'removed';
                $message = 'Ürün favorilerinizden kaldırıldı.';
            } else {
                Favorite::create([
                    'product_id' => $productId,
                    'customer_id' => $customerId,
                    'guest_token' => $customerId ? null : $guestToken,
                ]);
                $status = 'added';
                $message = 'Ürün favorilerinize eklendi.';
            }

            // Get updated count
            $countQuery = Favorite::query();
            if ($customerId) {
                $countQuery->where('customer_id', $customerId);
            } else {
                $countQuery->where('guest_token', $guestToken);
            }
            $count = $countQuery->count();

            return response()->json([
                'success' => true,
                'status' => $status,
                'count' => $count,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error('Favorites toggle error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }
}

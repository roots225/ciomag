<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $search = trim((string) $request->query('q', ''));
        $perPage = min(50, max(1, (int) $request->query('per_page', 9)));

        $featuredProducts = Product::query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->orderByDesc('publication_date')
            ->limit(2)
            ->get();

        $products = Product::query()
            ->where('status', 'published')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhere('issue_number', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('publication_date')
            ->paginate($perPage)
            ->withQueryString();

        return view('magazine.shop.index', compact('products', 'featuredProducts', 'type', 'search'));
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->where('status', 'published')
            ->where('id', '!=', $product->id)
            ->orderByDesc('publication_date')
            ->limit(3)
            ->get();

        return view('magazine.shop.show', compact('product', 'relatedProducts'));
    }

    public function checkout(Request $request, string $slug): RedirectResponse
    {
        $product = Product::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:60'],
            'customer_company' => ['nullable', 'string', 'max:150'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'payment_method' => ['required', 'in:card,mobile_money,bank_transfer'],
        ]);

        $qty = (int) $validated['quantity'];
        $total = round(((float) $product->price) * $qty, 2);
        $orderNumber = 'CMD-'.now()->format('Y').'-'.strtoupper(Str::random(5));

        Order::create([
            'order_number' => $orderNumber,
            'user_id' => $request->user()?->id,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_company' => $validated['customer_company'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'total_amount' => $total,
            'currency' => $product->currency,
            'payment_method' => $validated['payment_method'],
            'status' => 'paid',
            'items' => [
                [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'type' => $product->type,
                    'issue_number' => $product->issue_number,
                    'quantity' => $qty,
                    'unit_price' => (float) $product->price,
                ],
            ],
        ]);

        if ($product->stock > 0) {
            $product->decrement('stock', min($product->stock, $qty));
        }

        return redirect()->route('magazine.shop.show', $product->slug)
            ->with('success', "Commande {$orderNumber} confirmée avec succès ! Un exemplaire / reçu a été envoyé à {$validated['customer_email']}.");
    }
}

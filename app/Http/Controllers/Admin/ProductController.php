<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $products = Product::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $ordersCount = Order::count();

        return view('admin.shop.index', compact('products', 'type', 'ordersCount'));
    }

    public function orders(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));
        $perPage = min(50, max(1, (int) $request->query('per_page', 12)));

        $orders = Order::query()
            ->with('user')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_company', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => Order::count(),
            'paid' => Order::whereIn('status', ['paid', 'shipped', 'completed'])->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'revenue' => (float) Order::whereIn('status', ['paid', 'shipped', 'completed'])->sum('total_amount'),
        ];

        return view('admin.shop.orders', compact('orders', 'status', 'search', 'stats'));
    }

    public function create(): View
    {
        $product = new Product([
            'type' => 'magazine',
            'status' => 'published',
            'currency' => 'EUR',
            'price' => 19.00,
            'stock' => 100,
            'is_digital' => true,
            'is_featured' => false,
            'publication_date' => now(),
        ]);

        return view('admin.shop.form', compact('product'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        Product::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'subtitle' => $validated['subtitle'] ?? null,
            'type' => $validated['type'],
            'issue_number' => $validated['issue_number'] ?? null,
            'author_name' => $validated['author_name'] ?? 'Rédaction CIOMAG',
            'excerpt' => $validated['excerpt'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'currency' => $validated['currency'] ?? 'EUR',
            'stock' => (int) ($validated['stock'] ?? 100),
            'is_digital' => $request->boolean('is_digital'),
            'digital_file_url' => $validated['digital_file_url'] ?? null,
            'cover_url' => $validated['cover_url'] ?? null,
            'pages_count' => $validated['pages_count'] ?? null,
            'publication_date' => $validated['publication_date'] ?? now(),
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'seo_meta' => [],
        ]);

        return redirect()->route('admin.shop.index')
            ->with('success', 'Produit ajouté à la boutique avec succès.');
    }

    public function edit(Product $product): View
    {
        return view('admin.shop.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $product->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'subtitle' => $validated['subtitle'] ?? null,
            'type' => $validated['type'],
            'issue_number' => $validated['issue_number'] ?? null,
            'author_name' => $validated['author_name'] ?? 'Rédaction CIOMAG',
            'excerpt' => $validated['excerpt'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'currency' => $validated['currency'] ?? 'EUR',
            'stock' => (int) ($validated['stock'] ?? 100),
            'is_digital' => $request->boolean('is_digital'),
            'digital_file_url' => $validated['digital_file_url'] ?? null,
            'cover_url' => $validated['cover_url'] ?? null,
            'pages_count' => $validated['pages_count'] ?? null,
            'publication_date' => $validated['publication_date'] ?? $product->publication_date,
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.shop.index')
            ->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.shop.index')
            ->with('success', 'Produit supprimé de la boutique.');
    }

    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,shipped,completed,cancelled'],
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', "Statut de la commande {$order->order_number} mis à jour.");
    }

    public function destroyOrder(Order $order): RedirectResponse
    {
        $orderNumber = $order->order_number;
        $order->delete();

        return back()->with('success', "La commande {$orderNumber} a été supprimée.");
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:magazine,book,report,bundle'],
            'issue_number' => ['nullable', 'string', 'max:50'],
            'author_name' => ['nullable', 'string', 'max:150'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'digital_file_url' => ['nullable', 'string', 'max:1000'],
            'cover_url' => ['nullable', 'string', 'max:1000'],
            'pages_count' => ['nullable', 'integer', 'min:1'],
            'publication_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);
    }
}

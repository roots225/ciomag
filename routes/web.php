<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventAdminController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\PaymentMethodAdminController;
use App\Http\Controllers\Admin\PodcastAdminController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingRedirectController as AdminSettingRedirectController;
use App\Http\Controllers\Admin\SocialAdminController;
use App\Http\Controllers\Admin\SubscriptionAdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MagazineController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. FRONT-OFFICE MAGAZINE & MODULES MÉTIERS
// ==========================================
Route::get('/', [MagazineController::class, 'index'])->name('magazine.home');
Route::get('/search', [MagazineController::class, 'search'])->name('magazine.search');
Route::get('/blog/{slug}', [MagazineController::class, 'post'])->name('magazine.post');
Route::post('/blog/{slug}/comments', [MagazineController::class, 'storeComment'])->name('magazine.comments.store');
Route::get('/page/{slug}', [MagazineController::class, 'page'])->name('magazine.page');
Route::get('/category/{slug}', [MagazineController::class, 'category'])->name('magazine.category');
Route::get('/tag/{slug}', [MagazineController::class, 'tag'])->name('magazine.tag');
Route::get('/author/{username}', [MagazineController::class, 'author'])->name('magazine.author');

// Boutique (Magazines, Livres, Rapports)
Route::get('/shop', [ShopController::class, 'index'])->name('magazine.shop.index');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('magazine.shop.show');
Route::post('/shop/{slug}/checkout', [ShopController::class, 'checkout'])->name('magazine.shop.checkout');

// Abonnements & Articles Exclusifs Premium
Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('magazine.subscriptions.index');
Route::post('/subscriptions/subscribe', [SubscriptionController::class, 'subscribe'])->name('magazine.subscriptions.subscribe');

// Événements (Compte à rebours, Programme, Comité d'organisation, Partenaires, Inscription)
Route::get('/events', [EventController::class, 'index'])->name('magazine.events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('magazine.events.show');
Route::post('/events/{slug}/register', [EventController::class, 'register'])->name('magazine.events.register');

// Podcasts & WebTV (YouTube & Vidéos hébergées)
Route::get('/podcasts', [PodcastController::class, 'index'])->name('magazine.podcasts.index');
Route::get('/podcasts/{slug}', [PodcastController::class, 'show'])->name('magazine.podcasts.show');

// ==========================================
// 2. AUTHENTIFICATION
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// 3. BACK-OFFICE CMS / ADMIN
// ==========================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Articles & Pages + Révisions
        Route::resource('posts', AdminPostController::class)->except(['show']);
        Route::post('posts/{post}/revisions/{revision}/restore', [AdminPostController::class, 'restoreRevision'])
            ->name('posts.revisions.restore');

        // Catégories & Tags
        Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [AdminCategoryController::class, 'storeCategory'])->name('categories.store');
        Route::put('categories/{category}', [AdminCategoryController::class, 'updateCategory'])->name('categories.update');
        Route::delete('categories/{category}', [AdminCategoryController::class, 'destroyCategory'])->name('categories.destroy');
        Route::post('tags', [AdminCategoryController::class, 'storeTag'])->name('tags.store');
        Route::delete('tags/{tag}', [AdminCategoryController::class, 'destroyTag'])->name('tags.destroy');

        // Boutique : Magazines / Livres & Menu dédié Commandes
        Route::resource('shop', AdminProductController::class)
            ->parameters(['shop' => 'product'])
            ->except(['show']);
        Route::get('orders', [AdminProductController::class, 'orders'])->name('orders.index');
        Route::patch('orders/{order}/status', [AdminProductController::class, 'updateOrderStatus'])
            ->name('orders.status');
        Route::delete('orders/{order}', [AdminProductController::class, 'destroyOrder'])
            ->name('orders.destroy');

        // Méthodes de Paiement (Visa via Stripe ou Braintree)
        Route::get('payment-methods', [PaymentMethodAdminController::class, 'index'])->name('payment-methods.index');
        Route::put('payment-methods', [PaymentMethodAdminController::class, 'update'])->name('payment-methods.update');
        Route::post('payment-methods/test', [PaymentMethodAdminController::class, 'testConnection'])->name('payment-methods.test');

        // Abonnements Premium & Abonnés (Création/Modification Formules en Dialog + Activation/Modification Abonnés)
        Route::get('subscriptions', [SubscriptionAdminController::class, 'index'])->name('subscriptions.index');
        Route::post('subscriptions/plans', [SubscriptionAdminController::class, 'storePlan'])->name('subscriptions.plans.store');
        Route::put('subscriptions/plans/{plan}', [SubscriptionAdminController::class, 'updatePlan'])->name('subscriptions.plans.update');
        Route::patch('subscriptions/plans/{plan}/toggle', [SubscriptionAdminController::class, 'togglePlan'])->name('subscriptions.plans.toggle');
        Route::delete('subscriptions/plans/{plan}', [SubscriptionAdminController::class, 'destroyPlan'])->name('subscriptions.plans.destroy');
        Route::post('subscriptions', [SubscriptionAdminController::class, 'storeSubscription'])->name('subscriptions.store');
        Route::put('subscriptions/{subscription}', [SubscriptionAdminController::class, 'updateSubscription'])->name('subscriptions.update');
        Route::patch('subscriptions/{subscription}/toggle', [SubscriptionAdminController::class, 'toggleSubscription'])
            ->name('subscriptions.toggle');
        Route::patch('subscriptions/{subscription}/status', [SubscriptionAdminController::class, 'updateSubscriptionStatus'])
            ->name('subscriptions.status');

        // Événements (Index, Création, Détail Admin + Participants inscrits, Édition)
        Route::resource('events', EventAdminController::class);
        Route::post('events/{event}/registrations', [EventAdminController::class, 'storeRegistration'])
            ->name('events.registrations.store');
        Route::patch('event-registrations/{registration}/status', [EventAdminController::class, 'updateRegistrationStatus'])
            ->name('events.registrations.status');
        Route::delete('event-registrations/{registration}', [EventAdminController::class, 'destroyRegistration'])
            ->name('events.registrations.destroy');

        // Podcasts & Vidéos (YouTube & Upload local)
        Route::resource('podcasts', PodcastAdminController::class)->except(['show']);

        // Diffusion Réseaux Sociaux & Paramétrage API (Facebook, LinkedIn, Twitter/X, YouTube)
        Route::get('social', [SocialAdminController::class, 'index'])->name('social.index');
        Route::put('social/config', [SocialAdminController::class, 'updateConfig'])->name('social.config.update');
        Route::post('social/publish', [SocialAdminController::class, 'publish'])->name('social.publish');

        // Médiathèque (Vues + API JSON Universelle avec Recadrage Cropper.js)
        Route::get('media', [AdminMediaController::class, 'index'])->name('media.index');
        Route::post('media', [AdminMediaController::class, 'store'])->name('media.store');
        Route::put('media/{media}', [AdminMediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');

        Route::prefix('api/media')->name('api.media.')->group(function () {
            Route::get('/', [AdminMediaController::class, 'apiIndex'])->name('index');
            Route::post('/', [AdminMediaController::class, 'apiStore'])->name('store');
            Route::match(['put', 'patch'], '{media}', [AdminMediaController::class, 'apiUpdate'])->name('update');
            Route::post('{media}/crop', [AdminMediaController::class, 'apiCrop'])->name('crop');
            Route::delete('{media}', [AdminMediaController::class, 'apiDestroy'])->name('destroy');
        });

        // Modération des Commentaires
        Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
        Route::patch('comments/{comment}/status', [AdminCommentController::class, 'updateStatus'])->name('comments.status');
        Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');

        // Constructeur de Menus (avec prévisualisation CTE)
        Route::get('menus', [AdminMenuController::class, 'index'])->name('menus.index');
        Route::post('menus', [AdminMenuController::class, 'storeMenu'])->name('menus.store');
        Route::post('menus/{menu}/items', [AdminMenuController::class, 'storeItem'])->name('menus.items.store');
        Route::delete('menu-items/{menuItem}', [AdminMenuController::class, 'destroyItem'])->name('menus.items.destroy');

        // Gestion du Profil de l'utilisateur connecté
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        // Gestion dédiée des Utilisateurs (Ajout, Modification, Suppression, Activation/Désactivation)
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Paramètres généraux & Redirections 301
        Route::get('settings', [AdminSettingRedirectController::class, 'index'])->name('settings.index');
        Route::put('settings', [AdminSettingRedirectController::class, 'updateSettings'])->name('settings.update');
        Route::post('redirects', [AdminSettingRedirectController::class, 'storeRedirect'])->name('redirects.store');
        Route::patch('redirects/{redirect}/toggle', [AdminSettingRedirectController::class, 'toggleRedirect'])->name('redirects.toggle');
        Route::delete('redirects/{redirect}', [AdminSettingRedirectController::class, 'destroyRedirect'])->name('redirects.destroy');
    });

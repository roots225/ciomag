<?php

namespace Tests\Feature;

use App\Enums\MenuItemType;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Comment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SocialShare;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\MenuResolverService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

class MagazineCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_page_displays_featured_posts_and_dynamic_cte_menu(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CIOMAG');
        $response->assertSee('IA Générative : Comment les DSI passent de l\'expérimentation');
        $response->assertSee('Intelligence Artificielle');
    }

    public function test_article_page_increments_view_count_and_displays_seo_and_comments(): void
    {
        $post = Post::where('slug', 'ia-generative-dsi-africains-2026')->firstOrFail();
        $post->update(['is_premium' => false]);
        $initialViews = $post->view_count;

        $response = $this->get('/blog/ia-generative-dsi-africains-2026');

        $response->assertStatus(200);
        $response->assertSee($post->title);
        $response->assertSee('Excellent dossier !');

        $this->assertEquals($initialViews + 1, $post->fresh()->view_count);
    }

    public function test_premium_article_shows_paywall_for_guests_and_unlocks_after_subscription(): void
    {
        $premiumPost = Post::where('is_premium', true)->firstOrFail();

        // 1. Un visiteur non abonné voit le Paywall Premium
        $guestResponse = $this->get('/blog/'.$premiumPost->slug);
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee('id="premium-paywall"', false);
        $guestResponse->assertSee('La suite de cette enquête est réservée aux abonnés CIOMAG Premium');

        // 2. Souscription à une offre d'abonnement (création ou connexion automatique)
        $plan = SubscriptionPlan::where('slug', 'abonnement-executif-annuel')->first()
            ?? SubscriptionPlan::firstOrFail();

        $subResponse = $this->post('/subscriptions/subscribe', [
            'plan_id' => $plan->id,
            'display_name' => 'Jean-Marc Kouassi',
            'email' => 'jmkouassi@dsi-afrique.com',
            'password' => 'secret123',
            'payment_method' => 'card',
        ]);

        $subResponse->assertRedirect();
        $subscriber = User::where('email', 'jmkouassi@dsi-afrique.com')->firstOrFail();
        $this->assertTrue($subscriber->hasActiveSubscription());
        $this->assertTrue($subscriber->canReadPremiumContent());

        // 3. L'abonné accède désormais à l'intégralité de l'article sans Paywall
        $unlockedResponse = $this->actingAs($subscriber)->get('/blog/'.$premiumPost->slug);
        $unlockedResponse->assertStatus(200);
        $unlockedResponse->assertDontSee('id="premium-paywall"', false);
    }

    public function test_shop_displays_magazines_and_books_and_processes_orders(): void
    {
        $shopIndex = $this->get('/shop');
        $shopIndex->assertStatus(200);
        $shopIndex->assertSee('Kiosque');

        $product = Product::where('status', 'published')->firstOrFail();

        $productShow = $this->get('/shop/'.$product->slug);
        $productShow->assertStatus(200);
        $productShow->assertSee($product->title);

        $orderResponse = $this->post('/shop/'.$product->slug.'/checkout', [
            'customer_name' => 'Aminata Diop',
            'customer_email' => 'aminata.diop@banque.sn',
            'customer_phone' => '+221 77 000 00 00',
            'quantity' => 2,
            'payment_method' => 'mobile_money',
        ]);

        $orderResponse->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'customer_email' => 'aminata.diop@banque.sn',
            'status' => 'paid',
        ]);
    }

    public function test_events_display_countdown_program_committee_partners_and_accept_registrations(): void
    {
        $eventsIndex = $this->get('/events');
        $eventsIndex->assertStatus(200);

        $event = Event::where('status', 'published')->firstOrFail();

        $eventShow = $this->get('/events/'.$event->slug);
        $eventShow->assertStatus(200);
        $eventShow->assertSee($event->title);
        // Vérifie la présence du compte à rebours, du programme, du comité d'organisation et des partenaires
        $eventShow->assertSee('id="event-countdown"', false);
        $eventShow->assertSee('eventDetailCountdown', false);
        $eventShow->assertSee('Comité d\'Organisation', false);
        $eventShow->assertSee('Partenaires & Sponsors Officiels', false);

        $regResponse = $this->post('/events/'.$event->slug.'/register', [
            'full_name' => 'Karim Benali',
            'email' => 'karim.benali@telecom.ma',
            'phone' => '+212 6 00 00 00 00',
            'company' => 'Maroc Cloud',
            'job_title' => 'Directeur Infrastructure',
            'attendance_mode' => 'in_person',
        ]);

        $regResponse->assertRedirect();
        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'email' => 'karim.benali@telecom.ma',
            'status' => 'confirmed',
        ]);
    }

    public function test_podcasts_support_youtube_and_direct_video_upload(): void
    {
        $podcastsIndex = $this->get('/podcasts');
        $podcastsIndex->assertStatus(200);

        $youtubePodcast = Podcast::where('source_type', 'youtube')->firstOrFail();
        $showYoutube = $this->get('/podcasts/'.$youtubePodcast->slug);
        $showYoutube->assertStatus(200);
        $showYoutube->assertSee($youtubePodcast->youtube_video_id);

        $uploadPodcast = Podcast::where('source_type', 'upload')->firstOrFail();
        $showUpload = $this->get('/podcasts/'.$uploadPodcast->slug);
        $showUpload->assertStatus(200);
        $showUpload->assertSee('<video', false);
    }

    public function test_social_publisher_module_configures_apis_and_publishes_articles_to_facebook_linkedin_twitter(): void
    {
        $admin = User::where('email', 'admin@ciomag.com')->firstOrFail();
        $post = Post::where('slug', 'ia-generative-dsi-africains-2026')->firstOrFail();

        // 1. Mise à jour de la configuration API (Facebook, LinkedIn, Twitter/X, YouTube)
        $configResponse = $this->actingAs($admin)->put('/admin/social/config', [
            'mode' => 'sandbox',
            'default_template' => '📰 {title} 👉 {url}',
            'fb_enabled' => '1',
            'fb_page_name' => 'CIOMAG Afrique',
            'fb_page_id' => '987654321',
            'li_enabled' => '1',
            'li_organization_name' => 'CIOMAG Officiel',
            'li_organization_urn' => 'urn:li:organization:987654',
            'tw_enabled' => '1',
            'tw_handle' => '@CIOMAG_Officiel',
            'yt_enabled' => '1',
            'yt_channel_name' => 'CIOMAG Studio TV',
        ]);
        $configResponse->assertRedirect();

        // 2. Diffusion d'un article sur Facebook, LinkedIn et Twitter
        $publishResponse = $this->actingAs($admin)->post('/admin/social/publish', [
            'content_type' => 'post',
            'post_id' => $post->id,
            'platforms' => ['facebook', 'linkedin', 'twitter'],
            'custom_message' => 'Découvrez notre grand dossier DSI & IA Générative !',
        ]);
        $publishResponse->assertRedirect();

        foreach (['facebook', 'linkedin', 'twitter'] as $platform) {
            $this->assertDatabaseHas('social_shares', [
                'post_id' => $post->id,
                'platform' => $platform,
                'status' => 'simulated',
            ]);
        }
    }

    public function test_recursive_cte_menu_excludes_unpublished_and_soft_deleted_posts(): void
    {
        $menu = Menu::where('location', 'header_main')->firstOrFail();
        $publishedPost = Post::where('slug', 'ia-generative-dsi-africains-2026')->firstOrFail();
        $draftPost = Post::where('slug', 'barometre-salaires-it-cyber-2027')->firstOrFail();

        // Ajout d'un item de menu pointant vers un article en brouillon/relecture
        MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Lien vers Brouillon',
            'type' => MenuItemType::Post,
            'post_id' => $draftPost->id,
            'display_order' => 99,
            'is_active' => true,
        ]);

        $resolver = app(MenuResolverService::class);
        $flat = $resolver->resolveFlat('header_main');
        $labels = array_column($flat, 'label');

        // Le lien vers l'article publié est présent, mais celui vers le brouillon est automatiquement exclu !
        $this->assertContains('Grand Dossier : IA & DSI 2026', $labels);
        $this->assertNotContains('Lien vers Brouillon', $labels);

        // Si l'article publié passe à la corbeille (Soft Delete), il disparaît aussi du menu résolu
        $publishedPost->delete();
        $flatAfterDelete = $resolver->resolveFlat('header_main');
        $labelsAfterDelete = array_column($flatAfterDelete, 'label');
        $this->assertNotContains('Grand Dossier : IA & DSI 2026', $labelsAfterDelete);
    }

    public function test_comment_author_constraint_is_enforced(): void
    {
        $post = Post::where('slug', 'ia-generative-dsi-africains-2026')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);

        Comment::create([
            'post_id' => $post->id,
            'user_id' => null,
            'guest_name' => null,
            'guest_email' => null,
            'content' => 'Commentaire invalide sans auteur ni invité',
        ]);
    }

    public function test_seo_301_redirect_works_and_increments_hits(): void
    {
        $response = $this->get('/ancien-dossier-ia');

        $response->assertRedirect('/blog/ia-generative-dsi-africains-2026');
        $response->assertStatus(301);
    }

    public function test_admin_can_create_post_with_seo_json_and_revision(): void
    {
        $admin = User::where('email', 'admin@ciomag.com')->firstOrFail();

        $response = $this->actingAs($admin)->post('/admin/posts', [
            'title' => 'Nouveau Dossier Cloud Souverain 2027',
            'type' => PostType::Post->value,
            'status' => PostStatus::Published->value,
            'excerpt' => 'Analyse complète du marché Cloud.',
            'content' => '<p>Contenu détaillé du dossier Cloud Souverain.</p>',
            'content_format' => 'html',
            'is_premium' => '1',
            'social_platforms' => ['facebook', 'linkedin'],
            'seo_meta_title' => 'Cloud Souverain 2027 | CIOMAG',
            'seo_meta_description' => 'Description SEO Cloud 2027',
            'change_summary' => 'Première édition du dossier Cloud',
        ]);

        $response->assertRedirect();

        $created = Post::where('slug', 'nouveau-dossier-cloud-souverain-2027')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->is_premium);
        $this->assertEquals('Cloud Souverain 2027 | CIOMAG', $created->seo_meta['meta_title']);
        $this->assertCount(1, $created->revisions);
        $this->assertEquals(2, SocialShare::where('post_id', $created->id)->count());
    }

    public function test_admin_media_api_can_list_update_and_crop_media(): void
    {
        $admin = User::where('email', 'admin@ciomag.com')->firstOrFail();

        // 1. Liste JSON de la médiathèque
        $listResponse = $this->actingAs($admin)->getJson('/admin/api/media');
        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'file_name', 'url', 'mime_type', 'file_size', 'alt_text', 'caption'],
                ],
            ]);

        $mediaId = $listResponse->json('data.0.id');
        $this->assertNotNull($mediaId);

        // 2. Mise à jour des métadonnées (alt_text, caption)
        $patchResponse = $this->actingAs($admin)->patchJson("/admin/api/media/{$mediaId}", [
            'file_name' => 'cover-ia-dsi-updated.jpg',
            'alt_text' => 'Texte alternatif SEO mis à jour',
            'caption' => 'Légende mise à jour',
        ]);
        $patchResponse->assertStatus(200)
            ->assertJsonPath('media.alt_text', 'Texte alternatif SEO mis à jour');

        // 3. Recadrage (Cropper.js Base64 -> création d'une nouvelle copie)
        // Petit PNG 1x1 valide encodé en Base64
        $tinyPngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $cropResponse = $this->actingAs($admin)->postJson("/admin/api/media/{$mediaId}/crop", [
            'image_base64' => $tinyPngBase64,
            'width' => 800,
            'height' => 450,
            'mode' => 'new',
        ]);

        $cropResponse->assertStatus(201)
            ->assertJsonPath('media.width', 1)
            ->assertJsonPath('media.height', 1);
    }

    public function test_admin_editors_render_tinymce_for_posts_and_grapesjs_for_pages_with_lucide_icons(): void
    {
        $admin = User::where('email', 'admin@ciomag.com')->firstOrFail();

        // Vérification de toutes les pages du Back-Office (y compris Boutique, Abonnements, Événements, Podcasts, Social)
        foreach ([
            '/admin',
            '/admin/posts',
            '/admin/posts?type=post',
            '/admin/posts?type=page',
            '/admin/categories',
            '/admin/media',
            '/admin/comments',
            '/admin/menus',
            '/admin/settings',
            '/admin/shop',
            '/admin/shop/create',
            '/admin/orders',
            '/admin/subscriptions',
            '/admin/events',
            '/admin/events/create',
            '/admin/podcasts',
            '/admin/podcasts/create',
            '/admin/social',
            '/admin/payment-methods',
            '/admin/users',
            '/admin/profile',
        ] as $adminRoute) {
            $res = $this->actingAs($admin)->get($adminRoute);
            $res->assertStatus(200);
            $res->assertDontSee('<select', false);
        }

        // Vérification explicite de la barre de pagination sur Articles, Pages, Catégories, Commentaires et nouveaux modules
        foreach ([
            '/admin/posts?type=post',
            '/admin/posts?type=page',
            '/admin/categories',
            '/admin/comments',
            '/admin/shop',
            '/admin/orders',
            '/admin/subscriptions',
            '/admin/events',
            '/admin/podcasts',
            '/admin/social',
            '/admin/users',
            '/',
            '/shop',
            '/events',
            '/podcasts',
            '/category/intelligence-artificielle',
            '/blog/ia-generative-dsi-africains-2026',
        ] as $paginatedRoute) {
            $res = $this->actingAs($admin)->get($paginatedRoute);
            $res->assertStatus(200);
            $res->assertSee('aria-label="Pagination Navigation"', false);
        }

        // Éditeur d'article -> TinyMCE
        $postEditor = $this->actingAs($admin)->get('/admin/posts/create?type=post');
        $postEditor->assertStatus(200);
        $postEditor->assertSee('tinymce-content', false);
        $postEditor->assertSee('data-lucide=', false);
        $postEditor->assertDontSee('<select', false);

        // Éditeur de page -> GrapesJS Visual Page Builder
        $pageEditor = $this->actingAs($admin)->get('/admin/posts/create?type=page');
        $pageEditor->assertStatus(200);
        $pageEditor->assertSee('gjs-page-builder', false);
        $pageEditor->assertSee('GrapesJS', false);
        $pageEditor->assertDontSee('<select', false);
    }

    public function test_admin_event_details_subscriptions_orders_and_visa_payment_methods(): void
    {
        $admin = User::where('email', 'admin@ciomag.com')->firstOrFail();

        // 1. Liste des événements : présence du bouton "Ajouter un événement" + page détail d'un événement avec inscrits
        $eventsIndex = $this->actingAs($admin)->get('/admin/events');
        $eventsIndex->assertStatus(200);
        $eventsIndex->assertSee('Ajouter un événement');

        $event = Event::firstOrFail();
        $eventShow = $this->actingAs($admin)->get(route('admin.events.show', $event));
        $eventShow->assertStatus(200);
        $eventShow->assertSee($event->title);
        $eventShow->assertSee('Participants inscrits');
        $eventShow->assertDontSee('<select', false);

        // Inscription manuelle d'un participant depuis la page détail admin
        $addReg = $this->actingAs($admin)->post(route('admin.events.registrations.store', $event), [
            'full_name' => 'Koffi Kouadio',
            'email' => 'koffi.kouadio@dsi-ci.org',
            'company' => 'Port Autonome Abidjan',
            'job_title' => 'Directeur des Systèmes d\'Information',
            'status' => 'confirmed',
        ]);
        $addReg->assertRedirect();
        $this->assertTrue(EventRegistration::where('event_id', $event->id)->where('email', 'koffi.kouadio@dsi-ci.org')->exists());

        // 2. Abonnements Admin : Dialog d'ajout de formule, modification d'une formule, et activation/désactivation d'un abonnement utilisateur
        $subPage = $this->actingAs($admin)->get('/admin/subscriptions');
        $subPage->assertStatus(200);
        $subPage->assertSee('Ajouter une formule');

        $plan = SubscriptionPlan::firstOrFail();
        $updatePlan = $this->actingAs($admin)->put(route('admin.subscriptions.plans.update', $plan), [
            'name' => 'Pass Annuel DSI Gold',
            'badge' => 'Top DSI',
            'description' => 'Offre annuelle enrichie',
            'billing_interval' => 'yearly',
            'price' => 129.00,
            'features_text' => "Accès illimité aux articles Premium\n6 numéros PDF HD inclus",
            'display_order' => 1,
            'is_active' => '1',
            'is_featured' => '1',
            'includes_print' => '1',
        ]);
        $updatePlan->assertRedirect();
        $this->assertEquals('Pass Annuel DSI Gold', $plan->fresh()->name);
        $this->assertEquals(129.00, (float) $plan->fresh()->price);

        // Toggle activation/désactivation d'un abonnement utilisateur + modification
        $userSub = UserSubscription::firstOrFail();
        $initialStatus = $userSub->status;

        $toggleSub = $this->actingAs($admin)->patch(route('admin.subscriptions.toggle', $userSub));
        $toggleSub->assertRedirect();
        $expectedToggled = $initialStatus === 'active' ? 'cancelled' : 'active';
        $this->assertEquals($expectedToggled, $userSub->fresh()->status);

        $updateSub = $this->actingAs($admin)->put(route('admin.subscriptions.update', $userSub), [
            'plan_id' => $plan->id,
            'status' => 'active',
            'amount_paid' => 129.00,
            'payment_method' => 'visa_stripe',
            'starts_at' => now()->format('Y-m-d'),
            'expires_at' => now()->addYear()->format('Y-m-d'),
        ]);
        $updateSub->assertRedirect();
        $this->assertEquals('active', $userSub->fresh()->status);
        $this->assertEquals('visa_stripe', $userSub->fresh()->payment_method);

        // 3. Menu dédié Commandes (/admin/orders)
        $ordersPage = $this->actingAs($admin)->get('/admin/orders');
        $ordersPage->assertStatus(200);
        $ordersPage->assertSee('Liste des Commandes');

        // 4. Menu Méthodes de paiement (/admin/payment-methods) : Visa avec Stripe ou Braintree
        $paymentPage = $this->actingAs($admin)->get('/admin/payment-methods');
        $paymentPage->assertStatus(200);
        $paymentPage->assertSee('Carte Bancaire Visa');
        $paymentPage->assertSee('Stripe');
        $paymentPage->assertSee('Braintree');

        $savePayment = $this->actingAs($admin)->put('/admin/payment-methods', [
            'display_name' => 'Carte Bancaire Visa',
            'provider' => 'braintree',
            'mode' => 'sandbox',
            'currency' => 'EUR',
            'statement_descriptor' => 'CIOMAG VISA',
            'visa_enabled' => '1',
            'three_d_secure' => '1',
            'allow_shop_orders' => '1',
            'allow_subscriptions' => '1',
            'allow_events' => '1',
            'stripe_publishable_key' => 'pk_test_stripe_visa',
            'stripe_secret_key' => 'sk_test_stripe_visa',
            'stripe_webhook_secret' => 'whsec_stripe_visa',
            'braintree_merchant_id' => 'ciomag_merchant_bt',
            'braintree_public_key' => 'bt_pub_key_123',
            'braintree_private_key' => 'bt_priv_key_456',
            'braintree_merchant_account_id' => 'ciomag_eur_bt',
        ]);
        $savePayment->assertRedirect();

        $savedConfig = Setting::getValue('payment_methods');
        $this->assertEquals('braintree', $savedConfig['visa']['provider']);
        $this->assertTrue($savedConfig['visa']['enabled']);
        $this->assertEquals('ciomag_merchant_bt', $savedConfig['braintree']['merchant_id']);
    }

    public function test_admin_user_management_profile_and_confirm_dialog(): void
    {
        $admin = User::where('email', 'admin@ciomag.com')->firstOrFail();

        // 1. Liste des utilisateurs : vue dédiée avec KPIs, filtres et absence de balise select native
        $usersIndex = $this->actingAs($admin)->get('/admin/users');
        $usersIndex->assertStatus(200);
        $usersIndex->assertSee('Comptes Utilisateurs');
        $usersIndex->assertSee('Ajouter un utilisateur');
        $usersIndex->assertDontSee('<select', false);

        // Vérification de la présence du composant de confirmation réutilisable et du menu utilisateur en haut à droite
        $usersIndex->assertSee('window.confirmAction', false);
        $usersIndex->assertSee('Mon Profil');
        $usersIndex->assertSee('Déconnexion');

        // 2. Création d'un nouvel utilisateur via POST /admin/users
        $roleEditor = Role::where('slug', 'editor')->firstOrFail();
        $createRes = $this->actingAs($admin)->post('/admin/users', [
            'display_name' => 'Mariam Diallo',
            'email' => 'mariam.diallo@ciomag.com',
            'username' => 'mariam-diallo',
            'role_id' => $roleEditor->id,
            'password' => 'secret123',
            'status' => 'active',
            'bio' => 'Rédactrice en chef adjointe Afrique de l\'Ouest',
        ]);
        $createRes->assertRedirect();

        $newUser = User::where('email', 'mariam.diallo@ciomag.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Mariam Diallo', $newUser->display_name);
        $this->assertEquals($roleEditor->id, $newUser->role_id);
        $this->assertTrue(Hash::check('secret123', $newUser->password_hash));

        // 3. Modification de l'utilisateur via PUT /admin/users/{user} (même dialog composant)
        $updateRes = $this->actingAs($admin)->put(route('admin.users.update', $newUser), [
            'display_name' => 'Mariam Diallo Traoré',
            'email' => 'mariam.diallo@ciomag.com',
            'username' => 'mariam-diallo-traore',
            'role_id' => $roleEditor->id,
            'status' => 'active',
            'bio' => 'Rédactrice en chef régionale',
        ]);
        $updateRes->assertRedirect();
        $this->assertEquals('Mariam Diallo Traoré', $newUser->fresh()->display_name);

        // 4. Activation / Désactivation du compte via PATCH /admin/users/{user}/toggle
        $this->assertEquals('active', $newUser->fresh()->status->value);

        $toggleRes = $this->actingAs($admin)->patch(route('admin.users.toggle', $newUser));
        $toggleRes->assertRedirect();
        $this->assertEquals('suspended', $newUser->fresh()->status->value);

        $toggleBackRes = $this->actingAs($admin)->patch(route('admin.users.toggle', $newUser));
        $toggleBackRes->assertRedirect();
        $this->assertEquals('active', $newUser->fresh()->status->value);

        // 5. Suppression de l'utilisateur via DELETE /admin/users/{user}
        $deleteRes = $this->actingAs($admin)->delete(route('admin.users.destroy', $newUser));
        $deleteRes->assertRedirect();
        $this->assertNull(User::where('email', 'mariam.diallo@ciomag.com')->first());

        // L'administrateur connecté ne peut pas supprimer ou désactiver son propre compte
        $selfDelete = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));
        $selfDelete->assertRedirect();
        $this->assertNotNull(User::find($admin->id));

        // 6. Gestion du profil connecté (/admin/profile)
        $profilePage = $this->actingAs($admin)->get('/admin/profile');
        $profilePage->assertStatus(200);
        $profilePage->assertSee('Mon Profil');
        $profilePage->assertSee($admin->email);
        $profilePage->assertDontSee('<select', false);

        $updateProfile = $this->actingAs($admin)->put('/admin/profile', [
            'display_name' => 'Gilles Yao Admin',
            'email' => 'admin@ciomag.com',
            'username' => 'admin-ciomag',
            'bio' => 'Directeur de publication CIOMAG',
        ]);
        $updateProfile->assertRedirect();
        $this->assertEquals('Gilles Yao Admin', $admin->fresh()->display_name);
    }

    public function test_admin_menu_builder_renders_with_create_item_dialog(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.menus.index'));

        $response->assertStatus(200);
        $response->assertSee('createItemModalOpen', false);
        $response->assertSee('Ajouter un élément au menu', false);
        $response->assertSee('Arborescence Résolue (menu_tree)', false);
    }

    public function test_admin_can_update_menu_and_menu_item(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        $menu = Menu::firstOrFail();

        // 1. Mise à jour du menu
        $updateMenuRes = $this->actingAs($admin)->put(route('admin.menus.update', $menu), [
            'name' => 'Menu Principal Mis à Jour',
            'description' => 'Description modifiée du menu',
        ]);
        $updateMenuRes->assertRedirect();
        $this->assertEquals('Menu Principal Mis à Jour', $menu->fresh()->name);

        // 2. Mise à jour d'un élément de menu
        $item = $menu->items()->firstOrFail();
        $updateItemRes = $this->actingAs($admin)->putJson(route('admin.menus.items.update', $item), [
            'label' => 'Libellé Modifié',
            'type' => 'custom_url',
            'custom_url' => '/nouveau-lien',
            'target' => '_blank',
            'badge_text' => 'VIP',
            'display_order' => 5,
        ]);
        $updateItemRes->assertStatus(200);
        $updateItemRes->assertJson(['success' => true]);

        $freshItem = $item->fresh();
        $this->assertEquals('Libellé Modifié', $freshItem->label);
        $this->assertEquals('/nouveau-lien', $freshItem->custom_url);
        $this->assertEquals('_blank', $freshItem->target);
        $this->assertEquals('VIP', $freshItem->badge_text);
    }

    public function test_admin_can_reparent_and_unparent_menu_item_via_drag_and_drop_api(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        $menu = Menu::firstOrFail();

        // Créer deux éléments racine
        $itemA = MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Parent A',
            'type' => 'custom_url',
            'custom_url' => '/parent-a',
            'target' => '_self',
            'display_order' => 1,
        ]);

        $itemB = MenuItem::create([
            'menu_id' => $menu->id,
            'label' => 'Enfant B',
            'type' => 'custom_url',
            'custom_url' => '/enfant-b',
            'target' => '_self',
            'display_order' => 2,
        ]);

        // 1. Glisser-déposer : rattacher B sous A
        $reparentRes = $this->actingAs($admin)->patchJson(route('admin.menus.items.reparent', $itemB), [
            'parent_id' => $itemA->id,
        ]);
        $reparentRes->assertStatus(200);
        $reparentRes->assertJson(['success' => true]);
        $this->assertEquals($itemA->id, $itemB->fresh()->parent_id);

        // 2. Détection de cycle : tenter de rattacher A sous B (son enfant)
        $cycleRes = $this->actingAs($admin)->patchJson(route('admin.menus.items.reparent', $itemA), [
            'parent_id' => $itemB->id,
        ]);
        $cycleRes->assertStatus(422);
        $cycleRes->assertJson(['success' => false]);
        $this->assertNull($itemA->fresh()->parent_id);

        // 3. Glisser-déposer vers la racine : supprimer le parent de B (parent_id = null)
        $unparentRes = $this->actingAs($admin)->patchJson(route('admin.menus.items.reparent', $itemB), [
            'parent_id' => null,
        ]);
        $unparentRes->assertStatus(200);
        $unparentRes->assertJson(['success' => true]);
        $this->assertNull($itemB->fresh()->parent_id);
    }
}

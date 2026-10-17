<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // 1. ARTICLES EXCLUSIFS PREMIUM (SUR POSTS)
        // ==========================================
        if (! Schema::hasColumn('posts', 'is_premium')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->boolean('is_premium')->default(false)->after('is_featured');
                $table->index(['is_premium', 'status']);
            });
        }

        // ==========================================
        // 2. SYSTÈME D'ABONNEMENTS PREMIUM
        // ==========================================
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('badge', 60)->nullable();
            $table->text('description')->nullable();
            $table->enum('billing_interval', ['monthly', 'yearly', 'lifetime'])->default('monthly');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 10)->default('EUR');
            $table->json('features');
            $table->boolean('includes_print')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->enum('status', ['active', 'cancelled', 'expired', 'pending'])->default('active');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('currency', 10)->default('EUR');
            $table->string('payment_method', 50)->default('card');
            $table->string('payment_reference', 120)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'expires_at']);
        });

        // ==========================================
        // 3. BOUTIQUE (MAGAZINES, LIVRES, RAPPORTS)
        // ==========================================
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('subtitle', 255)->nullable();
            $table->enum('type', ['magazine', 'book', 'report', 'bundle'])->default('magazine');
            $table->string('issue_number', 50)->nullable(); // ex: "N° 112 - Sept/Oct 2026"
            $table->string('author_name', 150)->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('currency', 10)->default('EUR');
            $table->integer('stock')->default(100);
            $table->boolean('is_digital')->default(true);
            $table->string('digital_file_url', 1000)->nullable();
            $table->string('cover_url', 1000)->nullable();
            $table->integer('pages_count')->nullable();
            $table->date('publication_date')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->boolean('is_featured')->default(false);
            $table->json('seo_meta');
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number', 50)->unique();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name', 150);
            $table->string('customer_email', 255);
            $table->string('customer_phone', 60)->nullable();
            $table->string('customer_company', 150)->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 10)->default('EUR');
            $table->string('payment_method', 50)->default('card');
            $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled'])->default('paid');
            $table->json('items'); // Détail des produits commandés (product_id, title, qty, unit_price, type)
            $table->timestamps();
        });

        // ==========================================
        // 4. GESTION D'ÉVÉNEMENTS (PROGRAMME, COMITÉ, PARTENAIRES, INSCRIPTIONS)
        // ==========================================
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('subtitle', 255)->nullable();
            $table->string('event_type', 80)->default('Conférence & Forum'); // Forum, Table Ronde, Webinaire, Sommet
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->string('venue', 255)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('country', 120)->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('online_url', 500)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('cover_url', 1000)->nullable();
            $table->integer('capacity')->default(250);
            $table->decimal('ticket_price', 10, 2)->default(0); // 0 = Gratuit sur inscription
            $table->string('currency', 10)->default('EUR');
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('published');
            $table->boolean('is_featured')->default(false);
            $table->boolean('registration_open')->default(true);
            $table->json('program');    // Sessions [{time, title, speaker, role, track, description}]
            $table->json('committee');  // Membres comité/speakers [{name, role, company, photo_url, linkedin, twitter, website}]
            $table->json('partners');   // Partenaires [{name, tier, logo_url, website}]
            $table->json('seo_meta');
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ticket_code', 40)->unique();
            $table->string('full_name', 150);
            $table->string('email', 255);
            $table->string('phone', 60)->nullable();
            $table->string('company', 150)->nullable();
            $table->string('job_title', 150)->nullable();
            $table->string('country', 100)->nullable();
            $table->enum('attendance_mode', ['in_person', 'online'])->default('in_person');
            $table->enum('status', ['confirmed', 'pending', 'cancelled'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status']);
        });

        // ==========================================
        // 5. SYSTÈME DE PODCASTS & VIDÉOS (YOUTUBE + UPLOAD LOCAL)
        // ==========================================
        Schema::create('podcasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->integer('season_number')->default(1);
            $table->integer('episode_number')->default(1);
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->enum('source_type', ['youtube', 'upload'])->default('youtube');
            $table->string('youtube_url', 500)->nullable();
            $table->string('youtube_video_id', 60)->nullable();
            $table->string('video_file_path', 1000)->nullable();
            $table->string('audio_file_path', 1000)->nullable();
            $table->string('thumbnail_url', 1000)->nullable();
            $table->string('duration_label', 40)->default('24:00');
            $table->string('guest_name', 150)->nullable();
            $table->string('guest_role', 200)->nullable();
            $table->string('host_name', 150)->default('Rédaction CIOMAG');
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->json('social_distribution'); // Prêt pour publication API YouTube, Facebook, Twitter, LinkedIn
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        // ==========================================
        // 6. PUBLICATIONS RÉSEAUX SOCIAUX (FACEBOOK, LINKEDIN, TWITTER/X, YOUTUBE)
        // ==========================================
        Schema::create('social_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->foreignUuid('podcast_id')->nullable()->constrained('podcasts')->cascadeOnDelete();
            $table->enum('platform', ['facebook', 'linkedin', 'twitter', 'youtube']);
            $table->enum('status', ['published', 'simulated', 'queued', 'failed'])->default('published');
            $table->text('message');
            $table->string('target_url', 1000)->nullable();
            $table->string('external_post_id', 150)->nullable();
            $table->string('external_url', 1000)->nullable();
            $table->json('response_payload');
            $table->foreignUuid('shared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('shared_at')->nullable();
            $table->timestamps();

            $table->index(['platform', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_shares');
        Schema::dropIfExists('podcasts');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');

        if (Schema::hasColumn('posts', 'is_premium')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('is_premium');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==========================================
        // 3. BIBLIOTHÈQUE DE MÉDIAS
        // ==========================================
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('uploader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name', 255);
            $table->text('file_path');
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->text('caption')->nullable();
            $table->json('variants');
            $table->timestamp('created_at')->useCurrent();
        });

        // ==========================================
        // 4. CONTENU (ARTICLES, PAGES & RÉVISIONS)
        // ==========================================
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->uuid('parent_id')->nullable();

            $table->enum('type', ['post', 'page'])->default('post');
            $table->enum('status', ['draft', 'pending_review', 'scheduled', 'published', 'archived'])->default('draft');

            $table->string('title', 255);
            $table->string('slug', 255);
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('content_format', 20)->default('html');

            $table->boolean('is_featured')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->unsignedInteger('reading_time_minutes')->default(1);
            $table->unsignedBigInteger('view_count')->default(0);

            $table->json('seo_meta');

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['type', 'slug'], 'unique_slug_per_type');

            // Indexation stratégique
            $table->index(['type', 'slug', 'status', 'deleted_at'], 'idx_posts_lookup');
            $table->index(['type', 'status', 'deleted_at', 'is_featured', 'published_at'], 'idx_posts_feed');
            $table->index(['author_id', 'published_at'], 'idx_posts_author');
            $table->fullText(['title', 'excerpt', 'content'], 'idx_posts_search');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('posts')->nullOnDelete();
        });

        Schema::create('post_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignUuid('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('change_summary', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // ==========================================
        // 5. TAXONOMIES (CATÉGORIES & TAGS)
        // ==========================================
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->json('seo_meta');
            $table->integer('display_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
        });

        Schema::create('post_categories', function (Blueprint $table) {
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);

            $table->primary(['post_id', 'category_id']);
            $table->index('category_id', 'idx_post_categories_cat');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('post_tags', function (Blueprint $table) {
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignUuid('tag_id')->constrained('tags')->cascadeOnDelete();

            $table->primary(['post_id', 'tag_id']);
            $table->index('tag_id', 'idx_post_tags_tag');
        });

        // ==========================================
        // 6. COMMENTAIRES
        // ==========================================
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->uuid('parent_id')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('guest_name', 100)->nullable();
            $table->string('guest_email', 255)->nullable();
            $table->string('guest_website', 255)->nullable();

            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'spam', 'trash'])->default('pending');

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['post_id', 'status', 'created_at'], 'idx_comments_post_approved');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('comments')->cascadeOnDelete();
        });

        // ==========================================
        // 7. CONFIGURATION & REDIRECTIONS SEO
        // ==========================================
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->json('value');
            $table->text('description')->nullable();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_path', 500)->unique();
            $table->string('target_path', 500);
            $table->smallInteger('status_code')->default(301);
            $table->unsignedBigInteger('hits')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement('ALTER TABLE redirects ADD CONSTRAINT chk_redirects_status_code CHECK (status_code IN (301, 302, 307, 308))');

        // ==========================================
        // 8. SYSTÈME DE MENUS DYNAMIQUES
        // ==========================================
        Schema::create('menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('location', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->uuid('parent_id')->nullable();

            $table->string('label', 100);
            $table->string('title_attr', 150)->nullable();
            $table->enum('type', ['post', 'category', 'custom_url', 'container'])->default('custom_url');

            $table->foreignUuid('post_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->string('custom_url', 500)->nullable();

            $table->string('target', 20)->default('_self');
            $table->string('icon', 50)->nullable();
            $table->string('css_classes', 150)->nullable();
            $table->string('badge_text', 30)->nullable();

            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['menu_id', 'is_active', 'parent_id', 'display_order'], 'idx_menu_items_lookup');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('menu_items')->cascadeOnDelete();
        });

        DB::statement("ALTER TABLE menu_items ADD CONSTRAINT chk_menu_items_target_attr CHECK (target IN ('_self', '_blank'))");

        // Triggers MySQL 8.0 pour appliquer les contraintes d'intégrité sur colonnes avec clés étrangères
        $triggers = [
            "CREATE TRIGGER trg_comments_check_author_bi BEFORE INSERT ON comments
            FOR EACH ROW
            BEGIN
                IF (NEW.user_id IS NULL AND (NEW.guest_name IS NULL OR NEW.guest_name = '' OR NEW.guest_email IS NULL OR NEW.guest_email = '')) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'check_comment_author: user_id or (guest_name and guest_email) must be provided';
                END IF;
            END",

            "CREATE TRIGGER trg_comments_check_author_bu BEFORE UPDATE ON comments
            FOR EACH ROW
            BEGIN
                IF (NEW.user_id IS NULL AND (NEW.guest_name IS NULL OR NEW.guest_name = '' OR NEW.guest_email IS NULL OR NEW.guest_email = '')) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'check_comment_author: user_id or (guest_name and guest_email) must be provided';
                END IF;
            END",

            "CREATE TRIGGER trg_menu_items_check_target_bi BEFORE INSERT ON menu_items
            FOR EACH ROW
            BEGIN
                IF NOT (
                    (NEW.type = 'post'       AND NEW.post_id IS NOT NULL AND NEW.category_id IS NULL AND NEW.custom_url IS NULL) OR
                    (NEW.type = 'category'   AND NEW.category_id IS NOT NULL AND NEW.post_id IS NULL AND NEW.custom_url IS NULL) OR
                    (NEW.type = 'custom_url' AND NEW.custom_url IS NOT NULL AND NEW.post_id IS NULL AND NEW.category_id IS NULL) OR
                    (NEW.type = 'container'  AND NEW.post_id IS NULL AND NEW.category_id IS NULL AND NEW.custom_url IS NULL)
                ) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'check_menu_item_target: target columns do not match declared menu_item_type';
                END IF;
            END",

            "CREATE TRIGGER trg_menu_items_check_target_bu BEFORE UPDATE ON menu_items
            FOR EACH ROW
            BEGIN
                IF NOT (
                    (NEW.type = 'post'       AND NEW.post_id IS NOT NULL AND NEW.category_id IS NULL AND NEW.custom_url IS NULL) OR
                    (NEW.type = 'category'   AND NEW.category_id IS NOT NULL AND NEW.post_id IS NULL AND NEW.custom_url IS NULL) OR
                    (NEW.type = 'custom_url' AND NEW.custom_url IS NOT NULL AND NEW.post_id IS NULL AND NEW.category_id IS NULL) OR
                    (NEW.type = 'container'  AND NEW.post_id IS NULL AND NEW.category_id IS NULL AND NEW.custom_url IS NULL)
                ) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'check_menu_item_target: target columns do not match declared menu_item_type';
                END IF;
            END",
        ];

        foreach ($triggers as $triggerSql) {
            try {
                DB::unprepared($triggerSql);
            } catch (\Throwable) {
                // La validation Eloquent (saving) assure également cette contrainte si l'utilisateur MySQL n'a pas le privilège TRIGGER
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('post_tags');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('post_categories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('post_revisions');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('media');
    }
};

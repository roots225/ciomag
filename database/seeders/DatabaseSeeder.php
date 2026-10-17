<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Enums\ContentFormat;
use App\Enums\MenuItemType;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\UserStatus;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Redirect;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Services\MenuResolverService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. RÔLES
        // ==========================================
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrateur & Directeur de Publication',
                'permissions' => ['*'],
            ]
        );

        $editorRole = Role::updateOrCreate(
            ['slug' => 'editor'],
            [
                'name' => 'Rédacteur en Chef',
                'permissions' => [
                    'posts.create', 'posts.edit', 'posts.publish', 'posts.delete',
                    'categories.manage', 'tags.manage', 'media.manage',
                    'comments.moderate', 'menus.manage', 'redirects.manage',
                ],
            ]
        );

        $authorRole = Role::updateOrCreate(
            ['slug' => 'author'],
            [
                'name' => 'Journaliste / Chroniqueur',
                'permissions' => ['posts.create', 'posts.edit', 'media.upload'],
            ]
        );

        $subscriberRole = Role::updateOrCreate(
            ['slug' => 'subscriber'],
            [
                'name' => 'Lecteur Abonné',
                'permissions' => ['comments.create'],
            ]
        );

        // ==========================================
        // 2. UTILISATEURS (ÉQUIPE ÉDITORIALE)
        // ==========================================
        $admin = User::updateOrCreate(
            ['email' => 'admin@ciomag.com'],
            [
                'role_id' => $adminRole->id,
                'username' => 'koffi-mensah',
                'password_hash' => Hash::make('password'),
                'display_name' => 'Koffi Mensah',
                'bio' => 'Directeur de la rédaction chez CIOMAG. Spécialiste des politiques publiques numériques, de la souveraineté des données et de la gouvernance IT.',
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
                'status' => UserStatus::Active,
                'social_links' => [
                    'linkedin' => 'https://linkedin.com/in/koffi-mensah',
                    'twitter' => 'https://x.com/koffimensah_cio',
                ],
                'last_login_at' => now()->subMinutes(15),
            ]
        );

        $editor = User::updateOrCreate(
            ['email' => 'redaction@ciomag.com'],
            [
                'role_id' => $editorRole->id,
                'username' => 'amina-diallo',
                'password_hash' => Hash::make('password'),
                'display_name' => 'Amina Diallo',
                'bio' => 'Rédactrice en chef adjointe, en charge des dossiers Intelligence Artificielle, Cloud Souverain et Infrastructures critiques.',
                'avatar_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80',
                'status' => UserStatus::Active,
                'social_links' => [
                    'linkedin' => 'https://linkedin.com/in/amina-diallo',
                    'twitter' => 'https://x.com/aminadiallo_tech',
                ],
                'last_login_at' => now()->subHours(2),
            ]
        );

        $author = User::updateOrCreate(
            ['email' => 'cyber@ciomag.com'],
            [
                'role_id' => $authorRole->id,
                'username' => 'marc-kouassi',
                'password_hash' => Hash::make('password'),
                'display_name' => 'Marc Kouassi',
                'bio' => 'Journaliste d\'investigation spécialisé en Cybersécurité, FinTech et résilience bancaire.',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
                'status' => UserStatus::Active,
                'social_links' => [
                    'linkedin' => 'https://linkedin.com/in/marc-kouassi',
                ],
                'last_login_at' => now()->subDay(),
            ]
        );

        $subscriber = User::updateOrCreate(
            ['email' => 'lecteur@ciomag.com'],
            [
                'role_id' => $subscriberRole->id,
                'username' => 'sarah-konan',
                'password_hash' => Hash::make('password'),
                'display_name' => 'Sarah Konan (DSI Groupe)',
                'bio' => 'Directrice des Systèmes d\'Information dans le secteur bancaire.',
                'status' => UserStatus::Active,
                'social_links' => [],
            ]
        );

        // ==========================================
        // 3. MÉDIATHÈQUE
        // ==========================================
        $mediaAi = Media::updateOrCreate(
            ['file_name' => 'ia-generative-dsi-2026.jpg'],
            [
                'uploader_id' => $editor->id,
                'file_path' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1400&q=85',
                'mime_type' => 'image/jpeg',
                'file_size' => 428500,
                'width' => 1400,
                'height' => 900,
                'alt_text' => 'Architecture IA générative et salle de contrôle numérique',
                'caption' => 'L\'industrialisation des modèles de langage privés transforme la feuille de route des DSI.',
                'variants' => [
                    'thumbnail' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=400&q=80',
                    'medium' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=800&q=80',
                ],
            ]
        );

        $mediaCyber = Media::updateOrCreate(
            ['file_name' => 'zero-trust-banques-2026.jpg'],
            [
                'uploader_id' => $author->id,
                'file_path' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1400&q=85',
                'mime_type' => 'image/jpeg',
                'file_size' => 389200,
                'width' => 1400,
                'height' => 900,
                'alt_text' => 'Centre d\'opérations de sécurité SOC et architecture Zero Trust',
                'caption' => 'Les centres SOC de nouvelle génération misent sur la détection comportementale automatisée.',
                'variants' => [
                    'thumbnail' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=400&q=80',
                    'medium' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=800&q=80',
                ],
            ]
        );

        $mediaCloud = Media::updateOrCreate(
            ['file_name' => 'datacenters-souverains-afrique.jpg'],
            [
                'uploader_id' => $admin->id,
                'file_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1400&q=85',
                'mime_type' => 'image/jpeg',
                'file_size' => 512000,
                'width' => 1400,
                'height' => 900,
                'alt_text' => 'Allée de serveurs dans un Data Center Tier IV',
                'caption' => 'L\'essor des Data Centers neutres et souverains redessine la carte de l\'hébergement.',
                'variants' => [
                    'thumbnail' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=400&q=80',
                    'medium' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=800&q=80',
                ],
            ]
        );

        $mediaFintech = Media::updateOrCreate(
            ['file_name' => 'open-banking-interop.jpg'],
            [
                'uploader_id' => $author->id,
                'file_path' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1400&q=85',
                'mime_type' => 'image/jpeg',
                'file_size' => 345000,
                'width' => 1400,
                'height' => 900,
                'alt_text' => 'Paiements instantanés et API Open Banking',
                'caption' => 'L\'interopérabilité régionale ouvre une nouvelle ère pour les services financiers numériques.',
                'variants' => [
                    'thumbnail' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=400&q=80',
                    'medium' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80',
                ],
            ]
        );

        $mediaStrategy = Media::updateOrCreate(
            ['file_name' => 'comex-dsi-leadership.jpg'],
            [
                'uploader_id' => $admin->id,
                'file_path' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1400&q=85',
                'mime_type' => 'image/jpeg',
                'file_size' => 410000,
                'width' => 1400,
                'height' => 900,
                'alt_text' => 'Réunion stratégique du comité exécutif',
                'caption' => 'Le DSI s\'impose désormais comme co-pilote de la création de valeur au sein du COMEX.',
                'variants' => [
                    'thumbnail' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=400&q=80',
                    'medium' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80',
                ],
            ]
        );

        $mediaGreenIt = Media::updateOrCreate(
            ['file_name' => 'green-it-sobriete-numerique.jpg'],
            [
                'uploader_id' => $editor->id,
                'file_path' => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?auto=format&fit=crop&w=1400&q=85',
                'mime_type' => 'image/jpeg',
                'file_size' => 360000,
                'width' => 1400,
                'height' => 900,
                'alt_text' => 'Énergie solaire et infrastructure numérique durable',
                'caption' => 'Concilier puissance de calcul pour l\'IA et sobriété énergétique devient un impératif ESG.',
                'variants' => [
                    'thumbnail' => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?auto=format&fit=crop&w=400&q=80',
                    'medium' => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?auto=format&fit=crop&w=800&q=80',
                ],
            ]
        );

        // ==========================================
        // 4. CATÉGORIES HIÉRARCHIQUES
        // ==========================================
        $catAi = Category::updateOrCreate(
            ['slug' => 'intelligence-artificielle'],
            [
                'name' => 'Intelligence Artificielle',
                'description' => 'Analyses, cas d\'usage industriels, LLM souverains et gouvernance de l\'IA pour les grandes organisations.',
                'display_order' => 1,
                'seo_meta' => [
                    'meta_title' => 'Intelligence Artificielle & Data | CIOMAG',
                    'meta_description' => 'Toute l\'actualité de l\'IA générative, du Machine Learning et de la gouvernance des données pour les DSI.',
                ],
            ]
        );

        $catAiGen = Category::updateOrCreate(
            ['slug' => 'ia-generative-llm'],
            [
                'parent_id' => $catAi->id,
                'name' => 'IA Générative & LLM',
                'description' => 'Déploiement de modèles RAG, agents autonomes et souveraineté linguistique.',
                'display_order' => 1,
                'seo_meta' => [],
            ]
        );

        $catCyber = Category::updateOrCreate(
            ['slug' => 'cybersecurite'],
            [
                'name' => 'Cybersécurité',
                'description' => 'Menaces avancées, Zero Trust, conformité réglementaire et résilience opérationnelle des infrastructures critiques.',
                'display_order' => 2,
                'seo_meta' => [
                    'meta_title' => 'Cybersécurité & Résilience IT | CIOMAG',
                    'meta_description' => 'Enquêtes et dossiers sur la cybersécurité, les SOC, le Zero Trust et la protection des données.',
                ],
            ]
        );

        $catCloud = Category::updateOrCreate(
            ['slug' => 'cloud-infrastructures'],
            [
                'name' => 'Cloud & Infrastructures',
                'description' => 'Data Centers Tier III/IV, Cloud hybride, souveraineté numérique, 5G et connectivité sous-marine.',
                'display_order' => 3,
                'seo_meta' => [],
            ]
        );

        $catFintech = Category::updateOrCreate(
            ['slug' => 'fintech-govtech'],
            [
                'name' => 'FinTech & GovTech',
                'description' => 'Innovation bancaire, Open Banking, identité numérique et modernisation des services publics.',
                'display_order' => 4,
                'seo_meta' => [],
            ]
        );

        $catStrategy = Category::updateOrCreate(
            ['slug' => 'strategie-dsi'],
            [
                'name' => 'Stratégie & DSI',
                'description' => 'Leadership technologique, conduite du changement, FinOps, Green IT et gouvernance au COMEX.',
                'display_order' => 5,
                'seo_meta' => [],
            ]
        );

        // ==========================================
        // 5. TAGS
        // ==========================================
        $tagsData = [
            ['name' => 'IA Souveraine', 'slug' => 'ia-souveraine', 'description' => 'Modèles d\'IA hébergés et entraînés localement'],
            ['name' => 'Zero Trust', 'slug' => 'zero-trust', 'description' => 'Architecture de sécurité sans confiance implicite'],
            ['name' => 'Data Centers', 'slug' => 'data-centers', 'description' => 'Infrastructures d\'hébergement haute disponibilité'],
            ['name' => 'Open Banking', 'slug' => 'open-banking', 'description' => 'Interopérabilité financière par API'],
            ['name' => 'FinOps', 'slug' => 'finops', 'description' => 'Optimisation financière des dépenses Cloud'],
            ['name' => 'Green IT', 'slug' => 'green-it', 'description' => 'Numérique responsable et efficacité énergétique'],
            ['name' => 'GovTech', 'slug' => 'govtech', 'description' => 'Digitalisation de l\'État et des services citoyens'],
            ['name' => 'Leadership DSI', 'slug' => 'leadership-dsi', 'description' => 'Posture stratégique du CIO/DSI'],
        ];

        $tags = [];
        foreach ($tagsData as $t) {
            $tags[$t['slug']] = Tag::updateOrCreate(['slug' => $t['slug']], $t);
        }

        // ==========================================
        // 6. ARTICLES DU MAGAZINE (POSTS) & PAGES
        // ==========================================
        $post1 = Post::updateOrCreate(
            ['type' => PostType::Post, 'slug' => 'ia-generative-dsi-africains-2026'],
            [
                'author_id' => $editor->id,
                'featured_media_id' => $mediaAi->id,
                'status' => PostStatus::Published,
                'title' => 'IA Générative : Comment les DSI passent de l\'expérimentation à l\'industrialisation souveraine',
                'excerpt' => 'Après deux années de preuves de concept (PoC), les Directions des Systèmes d\'Information structurent désormais des architectures RAG privées et maîtrisent leurs coûts d\'inférence.',
                'content_format' => ContentFormat::Html,
                'is_featured' => true,
                'allow_comments' => true,
                'view_count' => 14820,
                'published_at' => now()->subHours(5),
                'seo_meta' => [
                    'meta_title' => 'IA Générative en entreprise : le guide d\'industrialisation pour les DSI | CIOMAG',
                    'meta_description' => 'Découvrez comment les DSI déploient des architectures RAG sécurisées, des modèles open-weights locaux et une gouvernance stricte des données.',
                    'canonical_url' => url('/blog/ia-generative-dsi-africains-2026'),
                    'og_image' => $mediaAi->url(),
                    'noindex' => false,
                ],
                'content' => <<<'HTML'
<p class="lead">Le temps des démonstrations spectaculaires mais isolées est révolu. En 2026, les comités exécutifs attendent des Directions des Systèmes d'Information (DSI) des gains de productivité mesurables, une sécurité sans faille et une maîtrise totale de la propriété intellectuelle.</p>

<h2>1. Du Cloud public aux architectures RAG hybrides</h2>
<p>La première préoccupation exprimée par 78 % des DSI interrogés dans notre baromètre annuel concerne la confidentialité des données métiers. Plutôt que d'exposer des bases documentaires sensibles à des API tierces non souveraines, les entreprises privilégient désormais les architectures <strong>Retrieval-Augmented Generation (RAG)</strong> adossées à des bases vectorielles internes.</p>
<blockquote>« L'avantage compétitif ne réside pas dans le modèle fondation lui-même, mais dans la qualité, la fraîcheur et la gouvernance des données propriétaires qui l'alimentent en temps réel. » — Amina Diallo, Rédactrice en chef adjointe.</blockquote>

<h2>2. Maîtriser l'équation économique de l'inférence (FinOps IA)</h2>
<p>Le passage à l'échelle révèle rapidement un piège budgétaire : le coût par requête sur des modèles géants généralistes. Les architectes IT adoptent aujourd'hui une approche multi-modèles :</p>
<ul>
    <li><strong>Small Language Models (SLM) spécialisés</strong> (7B à 14B paramètres) hébergés sur infrastructure propre pour 80 % des tâches de classification, synthèse et extraction.</li>
    <li><strong>Routage intelligent de prompts</strong> réservant les modèles frontières aux raisonnements complexes.</li>
    <li><strong>Mise en cache sémantique</strong> permettant de réduire de 45 % la facture d'inférence mensuelle.</li>
</ul>

<h2>3. La gouvernance et la conformité au cœur du dispositif</h2>
<p>Avec l'entrée en vigueur de nouvelles réglementations sur la traçabilité algorithmique, chaque réponse générée doit pouvoir être auditée : quelles sources documentaires ont été mobilisées, quelle version du modèle a répondu, et quels garde-fous (guardrails) ont filtré les données à caractère personnel.</p>
HTML,
            ]
        );
        $post1->categories()->sync([
            $catAi->id => ['is_primary' => true],
            $catAiGen->id => ['is_primary' => false],
            $catStrategy->id => ['is_primary' => false],
        ]);
        $post1->tags()->sync([$tags['ia-souveraine']->id, $tags['finops']->id, $tags['leadership-dsi']->id]);

        PostRevision::firstOrCreate(
            ['post_id' => $post1->id, 'change_summary' => 'Version initiale du grand dossier IA 2026'],
            [
                'editor_id' => $editor->id,
                'title' => $post1->title,
                'excerpt' => $post1->excerpt,
                'content' => $post1->content,
            ]
        );

        $post2 = Post::updateOrCreate(
            ['type' => PostType::Post, 'slug' => 'cybersecurite-zero-trust-secteur-bancaire'],
            [
                'author_id' => $author->id,
                'featured_media_id' => $mediaCyber->id,
                'status' => PostStatus::Published,
                'title' => 'Résilience Bancaire : Pourquoi l\'architecture Zero Trust devient la norme face aux cybermenaces hybrides',
                'excerpt' => 'Face à l\'automatisation des attaques par rançongiciel et à la multiplication des API partenaires, les RSSI abandonnent définitivement la sécurité périmétrique classique.',
                'content_format' => ContentFormat::Html,
                'is_featured' => true,
                'allow_comments' => true,
                'view_count' => 9640,
                'published_at' => now()->subHours(14),
                'seo_meta' => [
                    'meta_title' => 'Zero Trust & Résilience Bancaire : Enquête RSSI | CIOMAG',
                    'meta_description' => 'Comment les banques et institutions financières déploient la micro-segmentation et les SOC augmentés par l\'IA.',
                    'noindex' => false,
                ],
                'content' => <<<'HTML'
<p class="lead">L'interconnexion accélérée entre banques traditionnelles, opérateurs Mobile Money et FinTechs a considérablement élargi la surface d'attaque. Un seul jeton d'API compromis peut aujourd'hui menacer toute une chaîne de compensation.</p>

<h2>Ne jamais faire confiance, toujours vérifier</h2>
<p>Le paradigme <strong>Zero Trust</strong> repose sur un principe cardinal : aucune entité — qu'elle soit située à l'intérieur ou à l'extérieur du réseau d'entreprise — ne bénéficie d'une confiance implicite. Chaque accès fait l'objet d'une authentification continue basée sur le contexte, l'état du terminal et le score de risque comportemental.</p>

<h2>Micro-segmentation et SOC de nouvelle génération</h2>
<p>En isolant les environnements critiques (Core Banking, passerelles monétiques, bases clients) dans des micro-segments hermétiques, les RSSI empêchent tout mouvement latéral d'un attaquant. Parallèlement, l'intégration d'outils SOAR (Security Orchestration, Automation and Response) permet de ramener le temps moyen de confinement (MTTR) de plusieurs heures à quelques secondes.</p>
HTML,
            ]
        );
        $post2->categories()->sync([
            $catCyber->id => ['is_primary' => true],
            $catFintech->id => ['is_primary' => false],
        ]);
        $post2->tags()->sync([$tags['zero-trust']->id, $tags['open-banking']->id]);

        $post3 = Post::updateOrCreate(
            ['type' => PostType::Post, 'slug' => 'essor-data-centers-tier-iv-souverainete-cloud'],
            [
                'author_id' => $admin->id,
                'featured_media_id' => $mediaCloud->id,
                'status' => PostStatus::Published,
                'title' => 'Data Centers Tier IV & Câbles sous-marins : La nouvelle géopolitique des infrastructures souveraines',
                'excerpt' => 'L\'arrivée de nouveaux câbles sous-marins à très haute capacité conjuguée à la construction de Data Centers neutres redéfinit la latence et le rapatriement des données.',
                'content_format' => ContentFormat::Html,
                'is_featured' => false,
                'allow_comments' => true,
                'view_count' => 7310,
                'published_at' => now()->subDays(1),
                'seo_meta' => [
                    'meta_title' => 'Data Centers Tier IV et Souveraineté Cloud | CIOMAG',
                    'meta_description' => 'Panorama des investissements dans les infrastructures Cloud et Data Centers neutres.',
                    'noindex' => false,
                ],
                'content' => <<<'HTML'
<p class="lead">Pendant plus d'une décennie, la majorité du trafic numérique régional transitait par des points d'échange européens avant de revenir vers les utilisateurs locaux. Cette anomalie topologique appartient désormais au passé.</p>

<h2>Rapatrier la donnée au plus près des usages</h2>
<p>Les exigences réglementaires sur la localisation des données de santé, fiscales et bancaires accélèrent la demande pour des salles blanches certifiées <strong>Tier III et Tier IV</strong>. Au-delà de la conformité, la réduction de la latence de 120 ms à moins de 8 ms ouvre la voie aux applications industrielles temps réel et à l'Edge Computing.</p>
HTML,
            ]
        );
        $post3->categories()->sync([$catCloud->id => ['is_primary' => true]]);
        $post3->tags()->sync([$tags['data-centers']->id, $tags['green-it']->id]);

        $post4 = Post::updateOrCreate(
            ['type' => PostType::Post, 'slug' => 'open-banking-paiements-instantanes-interoperabilite'],
            [
                'author_id' => $author->id,
                'featured_media_id' => $mediaFintech->id,
                'status' => PostStatus::Published,
                'title' => 'Paiements instantanés et Open Banking : L\'architecture API au service de l\'inclusion financière',
                'excerpt' => 'Le lancement des commutateurs régionaux de paiement instantané oblige les banques à moderniser leur Core Banking vers des architectures orientées événements.',
                'content_format' => ContentFormat::Html,
                'is_featured' => false,
                'allow_comments' => true,
                'view_count' => 5490,
                'published_at' => now()->subDays(2),
                'seo_meta' => [],
                'content' => <<<'HTML'
<p class="lead">Le traitement par lots (batch) de fin de journée cède la place au temps réel intégral 24/7/365. Pour supporter des milliers de transactions par seconde avec une latence inférieure à la seconde, l'architecture logicielle bancaire se réinvente.</p>
<h2>Des monolithes historiques aux architectures Event-Driven</h2>
<p>En adoptant des bus d'événements distribués et des passerelles API standardisées (ISO 20022), les institutions financières permettent à tout un écosystème de FinTechs de créer des services à valeur ajoutée : crédit instantané, épargne automatisée et trésorerie prédictive pour les PME.</p>
HTML,
            ]
        );
        $post4->categories()->sync([$catFintech->id => ['is_primary' => true]]);
        $post4->tags()->sync([$tags['open-banking']->id, $tags['govtech']->id]);

        $post5 = Post::updateOrCreate(
            ['type' => PostType::Post, 'slug' => 'dsi-au-comex-de-centre-de-cout-a-moteur-de-croissance'],
            [
                'author_id' => $admin->id,
                'featured_media_id' => $mediaStrategy->id,
                'status' => PostStatus::Published,
                'title' => 'Le DSI au COMEX : De gestionnaire d\'infrastructures à stratège de la création de valeur',
                'excerpt' => 'Entretien croisé avec cinq Directeurs des Systèmes d\'Information qui ont transformé leur direction IT en véritable incubateur de revenus numériques.',
                'content_format' => ContentFormat::Html,
                'is_featured' => false,
                'allow_comments' => true,
                'view_count' => 6120,
                'published_at' => now()->subDays(3),
                'seo_meta' => [],
                'content' => <<<'HTML'
<p class="lead">Longtemps cantonnée au maintien en condition opérationnelle et à la réduction des coûts, la fonction DSI occupe aujourd'hui une place centrale dans les arbitrages stratégiques des grands groupes.</p>
<h2>Parler le langage du métier et du ROI</h2>
<p>Pour peser au Comité Exécutif, le CIO moderne ne présente plus des indicateurs purement techniques (disponibilité serveur ou nombre de tickets résolus), mais des métriques d'impact métier : réduction du Time-to-Market, taux de conversion omnicanal et résilience de la chaîne logistique.</p>
HTML,
            ]
        );
        $post5->categories()->sync([$catStrategy->id => ['is_primary' => true]]);
        $post5->tags()->sync([$tags['leadership-dsi']->id, $tags['finops']->id]);

        $post6 = Post::updateOrCreate(
            ['type' => PostType::Post, 'slug' => 'green-it-sobriete-energetique-defi-ia-data-centers'],
            [
                'author_id' => $editor->id,
                'featured_media_id' => $mediaGreenIt->id,
                'status' => PostStatus::Published,
                'title' => 'Green IT & Sobriété Numérique : Comment concilier explosion de l\'IA et neutralité carbone',
                'excerpt' => 'Refroidissement liquide (Direct-to-Chip), autoconsommation solaire et éco-conception logicielle : les solutions concrètes déployées par les opérateurs.',
                'content_format' => ContentFormat::Html,
                'is_featured' => false,
                'allow_comments' => true,
                'view_count' => 4290,
                'published_at' => now()->subDays(4),
                'seo_meta' => [],
                'content' => <<<'HTML'
<p class="lead">Alors qu'une baie dédiée à l'entraînement de modèles d'IA peut dépasser 40 à 80 kW de densité électrique, l'efficacité énergétique (PUE) et hydrique (WUE) devient le critère numéro un des nouveaux projets d'infrastructure.</p>
<h2>L'éco-conception logicielle comme levier immédiat</h2>
<p>Au-delà du matériel, l'optimisation des requêtes SQL, l'abandon des architectures EAV gourmandes en jointures au profit de schémas relationnels stricts et l'usage intelligent du cache Redis réduisent jusqu'à 60 % la charge CPU des plateformes numériques à fort trafic.</p>
HTML,
            ]
        );
        $post6->categories()->sync([
            $catStrategy->id => ['is_primary' => true],
            $catCloud->id => ['is_primary' => false],
        ]);
        $post6->tags()->sync([$tags['green-it']->id, $tags['data-centers']->id]);

        // Un article en brouillon pour le Back-Office
        $postDraft = Post::updateOrCreate(
            ['type' => PostType::Post, 'slug' => 'barometre-salaires-it-cyber-2027'],
            [
                'author_id' => $editor->id,
                'featured_media_id' => $mediaStrategy->id,
                'status' => PostStatus::PendingReview,
                'title' => '[Enquête exclusive] Baromètre des talents et rémunérations IT, Cloud & Cyber 2027',
                'excerpt' => 'Quels sont les profils les plus recherchés par les DSI et comment retenir les architectes Cloud et experts SOC ?',
                'content_format' => ContentFormat::Html,
                'is_featured' => false,
                'allow_comments' => true,
                'view_count' => 0,
                'published_at' => null,
                'seo_meta' => [],
                'content' => '<p>Article en cours de validation par le comité de rédaction avant parution dans le prochain numéro.</p>',
            ]
        );
        $postDraft->categories()->sync([$catStrategy->id => ['is_primary' => true]]);

        // Pages statiques du magazine
        $pageAbout = Post::updateOrCreate(
            ['type' => PostType::Page, 'slug' => 'a-propos'],
            [
                'author_id' => $admin->id,
                'status' => PostStatus::Published,
                'title' => 'À propos de CIOMAG',
                'excerpt' => 'La référence média des décideurs IT, DSI, RSSI et acteurs de la transformation numérique.',
                'content_format' => ContentFormat::Html,
                'allow_comments' => false,
                'published_at' => now()->subMonths(6),
                'seo_meta' => [
                    'meta_title' => 'À propos de CIOMAG | Le Magazine des Dirigeants IT',
                    'meta_description' => 'Découvrez la mission éditoriale, l\'équipe de rédaction et le comité scientifique de CIOMAG.',
                ],
                'content' => <<<'HTML'
<p class="lead">Depuis sa création, <strong>CIOMAG</strong> accompagne les Directeurs des Systèmes d'Information (DSI), les Responsables de la Sécurité des SI (RSSI), les Chief Digital Officers et les décideurs publics dans la compréhension des mutations technologiques.</p>
<h2>Notre mission éditoriale</h2>
<p>À travers nos enquêtes, nos grands dossiers thématiques, nos baromètres exclusifs et nos tribunes d'experts, nous donnons la parole à celles et ceux qui bâtissent des infrastructures numériques performantes, souveraines et inclusives.</p>
<h2>Une architecture technique de pointe</h2>
<p>Cette plateforme éditoriale repose sur un schéma relationnel normalisé haute performance couplé à Redis et à une recherche plein texte native.</p>
HTML,
            ]
        );

        $pageCharter = Post::updateOrCreate(
            ['type' => PostType::Page, 'slug' => 'charte-editoriale'],
            [
                'author_id' => $admin->id,
                'parent_id' => $pageAbout->id,
                'status' => PostStatus::Published,
                'title' => 'Charte Éditoriale & Déontologie',
                'excerpt' => 'Nos engagements d\'indépendance, de vérification des sources et de rigueur journalistique.',
                'content_format' => ContentFormat::Html,
                'allow_comments' => false,
                'published_at' => now()->subMonths(6),
                'seo_meta' => [],
                'content' => <<<'HTML'
<p>La rédaction de CIOMAG s'engage à respecter les plus hauts standards d'indépendance journalistique. Les contenus partenaires ou publirédactionnels sont systématiquement identifiés comme tels.</p>
HTML,
            ]
        );

        // ==========================================
        // 7. COMMENTAIRES (IMBRIQUÉS + MODÉRATION)
        // ==========================================
        $comment1 = Comment::firstOrCreate(
            ['post_id' => $post1->id, 'user_id' => $subscriber->id],
            [
                'content' => 'Excellent dossier ! Nous avons justement migré notre PoC vers une architecture RAG interne ce trimestre et la baisse des coûts d\'inférence grâce au cache sémantique est spectaculaire.',
                'status' => CommentStatus::Approved,
                'ip_address' => '192.0.2.10',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X)',
            ]
        );

        Comment::firstOrCreate(
            ['post_id' => $post1->id, 'parent_id' => $comment1->id, 'user_id' => $editor->id],
            [
                'content' => 'Merci pour votre retour d\'expérience Sarah ! Nous préparons justement un comparatif détaillé des bases vectorielles en production pour le numéro du mois prochain.',
                'status' => CommentStatus::Approved,
                'ip_address' => '192.0.2.11',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X)',
            ]
        );

        Comment::firstOrCreate(
            ['post_id' => $post2->id, 'guest_email' => 'jean.dupont.rssi@example.com'],
            [
                'guest_name' => 'Jean-Luc Kouadio (RSSI)',
                'guest_website' => 'https://cyber-forum.example.com',
                'content' => 'La micro-segmentation reste le chantier le plus complexe sur les Core Banking historiques, mais c\'est effectivement la seule parade viable contre les mouvements latéraux.',
                'status' => CommentStatus::Approved,
                'ip_address' => '198.51.100.24',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ]
        );

        Comment::firstOrCreate(
            ['post_id' => $post1->id, 'guest_email' => 'veille.tech@example.org'],
            [
                'guest_name' => 'Dr. Ibrahim Touré',
                'content' => 'Auriez-vous des chiffres précis sur la part des modèles open-weights (Mistral, Llama) par rapport aux API propriétaires dans les banques ?',
                'status' => CommentStatus::Pending,
                'ip_address' => '203.0.113.45',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
            ]
        );

        // ==========================================
        // 8. CONFIGURATION & REDIRECTIONS SEO
        // ==========================================
        Setting::updateOrCreate(
            ['key' => 'site_general'],
            [
                'value' => [
                    'site_name' => 'CIOMAG',
                    'tagline' => 'Le Magazine des Dirigeants IT & de la Transformation Digitale',
                    'edition' => 'Édition Panafricaine & Internationale • N° 118',
                    'contact_email' => 'redaction@ciomag.com',
                    'footer_about' => 'CIOMAG est le média de référence dédié aux Directeurs des Systèmes d\'Information (DSI), RSSI, CDO et décideurs technologiques.',
                ],
                'description' => 'Paramètres généraux d\'identité du magazine',
                'updated_by' => $admin->id,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'seo_defaults'],
            [
                'value' => [
                    'default_meta_title' => 'CIOMAG — Le Magazine des Dirigeants IT, Cloud, IA & Cybersécurité',
                    'default_meta_description' => 'Actualités, enquêtes, grands dossiers et analyses stratégiques pour les DSI, RSSI et leaders de la transformation digitale.',
                    'twitter_handle' => '@CIOMAG_Officiel',
                ],
                'description' => 'Métadonnées SEO par défaut',
                'updated_by' => $admin->id,
            ]
        );

        Redirect::updateOrCreate(
            ['source_path' => '/ancien-dossier-ia'],
            [
                'target_path' => '/blog/ia-generative-dsi-africains-2026',
                'status_code' => 301,
                'hits' => 42,
                'is_active' => true,
            ]
        );

        Redirect::updateOrCreate(
            ['source_path' => '/qui-sommes-nous'],
            [
                'target_path' => '/page/a-propos',
                'status_code' => 301,
                'hits' => 19,
                'is_active' => true,
            ]
        );

        // ==========================================
        // 9. SYSTÈME DE MENUS DYNAMIQUES (ARBORESCENCE)
        // ==========================================
        $headerMenu = Menu::updateOrCreate(
            ['location' => 'header_main'],
            [
                'name' => 'Navigation Principale Magazine',
                'description' => 'Menu principal du header avec sous-menus résolus par CTE récursive',
                'is_active' => true,
            ]
        );

        // Nettoyage et recréation propre des items pour idempotence
        MenuItem::where('menu_id', $headerMenu->id)->delete();

        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'À la Une',
            'type' => MenuItemType::CustomUrl,
            'custom_url' => '/',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $aiMenuParent = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Intelligence Artificielle',
            'type' => MenuItemType::Category,
            'category_id' => $catAi->id,
            'badge_text' => 'Dossier',
            'display_order' => 2,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => $aiMenuParent->id,
            'label' => 'IA Générative & LLM',
            'type' => MenuItemType::Category,
            'category_id' => $catAiGen->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => $aiMenuParent->id,
            'label' => 'Grand Dossier : IA & DSI 2026',
            'type' => MenuItemType::Post,
            'post_id' => $post1->id,
            'badge_text' => 'Exclusif',
            'display_order' => 2,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Cybersécurité',
            'type' => MenuItemType::Category,
            'category_id' => $catCyber->id,
            'display_order' => 3,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Cloud & Infra',
            'type' => MenuItemType::Category,
            'category_id' => $catCloud->id,
            'display_order' => 4,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'FinTech & GovTech',
            'type' => MenuItemType::Category,
            'category_id' => $catFintech->id,
            'display_order' => 5,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Stratégie & DSI',
            'type' => MenuItemType::Category,
            'category_id' => $catStrategy->id,
            'display_order' => 6,
            'is_active' => true,
        ]);

        $aboutContainer = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'label' => 'Le Magazine',
            'type' => MenuItemType::Container,
            'display_order' => 7,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => $aboutContainer->id,
            'label' => 'À propos de CIOMAG',
            'type' => MenuItemType::Post,
            'post_id' => $pageAbout->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => $aboutContainer->id,
            'label' => 'Charte Éditoriale',
            'type' => MenuItemType::Post,
            'post_id' => $pageCharter->id,
            'display_order' => 2,
            'is_active' => true,
        ]);

        // Menus du Footer
        $footerCol1 = Menu::updateOrCreate(
            ['location' => 'footer_col_1'],
            [
                'name' => 'Rubriques Éditoriales (Footer)',
                'description' => 'Colonne 1 du pied de page',
                'is_active' => true,
            ]
        );
        MenuItem::where('menu_id', $footerCol1->id)->delete();

        foreach ([$catAi, $catCyber, $catCloud, $catFintech, $catStrategy] as $idx => $cat) {
            MenuItem::create([
                'menu_id' => $footerCol1->id,
                'label' => $cat->name,
                'type' => MenuItemType::Category,
                'category_id' => $cat->id,
                'display_order' => $idx + 1,
                'is_active' => true,
            ]);
        }

        $footerCol2 = Menu::updateOrCreate(
            ['location' => 'footer_col_2'],
            [
                'name' => 'Informations & Services (Footer)',
                'description' => 'Colonne 2 du pied de page',
                'is_active' => true,
            ]
        );
        MenuItem::where('menu_id', $footerCol2->id)->delete();

        MenuItem::create([
            'menu_id' => $footerCol2->id,
            'label' => 'À propos de CIOMAG',
            'type' => MenuItemType::Post,
            'post_id' => $pageAbout->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $footerCol2->id,
            'label' => 'Charte Éditoriale & Déontologie',
            'type' => MenuItemType::Post,
            'post_id' => $pageCharter->id,
            'display_order' => 2,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $footerCol2->id,
            'label' => 'Espace Rédaction (Admin)',
            'type' => MenuItemType::CustomUrl,
            'custom_url' => '/admin',
            'display_order' => 3,
            'is_active' => true,
        ]);

        $this->call(MagazineModulesSeeder::class);

        app(MenuResolverService::class)->clearAllMenuCaches();
    }
}

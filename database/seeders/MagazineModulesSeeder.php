<?php

namespace Database\Seeders;

use App\Enums\MenuItemType;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Product;
use App\Models\SocialShare;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\MenuResolverService;
use App\Services\SocialPublisherService;
use Illuminate\Database\Seeder;

class MagazineModulesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@ciomag.com')->first();
        $catIa = Category::where('slug', 'intelligence-artificielle')->first();
        $catCyber = Category::where('slug', 'cybersecurite')->first();

        // ==========================================
        // 1. PLANS D'ABONNEMENT & ARTICLE EXCLUSIF PREMIUM
        // ==========================================
        $planMonthly = SubscriptionPlan::updateOrCreate(
            ['slug' => 'pass-essentiel-digital'],
            [
                'name' => 'Pass Essentiel Digital',
                'badge' => 'Accès Immédiat',
                'description' => 'Accès illimité à tous les articles et dossiers exclusifs réservés aux abonnés sur le web et mobile.',
                'billing_interval' => 'monthly',
                'price' => 14.90,
                'currency' => 'EUR',
                'features' => [
                    'Accès illimité aux enquêtes & articles Premium',
                    'Briefing hebdomadaire exclusif des DSI',
                    'Lecture sans publicité sur tous vos écrans',
                    'Accès aux replays des webinaires CIOMAG',
                ],
                'includes_print' => false,
                'is_featured' => false,
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        $planYearly = SubscriptionPlan::updateOrCreate(
            ['slug' => 'abonnement-executif-annuel'],
            [
                'name' => 'Abonnement Exécutif Annuel',
                'badge' => 'Le Choix des DSI',
                'description' => 'La formule complète pour les dirigeants IT : articles Premium illimités, bimestriels PDF et accès VIP aux événements.',
                'billing_interval' => 'yearly',
                'price' => 139.00,
                'currency' => 'EUR',
                'features' => [
                    'Tous les avantages du Pass Essentiel Digital',
                    '6 numéros bimestriels du magazine CIOMAG (Édition PDF HD)',
                    'Accès aux Baromètres annuels & Livres Blancs',
                    'Invitation prioritaire aux Assises & Forums CIOMAG',
                    '2 mois offerts par rapport au tarif mensuel',
                ],
                'includes_print' => true,
                'is_featured' => true,
                'is_active' => true,
                'display_order' => 2,
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'pass-corporate-dsi'],
            [
                'name' => 'Pass Corporate Direction IT (5 Licences)',
                'badge' => 'Entreprise & Comité',
                'description' => 'Conçu pour les Directions des Systèmes d\'Information souhaitant équiper leurs directeurs et chefs de département.',
                'billing_interval' => 'yearly',
                'price' => 490.00,
                'currency' => 'EUR',
                'features' => [
                    'Jusqu\'à 5 comptes nominatifs Premium inclus',
                    'Envoi des exemplaires papier au siège de l\'entreprise',
                    'Accès complet aux études sectorielles et benchmarks salaires',
                    'Accréditations VIP aux événements annuels CIOMAG',
                    'Facturation entreprise dédiée',
                ],
                'includes_print' => true,
                'is_featured' => false,
                'is_active' => true,
                'display_order' => 3,
            ]
        );

        if ($admin) {
            UserSubscription::updateOrCreate(
                ['user_id' => $admin->id, 'plan_id' => $planYearly->id],
                [
                    'status' => 'active',
                    'amount_paid' => 139.00,
                    'currency' => 'EUR',
                    'payment_method' => 'card',
                    'payment_reference' => 'SUB-CIOMAG-2026-001',
                    'starts_at' => now()->subDays(15),
                    'expires_at' => now()->addYear(),
                ]
            );

            // Création d'un grand dossier exclusif Premium
            $premiumPost = Post::updateOrCreate(
                ['type' => PostType::Post, 'slug' => 'benchmark-exclusif-budgets-it-cyber-dsi-2027'],
                [
                    'author_id' => $admin->id,
                    'status' => PostStatus::Published,
                    'title' => '[Exclusif Abonnés] Benchmark 2027 des Budgets IT, Cloud Souverain & Rémunérations des DSI',
                    'excerpt' => 'Notre enquête confidentielle menée auprès de 180 Directions des Systèmes d\'Information révèle les arbitrages budgétaires majeurs entre IA générative, cybersécurité et souveraineté des infrastructures.',
                    'content' => '<p>Ce rapport exclusif réservé aux abonnés CIOMAG compile six mois d\'entretiens confidentiels avec les DSI, RSSI et Chief Data Officers des plus grands groupes bancaires, télécoms, industriels et administrations publiques.</p><p>Dans un contexte de rationalisation FinOps et de durcissement réglementaire, 68 % des dirigeants interrogés prévoient d\'augmenter de plus de 18 % leur enveloppe consacrée à la cyber-résilience et à la souveraineté des données critiques.</p><h2>1. Répartition des investissements IT en 2027</h2><p>L\'analyse détaillée par secteur montre que la banque-assurance consacre désormais 14,5 % de son budget IT global à la sécurité opérationnelle (SOC, XDR, Zero Trust) et à la conformité réglementaire.</p><h2>2. Grille comparative des rémunérations des DSI, CISO et Chief AI Officers</h2><p>Les profils capables d\'orchestrer l\'industrialisation de l\'IA générative tout en garantissant la gouvernance des données enregistrent une progression salariale médiane de 16 % sur douze mois.</p><blockquote>« La souveraineté numérique n\'est plus un slogan politique : c\'est devenu une clause contractuelle d\'audit de résilience exigée par les conseils d\'administration. »</blockquote><p>Retrouvez dans les tableaux ci-dessus l\'intégralité des indicateurs par pays et par taille d\'organisation.</p>',
                    'content_format' => 'html',
                    'is_featured' => true,
                    'is_premium' => true,
                    'allow_comments' => true,
                    'view_count' => 3420,
                    'published_at' => now()->subHours(6),
                    'seo_meta' => [
                        'meta_title' => 'Benchmark Exclusif Budgets IT & DSI 2027 | CIOMAG Premium',
                        'meta_description' => 'Enquête exclusive réservée aux abonnés CIOMAG sur les budgets IT et cyber 2027.',
                        'og_image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
                    ],
                ]
            );

            if ($catIa) {
                $premiumPost->categories()->syncWithoutDetaching([$catIa->id => ['is_primary' => true]]);
            }
        }

        // ==========================================
        // 2. BOUTIQUE (MAGAZINES, LIVRES, RAPPORTS)
        // ==========================================
        $prod1 = Product::updateOrCreate(
            ['slug' => 'magazine-ciomag-n112-ia-souveraine-dsi'],
            [
                'title' => 'Magazine CIOMAG N° 112 — L\'Ère de l\'IA Souveraine & des Infrastructures Critiques',
                'subtitle' => 'Édition Bimestrielle Septembre / Octobre 2026 (Papier + PDF HD)',
                'type' => 'magazine',
                'issue_number' => 'N° 112',
                'author_name' => 'Rédaction CIOMAG',
                'excerpt' => 'Dossier spécial de 96 pages : interviews exclusives de 25 DSI, cartographie des Data Centers Tier III/IV et retours d\'expérience GenAI en production.',
                'description' => '<p>Dans ce numéro exceptionnel de 96 pages, la rédaction de CIOMAG dresse le panorama complet de l\'industrialisation de l\'Intelligence Artificielle et des infrastructures souveraines.</p><ul><li>Grand Entretien : Comment bâtir un Cloud national résilient ?</li><li>Enquête : FinOps et maîtrise des coûts énergétiques des Data Centers</li><li>Baromètre : Les 50 projets de transformation digitale de l\'année</li></ul>',
                'price' => 19.00,
                'compare_at_price' => 25.00,
                'currency' => 'EUR',
                'stock' => 250,
                'is_digital' => true,
                'cover_url' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=900&q=80',
                'pages_count' => 96,
                'publication_date' => now()->subDays(10),
                'status' => 'published',
                'is_featured' => true,
                'seo_meta' => ['meta_title' => 'Magazine CIOMAG N° 112 | Boutique Officielle'],
            ]
        );

        Product::updateOrCreate(
            ['slug' => 'magazine-ciomag-n111-cybersecurite-zero-trust'],
            [
                'title' => 'Magazine CIOMAG N° 111 — Cybersécurité, SOC & Architecture Zero Trust',
                'subtitle' => 'Édition Bimestrielle Juillet / Août 2026',
                'type' => 'magazine',
                'issue_number' => 'N° 111',
                'author_name' => 'Rédaction CIOMAG',
                'excerpt' => 'Face à l\'industrialisation des cyberattaques par IA, comment les RSSI restructurent la défense des banques, opérateurs télécoms et hôpitaux.',
                'description' => '<p>Un numéro de référence dédié aux RSSI, CISO et Directeurs des Opérations IT sur la résilience cyber et la gestion de crise.</p>',
                'price' => 19.00,
                'compare_at_price' => null,
                'currency' => 'EUR',
                'stock' => 180,
                'is_digital' => true,
                'cover_url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=900&q=80',
                'pages_count' => 88,
                'publication_date' => now()->subDays(65),
                'status' => 'published',
                'is_featured' => false,
                'seo_meta' => [],
            ]
        );

        Product::updateOrCreate(
            ['slug' => 'livre-guide-strategique-dsi-transformation-digitale'],
            [
                'title' => 'Le Guide Stratégique du DSI : Gouvernance, Cloud & IA (Édition 2026)',
                'subtitle' => 'Ouvrage de référence — Collection Dirigeants IT (320 pages)',
                'type' => 'book',
                'issue_number' => 'Édition 2026',
                'author_name' => 'Collectif des Chroniqueurs & Experts CIOMAG',
                'excerpt' => 'Un ouvrage méthodologique de 320 pages réunissant les meilleures pratiques d\'urbanisation du SI, de conduite du changement et de négociation avec les éditeurs.',
                'description' => '<p>Fruit de la collaboration entre 15 DSI en exercice et chercheurs en systèmes d\'information, cet ouvrage accompagne les décideurs dans leurs feuilles de route à 3 et 5 ans.</p>',
                'price' => 39.00,
                'compare_at_price' => 49.00,
                'currency' => 'EUR',
                'stock' => 120,
                'is_digital' => false,
                'cover_url' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=900&q=80',
                'pages_count' => 320,
                'publication_date' => now()->subDays(30),
                'status' => 'published',
                'is_featured' => true,
                'seo_meta' => [],
            ]
        );

        Product::updateOrCreate(
            ['slug' => 'rapport-barometre-cloud-souverain-data-centers-2026'],
            [
                'title' => 'Étude & Livre Blanc : Panorama des Data Centers & Cloud Souverain 2026-2030',
                'subtitle' => 'Rapport exécutif PDF + Tableaux de données comparatifs',
                'type' => 'report',
                'issue_number' => 'Étude Annuelle',
                'author_name' => 'Cellule Recherche & Data CIOMAG',
                'excerpt' => 'Cartographie détaillée des capacités d\'hébergement, coûts d\'interconnexion, normes Tier III/IV et stratégies multi-cloud.',
                'description' => '<p>Rapport complet destiné aux directions générales, DSI et investisseurs d\'infrastructures numériques.</p>',
                'price' => 59.00,
                'compare_at_price' => 75.00,
                'currency' => 'EUR',
                'stock' => 999,
                'is_digital' => true,
                'cover_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=900&q=80',
                'pages_count' => 142,
                'publication_date' => now()->subDays(5),
                'status' => 'published',
                'is_featured' => false,
                'seo_meta' => [],
            ]
        );

        Order::updateOrCreate(
            ['order_number' => 'CMD-2026-0001'],
            [
                'user_id' => $admin?->id,
                'customer_name' => 'Jean-Marc Kouassi',
                'customer_email' => 'jm.kouassi@banque-atlantique.org',
                'customer_phone' => '+225 07 08 09 10 11',
                'customer_company' => 'Groupe Financier Atlantique',
                'shipping_address' => 'Plateau, Avenue de la République, Abidjan',
                'total_amount' => 58.00,
                'currency' => 'EUR',
                'payment_method' => 'card',
                'status' => 'paid',
                'items' => [
                    [
                        'product_id' => $prod1->id,
                        'title' => $prod1->title,
                        'type' => 'magazine',
                        'quantity' => 1,
                        'unit_price' => 19.00,
                    ],
                ],
            ]
        );

        // ==========================================
        // 3. ÉVÉNEMENTS (COMPTE À REBOURS, PROGRAMME, COMITÉ, PARTENAIRES)
        // ==========================================
        $event1 = Event::updateOrCreate(
            ['slug' => 'assises-transformation-digitale-cybersecurite-2026'],
            [
                'title' => 'Les Grandes Assises de la Transformation Digitale & de la Cybersécurité',
                'subtitle' => 'Le rendez-vous annuel des DSI, RSSI, CDO et Décideurs Publics',
                'event_type' => 'Sommet & Forum International',
                'excerpt' => 'Deux journées de conférences plénières, tables rondes exécutives, ateliers retours d\'expérience et networking VIP autour de l\'IA souveraine et de la cyber-résilience.',
                'description' => '<p>Organisées par <strong>CIOMAG</strong>, Les Grandes Assises réunissent plus de 400 dirigeants IT, ministres du numérique, régulateurs, RSSI et grands éditeurs technologiques.</p><p>Au programme de cette édition : l\'industrialisation sécurisée de l\'IA générative, l\'interconnexion des infrastructures Cloud souveraines, la conformité Zero Trust et le financement des projets structurants.</p>',
                'venue' => 'Sofitel Hôtel Ivoire — Palais des Congrès',
                'city' => 'Abidjan',
                'country' => 'Côte d\'Ivoire',
                'is_online' => true,
                'online_url' => 'https://live.ciomag.com/assises-2026',
                'starts_at' => now()->addDays(32)->setTime(9, 0, 0),
                'ends_at' => now()->addDays(33)->setTime(18, 0, 0),
                'cover_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1400&q=80',
                'capacity' => 450,
                'ticket_price' => 0,
                'currency' => 'EUR',
                'status' => 'published',
                'is_featured' => true,
                'registration_open' => true,
                'program' => [
                    [
                        'time' => '08:30 - 09:15',
                        'title' => 'Accueil des Délégations VIP & Café Networking des DSI',
                        'speaker' => 'Comité d\'Organisation CIOMAG',
                        'role' => 'Ouverture Officielle',
                        'track' => 'Auditorium Principal',
                        'description' => 'Remise des badges exécutifs et accueil protocolaire des délégations.',
                    ],
                    [
                        'time' => '09:15 - 10:45',
                        'title' => 'Panel Inaugural : Souveraineté Numérique, Cloud & Infrastructures Critiques à l\'horizon 2030',
                        'speaker' => 'Koffi Mensah, Amina Diallo & Ministres invités',
                        'role' => 'Plénière Stratégique',
                        'track' => 'Auditorium Principal',
                        'description' => 'Comment conjuguer attractivité technologique, hébergement local Tier IV et indépendance stratégique ?',
                    ],
                    [
                        'time' => '11:15 - 12:45',
                        'title' => 'Table Ronde Cybersécurité : Architecture Zero Trust & Résilience des Banques et Télécoms',
                        'speaker' => 'Dr. Sarah Benali & CISO invités',
                        'role' => 'Panel Cyber & SOC',
                        'track' => 'Salle Exécutive A',
                        'description' => 'Retours d\'expérience sur la détection automatisée des menaces par IA et les exercices de gestion de crise.',
                    ],
                    [
                        'time' => '14:30 - 16:00',
                        'title' => 'Masterclass IA Générative : Passer des POC aux agents IA connectés à l\'ERP et au Core Banking',
                        'speaker' => 'Marc-Antoine Lefèvre & Directeurs Data',
                        'role' => 'Atelier Pratique DSI',
                        'track' => 'Auditorium Principal',
                        'description' => 'Architecture RAG souveraine, maîtrise des coûts d\'inférence et gouvernance des données confidentielles.',
                    ],
                    [
                        'time' => '16:30 - 18:00',
                        'title' => 'Cérémonie des Trophées du DSI de l\'Année & Cocktail de Clôture',
                        'speaker' => 'Jury International CIOMAG',
                        'role' => 'Remise des Prix',
                        'track' => 'Grand Salon Panoramique',
                        'description' => 'Célébration des projets de transformation digitale les plus innovants de l\'année.',
                    ],
                ],
                'committee' => [
                    [
                        'name' => 'Koffi Mensah',
                        'role' => 'Président du Comité d\'Organisation',
                        'company' => 'Directeur de la Publication CIOMAG',
                        'photo_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
                        'linkedin' => 'https://linkedin.com/in/koffi-mensah',
                        'twitter' => 'https://x.com/koffimensah_cio',
                        'website' => 'https://ciomag.com',
                    ],
                    [
                        'name' => 'Amina Diallo',
                        'role' => 'Directrice du Programme Scientifique',
                        'company' => 'Rédactrice en Chef Adjointe CIOMAG',
                        'photo_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80',
                        'linkedin' => 'https://linkedin.com/in/amina-diallo',
                        'twitter' => 'https://x.com/aminadiallo_tech',
                        'website' => '',
                    ],
                    [
                        'name' => 'Dr. Sarah Benali',
                        'role' => 'Responsable du Pôle Cybersécurité & Gouvernance',
                        'company' => 'Experte Cyber & Membre du Jury',
                        'photo_url' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=400&q=80',
                        'linkedin' => 'https://linkedin.com/in/sarah-benali-cyber',
                        'twitter' => 'https://x.com/sarahbenali_sec',
                        'website' => '',
                    ],
                    [
                        'name' => 'Marc-Antoine Lefèvre',
                        'role' => 'Coordinateur Relations Entreprises & Partenariats',
                        'company' => 'Club des DSI CIOMAG',
                        'photo_url' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80',
                        'linkedin' => 'https://linkedin.com/in/ma-lefevre',
                        'twitter' => 'https://x.com/malefevre_it',
                        'website' => '',
                    ],
                ],
                'partners' => [
                    [
                        'name' => 'CloudAfrica Sovereign DataCenters',
                        'tier' => 'Sponsor Diamant',
                        'logo_url' => 'https://images.unsplash.com/photo-1599305445671-ac291c95aaa9?auto=format&fit=crop&w=300&q=80',
                        'website' => 'https://example.com/cloudafrica',
                    ],
                    [
                        'name' => 'CyberShield Security Group',
                        'tier' => 'Sponsor Officiel Or',
                        'logo_url' => 'https://images.unsplash.com/photo-1614680376593-902f749f7ffc?auto=format&fit=crop&w=300&q=80',
                        'website' => 'https://example.com/cybershield',
                    ],
                    [
                        'name' => 'Fintech & OpenBanking Alliance',
                        'tier' => 'Partenaire Institutionnel',
                        'logo_url' => 'https://images.unsplash.com/photo-1560179707-f14e90ef3623?auto=format&fit=crop&w=300&q=80',
                        'website' => 'https://example.com/fintech-alliance',
                    ],
                    [
                        'name' => 'Association des DSI Francophones',
                        'tier' => 'Partenaire Écosystème',
                        'logo_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=300&q=80',
                        'website' => 'https://example.com/adsi',
                    ],
                ],
                'seo_meta' => [
                    'meta_title' => 'Grandes Assises de la Transformation Digitale 2026 | Événements CIOMAG',
                ],
            ]
        );

        Event::updateOrCreate(
            ['slug' => 'forum-ia-cloud-souverain-casablanca-2026'],
            [
                'title' => 'Forum Exécutif IA Générative, Data & Cloud Souverain',
                'subtitle' => 'Rencontre régionale des Directeurs IT, Data & Innovation',
                'event_type' => 'Forum & Table Ronde VIP',
                'excerpt' => 'Une journée d\'échanges opérationnels sur le déploiement des LLM souverains, l\'automatisation métier et la gouvernance des données sensibles.',
                'description' => '<p>Un format exclusif limité à 150 décideurs IT pour partager les retours d\'expérience concrets sur l\'IA en entreprise.</p>',
                'venue' => 'Hyatt Regency — Centre de Conférences',
                'city' => 'Casablanca',
                'country' => 'Maroc',
                'is_online' => true,
                'online_url' => 'https://live.ciomag.com/forum-casablanca',
                'starts_at' => now()->addDays(68)->setTime(9, 30, 0),
                'ends_at' => now()->addDays(68)->setTime(17, 30, 0),
                'cover_url' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1400&q=80',
                'capacity' => 150,
                'ticket_price' => 0,
                'currency' => 'EUR',
                'status' => 'published',
                'is_featured' => false,
                'registration_open' => true,
                'program' => [
                    [
                        'time' => '09:30 - 11:00',
                        'title' => 'Keynote : Gouvernance de l\'IA et Souveraineté des Modèles de Langage',
                        'speaker' => 'Amina Diallo & Panel DSI',
                        'role' => 'Keynote d\'ouverture',
                        'track' => 'Salle Plénière',
                        'description' => 'État de l\'art des déploiements IA privés et hybrides.',
                    ],
                ],
                'committee' => [
                    [
                        'name' => 'Amina Diallo',
                        'role' => 'Modératrice Générale',
                        'company' => 'CIOMAG',
                        'photo_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80',
                        'linkedin' => 'https://linkedin.com/in/amina-diallo',
                        'twitter' => 'https://x.com/aminadiallo_tech',
                        'website' => '',
                    ],
                ],
                'partners' => [
                    [
                        'name' => 'MedCloud DataCenters',
                        'tier' => 'Sponsor Principal',
                        'logo_url' => 'https://images.unsplash.com/photo-1599305445671-ac291c95aaa9?auto=format&fit=crop&w=300&q=80',
                        'website' => 'https://example.com',
                    ],
                ],
                'seo_meta' => [],
            ]
        );

        EventRegistration::updateOrCreate(
            ['event_id' => $event1->id, 'email' => 'dsi@telecom-souverain.org'],
            [
                'ticket_code' => 'CIO-EVT-2026-8841',
                'full_name' => 'Ibrahim Touré',
                'phone' => '+221 77 123 45 67',
                'company' => 'Consortium Télécom & Data',
                'job_title' => 'Directeur des Systèmes d\'Information (DSI)',
                'country' => 'Sénégal',
                'attendance_mode' => 'in_person',
                'status' => 'confirmed',
                'notes' => 'Participation à la table ronde Cloud Souverain.',
            ]
        );

        // ==========================================
        // 4. PODCASTS & WEBTV (YOUTUBE + UPLOAD PLATEFORME)
        // ==========================================
        Podcast::updateOrCreate(
            ['slug' => 'ia-generative-comment-les-dsi-industrialisent-les-cas-usage'],
            [
                'title' => 'IA Générative : Comment les DSI passent des POC à l\'industrialisation à grande échelle',
                'season_number' => 2,
                'episode_number' => 14,
                'excerpt' => 'Dans ce nouvel épisode du Podcast CIOMAG, nous recevons deux DSI du secteur bancaire et énergétique pour décrypter l\'architecture RAG, la sécurité des prompts et le ROI réel de l\'IA.',
                'description' => '<p>Au micro de la rédaction CIOMAG, nos invités partagent sans filtre leurs succès et les pièges évités lors du déploiement d\'assistants IA internes connectés aux bases documentaires de l\'entreprise.</p><p>Points abordés : choix entre modèles propriétaires et open-weights (Mistral, Llama), hébergement sur GPU locaux vs Cloud, et formation des équipes métiers.</p>',
                'source_type' => 'youtube',
                'youtube_url' => 'https://www.youtube.com/watch?v=aircAruvnKk',
                'youtube_video_id' => 'aircAruvnKk',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=1200&q=80',
                'duration_label' => '38:15',
                'guest_name' => 'Dr. Amadou Koné & Claire Vallet',
                'guest_role' => 'DSI Groupe Bancaire & Chief AI Officer',
                'host_name' => 'Amina Diallo (CIOMAG)',
                'category_id' => $catIa?->id,
                'status' => 'published',
                'is_featured' => true,
                'view_count' => 1840,
                'social_distribution' => [
                    'youtube' => ['enabled' => true, 'status' => 'synced'],
                    'linkedin' => ['enabled' => true, 'status' => 'ready'],
                    'facebook' => ['enabled' => true, 'status' => 'ready'],
                    'twitter' => ['enabled' => true, 'status' => 'ready'],
                ],
                'published_at' => now()->subDays(3),
            ]
        );

        Podcast::updateOrCreate(
            ['slug' => 'cyber-resilience-soc-et-zero-trust-face-aux-ransomwares'],
            [
                'title' => 'Cyber-Résilience : Bâtir un SOC nouvelle génération face aux attaques automatisées',
                'season_number' => 2,
                'episode_number' => 13,
                'excerpt' => 'Comment réduire le temps moyen de détection (MTTD) et orchestrer la réponse à incident sur des infrastructures critiques distribuées ?',
                'description' => '<p>Échange approfondi sur la modernisation des centres d\'opérations de sécurité (SOC), l\'adoption du modèle Zero Trust et les retours d\'expérience d\'exercices Red Team.</p>',
                'source_type' => 'youtube',
                'youtube_url' => 'https://www.youtube.com/watch?v=bBC-nXj3Ng4',
                'youtube_video_id' => 'bBC-nXj3Ng4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1200&q=80',
                'duration_label' => '42:10',
                'guest_name' => 'Youssef El Amrani',
                'guest_role' => 'CISO / Directeur de la Cybersécurité Groupe',
                'host_name' => 'Koffi Mensah (CIOMAG)',
                'category_id' => $catCyber?->id,
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 1290,
                'social_distribution' => [
                    'youtube' => ['enabled' => true, 'status' => 'synced'],
                ],
                'published_at' => now()->subDays(11),
            ]
        );

        Podcast::updateOrCreate(
            ['slug' => 'cloud-souverain-data-centers-tier-iv-enjeux-energetiques'],
            [
                'title' => 'Data Centers Tier IV & Cloud Souverain : L\'équation énergétique et réglementaire',
                'season_number' => 2,
                'episode_number' => 12,
                'excerpt' => 'Pourquoi l\'autonomie énergétique et la connectivité sous-marine redessinent la carte de l\'hébergement souverain.',
                'description' => '<p>Analyse des investissements massifs dans les infrastructures de données et des critères d\'audit pour les secteurs régulés.</p>',
                'source_type' => 'youtube',
                'youtube_url' => 'https://www.youtube.com/watch?v=3c-iBn73dDE',
                'youtube_video_id' => '3c-iBn73dDE',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                'duration_label' => '31:45',
                'guest_name' => 'Nadia Ouédraogo',
                'guest_role' => 'Directrice Générale Infrastructures & Cloud',
                'host_name' => 'Koffi Mensah (CIOMAG)',
                'category_id' => $catIa?->id,
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 980,
                'social_distribution' => [
                    'youtube' => ['enabled' => true, 'status' => 'synced'],
                ],
                'published_at' => now()->subDays(19),
            ]
        );

        Podcast::updateOrCreate(
            ['slug' => 'table-ronde-exclusive-fintech-open-banking-studio-ciomag'],
            [
                'title' => 'Studio Live CIOMAG : Open Banking & Interopérabilité des Paiements Instantanés',
                'season_number' => 2,
                'episode_number' => 11,
                'excerpt' => 'Émission spéciale enregistrée et hébergée directement sur la plateforme vidéo CIOMAG avec les acteurs de la FinTech.',
                'description' => '<p>Épisode hébergé nativement sur le serveur média CIOMAG et prêt pour la multi-diffusion API vers YouTube, Facebook, LinkedIn et X.</p>',
                'source_type' => 'upload',
                'video_file_path' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
                'duration_label' => '27:30',
                'guest_name' => 'Cheikh Tidiane Sy',
                'guest_role' => 'Directeur Monétique & API Banking',
                'host_name' => 'Amina Diallo (CIOMAG)',
                'category_id' => $catCyber?->id,
                'status' => 'published',
                'is_featured' => false,
                'view_count' => 760,
                'social_distribution' => [
                    'youtube' => ['enabled' => true, 'status' => 'ready'],
                    'facebook' => ['enabled' => true, 'status' => 'ready'],
                    'linkedin' => ['enabled' => true, 'status' => 'ready'],
                    'twitter' => ['enabled' => true, 'status' => 'ready'],
                ],
                'published_at' => now()->subDays(25),
            ]
        );

        // ==========================================
        // 5. PARAMÉTRAGE DES RÉSEAUX SOCIAUX & HISTORIQUE DE DIFFUSION
        // ==========================================
        $socialService = app(SocialPublisherService::class);
        $socialService->saveConfig([
            'mode' => 'sandbox',
            'auto_publish_on_post' => false,
        ], $admin?->id);

        $firstPost = Post::where('slug', 'ia-generative-dsi-africains-2026')->first();
        if ($firstPost && SocialShare::count() === 0) {
            $socialService->publishPost($firstPost, ['linkedin', 'twitter', 'facebook'], null, $admin?->id);
        }

        // ==========================================
        // 6. AJOUT DES LIENS AU MENU PRINCIPAL (SI ABSENTS)
        // ==========================================
        $headerMenu = Menu::where('location', 'header_main')->first();
        if ($headerMenu) {
            $extraLinks = [
                ['label' => 'Podcasts & WebTV', 'url' => '/podcasts', 'order' => 10],
                ['label' => 'Événements', 'url' => '/events', 'order' => 11],
                ['label' => 'Boutique', 'url' => '/shop', 'order' => 12],
                ['label' => 'Abonnement Premium', 'url' => '/subscriptions', 'order' => 13],
            ];

            foreach ($extraLinks as $link) {
                MenuItem::updateOrCreate(
                    ['menu_id' => $headerMenu->id, 'custom_url' => $link['url']],
                    [
                        'label' => $link['label'],
                        'type' => MenuItemType::CustomUrl,
                        'display_order' => $link['order'],
                        'is_active' => true,
                    ]
                );
            }
        }

        app(MenuResolverService::class)->clearAllMenuCaches();
    }
}

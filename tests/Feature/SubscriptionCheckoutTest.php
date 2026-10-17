<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Mail\SubscriptionOtpMail;
use App\Models\EmailOtpCode;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->plan = SubscriptionPlan::where('slug', 'abonnement-executif-annuel')->first()
            ?? SubscriptionPlan::firstOrFail();
    }

    public function test_subscription_index_displays_plans_with_links_to_checkout_page(): void
    {
        $response = $this->get('/subscriptions');

        $response->assertStatus(200);
        $response->assertSee('Choisir cette formule');
        $response->assertSee(route('magazine.subscriptions.subscribe.show', $this->plan->slug));
    }

    public function test_subscription_checkout_page_loads_with_plan_information(): void
    {
        $response = $this->get(route('magazine.subscriptions.subscribe.show', $this->plan->slug));

        $response->assertStatus(200);
        $response->assertSee($this->plan->name);
        $response->assertSee('Termes et Conditions Générales d\'Abonnement', false);
        $response->assertSee('Vos Informations Personnelles & Professionnelles', false);
        $response->assertSee('Vérification de votre Email', false);
        $response->assertSee('Règlement Sécurisé de votre Abonnement', false);
    }

    public function test_step3_sends_otp_and_creates_pending_user(): void
    {
        Mail::fake();

        $response = $this->postJson(route('magazine.subscriptions.otp.send'), [
            'plan_id' => $this->plan->id,
            'display_name' => 'Koffi Assoumou',
            'email' => 'koffi.assoumou@entreprise.ci',
            'password' => 'secretPass123',
            'company' => 'Banque Ouest-Africaine',
            'job_title' => 'Directeur des Systèmes d\'Information',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'email' => 'koffi.assoumou@entreprise.ci',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'koffi.assoumou@entreprise.ci',
            'display_name' => 'Koffi Assoumou',
            'status' => UserStatus::Pending->value,
        ]);

        $this->assertDatabaseHas('email_otp_codes', [
            'email' => 'koffi.assoumou@entreprise.ci',
            'purpose' => 'subscription_verification',
        ]);

        Mail::assertSent(SubscriptionOtpMail::class, function (SubscriptionOtpMail $mail) {
            return $mail->hasTo('koffi.assoumou@entreprise.ci')
                && strlen($mail->code) === 6;
        });
    }

    public function test_step4_verifies_otp_activates_user_and_authenticates(): void
    {
        $email = 'fatou.traore@fintech.sn';

        $role = Role::firstOrCreate(
            ['slug' => 'subscriber'],
            ['name' => 'Lecteur Abonné', 'permissions' => ['comments.create']]
        );

        $user = User::create([
            'role_id' => $role->id,
            'email' => $email,
            'username' => 'fatou-traore',
            'password_hash' => bcrypt('password123'),
            'display_name' => 'Fatou Traoré',
            'status' => UserStatus::Pending,
        ]);

        $otp = EmailOtpCode::generateFor(
            email: $email,
            userId: $user->id,
            purpose: 'subscription_verification'
        );

        // 1. Invalid OTP code rejected with 422
        $invalidResponse = $this->postJson(route('magazine.subscriptions.otp.verify'), [
            'email' => $email,
            'code' => '000000',
        ]);

        $invalidResponse->assertStatus(422);
        $invalidResponse->assertJson(['success' => false]);
        $this->assertFalse(Auth::check());

        // 2. Valid OTP code accepted
        $validResponse = $this->postJson(route('magazine.subscriptions.otp.verify'), [
            'email' => $email,
            'code' => $otp->code,
        ]);

        $validResponse->assertStatus(200);
        $validResponse->assertJson([
            'success' => true,
            'user' => [
                'email' => $email,
            ],
        ]);

        $user->refresh();
        $this->assertEquals(UserStatus::Active, $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_step5_and_step6_processes_payment_success(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson(route('magazine.subscriptions.payment.process'), [
            'plan_id' => $this->plan->id,
            'payment_method' => 'card',
            'card_name' => 'MARC KOUADIO',
            'card_number' => '4000 1234 5678 9010',
            'card_expiry' => '12/28',
            'card_cvc' => '123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'subscription' => [
                'plan_name' => $this->plan->name,
                'user_name' => $user->display_name,
            ],
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'payment_method' => 'card',
        ]);

        $user->refresh();
        $this->assertTrue($user->hasActiveSubscription());
        $this->assertTrue($user->canReadPremiumContent());
    }

    public function test_step5_and_step6_payment_failure_allows_going_back_and_retrying(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        // Simuler un échec de paiement (e.g. carte refusée ou simulate_failure: true)
        $failureResponse = $this->actingAs($user)->postJson(route('magazine.subscriptions.payment.process'), [
            'plan_id' => $this->plan->id,
            'payment_method' => 'card',
            'card_name' => 'MARC KOUADIO',
            'card_number' => '4000 0000 0000 0002', // Standard test declined card
            'simulate_failure' => true,
        ]);

        $failureResponse->assertStatus(422);
        $failureResponse->assertJson([
            'success' => false,
            'error_code' => 'payment_declined',
        ]);

        // Pas d'abonnement actif créé
        $this->assertDatabaseMissing('user_subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
        ]);

        // L'utilisateur reste authentifié et peut réessayer avec un moyen valide (Mobile Money par exemple)
        $retryResponse = $this->actingAs($user)->postJson(route('magazine.subscriptions.payment.process'), [
            'plan_id' => $this->plan->id,
            'payment_method' => 'mobile_money',
            'mobile_operator' => 'Orange Money',
            'mobile_phone' => '+225 07 12 34 56 78',
            'simulate_failure' => false,
        ]);

        $retryResponse->assertStatus(200);
        $retryResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'payment_method' => 'mobile_money',
            'status' => 'active',
        ]);
    }

    public function test_resend_otp_generates_fresh_code(): void
    {
        Mail::fake();

        $email = 'ousmane.diallo@telecoms.gn';

        $response = $this->postJson(route('magazine.subscriptions.otp.resend'), [
            'email' => $email,
            'plan_id' => $this->plan->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('email_otp_codes', [
            'email' => $email,
            'purpose' => 'subscription_verification',
        ]);

        Mail::assertSent(SubscriptionOtpMail::class, 1);
    }
}

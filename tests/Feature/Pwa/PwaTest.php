<?php

declare(strict_types=1);

namespace Tests\Feature\Pwa;

use Tests\TestCase;
use App\Models\User;
use App\Domains\Pwa\Models\PushSubscription;
use App\Domains\Pwa\Services\PushNotificationService;
use Spatie\Permission\Models\Role;

class PwaTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'user']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');
    }

    public function test_manifest_is_accessible(): void
    {
        $response = $this->get('/manifest.json');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('application/manifest+json', $response->header('Content-Type'));
    }

    public function test_manifest_contains_required_fields(): void
    {
        $response = $this->get('/manifest.json');

        $data = $response->json();

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('short_name', $data);
        $this->assertArrayHasKey('display', $data);
        $this->assertArrayHasKey('theme_color', $data);
        $this->assertArrayHasKey('icons', $data);
    }

    public function test_offline_page_is_accessible(): void
    {
        $response = $this->get('/offline');

        $this->assertEquals(503, $response->status());
        $this->assertStringContainsString('Vous êtes hors ligne', $response->getContent());
    }

    public function test_ping_endpoint(): void
    {
        $response = $this->get('/ping');

        $this->assertEquals(200, $response->status());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_service_worker_is_registered(): void
    {
        $response = $this->get('/sw.js');

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('Service Worker', $response->getContent());
    }

    public function test_pwa_manager_script_exists(): void
    {
        $response = $this->get('/js/pwa-manager.js');

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('PWAManager', $response->getContent());
    }

    public function test_subscribe_to_push_notifications(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
            'keys' => [
                'p256dh' => 'test-p256dh-key',
                'auth' => 'test-auth-key',
            ],
        ]);

        $this->assertEquals(201, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $this->user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        ]);
    }

    public function test_duplicate_subscription_is_updated(): void
    {
        $endpoint = 'https://fcm.googleapis.com/fcm/send/test-endpoint';

        // Première souscription
        $this->actingAs($this->user)->postJson('/api/push/subscribe', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'key1', 'auth' => 'auth1'],
        ]);

        // Deuxième souscription avec le même endpoint
        $this->actingAs($this->user)->postJson('/api/push/subscribe', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'key2', 'auth' => 'auth2'],
        ]);

        $subscriptions = PushSubscription::where('user_id', $this->user->id)->get();
        $this->assertEquals(1, $subscriptions->count());
    }

    public function test_unsubscribe_from_push_notifications(): void
    {
        $endpoint = 'https://fcm.googleapis.com/fcm/send/test-endpoint';

        // Créer une souscription
        $this->actingAs($this->user)->postJson('/api/push/subscribe', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'key', 'auth' => 'auth'],
        ]);

        // Se désabonner
        $response = $this->actingAs($this->user)->postJson('/api/push/unsubscribe', [
            'endpoint' => $endpoint,
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertTrue($response->json('success'));

        $this->assertDatabaseMissing('push_subscriptions', [
            'user_id' => $this->user->id,
            'endpoint' => $endpoint,
        ]);
    }

    public function test_get_user_subscriptions(): void
    {
        // Créer deux souscriptions
        $this->actingAs($this->user)->postJson('/api/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/endpoint1',
            'keys' => ['p256dh' => 'key1', 'auth' => 'auth1'],
        ]);

        $this->actingAs($this->user)->postJson('/api/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/endpoint2',
            'keys' => ['p256dh' => 'key2', 'auth' => 'auth2'],
        ]);

        // Récupérer les souscriptions
        $response = $this->actingAs($this->user)->getJson('/api/push/subscriptions');

        $this->assertEquals(200, $response->status());
        $this->assertEquals(2, $response->json('count'));
    }

    public function test_unauthenticated_user_cannot_subscribe(): void
    {
        $response = $this->postJson('/api/push/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test',
            'keys' => ['p256dh' => 'key', 'auth' => 'auth'],
        ]);

        $this->assertEquals(401, $response->status());
    }

    public function test_push_notification_service_subscribe(): void
    {
        $service = app(PushNotificationService::class);

        $subscription = $service->subscribe($this->user, 'https://example.com/push', [
            'p256dh' => 'test-key',
            'auth' => 'test-auth',
        ]);

        $this->assertNotNull($subscription);
        $this->assertEquals($this->user->id, $subscription->user_id);
        $this->assertTrue($subscription->is_active);
    }

    public function test_push_notification_service_unsubscribe(): void
    {
        $service = app(PushNotificationService::class);

        // Créer une souscription
        $service->subscribe($this->user, 'https://example.com/push', [
            'p256dh' => 'test-key',
            'auth' => 'test-auth',
        ]);

        // Se désabonner
        $unsubscribed = $service->unsubscribe($this->user, 'https://example.com/push');

        $this->assertTrue($unsubscribed);
    }

    public function test_cart_sync_offline(): void
    {
        // Simuler l'ajout d'un item au panier hors ligne
        $item = [
            'product_id' => 1,
            'quantity' => 2,
            'price' => 29.99,
        ];

        // Cette logique est gérée par le service worker
        // On teste juste que l'endpoint existe
        $response = $this->actingAs($this->user)->postJson('/api/cart/add', $item);

        $this->assertEquals(201, $response->status());
    }

    public function test_offline_page_detects_connection(): void
    {
        $response = $this->get('/offline');

        $this->assertStringContainsString('checkConnection', $response->getContent());
        $this->assertStringContainsString('navigator.onLine', $response->getContent());
    }
}

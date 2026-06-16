<?php

namespace Tests\Feature;

use App\Models\ZohoCrmFunctionEndpoint;
use Database\Seeders\ZohoCrmFunctionEndpointsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZohoCrmFunctionEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_inserts_expected_slugs(): void
    {
        $this->seed(ZohoCrmFunctionEndpointsSeeder::class);

        $this->assertDatabaseHas('zoho_crm_function_endpoints', [
            'slug' => ZohoCrmFunctionEndpoint::SLUG_GET_CURRENT_SUBSCRIPTION_STATUS,
        ]);
        $this->assertDatabaseHas('zoho_crm_function_endpoints', [
            'slug' => ZohoCrmFunctionEndpoint::SLUG_GET_SUBSCRIPTION_RELATED_INVOICES,
        ]);
        $this->assertDatabaseHas('zoho_crm_function_endpoints', [
            'slug' => ZohoCrmFunctionEndpoint::SLUG_GET_SUBSCRIPTION_RELATED_PAYMENTS,
        ]);

        $this->assertSame(6, ZohoCrmFunctionEndpoint::query()->count());
    }

    public function test_build_signed_execute_url_appends_per_function_zapikey_from_config(): void
    {
        $this->seed(ZohoCrmFunctionEndpointsSeeder::class);
        config([
            'heroportal.zoho_crm_function_api_keys' => [
                ZohoCrmFunctionEndpoint::SLUG_GET_CURRENT_SUBSCRIPTION_STATUS => 'test-key-status-only',
            ],
        ]);

        $row = ZohoCrmFunctionEndpoint::query()
            ->where('slug', ZohoCrmFunctionEndpoint::SLUG_GET_CURRENT_SUBSCRIPTION_STATUS)
            ->firstOrFail();

        $url = $row->buildSignedExecuteUrl();
        $this->assertIsString($url);
        $this->assertStringContainsString('auth_type=apikey', $url);
        $this->assertStringContainsString('zapikey=test-key-status-only', $url);
        $this->assertStringStartsWith('https://www.zohoapis.com/crm/v7/functions/get_current_subscription_status/actions/execute', $url);
    }

    public function test_build_signed_execute_url_returns_null_when_slug_has_no_zapikey(): void
    {
        $this->seed(ZohoCrmFunctionEndpointsSeeder::class);
        config(['heroportal.zoho_crm_function_api_keys' => []]);

        $row = ZohoCrmFunctionEndpoint::query()
            ->where('slug', ZohoCrmFunctionEndpoint::SLUG_CREATE_SUBSCRIPTION)
            ->firstOrFail();
        $this->assertNull($row->buildSignedExecuteUrl());
    }

    public function test_different_slugs_resolve_different_zapikeys(): void
    {
        $this->seed(ZohoCrmFunctionEndpointsSeeder::class);
        config([
            'heroportal.zoho_crm_function_api_keys' => [
                ZohoCrmFunctionEndpoint::SLUG_CREATE_SUBSCRIPTION => 'key-a',
                ZohoCrmFunctionEndpoint::SLUG_CANCEL_SUBSCRIPTION => 'key-b',
            ],
        ]);

        $create = ZohoCrmFunctionEndpoint::query()
            ->where('slug', ZohoCrmFunctionEndpoint::SLUG_CREATE_SUBSCRIPTION)
            ->firstOrFail();
        $cancel = ZohoCrmFunctionEndpoint::query()
            ->where('slug', ZohoCrmFunctionEndpoint::SLUG_CANCEL_SUBSCRIPTION)
            ->firstOrFail();

        $this->assertSame('key-a', $create->resolveZapikey());
        $this->assertSame('key-b', $cancel->resolveZapikey());
        $this->assertStringContainsString('zapikey=key-a', (string) $create->buildSignedExecuteUrl());
        $this->assertStringContainsString('zapikey=key-b', (string) $cancel->buildSignedExecuteUrl());
    }
}

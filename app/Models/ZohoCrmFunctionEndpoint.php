<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZohoCrmFunctionEndpoint extends Model
{
    public const SLUG_CREATE_SUBSCRIPTION = 'create_subscription_from_portal';

    public const SLUG_UPDATE_SUBSCRIPTION = 'update_subscription_from_portal';

    public const SLUG_CANCEL_SUBSCRIPTION = 'cancel_subscription_from_portal';

    public const SLUG_GET_CURRENT_SUBSCRIPTION_STATUS = 'get_current_subscription_status';

    public const SLUG_GET_SUBSCRIPTION_RELATED_INVOICES = 'get_subscription_related_invoices';

    public const SLUG_GET_SUBSCRIPTION_RELATED_PAYMENTS = 'get_subscription_related_payments';

    protected $fillable = [
        'slug',
        'execute_url',
        'label',
    ];

    /**
     * Zoho `zapikey` for this function (each Deluge function may use a different key).
     */
    public function resolveZapikey(): ?string
    {
        $map = config('heroportal.zoho_crm_function_api_keys', []);
        if (! is_array($map)) {
            return null;
        }

        $key = $map[$this->slug] ?? null;

        return is_string($key) && $key !== '' ? $key : null;
    }

    /**
     * Full CRM function execute URL including apikey query (server-side use only).
     */
    public function buildSignedExecuteUrl(): ?string
    {
        $key = $this->resolveZapikey();
        if ($key === null) {
            return null;
        }

        $base = rtrim((string) $this->execute_url, '?&');
        $separator = str_contains($base, '?') ? '&' : '?';

        return $base.$separator.http_build_query([
            'auth_type' => 'apikey',
            'zapikey' => $key,
        ]);
    }

    public static function signedUrlForSlug(string $slug): ?string
    {
        $row = static::query()->where('slug', $slug)->first();

        return $row?->buildSignedExecuteUrl();
    }
}

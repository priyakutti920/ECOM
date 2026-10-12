<?php

namespace App\Models;

use App\Services\WhatsAppService;

/**
 * Proxy helper for WhatsApp Fast2SMS configuration.
 * All credentials & toggles are read directly from .env / config('services.whatsapp').
 */
class WaApiSetting
{
    public static function isEngineEnabled(): bool
    {
        return !WhatsAppService::isDisabled();
    }

    public static function isCustomerNotifyEnabled(): bool
    {
        return WhatsAppService::isCustomerNotifyEnabled();
    }

    public static function getAdminPhone(): string
    {
        return WhatsAppService::getAdminPhone();
    }

    public static function getAuthKey(): string
    {
        return (string) (config('services.whatsapp.fast2sms_auth_key') ?: env('WA_FAST2SMS_AUTH_KEY', env('FAST2SMS_DEV_AUTH_KEY', '')));
    }

    public static function getPhoneNumberId(): string
    {
        return (string) (config('services.whatsapp.phone_number_id') ?: env('WA_PHONE_NUMBER_ID', ''));
    }

    public static function getSenderNumber(): string
    {
        return WhatsAppService::getSenderNumber();
    }
}

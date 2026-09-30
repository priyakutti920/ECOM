<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    private int $otpLength = 6;

    private int $expiresInMinutes = 10;

    /**
     * Generate and email an OTP to the customer.
     */
    public function generate(string $email): array
    {
        $email = $this->cleanEmail($email);

        // Wipe any prior OTPs for this email
        OtpCode::purgeOld($email);

        // Generate OTP code (6 digits, zero-padded)
        $code = str_pad((string) random_int(0, 999999), $this->otpLength, '0', STR_PAD_LEFT);

        OtpCode::create([
            'email' => $email,
            'code' => $code,
            'purpose' => 'login',
            'expires_at' => now()->addMinutes($this->expiresInMinutes),
        ]);

        $sent = $this->sendEmail($email, $code);

        Log::info("OTP generated for {$email}");

        if (! $sent) {
            return [
                'success' => false,
                'email' => $email,
                'message' => 'Unable to send OTP email at this time. Please use password login or contact support.',
            ];
        }

        return [
            'success' => true,
            'email' => $email,
            'message' => 'OTP sent successfully to '.$email,
        ];
    }

    /**
     * Verify the OTP code.
     */
    public function verify(string $email, string $code): array
    {
        $email = $this->cleanEmail($email);

        $otp = OtpCode::findValid($email, $code, 'login');

        if (! $otp) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP. Please try again.',
            ];
        }

        $otp->update(['verified_at' => now()]);

        // One-time use
        OtpCode::purgeOld($email);

        return [
            'success' => true,
            'message' => 'OTP verified successfully',
        ];
    }

    /**
     * Resend OTP — reuse the same code if one is still valid (unverified and
     * within its expiry window). Otherwise generate a fresh one.
     */
    public function resend(string $email): array
    {
        $email = $this->cleanEmail($email);

        $existing = OtpCode::latestValid($email, 'login');

        if ($existing) {
            $sent = $this->sendEmail($email, $existing->code);
            Log::info("OTP resent for {$email}");

            if (! $sent) {
                return [
                    'success' => false,
                    'message' => 'Unable to resend OTP email. Please try again later or use password login.',
                ];
            }

            return [
                'success' => true,
                'email' => $email,
                'message' => 'OTP resent successfully',
                'reused' => true,
            ];
        }

        return $this->generate($email);
    }

    /**
     * Send the OTP via SMTP using the credentials saved in store settings.
     */
    private function sendEmail(string $email, string $code): bool
    {
        $host = StoreSetting::getValue('smtp_host');
        $port = StoreSetting::getValue('smtp_port');
        $encryption = StoreSetting::getValue('smtp_encryption');
        $username = StoreSetting::getValue('smtp_username');
        $password = StoreSetting::getValue('smtp_password');
        $fromEmail = StoreSetting::getValue('smtp_from_email');
        $fromName = StoreSetting::getValue('smtp_from_name') ?: StoreSetting::getStoreName();

        if (app()->environment('testing')) {
            return true;
        }

        if (! $host || ! $username || ! $fromEmail) {
            Log::warning('SMTP not configured in admin settings. OTP not emailed.');

            return false;
        }

        // Configure isolated dynamic mailer for store SMTP
        config([
            'mail.mailers.store_smtp' => [
                'transport' => 'smtp',
                'host' => $host,
                'port' => (int) ($port ?: 587),
                'encryption' => $encryption ?: null,
                'username' => $username,
                'password' => $password,
                'timeout' => 10,
            ],
        ]);
        Mail::purge('store_smtp');

        try {
            $storeName = StoreSetting::getStoreName();
            $minutes = $this->expiresInMinutes;

            Mail::mailer('store_smtp')->send([], [], function ($message) use ($email, $code, $fromEmail, $fromName, $storeName, $minutes) {
                $message->to($email)
                    ->from($fromEmail, $fromName)
                    ->subject("Your {$storeName} login verification code")
                    ->html($this->buildHtml($code, $storeName, $minutes));
            });

            Log::info("OTP email sent to {$email}");

            return true;
        } catch (\Exception $e) {
            Log::error('OTP email send failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Render branded HTML body for the OTP email using Blade template.
     */
    private function buildHtml(string $code, string $storeName, int $minutes): string
    {
        return view('emails.otp', compact('code', 'storeName', 'minutes'))->render();
    }

    /**
     * Normalize and validate the email address.
     */
    private function cleanEmail(string $email): string
    {
        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Please enter a valid email address.');
        }

        return $email;
    }
}

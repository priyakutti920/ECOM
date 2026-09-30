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
            'email'      => $email,
            'code'       => $code,
            'purpose'    => 'login',
            'expires_at' => now()->addMinutes($this->expiresInMinutes),
        ]);

        $sent = $this->sendEmail($email, $code);

        Log::info("OTP generated for {$email}");

        if (!$sent) {
            return [
                'success' => false,
                'email'   => $email,
                'message' => 'Unable to send OTP email at this time. Please use password login or contact support.',
            ];
        }

        return [
            'success' => true,
            'email'   => $email,
            'message' => 'OTP sent successfully to ' . $email,
        ];
    }

    /**
     * Verify the OTP code.
     */
    public function verify(string $email, string $code): array
    {
        $email = $this->cleanEmail($email);

        $otp = OtpCode::findValid($email, $code, 'login');

        if (!$otp) {
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

            if (!$sent) {
                return [
                    'success' => false,
                    'message' => 'Unable to resend OTP email. Please try again later or use password login.',
                ];
            }

            return [
                'success' => true,
                'email'   => $email,
                'message' => 'OTP resent successfully',
                'reused'  => true,
            ];
        }

        return $this->generate($email);
    }

    /**
     * Send the OTP via SMTP using the credentials saved in store settings.
     */
    private function sendEmail(string $email, string $code): bool
    {
        $host       = StoreSetting::getValue('smtp_host');
        $port       = StoreSetting::getValue('smtp_port');
        $encryption = StoreSetting::getValue('smtp_encryption');
        $username   = StoreSetting::getValue('smtp_username');
        $password   = StoreSetting::getValue('smtp_password');
        $fromEmail  = StoreSetting::getValue('smtp_from_email');
        $fromName   = StoreSetting::getValue('smtp_from_name') ?: StoreSetting::getStoreName();

        if (app()->environment('testing')) {
            return true;
        }

        if (!$host || !$username || !$fromEmail) {
            Log::warning('SMTP not configured in admin settings. OTP not emailed.');
            return false;
        }

        // Override mail config for this request
        config([
            'mail.default'                 => 'smtp',
            'mail.mailers.smtp.host'       => $host,
            'mail.mailers.smtp.port'       => $port ?: 587,
            'mail.mailers.smtp.encryption' => $encryption ?: null,
            'mail.mailers.smtp.username'   => $username,
            'mail.mailers.smtp.password'   => $password,
            'mail.from.address'            => $fromEmail,
            'mail.from.name'               => $fromName,
        ]);

        try {
            $storeName = StoreSetting::getStoreName();
            $minutes   = $this->expiresInMinutes;

            Mail::send([], [], function ($message) use ($email, $code, $fromEmail, $fromName, $storeName, $minutes) {
                $message->to($email)
                    ->from($fromEmail, $fromName)
                    ->subject("Your {$storeName} login OTP: {$code}")
                    ->html($this->buildHtml($code, $storeName, $minutes));
            });

            Log::info("OTP email sent to {$email}");
            return true;
        } catch (\Exception $e) {
            Log::error('OTP email send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Simple branded HTML body for the OTP email.
     */
    private function buildHtml(string $code, string $storeName, int $minutes): string
    {
        $safeStore = htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f3f3f3;">
  <div style="max-width:480px;margin:24px auto;background:#fff;border-radius:8px;padding:32px 28px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
    <h2 style="margin:0 0 8px;color:#111;font-size:22px;">{$safeStore}</h2>
    <p style="margin:0 0 20px;color:#555;font-size:14px;">Use the code below to sign in to your account.</p>
    <div style="background:#f7f8fa;border:1px dashed #d5d9d9;border-radius:6px;padding:18px;text-align:center;margin-bottom:20px;">
      <div style="font-size:34px;font-weight:700;letter-spacing:8px;color:#0f1111;">{$code}</div>
    </div>
    <p style="margin:0 0 12px;color:#555;font-size:13px;">This code is valid for <strong>{$minutes} minutes</strong>. Do not share it with anyone.</p>
    <p style="margin:0;color:#999;font-size:12px;">If you didn't request this, you can safely ignore this email.</p>
  </div>
</body>
</html>
HTML;
    }

    /**
     * Normalize and validate the email address.
     */
    private function cleanEmail(string $email): string
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Please enter a valid email address.');
        }

        return $email;
    }
}

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset your password — {{ $storeName }}</title>
</head>
<body style="margin:0;padding:0;font-family:sans-serif;background:#f3f3f3;">
    <div style="max-width:500px;margin:24px auto;background:#fff;border-radius:8px;padding:32px 28px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <h2 style="margin:0 0 12px;color:#111;font-size:22px;">{{ $storeName }}</h2>
        <p style="margin:0 0 16px;color:#4a5568;font-size:14px;line-height:1.5;">You requested a password reset. Click the button below to choose a new password:</p>
        <p style="margin:24px 0;">
            <a href="{{ $resetUrl }}" style="background:#f97316;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;display:inline-block;">Reset Password</a>
        </p>
        <p style="margin:0;color:#718096;font-size:13px;">If you did not request this, you can safely ignore this email.</p>
    </div>
</body>
</html>

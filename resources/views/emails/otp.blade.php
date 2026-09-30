<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $storeName }} Login Verification Code</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f3f3f3;">
  <div style="max-width:480px;margin:24px auto;background:#fff;border-radius:8px;padding:32px 28px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
    <h2 style="margin:0 0 8px;color:#111;font-size:22px;">{{ $storeName }}</h2>
    <p style="margin:0 0 20px;color:#555;font-size:14px;">Use the code below to sign in to your account.</p>
    <div style="background:#f7f8fa;border:1px dashed #d5d9d9;border-radius:6px;padding:18px;text-align:center;margin-bottom:20px;">
      <div style="font-size:34px;font-weight:700;letter-spacing:8px;color:#0f1111;">{{ $code }}</div>
    </div>
    <p style="margin:0 0 12px;color:#555;font-size:13px;">This code is valid for <strong>{{ $minutes }} minutes</strong>. Do not share it with anyone.</p>
    <p style="margin:0;color:#999;font-size:12px;">If you didn't request this, you can safely ignore this email.</p>
  </div>
</body>
</html>

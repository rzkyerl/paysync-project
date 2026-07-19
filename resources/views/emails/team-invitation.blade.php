<!doctype html>
<html lang="id">
<body style="font-family:Arial,sans-serif; color:#172033; line-height:1.6;">
    <h1>Undangan PaySync</h1>
    <p>Halo {{ $member->name }},</p>
    <p>Anda diundang untuk bergabung ke workspace PaySync sebagai <strong>{{ str_replace('_', ' ', ucfirst($member->role)) }}</strong>.</p>
    <p><a href="{{ url('/invite/'.$member->invitation_token) }}" style="display:inline-block;background:#2563eb;color:#fff;padding:12px 18px;border-radius:6px;text-decoration:none;">Aktifkan Akun</a></p>
    <p>Link ini berlaku selama 7 hari. Jika Anda tidak mengenali undangan ini, abaikan email ini.</p>
</body>
</html>

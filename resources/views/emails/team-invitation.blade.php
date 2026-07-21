<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Undangan Bergabung ke PaySync</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:'Segoe UI', Arial, sans-serif; color:#1e293b;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 48px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="padding-bottom: 28px;">
                            <span style="font-family: Georgia, serif; font-size: 26px; font-weight: 700; color: #0f3473; letter-spacing: -0.5px;">PaySync</span>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background:#ffffff; border-radius:12px; padding:40px 40px 32px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                            <p style="margin:0 0 8px; font-size:13px; font-weight:600; color:#2563eb; text-transform:uppercase; letter-spacing:0.8px;">Undangan Tim</p>
                            <h1 style="margin:0 0 20px; font-size:24px; font-weight:700; color:#0f172a; line-height:1.3;">Anda Diundang Bergabung ke PaySync</h1>

                            <p style="margin:0 0 16px; font-size:15px; color:#475569; line-height:1.7;">Halo <strong style="color:#0f172a;">{{ $member->name }}</strong>,</p>

                            <p style="margin:0 0 24px; font-size:15px; color:#475569; line-height:1.7;">
                                Anda telah diundang untuk bergabung ke platform manajemen penggajian <strong style="color:#0f172a;">PaySync</strong>
                                dengan peran sebagai <strong style="color:#0f172a;">{{ ucwords(str_replace('_', ' ', $member->role)) }}</strong>.
                                Klik tombol di bawah untuk mengaktifkan akun Anda dan mulai menggunakan platform.
                            </p>

                            {{-- CTA Button --}}
                            <table cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                                <tr>
                                    <td align="center" style="border-radius:8px; background:#1d4ed8;">
                                        <a href="{{ url('/invite/'.$member->invitation_token) }}"
                                           style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none; border-radius:8px; letter-spacing:0.2px;">
                                            Aktifkan Akun Saya
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            {{-- Info box --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td style="background:#f8fafc; border-left:3px solid #2563eb; border-radius:4px; padding:14px 16px;">
                                        <p style="margin:0; font-size:13px; color:#64748b; line-height:1.6;">
                                            Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser Anda:<br>
                                            <a href="{{ url('/invite/'.$member->invitation_token) }}"
                                               style="color:#2563eb; word-break:break-all; font-size:12px;">
                                                {{ url('/invite/'.$member->invitation_token) }}
                                            </a>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; font-size:13px; color:#94a3b8; line-height:1.6;">
                                Tautan aktivasi ini berlaku selama <strong>7 hari</strong> sejak email ini dikirim.
                                Jika Anda merasa tidak mengenali undangan ini, abaikan email ini - akun Anda tidak akan dibuat.
                            </p>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding-top:28px;">
                            <p style="margin:0 0 6px; font-size:12px; color:#94a3b8;">
                                Email ini dikirim oleh <strong>PaySync</strong> &mdash; Platform Manajemen Penggajian
                            </p>
                            <p style="margin:0; font-size:12px; color:#cbd5e1;">
                                &copy; {{ date('Y') }} PaySync. Seluruh hak dilindungi.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

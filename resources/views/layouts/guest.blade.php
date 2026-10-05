<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Hacklog' }}</title>
    <style>
        :root{--color-primary:#40a090;--color-primary-dark:#008060;--color-secondary:#4080c0;--color-accent:#d09020;--color-bg:#f0f0f0;--color-bg-secondary:#e0f0f0;--color-text:#202020;--color-text-secondary:#707070;--color-border:#c0c0c0;--color-surface:#ffffff;--danger:#e08070;--shadow-low:rgba(0,0,0,.16) 0 1px 0 0;--shadow-mid:rgba(32,32,32,.14) 0 2px 10px 0}
        *{box-sizing:border-box}body{margin:0;font-family:"Open Sans","Noto Sans JP","Hiragino Kaku Gothic ProN","Yu Gothic",Meiryo,sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.75;letter-spacing:.04em;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;word-break:normal;overflow-wrap:anywhere}
        a{color:var(--color-primary-dark);text-decoration:none}a:hover{text-decoration:underline}
        .login-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:8px;padding:30px;width:min(400px,100%);box-shadow:var(--shadow-mid)}
        .brand{font-size:24px;line-height:1.35;font-weight:700;color:var(--color-primary);margin-bottom:5px;letter-spacing:0}
        .sub{color:var(--color-text-secondary);margin-bottom:25px}
        .field{display:grid;gap:5px;margin-bottom:15px}
        .field label{font-size:12px;color:var(--color-text-secondary);font-weight:700}
        input{width:100%;min-height:44px;padding:9px 10px;border:1px solid var(--color-border);border-radius:4px;background:#fff;color:var(--color-text);font:inherit;letter-spacing:inherit}
        input:focus{outline:2px solid rgba(64,160,144,.25);border-color:var(--color-primary)}
        .btn{border:0;border-radius:4px;background:var(--color-primary);color:#fff;padding:10px 15px;font-weight:700;cursor:pointer;width:100%;font:inherit;min-height:44px}
        .btn:hover{filter:brightness(.96)}
        .btn.light{background:#fff;color:var(--color-text);border:1px solid var(--color-border)}
        .alert{padding:10px 15px;border-radius:4px;background:#e0f0f0;color:#008060;margin-bottom:15px;font-size:13px;border:1px solid #90c0a0}
        .errors{background:#f0d0c0;color:#a05040;padding:10px 15px;border-radius:4px;margin-bottom:15px;font-size:13px;border:1px solid #e08070}
        .remember{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--color-text-secondary);margin-bottom:15px}
        .remember input{width:auto;min-height:auto;accent-color:var(--color-primary)}
        .links{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-top:15px;font-size:12px}
        .lead{font-size:13px;color:var(--color-text-secondary);margin:0 0 15px}
        .hint{font-size:12px;color:var(--color-text-secondary);margin:20px 0 0;text-align:center}
        .hint code{background:var(--color-bg-secondary);padding:2px 5px;border-radius:4px;font-family:Consolas,Monaco,monospace;color:var(--color-text)}
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand">Hacklog</div>
    <div class="sub">案件と開発作業を管理するツール</div>

    @if(session('status') && session('status') !== 'verification-link-sent')<div class="alert">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="errors">{{ $errors->first() }}</div>@endif

    {{ $slot }}
</div>
</body>
</html>

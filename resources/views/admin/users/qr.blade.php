<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <title>طباعة QR للموظفين</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root{--brown:#925419;--gold:#fbc417;--gold-weak:#fbc41720;--text:#222}
    *{box-sizing:border-box}
    html,body{margin:0;padding:0;background:#faf9f6;color:var(--text);font-family:system-ui,-apple-system,"Segoe UI",Roboto,Ubuntu,"Helvetica Neue","Noto Sans",Arial}
    @page{size:A4;margin:10mm}
    @media print{
      .no-print{display:none!important}
      .card{break-inside:avoid;page-break-inside:avoid}
      body{background:#fff}
    }
    .container{max-width:1100px;margin:24px auto;padding:0 16px}
    .toolbar{background:#fff;border:2px solid var(--gold);border-radius:12px;padding:10px 12px;margin-bottom:16px;display:flex;gap:10px;justify-content:space-between;align-items:center}
    .btn{appearance:none;border:0;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600}
    .btn-primary{background:var(--brown);color:#fff}
    .grid{display:grid;gap:14px;grid-template-columns:repeat(2,1fr)}
    @media(min-width:768px){.grid{grid-template-columns:repeat(3,1fr)}}
    @media(min-width:1200px){.grid{grid-template-columns:repeat(4,1fr)}}
    .card{background:#fff;border:2px dashed var(--gold);border-radius:16px;padding:14px;display:flex;flex-direction:column;align-items:center;text-align:center}
    .qr{width:100%;aspect-ratio:1/1;max-width:210px;display:flex;align-items:center;justify-content:center;background:#fff;border:1px solid #ead79a;border-radius:12px;padding:10px}
    .qr svg,.qr img{width:100%;height:100%;object-fit:contain;display:block}
    .code{margin-top:10px;display:inline-block;background:#fff7d6;border:1px solid #f3d26b;border-radius:8px;padding:6px 10px;font-family:ui-monospace,Menlo,Consolas,monospace;color:#7a4a14;font-size:14px}
    .name{margin-top:8px;font-weight:800;font-size:16px;color:var(--brown);direction:ltr;unicode-bidi:plaintext}
  </style>
</head>
<body>
  <div class="container">
    <div class="toolbar no-print">
      <div>طباعة أكواد QR — الترتيب: QR ثم الكود ثم اسم الموظف.</div>
      <button class="btn btn-primary" onclick="window.print()">طباعة</button>
    </div>

    <div class="grid">
      @foreach($users as $user)
        <div class="card">
          <div class="qr">
            {!! QrCode::format('svg')->size(210)->margin(0)->generate($user->code ?? (string)$user->id) !!}
          </div>
          <div class="code">{{ $user->code ?? 'N/A' }}</div>
          <div class="name">{{ $user->name }}</div>
        </div>
      @endforeach
    </div>
  </div>
</body>
</html>

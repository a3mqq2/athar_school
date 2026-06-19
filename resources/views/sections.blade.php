{{-- resources/views/sections.blade.php --}}
@extends('layouts.auth')

@section('title', 'صلاحياتي')

@push('styles')
    <style>
        /* نفس ستايلاتك كما هي بدون تغيير */
        .auth-form{background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%)}
        .roles-container{padding:2rem;max-width:100%;height:100vh;overflow-y:auto}
        .page-header{text-align:center;margin-bottom:2rem;position:relative;padding:1.5rem 0}
        .page-header::before{content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);width:60px;height:2px;background:linear-gradient(90deg,transparent,#8B1538,transparent);border-radius:1px}
        .page-title{font-family:'Changa',sans-serif;font-size:1.8rem;font-weight:700;color:#8B1538;margin-bottom:1rem;text-shadow:0 1px 3px rgba(139,21,56,.1);position:relative}
        .page-title::after{content:'✦';position:absolute;left:50%;bottom:-10px;transform:translateX(-50%);color:#8B1538;font-size:.9rem;opacity:.6}
        .welcome-text{background:rgba(255,255,255,.9);backdrop-filter:blur(10px);padding:.8rem 1.5rem;border-radius:25px;display:inline-block;color:#6c757d;font-size:.95rem;font-weight:500;box-shadow:0 4px 15px rgba(0,0,0,.1);border:1px solid rgba(139,21,56,.1);font-family:'Changa',sans-serif}
        .role-card{background:rgba(255,255,255,.98);backdrop-filter:blur(10px);border:1px solid rgba(139,21,56,.15);border-radius:12px;padding:1.5rem;text-align:center;transition:all .3s cubic-bezier(.4,0,.2,1);position:relative;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,.06);height:fit-content;min-height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;opacity:0;transform:translateY(20px);animation:fadeInUp .5s ease forwards}
        .role-card::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(139,21,56,.05),transparent);transition:left .5s ease}
        .role-card:hover::before{left:100%}
        .role-card:hover{transform:translateY(-3px) scale(1.01);box-shadow:rgb(241 206 34);border-color:rgb(231 205 23)}
        .role-icon{width:55px;height:55px;background:linear-gradient(135deg, #fbd02b 0%, #d0c900 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;box-shadow:0 6px 15px rgba(139,21,56,.25);transition:all .3s ease;position:relative;z-index:2;margin-bottom:1rem}
        .role-icon::before{content:'';position:absolute;inset:-3px;background:linear-gradient(135deg, #fbd02b 0%, #d0c900 100%);border-radius:50%;z-index:-1;opacity:0;transition:opacity .3s ease}
        .role-card:hover .role-icon::before{opacity:1;animation:rotate 2s linear infinite}
        .role-card:hover .role-icon{transform:scale(1.05);box-shadow:0 8px 20px #fbd02b}
        @keyframes rotate{from{transform:rotate(0)}to{transform:rotate(360deg)}}
        .role-name{font-family:'Changa',sans-serif;font-size:1.2rem;font-weight:600;color:#2c3e50;margin-bottom:.4rem;line-height:1.3}
        .role-description{color:#7f8c8d;font-size:.85rem;font-weight:400;letter-spacing:.3px;font-family:'Changa',sans-serif}
        .empty-state{grid-column:1 / -1;text-align:center;padding:3rem 2rem;background:rgba(255,255,255,.9);backdrop-filter:blur(15px);border:2px dashed rgb(231 205 23);border-radius:16px;position:relative}
        .empty-state::before{content:'';position:absolute;top:15px;left:15px;right:15px;bottom:15px;border:1px dashed rgba(139,21,56,.2);border-radius:12px}
        .empty-state-icon{font-size:3.5rem;color:rgb(231 205 23);margin-bottom:1rem;animation:pulse 2s infinite}
        @keyframes pulse{0%,100%{opacity:.3}50%{opacity:.6}}
        @keyframes fadeInUp{to{opacity:1;transform:translateY(0)}}
        .roles-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;max-height:calc(100vh - 240px);overflow-y:auto;padding-right:.3rem}
        .roles-grid::-webkit-scrollbar{width:6px}.roles-grid::-webkit-scrollbar-track{background:rgba(139,21,56,.1);border-radius:3px}.roles-grid::-webkit-scrollbar-thumb{background:rgb(231 205 23);border-radius:3px}.roles-grid::-webkit-scrollbar-thumb:hover{background:rgba(139,21,56,.5)}
        @media (max-width:768px){.roles-grid{grid-template-columns:1fr}}
    </style>
@endpush

@section('content')
<div class="roles-container">

    <div class="head-page">
        <h3 style="font-weight:bold;color:#fbd02b;">مرحبًا بعودتك {{ auth()->user()->name }}</h3>
        <div class="prayer font-weight-bold mb-4">
            اللهم بارك لي في وقتي ورزقي وجهدي وجسدي ومالي وعملي وارزقني البركة في كل شيء.. اللهم اجعلني مباركًا أينما كنت.
        </div>
    </div>

    @php
        // تعريف الأدوار المدعومة حاليًا حسب السييدر
        $roleConfig = [
            'admin' => [
                'icon' => 'ph-duotone ph-crown',
                'name' => 'الإدارة',
                'description' => 'إدارة عامة وإشراف كامل',
                'route' => 'admin.dashboard', // عدّليها على اسم Route الفعلي
            ],
            'finance' => [
                'icon' => 'ph-duotone ph-coins',
                'name' => 'المالية',
                'description' => 'إدارة الشؤون المالية والتقارير',
                'route' => 'finance.dashboard', // عدّليها على اسم Route الفعلي
            ],
            'supervisor' => [
                'icon' => 'ph-duotone ph-shield-check',
                'name' => 'المشرف',
                'description' => 'إشراف ومتابعة وتسجيل الحضور والغياب',
                'route' => 'supervisor.dashboard', // عدّليها على اسم Route الفعلي
            ],
        ];

        // أدوار المستخدم الحالية
        $userRoles = auth()->user()->getRoleNames()->toArray();

        // نعرض فقط الكروت التي يملكها المستخدم
        $cards = array_filter($roleConfig, fn($v, $k) => in_array($k, $userRoles), ARRAY_FILTER_USE_BOTH);
    @endphp

    <div class="roles-grid">
        @forelse($cards as $key => $config)
            <a @if(isset($config['route'])) href="{{ route($config['route']) }}" @endif class="text-decoration-none">
                <div class="role-card">
                    <div class="role-icon"><i class="{{ $config['icon'] }}"></i></div>
                    <h3 class="role-name">{{ $config['name'] }}</h3>
                    <p class="role-description">{{ $config['description'] }}</p>
                </div>
            </a>
        @empty
            <div class="empty-state">
                <div class="empty-state-icon"><i class="ph-duotone ph-user-circle-minus"></i></div>
                <p class="empty-state-text">لا توجد أدوار مرتبطة بهذا الحساب</p>
            </div>
        @endforelse
    </div>

    <div class="text-center mt-4">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="ph-duotone ph-sign-out me-2"></i>
                تسجيل الخروج
            </button>
        </form>
    </div>
</div>
@endsection

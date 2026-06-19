@extends('layouts.app')
@section('title', 'تسجيل الحضور - جهاز QR')

@push('styles')
<style>
    .scanner-container {
        max-width: 600px;
        margin: 60px auto;
        text-align: center;
    }

    .scanner-input {
        font-size: 28px;
        padding: 18px 20px;
        text-align: center;
        border: 2px solid #998965;
        border-radius: 8px;
        width: 100%;
        outline: none;
    }

    .scanner-input:focus {
        border-color: #fbd02b;
        box-shadow: 0 0 0 3px rgba(251, 208, 43, 0.25);
    }

    .result-section {
        margin-top: 30px;
        padding: 24px;
        background: #fff;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        display: none;
    }

    .result-section h5 {
        font-size: 20px;
        margin-bottom: 16px;
        font-weight: 600;
        color: #333;
    }

    .user-info-row {
        background: #f8f9fa;
        padding: 12px 16px;
        border-radius: 4px;
        margin-bottom: 10px;
        font-size: 18px;
        text-align: right;
    }

    .user-info-row strong {
        color: #998965;
        min-width: 120px;
        display: inline-block;
    }

    .alert {
        border-radius: 6px;
        padding: 14px 18px;
        font-size: 16px;
        font-weight: 500;
        margin-top: 20px;
        text-align: center;
    }

    .alert-success { background: #f4f9f4; color: #155724; border: 1px solid #28a745; }
    .alert-danger  { background: #fff5f5; color: #721c24; border: 1px solid #dc3545; }
    .alert-info    { background: #f0f8ff; color: #004085; border: 1px solid #004085; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="scanner-container">
        <h3 class="mb-4">تسجيل الحضور بجهاز QR</h3>
        
        <!-- Input خاص بجهاز القارئ -->
        <input type="text" id="qr-input" class="scanner-input" placeholder="مرر الكود هنا..." autofocus>

        <!-- النتيجة -->
        <div id="result-section" class="result-section">
            <div id="user-info"></div>
            <div id="action-area" class="mt-3"></div>
        </div>

        <!-- التنبيهات -->
        <div id="alert-container"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const input = document.getElementById("qr-input");

    // دايمًا حافظ على التركيز في الحقل
    setInterval(() => { 
        if (document.activeElement != input) input.focus(); 
    }, 1000);

    input.addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            const code = input.value.trim();
            if (code) {
                processCode(code);
                input.value = "";
            }
        }
    });

    function processCode(code) {
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i> جاري البحث...');
        fetch('/supervisor/check-user', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ code })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) displayUserInfo(data.user);
            else { 
                showAlert('danger', data.message || 'المستخدم غير موجود'); 
                hideResultSection(); 
            }
        })
        .catch(() => showAlert('danger','حدث خطأ في المعالجة'));
    }

    function displayUserInfo(user) {
        const userInfoDiv   = document.getElementById("user-info");
        const actionAreaDiv = document.getElementById("action-area");
        const resultSection = document.getElementById("result-section");

        userInfoDiv.innerHTML = `
            <h5>معلومات المستخدم</h5>
            <div class="user-info-row"><strong>الاسم:</strong> ${user.name}</div>
            <div class="user-info-row"><strong>البريد:</strong> ${user.email}</div>
            <div class="user-info-row"><strong>الصلاحيات:</strong> ${Array.isArray(user.roles)? user.roles.join(', '): user.roles}</div>
            ${user.has_attendance_today ? '<div class="alert alert-info mt-3 mb-0">تم تسجيل الحضور مسبقاً</div>' : ''}
        `;

        if (!user.has_attendance_today) {
            if (user.is_teacher) {
                actionAreaDiv.innerHTML = `
                    <form id="attendance-form">
                        <div class="mb-3">
                            <label class="form-label">عدد الحصص المعطاة</label>
                            <input type="number" class="form-control" id="lessons-count" min="0" required placeholder="أدخل عدد الحصص">
                        </div>
                        <button type="submit" class="btn btn-success" style="width:100%">تسجيل الحضور</button>
                    </form>
                `;
                document.getElementById("attendance-form").addEventListener('submit', function(e){
                    e.preventDefault();
                    submitAttendance(user.id, document.getElementById("lessons-count").value);
                });
            } else {
                actionAreaDiv.innerHTML = `
                    <button onclick="submitAttendance(${user.id}, null)" class="btn btn-success" style="width:100%">تأكيد الحضور</button>
                `;
            }
        } else {
            actionAreaDiv.innerHTML = '';
        }

        resultSection.style.display = 'block';
        clearAlerts();
    }

    function submitAttendance(userId, lessonsCount) {
        showAlert('info', '<i class="fas fa-spinner fa-spin me-2"></i> جاري تسجيل الحضور...');
        fetch('/supervisor/record-attendance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ user_id: userId, lessons_count: lessonsCount })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message || 'تم تسجيل الحضور بنجاح');
                hideResultSection();
            } else {
                showAlert('danger', data.message || 'فشل تسجيل الحضور');
            }
        })
        .catch(() => showAlert('danger','حدث خطأ في تسجيل الحضور'));
    }

    function hideResultSection(){ 
        document.getElementById("result-section").style.display = 'none'; 
    }

    function showAlert(type, message) {
        document.getElementById("alert-container").innerHTML = `
            <div class="alert alert-${type}">${message}</div>
        `;
    }

    function clearAlerts(){ 
        document.getElementById("alert-container").innerHTML = ''; 
    }
</script>
@endpush

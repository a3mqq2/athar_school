@extends('layouts.app')

@section('title','تحويل بين الخزائن')

@push('styles')
@include('partials.page-styles')
<style>
.treasury-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    background: #ffffff;
}

.treasury-card.selected {
    border-color: #0d6efd;
    background-color: #f8f9ff;
}

.treasury-card:hover {
    border-color: #adb5bd;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.treasury-balance {
    font-size: 1.25rem;
    font-weight: 600;
    color: #198754;
}

.balance-warning {
    color: #dc3545;
    font-size: 0.875rem;
}

.transfer-arrow {
    font-size: 2rem;
    color: #6c757d;
    text-align: center;
    margin: 1rem 0;
}

.amount-input {
    font-size: 1.1rem;
    font-weight: 500;
}

.insufficient-balance {
    border-color: #dc3545 !important;
    background-color: #fff5f5 !important;
}
</style>
@endpush

@section('content')
<div class="">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="page-title">تحويل بين الخزائن</h3>
            <p class="text-muted mb-0">تحويل مبلغ من خزينة إلى أخرى</p>
        </div>
        <a href="{{ route('finance.treasury-transfers.index') }}" class="btn btn-secondary">
            العودة للقائمة
        </a>
    </div>



    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('finance.treasury-transfers.store') }}" id="transferForm">
                        @csrf

                        <!-- اختيار الخزائن -->
                        <div class="row g-4">
                            <!-- خزينة المرسل -->
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">من خزينة</label>
                                <div class="row g-2">
                                    @foreach($treasuries as $treasury)
                                        <div class="col-12">
                                            <div class="treasury-card" data-treasury-id="{{ $treasury->id }}" 
                                                 data-balance="{{ $treasury->current_balance }}" 
                                                 data-type="from">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">{{ $treasury->name }}</h6>
                                                        <small class="text-muted">{{ optional($treasury->responsible)->name }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="treasury-balance">{{ number_format($treasury->current_balance, 2) }}</div>
                                                        <small class="text-muted">الرصيد الحالي</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="from_treasury_id" id="from_treasury_id" value="{{ old('from_treasury_id') }}">
                                @error('from_treasury_id')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- رمز التحويل -->
                            <div class="col-md-2">
                                <div class="transfer-arrow">
                                    <i class="fas fa-arrow-left"></i>
                                </div>
                            </div>

                            <!-- خزينة المستقبل -->
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">إلى خزينة</label>
                                <div class="row g-2">
                                    @foreach($treasuries as $treasury)
                                        <div class="col-12">
                                            <div class="treasury-card" data-treasury-id="{{ $treasury->id }}" 
                                                 data-type="to">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">{{ $treasury->name }}</h6>
                                                        <small class="text-muted">{{ optional($treasury->responsible)->name }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="treasury-balance">{{ number_format($treasury->current_balance, 2) }}</div>
                                                        <small class="text-muted">الرصيد الحالي</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="to_treasury_id" id="to_treasury_id" value="{{ old('to_treasury_id') }}">
                                @error('to_treasury_id')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- تفاصيل التحويل -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">المبلغ المراد تحويله <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" 
                                       class="form-control amount-input @error('amount') is-invalid @enderror" 
                                       value="{{ old('amount') }}" required>
                                <div id="balance-info" class="mt-2" style="display: none;">
                                    <small class="text-muted">الرصيد المتاح: <span id="available-balance">0</span></small>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الرقم المرجعي</label>
                                <input type="text" name="reference_number" 
                                       class="form-control @error('reference_number') is-invalid @enderror" 
                                       value="{{ old('reference_number') }}" placeholder="رقم مرجعي اختياري">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">وصف التحويل</label>
                                <textarea name="description" rows="3" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          placeholder="وصف اختياري للتحويل">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                <i class="fas fa-exchange-alt me-2"></i>تنفيذ التحويل
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromTreasuryInput = document.getElementById('from_treasury_id');
    const toTreasuryInput = document.getElementById('to_treasury_id');
    const amountInput = document.getElementById('amount');
    const submitBtn = document.getElementById('submitBtn');
    const balanceInfo = document.getElementById('balance-info');
    const availableBalance = document.getElementById('available-balance');
    
    let selectedFromTreasury = null;
    let selectedToTreasury = null;

    // تحديد القيم المحفوظة مسبقاً
    if (fromTreasuryInput.value) {
        selectTreasury(fromTreasuryInput.value, 'from');
    }
    if (toTreasuryInput.value) {
        selectTreasury(toTreasuryInput.value, 'to');
    }

    // معالج النقر على بطاقات الخزائن
    document.querySelectorAll('.treasury-card').forEach(card => {
        card.addEventListener('click', function() {
            const treasuryId = this.dataset.treasuryId;
            const type = this.dataset.type;
            
            selectTreasury(treasuryId, type);
        });
    });

    function selectTreasury(treasuryId, type) {
        // إزالة التحديد من نفس النوع
        document.querySelectorAll(`[data-type="${type}"].selected`).forEach(card => {
            card.classList.remove('selected');
        });

        // تحديد البطاقة الجديدة
        const selectedCard = document.querySelector(`[data-treasury-id="${treasuryId}"][data-type="${type}"]`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
        }

        // تحديث المتغيرات
        if (type === 'from') {
            selectedFromTreasury = treasuryId;
            fromTreasuryInput.value = treasuryId;
            
            // تحديث معلومات الرصيد
            const balance = selectedCard.dataset.balance;
            availableBalance.textContent = parseFloat(balance).toLocaleString('ar', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            balanceInfo.style.display = 'block';
            
        } else {
            selectedToTreasury = treasuryId;
            toTreasuryInput.value = treasuryId;
        }

        // إزالة التحديد من الخزينة نفسها في النوع الآخر
        if (type === 'from') {
            const toCard = document.querySelector(`[data-treasury-id="${treasuryId}"][data-type="to"]`);
            if (toCard && toCard.classList.contains('selected')) {
                toCard.classList.remove('selected');
                selectedToTreasury = null;
                toTreasuryInput.value = '';
            }
        } else {
            const fromCard = document.querySelector(`[data-treasury-id="${treasuryId}"][data-type="from"]`);
            if (fromCard && fromCard.classList.contains('selected')) {
                fromCard.classList.remove('selected');
                selectedFromTreasury = null;
                fromTreasuryInput.value = '';
                balanceInfo.style.display = 'none';
            }
        }

        validateForm();
    }

    // التحقق من المبلغ
    amountInput.addEventListener('input', function() {
        validateAmount();
        validateForm();
    });

    function validateAmount() {
        if (!selectedFromTreasury || !this.value) return;

        const amount = parseFloat(this.value);
        const selectedCard = document.querySelector(`[data-treasury-id="${selectedFromTreasury}"][data-type="from"]`);
        const balance = parseFloat(selectedCard.dataset.balance);

        // إزالة التنسيق السابق
        this.classList.remove('is-invalid');
        selectedCard.classList.remove('insufficient-balance');

        if (amount > balance) {
            this.classList.add('is-invalid');
            selectedCard.classList.add('insufficient-balance');
            
            // إظهار رسالة خطأ
            let feedback = this.parentElement.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                this.parentElement.appendChild(feedback);
            }
            feedback.textContent = `المبلغ أكبر من الرصيد المتاح (${balance.toLocaleString('ar', {minimumFractionDigits: 2})})`;
        }
    }

    function validateForm() {
        const isValid = selectedFromTreasury && 
                       selectedToTreasury && 
                       selectedFromTreasury != selectedToTreasury &&
                       amountInput.value && 
                       parseFloat(amountInput.value) > 0 &&
                       !amountInput.classList.contains('is-invalid');

        submitBtn.disabled = !isValid;
    }

    // منع إرسال النموذج إذا كان غير صالح
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        if (submitBtn.disabled) {
            e.preventDefault();
            return false;
        }
        
        // تأكيد التحويل
        const fromName = document.querySelector(`[data-treasury-id="${selectedFromTreasury}"][data-type="from"] h6`).textContent;
        const toName = document.querySelector(`[data-treasury-id="${selectedToTreasury}"][data-type="to"] h6`).textContent;
        const amount = parseFloat(amountInput.value).toLocaleString('ar', {minimumFractionDigits: 2});
        
        const confirmed = confirm(
            `هل أنت متأكد من تحويل مبلغ ${amount} من خزينة "${fromName}" إلى خزينة "${toName}"؟`
        );
        
        if (!confirmed) {
            e.preventDefault();
        }
    });

    // التحقق الأولي
    validateForm();
});
</script>
@endpush
@endsection
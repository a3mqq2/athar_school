@extends('layouts.app')

@section('title','تعديل معاملة مالية')

@push('styles')
<style>
.transaction-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}
.transaction-type-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.transaction-type-card.selected {
    border-color: var(--bs-primary);
    background-color: rgba(13, 110, 253, 0.1);
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="page-header">تعديل معاملة مالية</h3>
        <a href="{{ route('finance.transactions.index') }}" class="btn btn-secondary">العودة للقائمة</a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('finance.transactions.update', $transaction) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label">نوع المعاملة</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="transaction-type-card card p-3 text-center {{ old('transaction_type', $transaction->transaction_type) === 'deposit' ? 'selected' : '' }}" data-type="deposit">
                                        <i class="fas fa-arrow-down text-success fa-2x mb-2"></i>
                                        <h5 class="text-success">إيداع</h5>
                                        <small class="text-muted">إضافة مبلغ للخزينة</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="transaction-type-card card p-3 text-center {{ old('transaction_type', $transaction->transaction_type) === 'withdrawal' ? 'selected' : '' }}" data-type="withdrawal">
                                        <i class="fas fa-arrow-up text-danger fa-2x mb-2"></i>
                                        <h5 class="text-danger">سحب</h5>
                                        <small class="text-muted">إخراج مبلغ من الخزينة</small>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="transaction_type" id="transaction_type" value="{{ old('transaction_type', $transaction->transaction_type) }}">
                            @error('transaction_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">اسم المستلم <span class="text-danger">*</span></label>
                                <input type="text" name="payee_name" class="form-control @error('payee_name') is-invalid @enderror" 
                                       value="{{ old('payee_name', $transaction->payee_name) }}" required>
                                @error('payee_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                                <input type="number" name="amount" step="0.01" min="0.01" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       value="{{ old('amount', $transaction->amount) }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الخزينة <span class="text-danger">*</span></label>
                                <select name="treasury_id" class="form-select @error('treasury_id') is-invalid @enderror" required>
                                    <option value="">اختر الخزينة</option>
                                    @foreach($treasuries as $treasury)
                                        <option value="{{ $treasury->id }}" @selected(old('treasury_id', $transaction->treasury_id)==$treasury->id)>
                                            {{ $treasury->name }} ({{ number_format($treasury->current_balance, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('treasury_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">تصنيف المعاملة <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="transaction_type_id" id="transaction_type_id" 
                                            class="form-select @error('transaction_type_id') is-invalid @enderror" required>
                                        <option value="">اختر التصنيف</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" id="add-transaction-type-btn" 
                                            data-bs-toggle="modal" data-bs-target="#addTransactionTypeModal">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                @error('transaction_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    <option value="">اختر طريقة الدفع</option>
                                    <option value="cash" @selected(old('payment_method', $transaction->payment_method)=='cash')>نقدي</option>
                                    <option value="bank" @selected(old('payment_method', $transaction->payment_method)=='bank_transfer')>تحويل بنكي</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">رقم المستند</label>
                                <input type="text" name="document_number" 
                                       class="form-control @error('document_number') is-invalid @enderror" 
                                       value="{{ old('document_number', $transaction->document_number) }}">
                                @error('document_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" rows="3" 
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $transaction->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">تحديث المعاملة</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addTransactionTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة تصنيف جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTransactionTypeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم التصنيف <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="new_transaction_type_name" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <input type="hidden" name="type" id="new_transaction_type_type">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        إضافة التصنيف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeCards = document.querySelectorAll('.transaction-type-card');
    const transactionTypeInput = document.getElementById('transaction_type');
    const transactionTypeSelect = document.getElementById('transaction_type_id');
    const addTransactionTypeBtn = document.getElementById('add-transaction-type-btn');
    const addTransactionTypeForm = document.getElementById('addTransactionTypeForm');
    const newTransactionTypeName = document.getElementById('new_transaction_type_name');
    const newTransactionTypeType = document.getElementById('new_transaction_type_type');

    if (transactionTypeInput.value) {
        newTransactionTypeType.value = transactionTypeInput.value;
        loadTransactionTypes(transactionTypeInput.value);
    }

    typeCards.forEach(card => {
        card.addEventListener('click', function() {
            typeCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const type = this.dataset.type;
            transactionTypeInput.value = type;
            newTransactionTypeType.value = type;
            loadTransactionTypes(type);
        });
    });

    addTransactionTypeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');
        const formData = new FormData(this);
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        fetch('{{ route('finance.api.store-transaction-type') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                const option = document.createElement('option');
                option.value = data.transaction_type.id;
                option.textContent = data.transaction_type.name;
                option.selected = true;
                transactionTypeSelect.appendChild(option);
                bootstrap.Modal.getInstance(document.getElementById('addTransactionTypeModal')).hide();
                this.reset();
                clearValidationErrors();
                showAlert('success', data.message);
            } else {
                showValidationErrors(data.errors);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'حدث خطأ أثناء إضافة التصنيف');
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        });
    });

    document.getElementById('addTransactionTypeModal').addEventListener('hidden.bs.modal', function() {
        addTransactionTypeForm.reset();
        clearValidationErrors();
    });

    function loadTransactionTypes(type) {
        transactionTypeSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        transactionTypeSelect.disabled = true;
        fetch(`{{ route('finance.api.transaction-types') }}?type=${type}`)
            .then(response => response.json())
            .then(data => {
                transactionTypeSelect.innerHTML = '<option value="">اختر التصنيف</option>';
                data.forEach(transactionType => {
                    const option = document.createElement('option');
                    option.value = transactionType.id;
                    option.textContent = transactionType.name;
                    if ('{{ old('transaction_type_id', $transaction->transaction_type_id) }}' == transactionType.id) {
                        option.selected = true;
                    }
                    transactionTypeSelect.appendChild(option);
                });
                transactionTypeSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                transactionTypeSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
            });
    }

    function showValidationErrors(errors) {
        clearValidationErrors();
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            const feedback = input.parentElement.querySelector('.invalid-feedback');
            input.classList.add('is-invalid');
            feedback.textContent = errors[field][0];
        });
    }

    function clearValidationErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.children[1]);
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
</script>
@endpush
@endsection

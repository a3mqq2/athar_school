<template>
  <div class="">
    <!-- Header -->
    <div class="header">
      <h3 class="text-white">صرف الرواتب</h3>
      <div class="controls">
        <input type="month" v-model="month" @change="loadEmployees">
        <button class="btn primary" @click="processPayroll" :disabled="!canProcess">
          صرف الرواتب
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading">جاري التحميل...</div>

    <!-- Employees List -->
    <div v-else class="employees">
      <div v-if="employees.length === 0" class="empty">
        لا يوجد موظفون لهذا الشهر
      </div>

      <div v-for="emp in employees" :key="emp.id" class="employee-card">
        <!-- Header -->
        <div class="emp-header">
          <div class="emp-info">
            <h4>{{ emp.name }}</h4>
            <span class="emp-role">{{ emp.role }}</span>
          </div>
          <div class="emp-balance">
            الرصيد الحالي: <strong>{{ formatMoney(emp.balance) }}</strong>
          </div>
        </div>

        <!-- Salary Details -->
        <div class="emp-body">
          <div class="salary-section">
            <div class="field">
              <label>الراتب الأساسي</label>
              <input type="number" v-model.number="emp.base_salary" step="0.01" min="0">
            </div>

            <div class="field">
              <label>المكافآت</label>
              <input type="number" v-model.number="emp.bonus" step="0.01" min="0">
            </div>

            <div class="field">
              <label>الخصومات</label>
              <input type="number" v-model.number="emp.deduction" step="0.01" min="0">
            </div>
          </div>

          <!-- Advance Info - Multiple Advances -->
          <div v-if="emp.advance" class="advance-section">
            <h5>السلف المستحقة ({{ emp.advance.advances_count }} سلفة)</h5>
            
            <div class="advance-info">
              <div>إجمالي المتبقي: <strong>{{ formatMoney(emp.advance.total_remaining) }}</strong></div>
              <div>الاستقطاع الشهري المقترح: <strong class="text-danger">{{ formatMoney(emp.advance.total_monthly_deduction) }}</strong></div>
            </div>

            <!-- List of advances -->
            <div v-if="emp.advance.advances" class="advances-list">
              <div v-for="adv in emp.advance.advances" :key="adv.id" class="advance-item">
                <span>{{ adv.description }}</span>
                <small>المتبقي: {{ formatMoney(adv.remaining_amount) }} | القسط: {{ formatMoney(adv.monthly_deduction) }}</small>
              </div>
            </div>

            <div class="field">
              <label>مبلغ الاستقطاع الفعلي</label>
              <input 
                type="number" 
                v-model.number="emp.actual_deduction" 
                step="0.01" 
                min="0" 
                :max="emp.advance.total_remaining"
              >
            </div>
          </div>

          <!-- Net Salary -->
          <div class="net-section">
            <div class="net-item">
              <span>الصافي قبل الاستقطاع:</span>
              <strong>{{ formatMoney(calculateGross(emp)) }}</strong>
            </div>
            <div v-if="emp.actual_deduction > 0" class="net-item deduction">
              <span>استقطاع السلف:</span>
              <strong>-{{ formatMoney(emp.actual_deduction) }}</strong>
            </div>
            <div class="net-item final">
              <span>صافي المستحق:</span>
              <strong>{{ formatMoney(calculateNet(emp)) }}</strong>
            </div>
          </div>

          <!-- Notes -->
          <div class="field">
            <label>ملاحظات</label>
            <textarea v-model="emp.notes" rows="2" placeholder="ملاحظات إضافية..."></textarea>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div v-if="employees.length > 0" class="summary">
        <div class="summary-item">
          <span>عدد الموظفين:</span>
          <strong>{{ employees.length }}</strong>
        </div>
        <div class="summary-item">
          <span>إجمالي الصرف:</span>
          <strong>{{ formatMoney(totalPayroll) }}</strong>
        </div>
        <div class="summary-item">
          <span>إجمالي الاستقطاعات:</span>
          <strong>{{ formatMoney(totalDeductions) }}</strong>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SimplePayroll',
  data() {
    return {
      month: new Date().toISOString().slice(0, 7),
      employees: [],
      loading: false
    }
  },
  computed: {
    canProcess() {
      return this.employees.length > 0 && this.month
    },
    totalPayroll() {
      return this.employees.reduce((sum, emp) => sum + this.calculateNet(emp), 0)
    },
    totalDeductions() {
      return this.employees.reduce((sum, emp) => sum + (emp.actual_deduction || 0), 0)
    }
  },
  mounted() {
    this.loadEmployees()
  },
  methods: {
    async loadEmployees() {
      this.loading = true
      try {
        const response = await fetch(`/finance/payrolls/employees?month=${this.month}`, {
          headers: { 'Accept': 'application/json' }
        })
        
        if (!response.ok) throw new Error('فشل في تحميل البيانات')
        
        const data = await response.json()
        this.employees = (data.employees || []).map(emp => ({
          ...emp,
          base_salary: Number(emp.base_salary) || 0,
          bonus: 0,
          deduction: 0,
          actual_deduction: emp.suggested_deduction || 0,
          notes: ''
        }))
      } catch (error) {
        console.error('خطأ في تحميل الموظفين:', error)
        alert('تعذر تحميل بيانات الموظفين')
        this.employees = []
      } finally {
        this.loading = false
      }
    },

    calculateGross(emp) {
      return Math.max(0, (emp.base_salary || 0) + (emp.bonus || 0) - (emp.deduction || 0))
    },

    calculateNet(emp) {
      return Math.max(0, this.calculateGross(emp) - (emp.actual_deduction || 0))
    },

    formatMoney(amount) {
      return Number(amount || 0).toFixed(2)
    },

    async processPayroll() {
      if (!this.canProcess) return

      const confirmation = confirm(`هل أنت متأكد من صرف الرواتب لشهر ${this.month}؟\nالإجمالي: ${this.formatMoney(this.totalPayroll)}\nالاستقطاعات: ${this.formatMoney(this.totalDeductions)}`)
      if (!confirmation) return

      this.loading = true
      try {
        const payload = {
          month: this.month,
          employees: this.employees.map(emp => ({
            user_id: emp.id,
            base_salary: emp.base_salary || 0,
            bonus: emp.bonus || 0,
            deduction: emp.deduction || 0,
            advance_deduction: emp.actual_deduction || 0,
            notes: emp.notes || ''
          }))
        }

        const response = await fetch('/finance/payrolls/process', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
          },
          body: JSON.stringify(payload)
        })

        if (!response.ok) {
          const errorData = await response.json()
          throw new Error(errorData.message || 'فشل في صرف الرواتب')
        }

        const result = await response.json()
        alert(`تم صرف الرواتب بنجاح!\nالإجمالي: ${this.formatMoney(result.gross_total)}\nالاستقطاعات: ${this.formatMoney(result.deductions)}\nالصافي: ${this.formatMoney(result.total)}`)
        
        await this.loadEmployees()
        
      } catch (error) {
        console.error('خطأ في صرف الرواتب:', error)
        alert('تعذر صرف الرواتب: ' + error.message)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
/* Header */
.header {
  background: #925419;
  color: white;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.header h3 {
  margin: 0;
}

.controls {
  display: flex;
  gap: 10px;
  align-items: center;
}

.controls input {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

/* Button */
.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.2s;
}

.btn.primary {
  background: #fbc417;
  color: #925419;
}

.btn.primary:hover:not(:disabled) {
  background: #e6b115;
}

.btn:disabled {
  background: #ccc;
  color: #666;
  cursor: not-allowed;
}

/* Loading & Empty */
.loading, .empty {
  text-align: center;
  padding: 40px;
  background: white;
  border-radius: 8px;
  border: 1px solid #ddd;
  color: #666;
}

/* Employee Card */
.employee-card {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.emp-header {
  background: #f8f9fa;
  padding: 15px;
  border-bottom: 1px solid #ddd;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.emp-info h4 {
  margin: 0 0 5px 0;
  color: #925419;
}

.emp-role {
  font-size: 12px;
  color: #666;
  background: #e9ecef;
  padding: 2px 8px;
  border-radius: 12px;
}

.emp-balance {
  font-size: 14px;
  color: #666;
}

.emp-body {
  padding: 20px;
}

/* Sections */
.salary-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.advance-section {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 6px;
  padding: 15px;
  margin-bottom: 20px;
}

.advance-section h5 {
  margin: 0 0 10px 0;
  color: #856404;
}

.advance-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
  margin-bottom: 15px;
  font-size: 14px;
}

.advances-list {
  margin: 15px 0;
  padding: 10px;
  background: white;
  border-radius: 4px;
}

.advance-item {
  padding: 8px;
  border-bottom: 1px solid #eee;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.advance-item:last-child {
  border-bottom: none;
}

.advance-item span {
  font-weight: bold;
  color: #333;
}

.advance-item small {
  color: #666;
  font-size: 12px;
}

.net-section {
  background: #f8f9fa;
  border-radius: 6px;
  padding: 15px;
  margin-bottom: 15px;
}

.net-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e9ecef;
}

.net-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.net-item.deduction {
  color: #dc3545;
}

.net-item.final {
  font-size: 16px;
  font-weight: bold;
  color: #925419;
  border-top: 2px solid #925419;
  padding-top: 10px;
}

/* Form Fields */
.field {
  margin-bottom: 15px;
}

.field label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #333;
  font-size: 14px;
}

.field input,
.field textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.field input:focus,
.field textarea:focus {
  outline: none;
  border-color: #925419;
}

/* Summary */
.summary {
  background: #925419;
  color: white;
  padding: 20px;
  border-radius: 8px;
  display: flex;
  justify-content: space-around;
  gap: 20px;
  margin-top: 20px;
}

.summary-item {
  text-align: center;
}

.summary-item span {
  display: block;
  margin-bottom: 5px;
  opacity: 0.9;
}

.summary-item strong {
  font-size: 18px;
}

/* Utilities */
.text-danger {
  color: #dc3545;
}

/* Responsive */
@media (max-width: 768px) {
  .header {
    flex-direction: column;
    text-align: center;
  }

  .emp-header {
    flex-direction: column;
    gap: 10px;
    text-align: center;
  }

  .salary-section {
    grid-template-columns: 1fr;
  }

  .advance-info {
    grid-template-columns: 1fr;
  }

  .summary {
    flex-direction: column;
    gap: 10px;
  }
}
</style>
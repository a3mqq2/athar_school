<template>
  <div class="student-promotion-container">
    <!-- Header -->
    <div class="promotion-header">
      <h2 class="page-title">
        <i class="fas fa-graduation-cap"></i>
        نظام ترحيل الطلاب
      </h2>
    </div>

    <!-- Section Selection -->
    <div class="section-selector-card">
      <div class="card-header">
        <i class="fas fa-filter"></i>
        اختيار القسم
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">القسم:</label>
          <select v-model="selectedSection" @change="loadSectionData" class="form-select">
            <option value="">اختر القسم</option>
            <option value="local">القسم المحلي</option>
            <option value="international">القسم الدولي</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-if="selectedSection" class="promotion-content">
      <!-- Source and Target Selection -->
      <div class="selection-panels">
        <!-- Source Panel -->
        <div class="selection-panel source-panel">
          <h4>من (السنة الحالية)</h4>
          <div class="selection-grid">
            <div class="form-group">
              <label>السنة الدراسية:</label>
              <select v-model="sourceYear" @change="loadSourceStages" class="form-select">
                <option value="">اختر السنة</option>
                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                  {{ year.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>المرحلة:</label>
              <select v-model="sourceStage" @change="loadSourceGrades" class="form-select">
                <option value="">اختر المرحلة</option>
                <option v-for="stage in sourceStages" :key="stage.id" :value="stage.id">
                  {{ stage.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>الصف:</label>
              <select v-model="sourceGrade" @change="loadStudents" class="form-select">
                <option value="">اختر الصف</option>
                <option v-for="grade in sourceGrades" :key="grade.id" :value="grade.id">
                  {{ grade.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Target Panel -->
        <div class="selection-panel target-panel">
          <h4>إلى (السنة المستهدفة)</h4>
          <div class="selection-grid">
            <div class="form-group">
              <label>السنة الدراسية:</label>
              <select v-model="targetYear" @change="loadTargetStages" class="form-select">
                <option value="">اختر السنة</option>
                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                  {{ year.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>المرحلة:</label>
              <select v-model="targetStage" @change="loadTargetGrades" class="form-select">
                <option value="">اختر المرحلة</option>
                <option v-for="stage in targetStages" :key="stage.id" :value="stage.id">
                  {{ stage.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>الصف:</label>
              <select v-model="targetGrade" @change="loadTargetClassrooms" class="form-select">
                <option value="">اختر الصف</option>
                <option v-for="grade in targetGrades" :key="grade.id" :value="grade.id">
                  {{ grade.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Students Table -->
      <div v-if="studentsData.length > 0" class="students-section">
        <div class="students-header">
          <h4>قائمة الطلاب - {{ getSourceInfo() }}</h4>
          <div class="bulk-actions">
            <button @click="selectAllStudents" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-check-square"></i>
              تحديد الكل
            </button>
            <button @click="clearAllSelections" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-square"></i>
              إلغاء التحديد
            </button>
            <button @click="previewPromotion" class="btn btn-success btn-sm" :disabled="!hasSelectedStudents">
              <i class="fas fa-eye"></i>
              معاينة الترحيل
            </button>
          </div>
        </div>

        <!-- Classrooms Groups -->
        <div class="classrooms-container">
          <div v-for="classroom in groupedStudents" :key="classroom.id" class="classroom-group">
            <div class="classroom-header">
              <h5>{{ classroom.name }}</h5>
              <span class="student-count">{{ classroom.students.length }} طالب</span>
            </div>

            <!-- Droppable Target Classrooms -->
            <div class="target-classrooms">
              <div 
                v-for="targetClass in targetClassrooms" 
                :key="targetClass.id"
                class="target-classroom"
                :class="{ 'drag-over': dragOverClass === targetClass.id }"
                @dragover.prevent="dragOverClass = targetClass.id"
                @dragleave="dragOverClass = null"
                @drop="handleDrop($event, targetClass.id)"
              >
                <i class="fas fa-arrow-down"></i>
                {{ targetClass.name }}
              </div>
            </div>

            <!-- Students Table -->
            <div class="students-table">
              <table class="table">
                <thead>
                  <tr>
                    <th width="40">
                      <input 
                        type="checkbox" 
                        :checked="isClassroomSelected(classroom.id)"
                        @change="toggleClassroomSelection(classroom.id)"
                      >
                    </th>
                    <th>الطالب</th>
                    <th>ولي الأمر</th>
                    <th>الهاتف</th>
                    <th>الحالة الحالية</th>
                    <th>الحالة الجديدة</th>
                    <th>الفصل المستهدف</th>
                    <th>العمليات</th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="student in classroom.students" 
                    :key="student.id"
                    class="student-row"
                    :class="{ 'selected': selectedStudents.includes(student.id) }"
                    draggable="true"
                    @dragstart="handleDragStart($event, student)"
                    @dragend="handleDragEnd"
                  >
                    <td>
                      <input 
                        type="checkbox" 
                        :value="student.id"
                        v-model="selectedStudents"
                      >
                    </td>
                    <td class="student-info">
                      <div class="student-name">{{ student.name }}</div>
                    </td>
                    <td>{{ student.parent_name }}</td>
                    <td>{{ student.phone || '-' }}</td>
                    <td>
                      <span class="status-badge" :class="'status-' + student.current_status">
                        {{ getStatusName(student.current_status) }}
                      </span>
                    </td>
                    <td>
                      <select 
                        v-model="student.new_status" 
                        class="form-select form-select-sm"
                        @change="updateStudentStatus(student)"
                      >
                        <option value="active">نشط</option>
                        <option value="graduated">متخرج</option>
                        <option value="transferred">منقول</option>
                        <option value="repeating">معيد</option>
                      </select>
                    </td>
                    <td>
                      <select 
                        v-model="student.target_classroom_id" 
                        class="form-select form-select-sm"
                        :disabled="student.new_status != 'active'"
                      >
                        <option value="">اختر الفصل</option>
                        <option v-for="targetClass in targetClassrooms" :key="targetClass.id" :value="targetClass.id">
                          {{ targetClass.name }}
                        </option>
                      </select>
                    </td>
                    <td>
                      <button @click="removeStudent(student.id)" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-times"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button @click="executePromotion" class="btn btn-primary" :disabled="!canExecutePromotion">
            <i class="fas fa-arrow-right"></i>
            تنفيذ الترحيل ({{ selectedStudents.length }} طالب)
          </button>
     
        </div>
      </div>

      <!-- Loading State -->
      <div v-else-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        جاري تحميل البيانات...
      </div>

      <!-- Empty State -->
      <div v-else-if="sourceGrade && !loading" class="empty-state">
        <i class="fas fa-user-slash"></i>
        <p>لا يوجد طلاب في هذا الصف</p>
      </div>
    </div>

    <!-- Preview Modal -->
    <div v-if="showPreview" class="modal-overlay" @click="closePreview">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h4>معاينة الترحيل</h4>
          <button @click="closePreview" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="promotion-summary">
            <div class="summary-item">
              <label>العدد الإجمالي:</label>
              <span>{{ selectedStudents.length }} طالب</span>
            </div>
            <div class="summary-item">
              <label>نشط:</label>
              <span>{{ getStatusCount('active') }} طالب</span>
            </div>
            <div class="summary-item">
              <label>متخرج:</label>
              <span>{{ getStatusCount('graduated') }} طالب</span>
            </div>
            <div class="summary-item">
              <label>منقول:</label>
              <span>{{ getStatusCount('transferred') }} طالب</span>
            </div>
            <div class="summary-item">
              <label>معيد:</label>
              <span>{{ getStatusCount('repeating') }} طالب</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="closePreview" class="btn btn-secondary">إلغاء</button>
          <button @click="confirmPromotion" class="btn btn-primary">تأكيد الترحيل</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Reactive data
const selectedSection = ref('')
const sourceYear = ref('')
const sourceStage = ref('')
const sourceGrade = ref('')
const targetYear = ref('')
const targetStage = ref('')
const targetGrade = ref('')

const academicYears = ref([])
const sourceStages = ref([])
const sourceGrades = ref([])
const targetStages = ref([])
const targetGrades = ref([])
const targetClassrooms = ref([])
const studentsData = ref([])
const selectedStudents = ref([])

const loading = ref(false)
const showPreview = ref(false)
const dragOverClass = ref(null)

// Computed properties
const groupedStudents = computed(() => {
  const groups = {}
  studentsData.value.forEach(student => {
    const classroomId = student.current_classroom_id
    const classroomName = student.current_classroom_name
    
    if (!groups[classroomId]) {
      groups[classroomId] = {
        id: classroomId,
        name: classroomName,
        students: []
      }
    }
    groups[classroomId].students.push(student)
  })
  return Object.values(groups)
})

const hasSelectedStudents = computed(() => selectedStudents.value.length > 0)

const canExecutePromotion = computed(() => {
  return hasSelectedStudents.value && targetYear.value && targetStage.value && targetGrade.value
})

// Methods
const loadSectionData = async () => {
  if (!selectedSection.value) return
  
  loading.value = true
  try {
    // Load academic years
    const response = await fetch(`/admin/students/promotion/academic-years`)
    academicYears.value = await response.json()
  } catch (error) {
    console.error('Error loading section data:', error)
  } finally {
    loading.value = false
  }
}

const loadSourceStages = async () => {
  if (!sourceYear.value) return
  
  try {
    const response = await fetch(`/admin/students/promotion/stages?section=${selectedSection.value}&year=${sourceYear.value}`)
    sourceStages.value = await response.json()
  } catch (error) {
    console.error('Error loading source stages:', error)
  }
}

const loadSourceGrades = async () => {
  if (!sourceStage.value) return
  
  try {
    const response = await fetch(`/admin/students/promotion/grades?stage=${sourceStage.value}`)
    sourceGrades.value = await response.json()
  } catch (error) {
    console.error('Error loading source grades:', error)
  }
}

const loadTargetStages = async () => {
  if (!targetYear.value) return
  
  try {
    const response = await fetch(`/admin/students/promotion/stages?section=${selectedSection.value}&year=${targetYear.value}`)
    targetStages.value = await response.json()
  } catch (error) {
    console.error('Error loading target stages:', error)
  }
}

const loadTargetGrades = async () => {
  if (!targetStage.value) return
  
  try {
    const response = await fetch(`/admin/students/promotion/grades?stage=${targetStage.value}`)
    targetGrades.value = await response.json()
  } catch (error) {
    console.error('Error loading target grades:', error)
  }
}

const loadTargetClassrooms = async () => {
  if (!targetGrade.value) return
  
  try {
    const response = await fetch(`/admin/students/promotion/classrooms?grade=${targetGrade.value}`)
    targetClassrooms.value = await response.json()

    // foreach students set target_classroom_id to the first classroom of the targetClassrooms
    studentsData.value.forEach(student => {
      if (student.new_status === 'active' && targetClassrooms.value.length > 0) {
        let findClassRoomByName = targetClassrooms.value.find(c => c.name === student.current_classroom_name);
        if (findClassRoomByName) {
          student.target_classroom_id = findClassRoomByName.id
        } else {
          student.target_classroom_id = targetClassrooms.value[0].id
        }

      } else {
        student.target_classroom_id = ''
      }
    })


  } catch (error) {
    console.error('Error loading target classrooms:', error)
  }
}

const loadStudents = async () => {
  if (!sourceGrade.value) return
  
  loading.value = true
  try {
    const response = await fetch(`/admin/students/promotion/students?grade=${sourceGrade.value}&year=${sourceYear.value}`)
    const students = await response.json()
    console.log(students);
    studentsData.value = students.map(student => ({
      ...student,
      new_status: student.current_status || 'active',
      target_classroom_id: student.current_classroom_id
    }))
  } catch (error) {
    console.error('Error loading students:', error)
  } finally {
    loading.value = false
  }
}

const getSourceInfo = () => {
  const year = academicYears.value.find(y => y.id == sourceYear.value)?.name
  const stage = sourceStages.value.find(s => s.id == sourceStage.value)?.name
  const grade = sourceGrades.value.find(g => g.id == sourceGrade.value)?.name
  return `${year} - ${stage} - ${grade}`
}

const getStatusName = (status) => {
  const statusNames = {
    active: 'نشط',
    graduated: 'متخرج',
    transferred: 'منقول',
    repeating: 'معيد'
  }
  return statusNames[status] || status
}

const getStatusCount = (status) => {
  return studentsData.value.filter(s => 
    selectedStudents.value.includes(s.id) && s.new_status === status
  ).length
}

const selectAllStudents = () => {
  selectedStudents.value = studentsData.value.map(s => s.id)
}

const clearAllSelections = () => {
  selectedStudents.value = []
}

const isClassroomSelected = (classroomId) => {
  const classroomStudents = studentsData.value.filter(s => s.current_classroom_id === classroomId)
  return classroomStudents.every(s => selectedStudents.value.includes(s.id))
}

const toggleClassroomSelection = (classroomId) => {
  const classroomStudents = studentsData.value.filter(s => s.current_classroom_id === classroomId)
  const allSelected = classroomStudents.every(s => selectedStudents.value.includes(s.id))
  
  if (allSelected) {
    selectedStudents.value = selectedStudents.value.filter(id => 
      !classroomStudents.some(s => s.id === id)
    )
  } else {
    const newSelections = classroomStudents.map(s => s.id)
    selectedStudents.value = [...new Set([...selectedStudents.value, ...newSelections])]
  }
}

const updateStudentStatus = (student) => {
  if (student.new_status != 'active') {
    student.target_classroom_id = ''
  }
}

const removeStudent = (studentId) => {
  studentsData.value = studentsData.value.filter(s => s.id != studentId)
  selectedStudents.value = selectedStudents.value.filter(id => id != studentId)
}

// Drag and Drop
const handleDragStart = (event, student) => {
  event.dataTransfer.setData('text/plain', JSON.stringify(student))
  event.target.classList.add('dragging')
}

const handleDragEnd = (event) => {
  event.target.classList.remove('dragging')
  dragOverClass.value = null
}

const handleDrop = (event, classroomId) => {
  event.preventDefault()
  const studentData = JSON.parse(event.dataTransfer.getData('text/plain'))
  const student = studentsData.value.find(s => s.id === studentData.id)
  
  if (student) {
    student.target_classroom_id = classroomId
    student.new_status = 'active'
  }
  
  dragOverClass.value = null
}

const previewPromotion = () => {
  showPreview.value = true
}

const closePreview = () => {
  showPreview.value = false
}

const confirmPromotion = () => {
  showPreview.value = false
  executePromotion()
}

const executePromotion = async () => {
  if (!canExecutePromotion.value) return
  
  const promotionData = {
    source: {
      year: sourceYear.value,
      stage: sourceStage.value,
      grade: sourceGrade.value
    },
    target: {
      year: targetYear.value,
      stage: targetStage.value,
      grade: targetGrade.value
    },
    students: studentsData.value
      .filter(s => selectedStudents.value.includes(s.id))
      .map(s => ({
        id: s.id,
        new_status: s.new_status,
        target_classroom_id: s.target_classroom_id
      }))
  }

  try {
    loading.value = true
    const response = await fetch('/admin/students/promotion/execute', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      body: JSON.stringify(promotionData)
    })

    if (response.ok) {
      alert('تم ترحيل الطلاب بنجاح')
      // Reset or redirect
    } else {
      alert('حدث خطأ في عملية الترحيل')
    }
  } catch (error) {
    console.error('Error executing promotion:', error)
    alert('حدث خطأ في عملية الترحيل')
  } finally {
    loading.value = false
  }
}



</script>

<style scoped>
.student-promotion-container {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.promotion-header {
  margin-bottom: 20px;
}

.page-title {
  color: #925419;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 0;
}

.section-selector-card,
.students-section {
  background: white;
  border: 2px solid #fbc417;
  border-radius: 12px;
  margin-bottom: 20px;
}

.card-header {
  background: #fbc41720;
  padding: 15px 20px;
  border-bottom: 2px solid #fbc417;
  font-weight: 600;
  color: #925419;
}

.card-body {
  padding: 20px;
}

.selection-panels {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.selection-panel {
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 20px;
}

.source-panel {
  border-left: 4px solid #dc3545;
}

.target-panel {
  border-left: 4px solid #28a745;
}

.selection-grid {
  display: grid;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  font-weight: 600;
  margin-bottom: 5px;
  color: #495057;
}

.form-select {
  padding: 8px 12px;
  border: 1px solid #ced4da;
  border-radius: 6px;
  font-size: 14px;
}

.students-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #dee2e6;
}

.bulk-actions {
  display: flex;
  gap: 10px;
}

.classrooms-container {
  padding: 20px;
}

.classroom-group {
  margin-bottom: 30px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  overflow: hidden;
}

.classroom-header {
  background: #f8f9fa;
  padding: 12px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #dee2e6;
}

.classroom-header h5 {
  margin: 0;
  color: #925419;
}

.student-count {
  color: #6c757d;
  font-size: 14px;
}

.target-classrooms {
  display: flex;
  gap: 10px;
  padding: 15px;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.target-classroom {
  background: white;
  border: 2px dashed #28a745;
  border-radius: 6px;
  padding: 10px 15px;
  text-align: center;
  min-width: 120px;
  cursor: pointer;
  transition: all 0.3s;
}

.target-classroom:hover,
.target-classroom.drag-over {
  background: #28a745;
  color: white;
  transform: translateY(-2px);
}

.students-table {
  overflow-x: auto;
}

.table {
  margin: 0;
  font-size: 14px;
}

.student-row {
  transition: background-color 0.2s;
}

.student-row:hover {
  background-color: #f8f9fa;
}

.student-row.selected {
  background-color: #e3f2fd;
}

.student-row.dragging {
  opacity: 0.5;
}

.student-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.student-name {
  font-weight: 600;
}

.student-code {
  font-size: 12px;
  color: #6c757d;
  font-family: monospace;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-active {
  background: #d4edda;
  color: #155724;
}

.status-graduated {
  background: #d1ecf1;
  color: #0c5460;
}

.status-transferred {
  background: #fff3cd;
  color: #856404;
}

.status-repeating {
  background: #f8d7da;
  color: #721c24;
}

.action-buttons {
  display: flex;
  gap: 10px;
  justify-content: center;
  padding: 20px;
  border-top: 1px solid #dee2e6;
  background: #f8f9fa;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary {
  background: #925419;
  color: white;
}

.btn-outline-primary {
  background: transparent;
  color: #925419;
  border: 1px solid #925419;
}

.btn-outline-secondary {
  background: transparent;
  color: #6c757d;
  border: 1px solid #6c757d;
}

.btn-outline-info {
  background: transparent;
  color: #17a2b8;
  border: 1px solid #17a2b8;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.loading-state i {
  font-size: 2rem;
  margin-bottom: 10px;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 15px;
  color: #dee2e6;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h4 {
  margin: 0;
  color: #925419;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.promotion-summary {
  display: grid;
  gap: 15px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f8f9fa;
  border-radius: 6px;
  border-left: 4px solid #925419;
}

.summary-item label {
  font-weight: 600;
  color: #495057;
}

.summary-item span {
  color: #925419;
  font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
  .selection-panels {
    grid-template-columns: 1fr;
  }
  
  .students-header {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }
  
  .bulk-actions {
    justify-content: center;
  }
  
  .target-classrooms {
    flex-wrap: wrap;
  }
  
  .target-classroom {
    min-width: 100px;
    font-size: 12px;
  }
  
  .action-buttons {
    flex-direction: column;
    align-items: center;
  }
  
  .modal-content {
    margin: 20px;
    width: auto;
  }
}

@media (max-width: 480px) {
  .student-promotion-container {
    padding: 15px;
  }
  
  .table {
    font-size: 12px;
  }
  
  .student-info {
    min-width: 120px;
  }
  
  .form-select {
    font-size: 12px;
  }
}

/* Print Styles */
@media print {
  .action-buttons,
  .bulk-actions,
  .target-classrooms,
  .modal-overlay {
    display: none !important;
  }
  
  .students-table {
    font-size: 12px;
  }
  
  .classroom-group {
    break-inside: avoid;
    margin-bottom: 20px;
  }
}

/* Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.classroom-group {
  animation: fadeIn 0.3s ease-out;
}

/* Drag and Drop Visual Feedback */
.student-row[draggable="true"] {
  cursor: move;
}

.student-row[draggable="true"]:hover {
  background-color: #fff3cd;
  border-left: 3px solid #ffc107;
}

.target-classroom.drag-over {
  animation: pulse 0.5s infinite alternate;
}

@keyframes pulse {
  from {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
  }
  to {
    transform: scale(1.05);
    box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
  }
}

</style>
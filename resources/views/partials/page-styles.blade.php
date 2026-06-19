{{-- resources/views/partials/page-styles.blade.php --}}
<style>
   .page-header{color:#925419;font-weight:600;margin-bottom:25px;padding-bottom:10px;border-bottom:3px solid #fbc417}
   .form-card{background:#fff;border-radius:8px;border:1px solid #e0e0e0;box-shadow:0 2px 4px rgba(0,0,0,.05);overflow:hidden}
   .form-card-header{background:#f8f8f8;padding:15px 20px;border-bottom:2px solid #fbc417;margin-bottom:20px}
   .form-card-title{color:#925419;font-weight:600;font-size:16px;margin:0}
   .form-card-body{padding:20px}
   .form-section{margin-bottom:25px}
   .section-title{color:#925419;font-weight:600;font-size:14px;margin-bottom:15px;padding-bottom:8px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center}
   .section-title i{margin-left:8px;color:#fbc417}
   .form-label{color:#925419;font-weight:500;font-size:14px;margin-bottom:6px}
   .form-label .required{color:#dc3545;margin-right:3px}
   .form-control,.form-select{border:1px solid #d0d0d0;border-radius:4px;padding:8px 12px;font-size:14px;transition:.2s}
   .form-control:focus,.form-select:focus{border-color:#fbc417;box-shadow:0 0 0 2px rgba(251,196,23,.1);outline:none}
   .form-control:hover,.form-select:hover{border-color:#b0b0b0}
   input[type="date"]{padding:7px 12px}
   .btn{border-radius:4px;padding:10px 24px;font-weight:500;border:none;font-size:14px;transition:.2s}
   .btn-primary{background:#925419;color:#fff}
   .btn-primary:hover{background:#7a4516;transform:translateY(-1px);box-shadow:0 4px 8px rgba(146,84,25,.2)}
   .btn-secondary{background:#fff;color:#925419;border:1px solid #d0d0d0}
   .btn-secondary:hover{background:#f8f8f8;border-color:#925419}
   .form-footer{background:#f8f8f8;padding:20px;margin:-20px;margin-top:30px;border-top:1px solid #e0e0e0;display:flex;justify-content:space-between;align-items:center}
   .form-text{color:#6c757d;font-size:12px;margin-top:4px}
   .input-group{position:relative}
   .input-group-text{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#925419;font-size:14px}
   .input-with-icon{padding-left:35px}
   .breadcrumb-nav{background:#fbc41715;padding:10px 15px;border-radius:6px;margin-bottom:20px;font-size:14px}
   .breadcrumb-nav a{color:#925419;text-decoration:none}
   .breadcrumb-nav a:hover{text-decoration:underline}
   .filter-card{background:#fff;padding:20px;border-radius:8px;margin-bottom:20px;border:1px solid #e0e0e0;box-shadow:0 2px 4px rgba(0,0,0,.05)}
   .filter-card-header{color:#925419;font-weight:600;margin-bottom:15px;padding-bottom:10px;border-bottom:2px solid #fbc417}
   .table-card{background:#fff;border-radius:8px;overflow:hidden;border:1px solid #e0e0e0;box-shadow:0 2px 4px rgba(0,0,0,.05)}
   .table-card-header{background:#f8f8f8;padding:15px 20px;border-bottom:2px solid #fbc417;display:flex;align-items:center}
   .table-card-title{color:#925419;font-weight:600;font-size:16px;margin:0}
   .table{margin-bottom:0}
   .table thead th{background:#f8f8f8;color:#925419;font-weight:600;font-size:14px;padding:12px;border-bottom:2px solid #fbc417;border-top:none}
   .table tbody td{padding:10px 12px;font-size:14px;vertical-align:middle;border-color:#f0f0f0}
   .table tbody tr{transition:background .2s}
   .table tbody tr:hover{background:#fffbf0}
   .badge-role{background:#fbc41730;color:#925419;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:500}
   .stats-info{background:#fbc41715;border-left:4px solid #fbc417;padding:10px 15px;border-radius:4px;margin-bottom:20px;color:#925419;font-size:14px}
   .pagination{justify-content:center;margin-top:20px}
   .page-link{color:#925419;border:1px solid #e0e0e0;margin:0 2px;border-radius:4px}
   .page-link:hover{background:#fbc417;border-color:#fbc417;color:#925419}
   .page-item.active .page-link{background:#925419;border-color:#925419;color:#fff}
   .table-responsive{overflow-x:auto}
   @media (max-width:768px){.form-footer{flex-direction:column;gap:10px}.btn{width:100%}}
</style>

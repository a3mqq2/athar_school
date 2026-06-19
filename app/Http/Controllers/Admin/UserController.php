<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $roles = \Spatie\Permission\Models\Role::all();

        $query = User::query()
            ->whereHas('roles', fn($q) => $q->whereIn('name', $roles->pluck('name')));

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%'.$request->email.'%');
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%'.$request->phone.'%');
        }

        if ($request->filled('role')) {
            $selected = $request->role;
            $names = is_array($selected) ? $selected : [$selected];
            $query->whereHas('roles', fn($q) => $q->whereIn('name', $names));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('hire_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('hire_date', '<=', $request->to_date);
        }

        if ($request->filled('subject')) {
            $query->where('subject', 'like', '%'.$request->subject.'%');
        }

        $users = $query->paginate(15)->appends($request->all());

        return view('admin.users.index', compact('users','roles'));
    }

    public function create()
    {
        $roles = Role::select('id','name','display_name')->get();
        $permissions = Permission::select('id','name','display_name')->get();

        $rolePermissions = Role::with(['permissions:id,name'])
            ->get()
            ->mapWithKeys(function ($role) {
                return [$role->name => $role->permissions->pluck('name')->toArray()];
            })
            ->toArray();

        return view('admin.users.create', compact('roles','permissions','rolePermissions'));
    }
    
    public function store(Request $request)
    {
        $rolesList = Role::pluck('name')->toArray();
        $permsList = Permission::pluck('name')->toArray();
    
        $validator = \Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'nullable|string|max:20|unique:users,phone',
            'password'       => 'required|string|min:6|confirmed',
            'hire_date'      => 'nullable|date',
    
            'role'           => 'required|array|min:1',
            'role.*'         => ['required', Rule::in($rolesList)],
    
            // حقول الموظف
            'salary'         => 'nullable|numeric|min:0',
            'job_title'      => 'nullable|string|max:255',
    
            // حقول المعلّم
            'subject'        => 'nullable|string|max:255',
            'session_price'  => 'nullable|numeric|min:0',
    
            // الصلاحيات اليدوية
            'permissions'    => 'nullable|array',
            'permissions.*'  => ['required', Rule::in($permsList)],
        ]);
    
        $validator->after(function ($v) use ($request) {
            $selected = collect($request->input('role', []))->map(fn ($r) => strtolower($r));
            $hasTeacher = $selected->contains('teacher');
            $hasAdminOrFinance = $selected->contains('admin') || $selected->contains('finance');
    
            if ($hasTeacher) {
                if (!$request->filled('subject')) {
                    $v->errors()->add('subject', 'حقل التخصص/المادة مطلوب عند اختيار دور المعلّم.');
                }
                if (!$request->filled('session_price')) {
                    $v->errors()->add('session_price', 'حقل سعر الحصة مطلوب عند اختيار دور المعلّم.');
                }
            }
    
            if ($hasAdminOrFinance) {
                if (!$request->filled('salary')) {
                    $v->errors()->add('salary', 'حقل الراتب مطلوب عند اختيار دور الإدارة أو المالية.');
                }
                if (!$request->filled('job_title')) {
                    $v->errors()->add('job_title', 'حقل المسمى الوظيفي مطلوب عند اختيار دور الإدارة أو المالية.');
                }
            }
        });
    
        $data = $validator->validate();
    
        return \DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'code'          => $this->generateUniqueCode(),
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'password'      => \Hash::make($data['password']),
                'hire_date'     => $data['hire_date'] ?? null,
    
                // موظف
                'salary'        => $data['salary'] ?? null,
                'job_title'     => $data['job_title'] ?? null,
    
                // معلّم
                'subject'       => $data['subject'] ?? null,
                'session_price' => $data['session_price'] ?? null,
            ]);
    
            // الأدوار
            $user->syncRoles($data['role']);
    
            // الصلاحيات اليدوية (إضافة مباشرة للمستخدم فوق صلاحيات الأدوار)
            $manualPerms = $request->input('permissions', []);
            if (!empty($manualPerms)) {
                $user->syncPermissions($manualPerms);
            }
    
            return redirect()
                ->route('admin.users.index')
                ->with('success', 'تمت إضافة المستخدم بنجاح');
        });
    }
    
    public function edit(User $user)
    {
        $roles        = Role::select('id','name','display_name')->get();
        $permissions  = Permission::select('id','name','display_name')->get();
    
        // خريطة صلاحيات كل دور لتعبئة تلقائية بالواجهة
        $rolePermissions = Role::with(['permissions:id,name'])
            ->get()
            ->mapWithKeys(fn ($role) => [$role->name => $role->permissions->pluck('name')->toArray()])
            ->toArray();
    
        // الصلاحيات المعينة مباشرة لهذا المستخدم (غير الموروثة من الأدوار)
        $userDirectPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
    
        return view(
            'admin.users.edit',
            compact('user','roles','permissions','rolePermissions','userDirectPermissions')
        );
    }
    
    public function update(Request $request, User $user)
    {
        $rolesList = Role::pluck('name')->toArray();
        $permsList = Permission::pluck('name')->toArray();
    
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'phone'          => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'password'       => 'nullable|string|min:6|confirmed',
            'hire_date'      => 'nullable|date',
    
            'role'           => 'required|array|min:1',
            'role.*'         => ['required', Rule::in($rolesList)],
    
            // موظف
            'salary'         => 'nullable|numeric|min:0',
            'job_title'      => 'nullable|string|max:255',
    
            // معلّم
            'subject'        => 'nullable|string|max:255',
            'session_price'  => 'nullable|numeric|min:0',
    
            // الصلاحيات اليدوية
            'permissions'    => 'nullable|array',
            'permissions.*'  => ['required', Rule::in($permsList)],

            'code'          => 'required|string|max:10|unique:users,code,' . $user->id,
        ]);
    
        $validator->after(function ($v) use ($request) {
            $selected = collect($request->input('role', []))->map(fn ($r) => strtolower($r));
            $hasTeacher = $selected->contains('teacher');
            $hasAdminOrFinance = $selected->contains('admin') || $selected->contains('finance');
    
            if ($hasTeacher) {
                if (!$request->filled('subject')) {
                    $v->errors()->add('subject', 'حقل التخصص/المادة مطلوب عند اختيار دور المعلّم.');
                }
                if (!$request->filled('session_price')) {
                    $v->errors()->add('session_price', 'حقل سعر الحصة مطلوب عند اختيار دور المعلّم.');
                }
            }
    
            if ($hasAdminOrFinance) {
                if (!$request->filled('salary')) {
                    $v->errors()->add('salary', 'حقل الراتب مطلوب عند اختيار دور الإدارة أو المالية.');
                }
                if (!$request->filled('job_title')) {
                    $v->errors()->add('job_title', 'حقل المسمى الوظيفي مطلوب عند اختيار دور الإدارة أو المالية.');
                }
            }
        });
    
        $data = $validator->validate();
    
        DB::transaction(function () use ($data, $request, $user) {
            $user->update([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'hire_date'     => $data['hire_date'] ?? null,
    
                // موظف
                'salary'        => $data['salary'] ?? null,
                'job_title'     => $data['job_title'] ?? null,
    
                // معلّم
                'subject'       => $data['subject'] ?? null,
                'session_price' => $data['session_price'] ?? null,
    
                // كلمة المرور (إن وُجدت)
                'password'      => filled($data['password'] ?? null) ? Hash::make($data['password']) : $user->password,
                'code'          => $data['code'],
            ]);
    
            // أدوار
            $user->syncRoles($data['role']);
    
            // الصلاحيات اليدوية (تسجّل مباشرة على المستخدم بجانب صلاحيات الأدوار)
            $manualPerms = $request->input('permissions', []);
            $user->syncPermissions($manualPerms);
        });
    
        return redirect()->route('admin.users.index')->with('success', 'تم تعديل المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم');
    }


    
    private function generateUniqueCode(): string
    {
        do {
            // رقم مكون من 6 أرقام عشوائية
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (User::where('code', $code)->exists());

        return $code;
    }
}

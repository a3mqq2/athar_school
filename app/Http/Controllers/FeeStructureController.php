<?php
// app/Http/Controllers/FeeStructureController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeStructureController extends Controller
{
    // صفحة الكتالوج (Grid) + فلاتر
    public function index()
    {
        // نجلب المراحل للفلاتر، مصنّفة حسب القسم
        $stages = DB::table('stages')
            ->select('id','name','section')
            ->orderBy('section')->orderBy('name')
            ->get()
            ->groupBy('section'); // local / international

        return view('admin.fees.catalog', [
            'stages' => $stages,
        ]);
    }

    /**
     * بيانات الجدول عبر AJAX مع فلاتر:
     * query params:
     *  - section (local|international|all)
     *  - stage_id (int|null)
     *  - q (string search in grade name)
     *  - only_missing (1|0) => إظهار الصفوف التي لا تملك تسعيرة فقط
     */
    public function data(Request $request)
    {
        $section     = $request->string('section')->toString();
        $stageId     = $request->integer('stage_id') ?: null;
        $q           = trim($request->string('q')->toString() ?? '');
        $onlyMissing = (int) $request->boolean('only_missing', false);

        // نبني قائمة الصفوف + معلومات المرحلة/القسم مع تطبيق الفلاتر
        $gradesQ = DB::table('grades as g')
            ->leftJoin('stages as s', 's.id', '=', 'g.stage_id')
            ->select(
                'g.id as grade_id',
                'g.name as grade_name',
                'g.stage_id',
                's.name as stage_name',
                DB::raw("COALESCE(s.section, 'local') as section_type")
            );

        if ($section && in_array($section, ['local','international'])) {
            $gradesQ->where('s.section', $section);
        }

        if ($stageId) {
            $gradesQ->where('g.stage_id', $stageId);
        }

        if ($q != '') {
            $gradesQ->where('g.name', 'like', $q.'%');
        }

        $grades = $gradesQ->orderBy('s.section')->orderBy('g.stage_id')->orderBy('g.name')->get();

        // نجلب الأسعار الحالية
        $fees = DB::table('fee_structures')
            ->select('grade_id','amount','year_amount')
            ->get()
            ->keyBy('grade_id');

        $rows = [];
        foreach ($grades as $g) {
            $amount = optional($fees->get($g->grade_id))->amount;
            $year_amount = optional($fees->get($g->grade_id))->year_amount;
            if ($onlyMissing && $amount != null) {
                continue; // لو وضعنا only_missing = true نخفي الصفوف التي لديها سعر
            }
            $rows[] = [
                'grade_id'     => (int)$g->grade_id,
                'grade_name'   => $g->grade_name,
                'stage_id'     => $g->stage_id,
                'stage_name'   => $g->stage_name ?? '',
                'section_type' => $g->section_type,     // من stages.section
                'amount'       => $amount != null ? (float)$amount : null,
                'year_amount'  =>  $year_amount != null ? (float)$year_amount : null,
            ];
        }

        return response()->json(
            ['rows' => $rows],
            200,
            ['Content-Type' => 'application/json; charset=utf-8'],
            JSON_UNESCAPED_UNICODE
        );
    }

   
    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'items'                 => ['required','array','min:1'],
            'items.*.grade_id'      => ['required','integer','exists:grades,id'],
            'items.*.stage_id'      => ['nullable','integer','exists:stages,id'],
            'items.*.section_type'  => ['required','in:local,international'],
            'items.*.amount'        => ['nullable','numeric','min:0'],
            'items.*.year_amount'   => ['nullable','numeric','min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $row) {
                $where = [
                    'section_type' => $row['section_type'],
                    'stage_id'     => $row['stage_id'] ?? null,
                    'grade_id'     => $row['grade_id'],
                ];

                if ($row['amount'] === null || $row['amount'] === '') {
                    DB::table('fee_structures')->where($where)->delete();
                } else {
                    DB::table('fee_structures')->updateOrInsert($where, [
                        'amount'     => $row['amount'],
                        'year_amount'=> $row['year_amount'] ?? null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
                }
            }
        });

        return response()->json(['status' => 'ok']);
    }
}

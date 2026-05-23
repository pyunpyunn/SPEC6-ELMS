<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\JsonResponse;

class PositionController extends Controller
{
    public function byDepartment(int $department): JsonResponse
    {
        $positions = Position::where('department_id', $department)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($positions);
    }
}

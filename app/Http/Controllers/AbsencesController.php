<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Teacher;
use App\Models\Lecture;
use App\Models\Holidays;
use Illuminate\Http\Request;
use Carbon\Carbon;
use OpenApi\Annotations as OA;

class AbsencesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/absences",
     *     summary="Get list of all absences",
     *     tags={"Absences"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Absence"))
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Absence::all());
    }

    /**
     * @OA\Post(
     *     path="/api/absences",
     *     summary="Create a new absence",
     *     tags={"Absences"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date", "teacher_id", "lecture_id"},
     *             @OA\Property(property="justified", type="boolean", example=true),
     *             @OA\Property(property="date", type="string", format="date", example="2024-05-01"),
     *             @OA\Property(property="teacher_id", type="integer", example=1),
     *             @OA\Property(property="lecture_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Absence created",
     *         @OA\JsonContent(ref="#/components/schemas/Absence")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'justified'   => 'sometimes|boolean',
            'date'        => 'required|date',
            'teacher_id'  => 'required|exists:teachers,id',
            'lecture_id'  => 'required|exists:lectures,id',
        ]);

        $absence = Absence::create([
            'justified'   => $request->justified,
            'date'        => $request->date,
            'teacher_id'  => $request->teacher_id,
            'lecture_id'  => $request->lecture_id,
        ]);

        return response()->json($absence, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/absences/{id}",
     *     summary="Get a specific absence",
     *     tags={"Absences"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Absence ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Absence found",
     *         @OA\JsonContent(ref="#/components/schemas/Absence")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Absence not found"
     *     )
     * )
     */
    public function show(Absence $absence)
    {
        return response()->json($absence);
    }

    /**
     * @OA\Put(
     *     path="/api/absences/{id}",
     *     summary="Update an existing absence",
     *     tags={"Absences"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Absence ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="justified", type="boolean", example=false),
     *             @OA\Property(property="date", type="string", format="date", example="2024-05-01"),
     *             @OA\Property(property="teacher_id", type="integer", example=1),
     *             @OA\Property(property="lecture_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Absence updated",
     *         @OA\JsonContent(ref="#/components/schemas/Absence")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Absence not found"
     *     )
     * )
     */
    public function update(Absence $absence, Request $request)
    {
        $request->validate([
            'id' => 'required|exists:absences,id',
            'justified'   => 'sometimes|boolean',
            'date'        => 'sometimes|date',
            'teacher_id'  => 'sometimes|exists:teachers,id',
            'lecture_id'  => 'sometimes|exists:lectures,id',
        ]);

        $absence->update($request->all());

        return response()->json($absence);
    }

    /**
     * @OA\Delete(
     *     path="/api/absences/{id}",
     *     summary="Delete an absence",
     *     tags={"Absences"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Absence ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Absence deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Absence deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Absence not found"
     *     )
     * )
     */
    public function destroy(Absence $absence)
    {
        $absence->delete();
        return response()->json(['message' => 'Absence deleted successfully']);
    }
}

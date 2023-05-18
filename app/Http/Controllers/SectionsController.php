<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionsController extends Controller
{
    public function index()
    {
        try {
            return Section::all();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'description' => 'required',
            'project_id' => 'required|exists:projects,id'
        ]);

        $section = Section::create([
            'description' => $validatedData['description'],
            'projects_id' => $validatedData['project_id']
        ]);

        return response()->json(['section' => $section, 'message' => 'Sessão criada com sucesso.'], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
    public function remove(Section $section)
    {
        try {
            $section->delete();
            return response()->json(['message' => 'Sessão removida com sucesso!'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getAllUserSections(Request $request)
    {
        try {
            $projectId = $request->route('project');

            $sections = Section::where('projects_id', $projectId)->get();

            return response()->json(['sections' => $sections], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\Util;
use App\Models\Question;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QuestionController extends Controller
{
    public function get_questions(){
        $questions = Question::with('choices')->get();

        return response()->json($questions, Response::HTTP_OK);
    }
}
